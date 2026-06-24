<?php

namespace App\Http\Controllers\Admin\EvtEvents;

use App\Http\Controllers\Controller;
use App\Models\Sport as AppSport;
use Domain\EvtEvents\Models\Sport;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SportController extends Controller
{
    /**
     * Display a listing of the sports.
     */
    public function index(): Application|Factory|View|RedirectResponse
    {
        $sports = Sport::orderBy('name')->paginate(25);

        if ($sports->isEmpty()) {
            return redirect()->action([self::class, 'create']);
        }

        return view('web.admin.evt_events.sport.index', compact('sports'));
    }

    /**
     * Show the form for creating a new sport.
     */
    public function create(): View
    {
        $sport = new Sport;

        return view('web.admin.evt_events.sport.create', compact('sport'));
    }

    /**
     * Store a newly created sport in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sport_type' => 'required|in:individual,team',
        ]);

        Sport::create($data);
        AppSport::create($data);

        return redirect()->route('admin.evt-events.sport.index')
            ->with('success', __('sports.sport_created'));
    }

    /**
     * Show the form for editing the specified sport.
     */
    public function edit(Sport $sport): View
    {
        return view('web.admin.evt_events.sport.edit', compact('sport'));
    }

    /**
     * Update the specified sport in storage.
     */
    public function update(Request $request, Sport $sport): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sport_type' => 'required|in:individual,team',
        ]);

        $oldName = $sport->name;
        $sport->update($data);

        $appSport = AppSport::where('name', $oldName)->first();
        if ($appSport) {
            $appSport->update($data);
        }

        return redirect()->route('admin.evt-events.sport.index')
            ->with('success', __('sports.sport_updated'));
    }

    /**
     * Remove the specified sport from storage.
     */
    public function destroy(Sport $sport): RedirectResponse
    {
        $appSport = AppSport::where('name', $sport->name)->first();

        if ($appSport && $this->sportHasAssociatedData($appSport)) {
            return redirect()->route('admin.evt-events.sport.index')
                ->with('error', __('sports.cannot_delete_sport_in_use'));
        }

        $sport->delete();

        if ($appSport) {
            $appSport->delete();
        }

        return redirect()->route('admin.evt-events.sport.index')
            ->with('success', __('sports.sport_deleted'));
    }

    private function sportHasAssociatedData(AppSport $appSport): bool
    {
        return $appSport->licenses()->exists()
            || $appSport->entityAthletes()->exists()
            || $appSport->disciplines()->exists();
    }
}
