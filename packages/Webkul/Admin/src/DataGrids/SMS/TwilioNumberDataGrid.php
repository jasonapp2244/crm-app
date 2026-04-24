<?php

namespace Webkul\Admin\DataGrids\SMS;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class TwilioNumberDataGrid extends DataGrid
{
    protected $sortColumn = 'created_at';

    protected $sortOrder = 'desc';

    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('twilio_numbers')
            ->select(
                'twilio_numbers.id',
                'twilio_numbers.label',
                'twilio_numbers.phone_number',
                'twilio_numbers.is_whatsapp',
                'twilio_numbers.is_active',
                'twilio_numbers.created_at',
                DB::raw('(SELECT COUNT(*) FROM sms_messages WHERE sms_messages.twilio_number_id = twilio_numbers.id) as messages_count')
            );

        $this->addFilter('id', 'twilio_numbers.id');
        $this->addFilter('label', 'twilio_numbers.label');
        $this->addFilter('is_active', 'twilio_numbers.is_active');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => 'ID',
            'type'       => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'label',
            'label'      => trans('admin::app.sms.numbers.datagrid.label'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'phone_number',
            'label'      => trans('admin::app.sms.numbers.datagrid.phone'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'is_whatsapp',
            'label'      => trans('admin::app.sms.numbers.datagrid.whatsapp'),
            'type'       => 'boolean',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => fn ($row) => $row->is_whatsapp
                ? '<span class="label-active">Yes</span>'
                : '<span class="label-info">No</span>',
        ]);

        $this->addColumn([
            'index'      => 'is_active',
            'label'      => trans('admin::app.sms.numbers.datagrid.status'),
            'type'       => 'boolean',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => fn ($row) => $row->is_active
                ? '<span class="label-active">Active</span>'
                : '<span class="label-canceled">Inactive</span>',
        ]);

        $this->addColumn([
            'index'      => 'messages_count',
            'label'      => trans('admin::app.sms.numbers.datagrid.messages'),
            'type'       => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => trans('admin::app.sms.numbers.datagrid.date'),
            'type'       => 'date_range',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
        ]);
    }

    public function prepareActions(): void
    {
        $this->addAction([
            'index'  => 'edit',
            'icon'   => 'icon-edit',
            'title'  => trans('admin::app.sms.numbers.datagrid.edit'),
            'method' => 'GET',
            'url'    => fn ($row) => route('admin.sms.numbers.edit', $row->id),
        ]);

        $this->addAction([
            'index'  => 'delete',
            'icon'   => 'icon-delete',
            'title'  => trans('admin::app.sms.numbers.datagrid.delete'),
            'method' => 'DELETE',
            'url'    => fn ($row) => route('admin.sms.numbers.delete', $row->id),
        ]);
    }
}
