<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PemilihDatatable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($query) {
                $data['action'] = $this->actions($query->id, $query->name);
                return view('datatable.actions', compact('data', 'query'))->render();
            })
            ->addIndexColumn()
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    public function actions($id, $name)
    {
        return [
            [
                'title' => 'Hapus',
                'icon' => 'bi bi-trash',
                'route' => route('backend.pemilih.delete', $id),
                'type' => 'delete',
            ],
            [
                'title' => 'Kirim Informasi Login',
                'icon' => 'bi bi-envelope',
                'route' => route('backend.pemilih.send_login_info', $id),
                'type' => 'button',
                'attributes' => [
                    'data-bs-toggle' => 'modal',
                    'data-bs-target' => '#sendLoginInfoModal',
                    'data-id' => $id,
                    'data-name' => $name,
                    'class' => 'btn btn-success btn-sm',
                ],
            ],
        ];
    }

    public function query(User $model): QueryBuilder
    {
        return $model->newQuery()
            ->whereHas('roles', function ($q) {
                $q->where('name', 'user');
            })
            ->orderBy('id', 'desc');
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('pemilih-table')
            ->columns($this->getColumns())
            ->minifiedAjax();
    }

    protected function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('#')->orderable(false)->searchable(false),
            Column::make('name')->title(__('field.voter_name')),
            Column::make('nim')->title(__('field.voter_nim')),
            Column::make('email')->title(__('field.voter_email')),
            Column::make('token')->title(__('field.voter_token')),
            Column::make('action')->title(__('field.action'))->orderable(false),
        ];
    }

    protected function filename(): string
    {
        return 'Pemilih_' . date('YmdHis');
    }
}
