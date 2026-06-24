<?php

namespace App\Http\Controllers;

use App\Reports\AthleteEnrollmentReport;
use App\Reports\DopingCompetitionReport;
use App\Services\ReportGeneratorService;
use Carbon\Carbon;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\EventPin;
use Illuminate\Http\Request;

class AntiDopingController extends Controller
{
    protected $reportGeneratorService;

    public function __construct(ReportGeneratorService $reportGeneratorService)
    {
        $this->reportGeneratorService = $reportGeneratorService;
    }

    public function index()
    {
        $currentYear = Carbon::now()->year;

        // Fetch competitions of the current year
        $competitions = Competition::with('venueCountry', 'antiDopingRecords', 'antiDopingRecord', 'event')
            ->whereYear('start_date', $currentYear)
            ->get();

        // Show list of events of the current year
        return view('web.public.doping.index', compact('competitions'));
    }

    public function enterPin()
    {
        return view('web.public.doping.enter_pin');
    }

    public function verifyPin(Request $request)
    {
        $pin = $request->input('pin');

        // Check if the PIN exists in the database
        if (EventPin::where('pin', $pin)->exists()) {

            // Increment the usage count and update the last used date
            $eventPin = EventPin::where('pin', $pin)->first();
            $eventPin->increment('usage_count');
            $eventPin->last_used_at = now();
            $eventPin->save();

            // Store the PIN in the session and redirect to events list.
            session(['event_pin' => $pin]);

            return redirect()->route('public.anti-doping.index');
        }

        // Redirect back with an error if the PIN is wrong.
        return back()->withErrors(['pin' => 'The PIN is incorrect.']);
    }

    public function downloadAthleteList(Request $request)
    {
        $eventId = $request->input('eventId');

        $report = new AthleteEnrollmentReport;
        $filters = ['event_id' => $eventId];
        $fileName = "athlete_enrollments_event_{$eventId}";

        return $this->reportGeneratorService->generateAndDownload($report, $filters, $fileName);
    }

    public function downloadCompetitionDopingList(Request $request)
    {
        // Validate the year or use the current year as default
        $currentYear = $request->input('year', Carbon::now()->year);

        $report = new DopingCompetitionReport;
        $filters = ['year' => $currentYear];
        $fileName = "doping_competition_{$currentYear}";

        return $this->reportGeneratorService->generateAndDownload($report, $filters, $fileName);
    }
}
