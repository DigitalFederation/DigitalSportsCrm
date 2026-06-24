<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use Domain\EvtEvents\Actions\AssignRefereeFunctionAction;
use Domain\EvtEvents\Actions\SaveChiefJudgeReportAction;
use Domain\EvtEvents\Actions\SaveTechnicalDelegateReportAction;
use Domain\EvtEvents\Actions\SubmitChiefJudgeReportAction;
use Domain\EvtEvents\Actions\SubmitTechnicalDelegateReportAction;
use Domain\EvtEvents\Actions\UpdateRefereePresenceAction;
use Domain\EvtEvents\Actions\UploadReportDocumentAction;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\EventReportDocument;
use Domain\EvtEvents\Models\EventRole;
use Domain\EvtEvents\Models\RefereeFunctionAssignment;
use Domain\EvtEvents\States\ArchiveEventState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TechnicalDelegateController extends Controller
{
    /**
     * Show list of events where current user is Technical Delegate or Chief Judge
     */
    public function index(): View
    {
        return view('web.individual.technical_delegate.index');
    }

    // ── Technical Delegate ──────────────────────────────────────────

    /**
     * Show enrollment data for a specific event (read-only access)
     */
    public function showEnrollments(Event $event): View
    {
        $this->authorizeTechnicalDelegate($event);

        return view('web.individual.technical_delegate.enrollments', compact('event'));
    }

    /**
     * Export enrollment data for Technical Delegate
     */
    public function exportEnrollments(Event $event): RedirectResponse
    {
        $this->authorizeTechnicalDelegate($event);

        return redirect()->back()->with('success', __('events.export_initiated'));
    }

    /**
     * Show the report form for Technical Delegate
     */
    public function tdReport(Event $event): View
    {
        $this->authorizeTechnicalDelegate($event);

        $report = $event->technicalDelegateReport;

        return view('web.individual.technical_delegate.td-report', compact('event', 'report'));
    }

    /**
     * Save the Technical Delegate report (draft)
     */
    public function saveTdReport(
        Event $event,
        Request $request,
        SaveTechnicalDelegateReportAction $saveAction
    ): RedirectResponse {
        $this->authorizeTechnicalDelegate($event);

        $existingReport = $event->technicalDelegateReport;
        if ($existingReport && $existingReport->is_submitted) {
            return redirect()->back()->with('error', __('events.report_already_submitted'));
        }

        $validated = $request->validate([
            'participants_withdrawals' => 'nullable|string|max:65535',
            'incidents_occurrences' => 'nullable|string|max:65535',
            'officials_performance' => 'nullable|string|max:65535',
            'facilities_evaluation' => 'nullable|string|max:65535',
            'safety_first_aid' => 'nullable|string|max:65535',
            'anti_doping_control' => 'nullable|string|max:65535',
            'sports_protests' => 'nullable|string|max:65535',
            'observations_recommendations' => 'nullable|string|max:65535',
        ]);

        try {
            $saveAction->execute($event, Auth::user()->individual, $validated);

            return redirect()->back()->with('success', __('events.report_saved'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Submit the Technical Delegate report (finalize)
     */
    public function submitTdReport(
        Event $event,
        SubmitTechnicalDelegateReportAction $submitAction
    ): RedirectResponse {
        $this->authorizeTechnicalDelegate($event);

        $report = $event->technicalDelegateReport;

        if (! $report) {
            return redirect()->back()->with('error', __('events.report_not_found'));
        }

        if ($report->is_submitted) {
            return redirect()->back()->with('error', __('events.report_already_submitted'));
        }

        try {
            $submitAction->execute($report);

            return redirect()->back()->with('success', __('events.report_submitted'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Upload a document to the TD report
     */
    public function uploadTdDocument(
        Event $event,
        Request $request,
        UploadReportDocumentAction $uploadAction
    ): RedirectResponse {
        $this->authorizeTechnicalDelegate($event);

        $report = $event->technicalDelegateReport;

        if (! $report) {
            return redirect()->back()->with('error', __('events.report_not_found'));
        }

        if ($report->is_submitted) {
            return redirect()->back()->with('error', __('events.cannot_modify_submitted_report'));
        }

        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        try {
            $uploadAction->execute($report, $request->file('document'), Auth::user()->individual);

            return redirect()->back()->with('success', __('events.document_uploaded'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete a document from the TD report
     */
    public function deleteTdDocument(
        Event $event,
        EventReportDocument $document,
        UploadReportDocumentAction $uploadAction
    ): RedirectResponse {
        $this->authorizeTechnicalDelegate($event);

        $report = $event->technicalDelegateReport;

        if (! $report || $document->documentable_id !== $report->id) {
            return redirect()->back()->with('error', __('events.document_not_found'));
        }

        if ($report->is_submitted) {
            return redirect()->back()->with('error', __('events.cannot_modify_submitted_report'));
        }

        try {
            $uploadAction->delete($document);

            return redirect()->back()->with('success', __('events.document_deleted'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Download a document from the TD report
     */
    public function downloadTdDocument(Event $event, EventReportDocument $document): StreamedResponse
    {
        $this->authorizeTechnicalDelegate($event);

        $report = $event->technicalDelegateReport;

        if (! $report || $document->documentable_id !== $report->id) {
            abort(404);
        }

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    // ── Chief Judge ─────────────────────────────────────────────────

    /**
     * Show referee function management for a specific event
     */
    public function showReferees(Event $event): View
    {
        $this->authorizeChiefJudge($event);

        return view('web.individual.technical_delegate.referees', compact('event'));
    }

    /**
     * Export referee functions report
     */
    public function exportReferees(Event $event): RedirectResponse
    {
        $this->authorizeChiefJudge($event);

        return redirect()->back()->with('success', __('events.export_initiated_successfully'));
    }

    /**
     * Assign function to referee
     */
    public function assignFunction(
        Event $event,
        Request $request,
        AssignRefereeFunctionAction $assignFunctionAction
    ): RedirectResponse {
        $this->authorizeChiefJudge($event);

        $request->validate([
            'referee_enrollment_id' => 'required|exists:evt_referees_enrollment,id',
            'referee_function_id' => 'nullable|exists:evt_referee_functions,id',
            'function_text' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        if (empty($request->referee_function_id) && empty($request->function_text)) {
            return redirect()->back()->withErrors(['function' => __('events.select_function_or_enter_text')]);
        }

        try {
            $refereeEnrollment = \Domain\EvtEvents\Models\RefereeEnrollment::findOrFail($request->referee_enrollment_id);

            $data = [
                'referee_function_id' => $request->referee_function_id,
                'function_text' => $request->function_text,
                'notes' => $request->notes,
            ];

            $assignFunctionAction->execute($event, $refereeEnrollment, Auth::user()->individual, $data);

            return redirect()->back()->with('success', __('events.referee_function_assigned'));
        } catch (\Exception $e) {
            Log::error('Error assigning referee function: ' . $e->getMessage());

            return redirect()->back()->with('error', __('events.error_assigning_function'));
        }
    }

    /**
     * Remove function assignment
     */
    public function removeFunction(Event $event, RefereeFunctionAssignment $assignment): RedirectResponse
    {
        $this->authorizeChiefJudge($event);

        if ($assignment->event_id !== $event->id) {
            abort(403, __('events.invalid_assignment_for_event'));
        }

        try {
            DB::beginTransaction();
            $assignment->delete();
            DB::commit();

            return redirect()->back()->with('success', __('events.function_assignment_removed'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error removing referee function assignment: ' . $e->getMessage());

            return redirect()->back()->with('error', __('events.error_removing_assignment'));
        }
    }

    /**
     * Update referee presence status and function assignments
     */
    public function updatePresence(
        Event $event,
        Request $request,
        UpdateRefereePresenceAction $presenceAction
    ): RedirectResponse {
        $this->authorizeChiefJudge($event);

        $request->validate([
            'presence' => 'required|array',
            'presence.*' => 'boolean',
            'competition_days' => 'nullable|array',
            'competition_days.*' => 'nullable|integer|min:0|max:365',
            'number_of_games' => 'nullable|array',
            'number_of_games.*' => 'nullable|integer|min:0|max:999',
            'functions' => 'nullable|array',
            'functions.*' => 'nullable|array',
            'functions.*.*' => 'nullable|exists:evt_referee_functions,id',
        ]);

        try {
            $presenceAction->execute(
                $event,
                Auth::user()->individual,
                $request->presence,
                $request->competition_days ?? [],
                $request->number_of_games ?? [],
                $request->functions ?? []
            );

            return redirect()->back()->with('success', __('events.presence_updated'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show the report form for Chief Judge
     */
    public function cjReport(Event $event): View
    {
        $this->authorizeChiefJudge($event);

        $report = $event->chiefJudgeReport;

        return view('web.individual.technical_delegate.cj-report', compact('event', 'report'));
    }

    /**
     * Save the Chief Judge report (draft)
     */
    public function saveCjReport(
        Event $event,
        Request $request,
        SaveChiefJudgeReportAction $saveAction
    ): RedirectResponse {
        $this->authorizeChiefJudge($event);

        $existingReport = $event->chiefJudgeReport;
        if ($existingReport && $existingReport->is_submitted) {
            return redirect()->back()->with('error', __('events.report_already_submitted'));
        }

        $validated = $request->validate([
            'technical_considerations' => 'nullable|string|max:65535',
        ]);

        try {
            $saveAction->execute($event, Auth::user()->individual, $validated);

            return redirect()->back()->with('success', __('events.report_saved'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Submit the Chief Judge report (finalize)
     */
    public function submitCjReport(
        Event $event,
        SubmitChiefJudgeReportAction $submitAction
    ): RedirectResponse {
        $this->authorizeChiefJudge($event);

        $report = $event->chiefJudgeReport;

        if (! $report) {
            return redirect()->back()->with('error', __('events.report_not_found'));
        }

        if ($report->is_submitted) {
            return redirect()->back()->with('error', __('events.report_already_submitted'));
        }

        try {
            $submitAction->execute($report);

            return redirect()->back()->with('success', __('events.report_submitted'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Upload a document to the CJ report
     */
    public function uploadCjDocument(
        Event $event,
        Request $request,
        UploadReportDocumentAction $uploadAction
    ): RedirectResponse {
        $this->authorizeChiefJudge($event);

        $report = $event->chiefJudgeReport;

        if (! $report) {
            return redirect()->back()->with('error', __('events.report_not_found'));
        }

        if ($report->is_submitted) {
            return redirect()->back()->with('error', __('events.cannot_modify_submitted_report'));
        }

        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        try {
            $uploadAction->execute($report, $request->file('document'), Auth::user()->individual);

            return redirect()->back()->with('success', __('events.document_uploaded'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete a document from the CJ report
     */
    public function deleteCjDocument(
        Event $event,
        EventReportDocument $document,
        UploadReportDocumentAction $uploadAction
    ): RedirectResponse {
        $this->authorizeChiefJudge($event);

        $report = $event->chiefJudgeReport;

        if (! $report || $document->documentable_id !== $report->id) {
            return redirect()->back()->with('error', __('events.document_not_found'));
        }

        if ($report->is_submitted) {
            return redirect()->back()->with('error', __('events.cannot_modify_submitted_report'));
        }

        try {
            $uploadAction->delete($document);

            return redirect()->back()->with('success', __('events.document_deleted'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Download a document from the CJ report
     */
    public function downloadCjDocument(Event $event, EventReportDocument $document): StreamedResponse
    {
        $this->authorizeChiefJudge($event);

        $report = $event->chiefJudgeReport;

        if (! $report || $document->documentable_id !== $report->id) {
            abort(404);
        }

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    // ── Authorization ───────────────────────────────────────────────

    /**
     * Get the authenticated user's individual profile or abort
     */
    protected function getAuthenticatedIndividual(): \Domain\Individuals\Models\Individual
    {
        $individual = Auth::user()->individual;

        if (! $individual) {
            abort(403, __('events.no_individual_profile'));
        }

        return $individual;
    }

    /**
     * Verify user has Technical Delegate role for this event
     */
    protected function authorizeTechnicalDelegate(Event $event): void
    {
        $individual = $this->getAuthenticatedIndividual();

        $hasRole = EventRole::where('event_id', $event->id)
            ->where('individual_id', $individual->id)
            ->where('role', EventRole::ROLE_TECHNICAL_DELEGATE)
            ->exists();

        if (! $hasRole) {
            abort(403, __('events.not_technical_delegate'));
        }

        if ($event->status_class === ArchiveEventState::class) {
            abort(403, __('events.event_archived'));
        }
    }

    /**
     * Verify user has Chief Judge role for this event
     */
    protected function authorizeChiefJudge(Event $event): void
    {
        $individual = $this->getAuthenticatedIndividual();

        $hasRole = EventRole::where('event_id', $event->id)
            ->where('individual_id', $individual->id)
            ->where('role', EventRole::ROLE_CHIEF_JUDGE)
            ->exists();

        if (! $hasRole) {
            abort(403, __('events.not_chief_judge'));
        }
    }
}
