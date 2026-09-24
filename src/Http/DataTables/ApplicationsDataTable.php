<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Http\DataTables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use temetvince\SeatCapitals\Models\CapitalApplication;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;

/**
 * The applications table, shown to applicants (own rows) and reviewers (all rows).
 *
 * Which rows appear is the caller's job through scopes. `forReview()` switches
 * the action column from "withdraw" to "approve / deny".
 *
 * @package temetvince\SeatCapitals\Http\DataTables
 */
class ApplicationsDataTable extends DataTable
{
    private bool $review = false;

    /**
     * Render reviewer actions instead of applicant actions.
     */
    public function forReview(): self
    {
        $this->review = true;

        return $this;
    }

    public function ajax(): JsonResponse
    {
        return datatables()
            ->eloquent($this->applyScopes($this->query()))
            ->editColumn('character.name', function (CapitalApplication $row) {
                return view('web::partials.character', ['character' => $row->character])->render();
            })
            ->editColumn('type.typeName', function (CapitalApplication $row) {
                $type_name = $row->type?->getAttribute('typeName');

                return view('web::partials.type', [
                    'type_id' => $row->type_id,
                    'type_name' => is_string($type_name) ? $type_name : trans('web::seat.unknown'),
                ])->render();
            })
            ->editColumn('status', function (CapitalApplication $row) {
                return view('seat-capitals::partials.status', ['status' => $row->status])->render();
            })
            // Columns outside rawColumns() are HTML-escaped by DataTables itself;
            // escaping here too would show entities such as &#039; to the user.
            ->editColumn('justification', function (CapitalApplication $row) {
                return Str::limit($row->justification, 120);
            })
            ->editColumn('created_at', function (CapitalApplication $row) {
                return view('web::partials.date', ['datetime' => $row->created_at])->render();
            })
            ->editColumn('reviewer.name', function (CapitalApplication $row) {
                $reviewer = $row->reviewer?->getAttribute('name');

                return is_string($reviewer) ? $reviewer : '';
            })
            ->addColumn('action', function (CapitalApplication $row) {
                return view('seat-capitals::partials.actions', [
                    'application' => $row,
                    'review' => $this->review,
                ])->render();
            })
            ->rawColumns(['character.name', 'type.typeName', 'status', 'created_at', 'action'])
            ->toJson();
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('capitals-applications')
            ->columns($this->getColumns())
            ->orderBy(0, 'desc')
            ->postAjax()
            ->parameters([
                'drawCallback' => 'function() { $("[data-toggle=tooltip]").tooltip(); }',
            ]);
    }

    /**
     * @return Builder<CapitalApplication>
     */
    public function query(): Builder
    {
        return CapitalApplication::with(['character', 'type', 'user', 'reviewer']);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getColumns(): array
    {
        return [
            ['data' => 'id', 'title' => '#'],
            ['data' => 'character.name', 'title' => trans('seat-capitals::capitals.column_character')],
            ['data' => 'type.typeName', 'title' => trans('seat-capitals::capitals.column_hull')],
            ['data' => 'status', 'title' => trans('seat-capitals::capitals.column_status')],
            ['data' => 'justification', 'title' => trans('seat-capitals::capitals.column_justification'), 'orderable' => false],
            ['data' => 'created_at', 'title' => trans('seat-capitals::capitals.column_submitted')],
            ['data' => 'reviewer.name', 'title' => trans('seat-capitals::capitals.column_reviewer')],
            ['data' => 'decision_note', 'title' => trans('seat-capitals::capitals.column_note'), 'orderable' => false],
            ['data' => 'action', 'title' => '', 'orderable' => false, 'searchable' => false],
        ];
    }
}
