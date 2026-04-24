<?php

namespace Webkul\Admin\Http\Controllers\SMS;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\SMS\Repositories\MessageRepository;
use Webkul\SMS\Repositories\TwilioNumberRepository;
use Webkul\SMS\Services\TwilioService;

class SMSController extends Controller
{
    public function __construct(
        protected MessageRepository $messageRepository,
        protected TwilioNumberRepository $twilioNumberRepository,
        protected TwilioService $twilioService
    ) {}

    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(\Webkul\Admin\DataGrids\SMS\SMSDataGrid::class)->process();
        }

        $stats = $this->twilioService->getStats();
        $activeNumbers = $this->twilioNumberRepository->findWhere(['is_active' => true]);

        return view('admin::sms.index', compact('stats', 'activeNumbers'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'to'               => 'required|string',
            'body'             => 'required|string',
            'channel'          => 'required|in:sms,whatsapp',
            'twilio_number_id' => 'nullable|exists:twilio_numbers,id',
            'scheduled_at'     => 'nullable|date|after:now',
            'template_id'      => 'nullable|exists:sms_templates,id',
        ]);

        $options = [
            'twilio_number_id' => $request->input('twilio_number_id'),
            'person_id'        => $request->input('person_id'),
            'lead_id'          => $request->input('lead_id'),
            'user_id'          => auth()->id(),
            'scheduled_at'     => $request->input('scheduled_at'),
            'template_id'      => $request->input('template_id'),
        ];

        // Support multiple recipients (comma-separated)
        $recipients = $request->input('to');

        if ($request->input('channel') === 'whatsapp') {
            $result = $this->twilioService->sendWhatsApp($recipients, $request->input('body'), $options);
        } else {
            $result = $this->twilioService->sendSMS($recipients, $request->input('body'), $options);
        }

        if ($result['success']) {
            if (! empty($result['scheduled'])) {
                $msg = $result['scheduled'] > 1
                    ? "Successfully scheduled {$result['scheduled']} messages."
                    : trans('admin::app.sms.index.schedule-success');

                return response()->json(['message' => $msg]);
            }

            $msg = $result['total'] > 1
                ? "Successfully sent to {$result['sent']}/{$result['total']} recipients."
                : trans('admin::app.sms.index.send-success');

            return response()->json(['message' => $msg]);
        }

        $msg = $result['total'] > 1
            ? "Sent {$result['sent']}/{$result['total']} messages. {$result['failed']} failed."
            : ($result['results'][0]['error'] ?? trans('admin::app.sms.index.send-failed'));

        if ($result['sent'] > 0) {
            return response()->json(['message' => $msg]);
        }

        return response()->json(['message' => $msg], 422);
    }

    public function conversation(int $personId): View
    {
        $person = \Webkul\Contact\Models\Person::findOrFail($personId);
        $messages = $this->twilioService->getConversation($personId);
        $activeNumbers = $this->twilioNumberRepository->findWhere(['is_active' => true]);

        return view('admin::sms.conversation', compact('person', 'messages', 'activeNumbers'));
    }

    public function conversationPoll(Request $request, int $personId): JsonResponse
    {
        $afterId = $request->input('after_id', 0);

        $messages = \Webkul\SMS\Models\Message::where('person_id', $personId)
            ->where('id', '>', $afterId)
            ->with(['twilioNumber'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($msg) => [
                'id'                  => $msg->id,
                'from'                => $msg->from,
                'to'                  => $msg->to,
                'body'                => $msg->body,
                'direction'           => $msg->direction,
                'status'              => $msg->status,
                'channel'             => $msg->channel,
                'created_at'          => $msg->created_at?->format('M d, h:i A'),
                'error_message'       => $msg->error_message,
                'twilio_number_label' => $msg->twilioNumber?->label,
            ]);

        return response()->json(['data' => $messages]);
    }

    public function view(int $id): JsonResponse
    {
        $message = $this->messageRepository->with(['person', 'lead', 'user', 'twilioNumber'])->findOrFail($id);

        return response()->json(['data' => $message]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->messageRepository->delete($id);

        return response()->json([
            'message' => trans('admin::app.sms.index.delete-success'),
        ]);
    }

    public function inboundWebhook(Request $request): JsonResponse
    {
        $this->twilioService->handleInbound($request->all());

        return response()->json(['status' => 'ok']);
    }

    public function stats(): JsonResponse
    {
        return response()->json($this->twilioService->getStats());
    }
}
