<?php

namespace App\Http\Controllers\Admin\EvtEvents;

use App\Exports\EventReportExport;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\ActiveRefereeEnrollmentState;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EventReportsController extends Controller
{
    /**
     * Show event reports and referee function assignments for admin.
     */
    public function show(Event $event): View
    {
        $event->load([
            'technicalDelegate.individual',
            'chiefJudge.individual',
            'competitionDirector.individual',
            'technicalDelegateReport.submittedBy',
            'technicalDelegateReport.documents',
            'chiefJudgeReport.submittedBy',
            'chiefJudgeReport.documents',
        ]);

        $refereeEnrollments = $this->getRefereeEnrollments($event);

        $refereePresentCount = $refereeEnrollments->filter(
            fn ($e) => $e->refereeFunctionAssignments->contains('is_present', true)
        )->count();

        $refereeEvaluatedCount = $refereeEnrollments->whereNotNull('evaluation')->count();

        return view('web.admin.evt_events.events.reports', compact(
            'event',
            'refereeEnrollments',
            'refereePresentCount',
            'refereeEvaluatedCount',
        ));
    }

    public function exportExcel(Event $event): BinaryFileResponse|RedirectResponse
    {
        try {
            $filename = 'event_report_' . $event->id . '_' . now()->format('Y-m-d_His') . '.xlsx';

            return Excel::download(new EventReportExport($event), $filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('events.export_failed'));
        }
    }

    public function exportPdf(Event $event): \Illuminate\Http\Response|RedirectResponse
    {
        try {
            $refereeEnrollments = $this->getRefereeEnrollments($event);

            $pdf = Pdf::loadView('pdf.evt-events.event-report', [
                'event' => $event,
                'refereeEnrollments' => $refereeEnrollments,
                'generatedAt' => now(),
            ])->setPaper('a4', 'landscape');

            $filename = 'event_report_' . $event->id . '_' . now()->format('Y-m-d_His') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('events.export_failed'));
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, \Domain\EvtEvents\Models\RefereeEnrollment>
     */
    private function getRefereeEnrollments(Event $event): \Illuminate\Support\Collection
    {
        return $event->refereeEnrollments()
            ->join('individual', 'evt_referees_enrollment.individual_id', '=', 'individual.id')
            ->with([
                'individual',
                'refereeFunctionAssignments' => function ($query) use ($event) {
                    $query->where('event_id', $event->id)->with('refereeFunction');
                },
            ])
            ->where('status_class', ActiveRefereeEnrollmentState::class)
            ->orderBy('individual.name')
            ->orderBy('individual.surname')
            ->select('evt_referees_enrollment.*')
            ->get();
    }
}
