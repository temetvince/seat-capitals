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
use temetvince\SeatCapitals\Http\Validation\Decision;
use temetvince\SeatCapitals\Models\ApplicationStatus;
use temetvince\SeatCapitals\Models\CapitalApplication;
use temetvince\SeatCapitals\Services\ApplicationWorkflow;

/**
 * The reviewer's side: see every application and approve or deny pending ones.
 *
 * Gated by `capitals.review` in the route group.
 *
 * @package temetvince\SeatCapitals\Http\Controllers
 */
class ReviewController extends Controller
{
    /**
     * Every application, with reviewer actions on pending rows.
     *
     * @return mixed the rendered page, or the table's JSON for DataTables requests
     */
    public function index(ApplicationsDataTable $dataTable)
    {
        return $dataTable
            ->forReview()
            ->render('seat-capitals::review.index');
    }

    public function decide(Decision $request, CapitalApplication $application, ApplicationWorkflow $workflow): RedirectResponse
    {
        $decision = ApplicationStatus::from((string) $request->input('decision'));
        $note = $request->input('note');

        try {
            $workflow->decide($application, $this->user(), $decision, is_string($note) ? $note : null);
        } catch (WorkflowException $exception) {
            return redirect()->back()
                ->with('error', trans('seat-capitals::capitals.error_' . $exception->reason));
        }

        return redirect()->route('seat-capitals::review.index')
            ->with('success', trans('seat-capitals::capitals.decided_' . $decision->value));
    }
}
