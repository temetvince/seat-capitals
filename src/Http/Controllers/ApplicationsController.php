<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use temetvince\SeatCapitals\Exceptions\WorkflowException;
use temetvince\SeatCapitals\Http\DataTables\ApplicationsDataTable;
use temetvince\SeatCapitals\Http\DataTables\Scopes\OwnApplicationsScope;
use temetvince\SeatCapitals\Http\Validation\NewApplication;
use temetvince\SeatCapitals\Models\CapitalApplication;
use temetvince\SeatCapitals\Services\ApplicationWorkflow;
use temetvince\SeatCapitals\Services\CapitalCatalog;

/**
 * The applicant's side: submit, follow and withdraw applications.
 *
 * Gated by `capitals.apply` in the route group.
 *
 * @package temetvince\SeatCapitals\Http\Controllers
 */
class ApplicationsController extends Controller
{
    /**
     * The application form and the signed-in user's own applications.
     *
     * @return mixed the rendered page, or the table's JSON for DataTables requests
     */
    public function index(ApplicationsDataTable $dataTable, CapitalCatalog $catalog)
    {
        $user = $this->user();

        return $dataTable
            ->addScope(new OwnApplicationsScope($user->id))
            ->render('seat-capitals::applications.index', [
                'characters' => $user->characters->sortBy('name')->values(),
                'types' => $catalog->types(),
            ]);
    }

    public function store(NewApplication $request, ApplicationWorkflow $workflow): RedirectResponse
    {
        try {
            $workflow->submit(
                $this->user(),
                (int) $request->input('character_id'),
                (int) $request->input('type_id'),
                (string) $request->input('justification')
            );
        } catch (WorkflowException $exception) {
            return redirect()->back()
                ->withInput()
                ->with('error', trans('seat-capitals::capitals.error_' . $exception->reason));
        }

        return redirect()->route('seat-capitals::applications.index')
            ->with('success', trans('seat-capitals::capitals.submitted'));
    }

    public function withdraw(CapitalApplication $application, ApplicationWorkflow $workflow): RedirectResponse
    {
        try {
            $workflow->withdraw($application, $this->user());
        } catch (WorkflowException $exception) {
            return redirect()->back()
                ->with('error', trans('seat-capitals::capitals.error_' . $exception->reason));
        }

        return redirect()->route('seat-capitals::applications.index')
            ->with('success', trans('seat-capitals::capitals.withdrawn'));
    }
}
