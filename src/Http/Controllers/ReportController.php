<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use temetvince\SeatCapitals\Http\DataTables\CapitalsReportDataTable;
use temetvince\SeatCapitals\Http\DataTables\Scopes\ReportCharacterScope;
use temetvince\SeatCapitals\Models\ReportScope;
use temetvince\SeatCapitals\Services\AltResolver;
use temetvince\SeatCapitals\Settings\HomeSystems;

/**
 * The capital ship report.
 *
 * Gated by `character.capitals` in the route group. The page pre-fills the
 * system filter with the configured home systems; the table request then
 * carries whatever the viewer selected, plus the chosen character scope.
 * The unrestricted scope is honoured only for viewers holding
 * `capitals.report_all`; anyone else silently gets the filtered scope.
 *
 * @package temetvince\SeatCapitals\Http\Controllers
 */
class ReportController extends Controller
{
    public const REPORT_ALL_ABILITY = 'capitals.report_all';

    /**
     * @return mixed the rendered page, or the table's JSON for DataTables requests
     */
    public function index(
        Request $request,
        CapitalsReportDataTable $dataTable,
        HomeSystems $home_systems,
        AltResolver $alts,
    ) {
        $can_report_all = Gate::allows(self::REPORT_ALL_ABILITY);
        $scope = ReportScope::fromInput($request->input('scope'));

        if ($scope->isUnrestricted() && ! $can_report_all) {
            $scope = ReportScope::Filtered;
        }

        return $dataTable
            ->filterSystems($this->requestedSystems($request))
            ->addScope(new ReportCharacterScope($scope, $alts))
            ->render('seat-capitals::report.index', [
                'home_systems' => $home_systems->systems(),
                'can_report_all' => $can_report_all,
                'default_scope' => ReportScope::Alts,
            ]);
    }

    /**
     * The system ids the table request carries, as integers, without duplicates.
     *
     * @return array<int, int>
     */
    private function requestedSystems(Request $request): array
    {
        $systems = $request->input('systems', []);

        if (! is_array($systems)) {
            return [];
        }

        return array_values(array_unique(array_map(
            'intval',
            array_filter($systems, 'is_numeric')
        )));
    }
}
