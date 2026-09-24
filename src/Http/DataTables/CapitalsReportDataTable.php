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
use temetvince\SeatCapitals\Models\CapitalHull;
use temetvince\SeatCapitals\Services\CapitalFleetQuery;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Services\DataTable;

/**
 * The capital ship report: every capital hull in the viewer's scope.
 *
 * Rows come from `CapitalFleetQuery`; the caller narrows characters through
 * scopes and systems through `filterSystems()`. The browser sends the chosen
 * systems and the "include alts" flag with every ajax request (see `html()`).
 *
 * @package temetvince\SeatCapitals\Http\DataTables
 */
class CapitalsReportDataTable extends DataTable
{
    /**
     * @var array<int, int>
     */
    private array $system_ids = [];

    public function __construct(private readonly CapitalFleetQuery $fleet)
    {
    }

    /**
     * Only show hulls located in these systems. An empty list shows every system.
     *
     * @param  array<int, int>  $system_ids
     */
    public function filterSystems(array $system_ids): self
    {
        $this->system_ids = $system_ids;

        return $this;
    }

    public function ajax(): JsonResponse
    {
        return datatables()
            ->eloquent($this->applyScopes($this->query()))
            ->editColumn('character.name', function (CapitalHull $row) {
                return view('web::partials.character', ['character' => $row->character])->render();
            })
            ->addColumn('main', function (CapitalHull $row) {
                $owner = $row->character?->getRelationValue('user');
                $main = $owner?->getRelationValue('main_character');

                return view('web::partials.character', ['character' => $main])->render();
            })
            ->editColumn('type_name', function (CapitalHull $row) {
                return view('web::partials.type', [
                    'type_id' => $row->type_id,
                    'type_name' => $row->type_name,
                ])->render();
            })
            ->editColumn('name', function (CapitalHull $row) {
                return e($row->name ?? '');
            })
            ->editColumn('is_singleton', function (CapitalHull $row) {
                return trans($row->is_singleton
                    ? 'seat-capitals::capitals.assembled'
                    : 'seat-capitals::capitals.packaged');
            })
            ->editColumn('system_name', function (CapitalHull $row) {
                if ($row->system_name === null) {
                    return trans('web::seat.unknown');
                }

                return view('web::partials.system', [
                    'system' => $row->system_name,
                    'security' => $row->system_security,
                ])->render();
            })
            ->filterColumn('type_name', function (Builder $query, string $keyword) {
                $query->where('invTypes.typeName', 'like', "%{$keyword}%");
            })
            ->filterColumn('group_name', function (Builder $query, string $keyword) {
                $query->where('invGroups.groupName', 'like', "%{$keyword}%");
            })
            ->filterColumn('system_name', function (Builder $query, string $keyword) {
                $query->where('solar_systems.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('name', function (Builder $query, string $keyword) {
                $query->where('character_assets.name', 'like', "%{$keyword}%");
            })
            ->orderColumn('type_name', 'invTypes.typeName $1')
            ->orderColumn('group_name', 'invGroups.groupName $1')
            ->orderColumn('system_name', 'solar_systems.name $1')
            ->orderColumn('name', 'character_assets.name $1')
            ->rawColumns(['character.name', 'main', 'type_name', 'system_name'])
            ->toJson();
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('capitals-report')
            ->columns($this->getColumns())
            ->orderBy(1, 'asc')
            ->postAjax([
                'data' => 'function(d) { '
                    . 'd.systems = $("#capitals-systems").val() || []; '
                    . 'd.include_alts = $("#capitals-include-alts").is(":checked") ? 1 : 0; '
                    . '}',
            ])
            ->parameters([
                'drawCallback' => 'function() { $("[data-toggle=tooltip]").tooltip(); }',
            ]);
    }

    /**
     * @return Builder<CapitalHull>
     */
    public function query(): Builder
    {
        // `CharacterInfo::user()` resolves its model at runtime, so the nested
        // relation is named inside a closure rather than a dotted path.
        return $this->fleet->build($this->system_ids)
            ->with([
                'character',
                'character.user' => function ($relation) {
                    $relation->with('main_character');
                },
            ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getColumns(): array
    {
        return [
            ['data' => 'main', 'title' => trans('seat-capitals::capitals.column_main'), 'orderable' => false, 'searchable' => false],
            ['data' => 'character.name', 'title' => trans('seat-capitals::capitals.column_character')],
            ['data' => 'type_name', 'title' => trans('seat-capitals::capitals.column_hull')],
            ['data' => 'group_name', 'title' => trans('seat-capitals::capitals.column_class')],
            ['data' => 'name', 'title' => trans('seat-capitals::capitals.column_ship_name')],
            ['data' => 'is_singleton', 'title' => trans('seat-capitals::capitals.column_state'), 'searchable' => false],
            ['data' => 'system_name', 'title' => trans('seat-capitals::capitals.column_system')],
        ];
    }
}
