<?php

namespace App\Http\Controllers\Admin\EvtEvents;

use App\Http\Controllers\Controller;
use Domain\EvtEvents\Models\Sport;
use Domain\EvtEvents\Models\SportAgeGroup;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SportAgeGroupController extends Controller
{
    /**
     * Display a listing of the age groups.
     */
    public function index(): Application|Factory|View|RedirectResponse
    {
        $ageGroups = SportAgeGroup::with('sport')->paginate(25);

        if ($ageGroups->isEmpty()) {
            return redirect()->action([self::class, 'create']);
        }

        return view('web.admin.evt_events.sport_age_groups.index', compact('ageGroups'));
    }

    /**
     * Show the form for creating a new age group.
     */
    public function create(): View
    {
        $sportAgeGroup = new SportAgeGroup;
        $sports = Sport::all();

        return view('web.admin.evt_events.sport_age_groups.create', compact('sportAgeGroup', 'sports'));
    }

    /**
     * Store a newly created age group in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sport_id' => 'required|integer|exists:evt_sports,id',
            'title' => 'required|string|max:255',
            'birthday_start' => 'required|date',
            'birthday_end' => 'required|date',
        ]);

        SportAgeGroup::create($data);

        return redirect()->route('admin.evt-events.sport-age-groups.index')->with('success', 'Sport Age ClassGroup created successfully');
    }

    /**
     * Show the form for editing the specified age group.
     */
    public function edit(SportAgeGroup $sportAgeGroup): View
    {

        $sports = Sport::all();

        return view('web.admin.evt_events.sport_age_groups.edit', compact('sportAgeGroup', 'sports'));
    }

    /**
     * Update the specified age group in storage.
     */
    public function update(Request $request, SportAgeGroup $sportAgeGroup): RedirectResponse
    {
        $data = $request->validate([
            'sport_id' => 'required|integer|exists:evt_sports,id',
            'title' => 'required|string|max:255',
            'birthday_start' => 'required|date',
            'birthday_end' => 'required|date',
        ]);

        $sportAgeGroup->update($data);

        return redirect()->route('admin.evt-events.sport-age-groups.index')->with('success', 'Sport Age ClassGroup updated successfully');
    }

    /**
     * Remove the specified age group from storage.
     */
    public function destroy(SportAgeGroup $ageGroup): RedirectResponse
    {
        $ageGroup->delete();

        return redirect()->route('admin.evt-events.sport-age-groups.index')->with('success', 'Sport Age ClassGroup deleted successfully');
    }
}
