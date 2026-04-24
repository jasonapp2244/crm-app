<?php

namespace Webkul\SMS\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Twilio\Rest\Client;
use Webkul\Contact\Models\Person;
use Webkul\SMS\Events\NewSMSMessage;
use Webkul\SMS\Models\Message;
use Webkul\SMS\Models\TwilioNumber;
use Webkul\SMS\Repositories\MessageRepository;
use Webkul\SMS\Repositories\TwilioNumberRepository;

class TwilioService
{
    protected array $clients = [];

    public function __construct(
        protected MessageRepository $messageRepository,
        protected TwilioNumberRepository $twilioNumberRepository
    ) {}

    protected function getClient(?int $twilioNumberId = null): Client
    {
        $key = $twilioNumberId ?? 'default';

        if (! isset($this->clients[$key])) {
            if ($twilioNumberId) {
                $number = $this->twilioNumberRepository->find($twilioNumberId);

                $sid = $number->twilio_sid ?: config('twilio.sid');
                $token = $number->twilio_token ?: config('twilio.auth_token');
            } else {
                $sid = config('twilio.sid');
                $token = config('twilio.auth_token');
            }

            $this->clients[$key] = new Client($sid, $token);
        }

        return $this->clients[$key];
    }

    protected function getFromNumber(?int $twilioNumberId, string $channel): string
    {
        if ($twilioNumberId) {
            $number = $this->twilioNumberRepository->find($twilioNumberId);
            $phone = $number->phone_number;
        } else {
            $phone = $channel === 'whatsapp'
                ? config('twilio.whatsapp_number')
                : config('twilio.phone_number');
        }

        return $channel === 'whatsapp' ? 'whatsapp:'.$phone : $phone;
    }

    /**
     * Send SMS to one or multiple recipients.
     */
    public function sendSMS(string|array $to, string $body, array $options = []): array
    {
        return $this->sendBulk($to, $body, 'sms', $options);
    }

    /**
     * Send WhatsApp to one or multiple recipients.
     */
    public function sendWhatsApp(string|array $to, string $body, array $options = []): array
    {
        return $this->sendBulk($to, $body, 'whatsapp', $options);
    }

    /**
     * Send to multiple recipients (bulk).
     */
    public function sendBulk(string|array $recipients, string $body, string $channel, array $options = []): array
    {
        if (is_string($recipients)) {
            $recipients = array_map('trim', explode(',', $recipients));
        }

        $recipients = array_filter($recipients);
        $twilioNumberId = $options['twilio_number_id'] ?? null;
        $scheduledAt = $options['scheduled_at'] ?? null;
        $templateId = $options['template_id'] ?? null;

        // If scheduled for the future, store without sending
        if ($scheduledAt && Carbon::parse($scheduledAt)->isFuture()) {
            return $this->scheduleMessages($recipients, $body, $channel, $options);
        }

        $from = $this->getFromNumber($twilioNumberId, $channel);
        $client = $this->getClient($twilioNumberId);

        $results = [];
        $successCount = 0;
        $failCount = 0;

        foreach ($recipients as $to) {
            $to = trim($to);
            $toNumber = $channel === 'whatsapp' ? 'whatsapp:'.$to : $to;

            try {
                $message = $client->messages->create($toNumber, [
                    'from' => $from,
                    'body' => $body,
                ]);

                $record = $this->messageRepository->create([
                    'from' => str_replace('whatsapp:', '', $from),
                    'to' => $to,
                    'body' => $body,
                    'direction' => 'outbound',
                    'status' => $message->status,
                    'channel' => $channel,
                    'twilio_sid' => $message->sid,
                    'twilio_number_id' => $twilioNumberId,
                    'person_id' => $options['person_id'] ?? $this->findPersonByPhone($to),
                    'lead_id' => $options['lead_id'] ?? null,
                    'user_id' => $options['user_id'] ?? null,
                    'template_id' => $templateId,
                ]);

                event(new NewSMSMessage($record));

                $results[] = ['to' => $to, 'success' => true, 'message_id' => $record->id];
                $successCount++;
            } catch (\Exception $e) {
                $this->messageRepository->create([
                    'from' => str_replace('whatsapp:', '', $from),
                    'to' => $to,
                    'body' => $body,
                    'direction' => 'outbound',
                    'status' => 'failed',
                    'channel' => $channel,
                    'error_message' => $e->getMessage(),
                    'twilio_number_id' => $twilioNumberId,
                    'person_id' => $options['person_id'] ?? $this->findPersonByPhone($to),
                    'lead_id' => $options['lead_id'] ?? null,
                    'user_id' => $options['user_id'] ?? null,
                    'template_id' => $templateId,
                ]);

                $results[] = ['to' => $to, 'success' => false, 'error' => $e->getMessage()];
                $failCount++;
            }
        }

        return [
            'success' => $failCount === 0,
            'total' => count($recipients),
            'sent' => $successCount,
            'failed' => $failCount,
            'results' => $results,
        ];
    }

    /**
     * Schedule messages for future delivery.
     */
    protected function scheduleMessages(array $recipients, string $body, string $channel, array $options): array
    {
        $twilioNumberId = $options['twilio_number_id'] ?? null;
        $from = $this->getFromNumber($twilioNumberId, $channel);
        $count = 0;

        foreach ($recipients as $to) {
            $to = trim($to);

            $this->messageRepository->create([
                'from' => str_replace('whatsapp:', '', $from),
                'to' => $to,
                'body' => $body,
                'direction' => 'outbound',
                'status' => 'scheduled',
                'channel' => $channel,
                'twilio_number_id' => $twilioNumberId,
                'person_id' => $options['person_id'] ?? $this->findPersonByPhone($to),
                'lead_id' => $options['lead_id'] ?? null,
                'user_id' => $options['user_id'] ?? null,
                'template_id' => $options['template_id'] ?? null,
                'scheduled_at' => $options['scheduled_at'],
            ]);

            $count++;
        }

        return [
            'success' => true,
            'total' => $count,
            'sent' => 0,
            'failed' => 0,
            'scheduled' => $count,
            'results' => [],
        ];
    }

    /**
     * Process and send all due scheduled messages.
     */
    public function processScheduledMessages(): int
    {
        $messages = Message::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();

        $sent = 0;

        foreach ($messages as $msg) {
            $twilioNumberId = $msg->twilio_number_id;
            $channel = $msg->channel;
            $from = $this->getFromNumber($twilioNumberId, $channel);
            $client = $this->getClient($twilioNumberId);
            $toNumber = $channel === 'whatsapp' ? 'whatsapp:'.$msg->to : $msg->to;

            try {
                $twilioMessage = $client->messages->create($toNumber, [
                    'from' => $from,
                    'body' => $msg->body,
                ]);

                $msg->update([
                    'status' => $twilioMessage->status,
                    'twilio_sid' => $twilioMessage->sid,
                ]);

                event(new NewSMSMessage($msg->fresh()));

                $sent++;
            } catch (\Exception $e) {
                $msg->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    /**
     * Handle inbound message from Twilio webhook.
     */
    public function handleInbound(array $data): void
    {
        $from = $data['From'] ?? '';
        $to = $data['To'] ?? '';
        $body = $data['Body'] ?? '';
        $sid = $data['MessageSid'] ?? null;

        $channel = str_contains($from, 'whatsapp:') ? 'whatsapp' : 'sms';
        $from = str_replace('whatsapp:', '', $from);
        $to = str_replace('whatsapp:', '', $to);

        // Find which Twilio number received this
        $twilioNumber = TwilioNumber::where('phone_number', $to)->first();

        $record = $this->messageRepository->create([
            'from' => $from,
            'to' => $to,
            'body' => $body,
            'direction' => 'inbound',
            'status' => 'received',
            'channel' => $channel,
            'twilio_sid' => $sid,
            'twilio_number_id' => $twilioNumber?->id,
            'person_id' => $this->findPersonByPhone($from),
        ]);

        event(new NewSMSMessage($record));
    }

    /**
     * Find person by phone number.
     */
    public function findPersonByPhone(string $phone): ?int
    {
        $person = Person::where('contact_numbers', 'LIKE', '%'.$phone.'%')->first();

        return $person?->id;
    }

    /**
     * Get conversation history for a person.
     */
    public function getConversation(int $personId): Collection
    {
        return Message::where('person_id', $personId)
            ->with(['user', 'twilioNumber'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get conversation history by phone number.
     */
    public function getConversationByPhone(string $phone): Collection
    {
        return Message::where(function ($query) use ($phone) {
            $query->where('from', $phone)->orWhere('to', $phone);
        })
            ->with(['user', 'twilioNumber', 'person'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get messaging stats.
     */
    public function getStats(): array
    {
        $base = Message::query();

        return [
            'total_sent' => (clone $base)->where('direction', 'outbound')->count(),
            'total_received' => (clone $base)->where('direction', 'inbound')->count(),
            'total_failed' => (clone $base)->where('status', 'failed')->count(),
            'today_sent' => (clone $base)->where('direction', 'outbound')->whereDate('created_at', today())->count(),
            'today_received' => (clone $base)->where('direction', 'inbound')->whereDate('created_at', today())->count(),
        ];
    }
}
