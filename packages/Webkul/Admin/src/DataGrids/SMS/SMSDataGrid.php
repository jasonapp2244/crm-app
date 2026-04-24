<?php

namespace Webkul\Admin\DataGrids\SMS;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Webkul\DataGrid\DataGrid;

class SMSDataGrid extends DataGrid
{
    protected $sortColumn = 'created_at';

    protected $sortOrder = 'desc';

    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('sms_messages')
            ->select(
                'sms_messages.id',
                'sms_messages.from',
                'sms_messages.to',
                'sms_messages.body',
                'sms_messages.direction',
                'sms_messages.status',
                'sms_messages.channel',
                'sms_messages.created_at',
                'sms_messages.person_id',
                'persons.name as person_name',
                'users.name as user_name'
            )
            ->leftJoin('persons', 'sms_messages.person_id', '=', 'persons.id')
            ->leftJoin('users', 'sms_messages.user_id', '=', 'users.id');

        $this->addFilter('id', 'sms_messages.id');
        $this->addFilter('direction', 'sms_messages.direction');
        $this->addFilter('status', 'sms_messages.status');
        $this->addFilter('channel', 'sms_messages.channel');
        $this->addFilter('created_at', 'sms_messages.created_at');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('admin::app.sms.index.datagrid.id'),
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'direction',
            'label' => trans('admin::app.sms.index.datagrid.direction'),
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
            'closure' => fn ($row) => $row->direction === 'inbound'
                ? '<span class="label-active">Inbound</span>'
                : '<span class="label-info">Outbound</span>',
        ]);

        $this->addColumn([
            'index' => 'channel',
            'label' => trans('admin::app.sms.index.datagrid.channel'),
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
            'closure' => fn ($row) => ucfirst($row->channel),
        ]);

        $this->addColumn([
            'index' => 'from',
            'label' => trans('admin::app.sms.index.datagrid.from'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'to',
            'label' => trans('admin::app.sms.index.datagrid.to'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'body',
            'label' => trans('admin::app.sms.index.datagrid.message'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable' => false,
            'closure' => fn ($row) => Str::limit($row->body, 80),
        ]);

        $this->addColumn([
            'index' => 'person_name',
            'label' => trans('admin::app.sms.index.datagrid.contact'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable' => true,
            'closure' => function ($row) {
                if ($row->person_name && $row->person_id) {
                    return '<a href="'.route('admin.sms.conversation', $row->person_id).'" class="text-brandColor hover:underline">'
                        .e($row->person_name).'</a>';
                }

                return $row->person_name ?? '-';
            },
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => trans('admin::app.sms.index.datagrid.status'),
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                $colors = [
                    'sent' => 'label-active',
                    'delivered' => 'label-active',
                    'received' => 'label-active',
                    'queued' => 'label-warning',
                    'scheduled' => 'label-warning',
                    'failed' => 'label-canceled',
                ];

                $class = $colors[$row->status] ?? 'label-info';

                return '<span class="'.$class.'">'.ucfirst($row->status).'</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('admin::app.sms.index.datagrid.date'),
            'type' => 'date_range',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
        ]);
    }

    public function prepareActions(): void
    {
        $this->addAction([
            'index' => 'view',
            'icon' => 'icon-eye',
            'title' => trans('admin::app.sms.index.datagrid.view'),
            'method' => 'GET',
            'url' => fn ($row) => route('admin.sms.view', $row->id),
        ]);

        $this->addAction([
            'index' => 'delete',
            'icon' => 'icon-delete',
            'title' => trans('admin::app.sms.index.datagrid.delete'),
            'method' => 'DELETE',
            'url' => fn ($row) => route('admin.sms.delete', $row->id),
        ]);
    }
}
