<?php

namespace App\Http\Controllers\Admin\EvtEvents;

use App\Enums\EvtDisciplineEnrollmentTypeEnum;
use App\Enums\EvtDisciplineGenderEnum;
use App\Http\Controllers\Controller;
use App\Rules\EvtEvents\ValidTeamCompositionRule;
use Domain\EvtEvents\Actions\DuplicateDisciplineAction;
use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Sport;
use Domain\EvtEvents\Models\SportAgeGroup;
use Domain\Licenses\Models\License;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DisciplineController extends Controller
{
    public function index(): View
    {
        $disciplines = QueryBuilder::for(Discipline::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_name'),
                AllowedFilter::scope('filter_sport'),
                AllowedFilter::scope('filter_enrollment_type'),
            ])
            ->allowedSorts(['name', 'enrollment_type', 'sport'])
            ->with(['sport', 'sportAgeGroups', 'attributes'])
            ->latest()
            ->paginate(50)
            ->appends(request()->query());

        $sports = Sport::select('id', 'name')->orderBy('name')->get();
        $enrollmentTypes = collect(config('enums.enrollment_types'))
            ->map(fn ($type) => ['id' => $type, 'name' => $type]);

        return view('web.admin.evt_events.disciplines.index', compact('disciplines', 'sports', 'enrollmentTypes'));
    }

    /**
     * Show the form for creating a new discipline.
     */
    public function create()
    {
        $discipline = new Discipline;
        $genders = EvtDisciplineGenderEnum::cases();
        $enrollment_types = EvtDisciplineEnrollmentTypeEnum::forForm();
        $sports = Sport::all();
        $licenses = License::all();
        $attributes = Attribute::where('enrollment_type', 'ATHLETE')->get();

        $ageGroups = SportAgeGroup::all();

        return view('web.admin.evt_events.disciplines.create', compact(
            'genders',
            'enrollment_types',
            'discipline',
            'licenses',
            'sports',
            'attributes',
            'ageGroups'
        ));
    }

    /**
     * Store a newly created discipline in storage.
     *
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required',
            'sport_id' => 'required|integer|exists:evt_sports,id',
            'enrollment_type' => ['required', Rule::in(EvtDisciplineEnrollmentTypeEnum::values())],
            'enrollment_type_value' => 'nullable|string',
            'interval_date_birth_start' => 'nullable|date',
            'interval_date_birth_end' => 'nullable|date',
            'team_composition_requirements' => [
                Rule::when(
                    fn () => $request->enrollment_type === EvtDisciplineEnrollmentTypeEnum::relay->value,
                    ['required', new ValidTeamCompositionRule($request->enrollment_type)]
                ),
            ],
            'athlete_limit' => 'nullable|integer',
            'sport_age_groups' => 'nullable|array',
            'sport_age_groups.*' => 'exists:evt_sport_age_groups,id',
            'discipline_attrs' => 'nullable|array',
            'discipline_attrs.*' => 'exists:evt_attributes,id',
            'distance' => 'nullable|string',
            'style' => 'nullable|string',
        ]);

        $discipline = Discipline::create($data);
        // Save the discipline to the competition
        // $competition->disciplines()->attach($discipline->id);

        if ($request->input('licenses', [])) {
            $discipline->licenses()->sync($request->input('licenses', []));
        }

        // Update attributes relationship
        if ($request->has('discipline_attrs')) {
            $discipline->attributes()->sync($request->input('discipline_attrs'));
        }

        if ($request->has('sport_age_groups')) {
            $discipline->sportAgeGroups()->sync($request->input('sport_age_groups'));
        }

        return redirect()->route('admin.evt-events.disciplines.index')->with('success', 'Discipline created successfully');
    }

    /**
     * Display the specified discipline.
     *
     * @return View
     */
    public function show(Discipline $discipline)
    {
        return view('web.admin.evt_events.disciplines.show', compact('discipline'));
    }

    /**
     * Show the form for editing the specified discipline.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Competition $competition, Discipline $discipline)
    {
        $genders = EvtDisciplineGenderEnum::cases();
        $enrollment_types = EvtDisciplineEnrollmentTypeEnum::forForm();
        $sports = Sport::all();
        $licenses = License::all();
        $attributes = Attribute::where('enrollment_type', 'ATHLETE')->get();
        $ageGroups = SportAgeGroup::all();

        return view(
            'web.admin.evt_events.disciplines.edit',
            compact(
                'discipline',
                'competition',
                'genders',
                'sports',
                'licenses',
                'enrollment_types',
                'attributes',
                'ageGroups'
            )
        );
    }

    /**
     * Update the specified discipline in storage.
     *
     * @return RedirectResponse
     */
    public function update(Request $request, Discipline $discipline)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required',
            'sport_id' => 'required|integer|exists:evt_sports,id',
            'enrollment_type' => ['required', Rule::in(EvtDisciplineEnrollmentTypeEnum::values())],
            'enrollment_type_value' => 'nullable|string',
            'team_composition_requirements' => [
                Rule::when(
                    fn () => $request->enrollment_type === EvtDisciplineEnrollmentTypeEnum::relay->value,
                    ['required', new ValidTeamCompositionRule($request->enrollment_type)]
                ),
            ],
            'athlete_limit' => 'nullable|integer',
            'sport_age_groups' => 'nullable|array',
            'sport_age_groups.*' => 'exists:evt_sport_age_groups,id',
            'discipline_attrs' => 'nullable|array',
            'discipline_attrs.*' => 'exists:evt_attributes,id',
            'distance' => 'nullable|string',
            'style' => 'nullable|string',
        ]);

        $discipline->update($data);

        // Update licenses relationship
        if ($request->has('licenses')) {
            $discipline->licenses()->sync($request->input('licenses'));
        } else {
            // If licenses are not present in request, detach all
            $discipline->licenses()->detach();
        }

        // Update attributes relationship
        if ($request->has('discipline_attrs')) {
            $discipline->attributes()->sync($request->input('discipline_attrs'));
        }

        if ($request->has('sport_age_groups')) {
            $discipline->sportAgeGroups()->sync($request->input('sport_age_groups'));
        }

        return redirect()->route('admin.evt-events.disciplines.index')->with('success', 'Discipline updated successfully');
    }

    /**
     * Remove the specified discipline from storage.
     *
     * @return RedirectResponse
     */
    public function destroy(Competition $competition, Discipline $discipline)
    {
        // $discipline->competitions()->detach();
        // $discipline->attributes()->delete();
        $discipline->delete();

        return redirect()->route('admin.evt-events.disciplines.index', $competition->id)->with('success', 'Discipline deleted successfully');
    }

    /**
     * Duplicate the specified discipline with all its relationships.
     */
    public function duplicate(Discipline $discipline, DuplicateDisciplineAction $action): RedirectResponse
    {
        try {
            $newDiscipline = $action->execute($discipline);

            return redirect()
                ->route('admin.evt-events.disciplines.index')
                ->with('success', "Discipline '{$discipline->name}' has been duplicated successfully.");
        } catch (\Exception $e) {
            Log::error('Failed to duplicate discipline: ' . $e->getMessage());

            return redirect()
                ->route('admin.evt-events.disciplines.index')
                ->with('error', 'Failed to duplicate discipline. Please try again.');
        }
    }
}
