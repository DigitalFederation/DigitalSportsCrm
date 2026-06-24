<?php

namespace App\Http\Controllers\Admin\EvtEvents;

use App\Exports\TechnicalOfficialAssignmentsExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TechnicalOfficialAssignmentsController extends Controller
{
    public function index(): View
    {
        return view('web.admin.evt_events.technical_official_assignments.index');
    }

    public function export(): BinaryFileResponse|RedirectResponse
    {
        try {
            $filename = 'technical_official_assignments_' . now()->format('Y-m-d_His') . '.xlsx';

            return Excel::download(new TechnicalOfficialAssignmentsExport, $filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('events.export_failed'));
        }
    }
}
