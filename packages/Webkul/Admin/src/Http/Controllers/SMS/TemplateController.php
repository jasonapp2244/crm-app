<?php

namespace Webkul\Admin\Http\Controllers\SMS;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\SMS\Repositories\TemplateRepository;

class TemplateController extends Controller
{
    public function __construct(
        protected TemplateRepository $templateRepository
    ) {}

    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(\Webkul\Admin\DataGrids\SMS\TemplateDataGrid::class)->process();
        }

        return view('admin::sms.templates.index');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'body'    => 'required|string',
            'channel' => 'required|in:sms,whatsapp,both',
        ]);

        $this->templateRepository->create($request->only([
            'name', 'body', 'channel',
        ]));

        return response()->json([
            'message' => trans('admin::app.sms.templates.create-success'),
        ]);
    }

    public function edit(int $id): JsonResponse
    {
        $template = $this->templateRepository->findOrFail($id);

        return response()->json(['data' => $template]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'body'      => 'required|string',
            'channel'   => 'required|in:sms,whatsapp,both',
            'is_active' => 'sometimes|boolean',
        ]);

        $this->templateRepository->update($request->only([
            'name', 'body', 'channel', 'is_active',
        ]), $id);

        return response()->json([
            'message' => trans('admin::app.sms.templates.update-success'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->templateRepository->delete($id);

        return response()->json([
            'message' => trans('admin::app.sms.templates.delete-success'),
        ]);
    }

    public function active(Request $request): JsonResponse
    {
        $channel = $request->input('channel');

        $query = \Webkul\SMS\Models\Template::active();

        if ($channel && $channel !== 'both') {
            $query->where(function ($q) use ($channel) {
                $q->where('channel', $channel)->orWhere('channel', 'both');
            });
        }

        return response()->json(['data' => $query->get()]);
    }
}
