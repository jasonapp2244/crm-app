<?php

namespace Webkul\Admin\Http\Controllers\SMS;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\DataGrids\SMS\TwilioNumberDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\SMS\Repositories\TwilioNumberRepository;

class TwilioNumberController extends Controller
{
    public function __construct(
        protected TwilioNumberRepository $twilioNumberRepository
    ) {}

    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(TwilioNumberDataGrid::class)->process();
        }

        return view('admin::sms.numbers.index');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'twilio_sid' => 'nullable|string|max:255',
            'twilio_token' => 'nullable|string|max:255',
            'is_whatsapp' => 'sometimes|boolean',
        ]);

        $this->twilioNumberRepository->create($request->only([
            'label', 'phone_number', 'twilio_sid', 'twilio_token', 'is_whatsapp',
        ]));

        return response()->json([
            'message' => trans('admin::app.sms.numbers.create-success'),
        ]);
    }

    public function edit(int $id): JsonResponse
    {
        $number = $this->twilioNumberRepository->findOrFail($id);

        return response()->json(['data' => $number]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'twilio_sid' => 'nullable|string|max:255',
            'twilio_token' => 'nullable|string|max:255',
            'is_whatsapp' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        $this->twilioNumberRepository->update($request->only([
            'label', 'phone_number', 'twilio_sid', 'twilio_token', 'is_whatsapp', 'is_active',
        ]), $id);

        return response()->json([
            'message' => trans('admin::app.sms.numbers.update-success'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->twilioNumberRepository->delete($id);

        return response()->json([
            'message' => trans('admin::app.sms.numbers.delete-success'),
        ]);
    }

    public function activeNumbers(): JsonResponse
    {
        $numbers = $this->twilioNumberRepository->findWhere(['is_active' => true]);

        return response()->json(['data' => $numbers]);
    }
}
