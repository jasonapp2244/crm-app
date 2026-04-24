<?php

namespace Webkul\Admin\DataGrids\SMS;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Webkul\DataGrid\DataGrid;

class TemplateDataGrid extends DataGrid
{
    protected $sortColumn = 'created_at';

    protected $sortOrder = 'desc';

    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('sms_templates')
            ->select(
                'sms_templates.id',
                'sms_templates.name',
                'sms_templates.body',
                'sms_templates.channel',
                'sms_templates.is_active',
                'sms_templates.created_at'
            );

        $this->addFilter('id', 'sms_templates.id');
        $this->addFilter('name', 'sms_templates.name');
        $this->addFilter('channel', 'sms_templates.channel');
        $this->addFilter('is_active', 'sms_templates.is_active');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('admin::app.sms.templates.datagrid.id'),
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => trans('admin::app.sms.templates.datagrid.name'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'body',
            'label' => trans('admin::app.sms.templates.datagrid.body'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable' => false,
            'closure' => fn ($row) => Str::limit($row->body, 80),
        ]);

        $this->addColumn([
            'index' => 'channel',
            'label' => trans('admin::app.sms.templates.datagrid.channel'),
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
            'closure' => fn ($row) => ucfirst($row->channel),
        ]);

        $this->addColumn([
            'index' => 'is_active',
            'label' => trans('admin::app.sms.templates.datagrid.status'),
            'type' => 'boolean',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
            'closure' => fn ($row) => $row->is_active
                ? '<span class="label-active">Active</span>'
                : '<span class="label-canceled">Inactive</span>',
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('admin::app.sms.templates.datagrid.date'),
            'type' => 'date_range',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
        ]);
    }

    public function prepareActions(): void
    {
        $this->addAction([
            'index' => 'edit',
            'icon' => 'icon-edit',
            'title' => trans('admin::app.sms.templates.datagrid.edit'),
            'method' => 'GET',
            'url' => fn ($row) => route('admin.sms.templates.edit', $row->id),
        ]);

        $this->addAction([
            'index' => 'delete',
            'icon' => 'icon-delete',
            'title' => trans('admin::app.sms.templates.datagrid.delete'),
            'method' => 'DELETE',
            'url' => fn ($row) => route('admin.sms.templates.delete', $row->id),
        ]);
    }
}
