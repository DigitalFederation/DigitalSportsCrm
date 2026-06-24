<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Discipline;
use Illuminate\Support\Facades\DB;

class DuplicateDisciplineAction
{
    /**
     * Execute the action to duplicate a discipline with all its relationships.
     *
     * @param  Discipline  $discipline  The discipline to duplicate
     * @return Discipline The newly created discipline
     */
    public function execute(Discipline $discipline): Discipline
    {
        DB::beginTransaction();

        try {
            // Create a new discipline with copied attributes
            $newDiscipline = Discipline::create([
                'name' => $discipline->name . ' - COPY',
                'sport_id' => $discipline->sport_id,
                'gender' => $discipline->gender,
                'enrollment_type' => $discipline->enrollment_type,
                'enrollment_type_value' => $discipline->enrollment_type_value,
                'team_composition_requirements' => $discipline->team_composition_requirements,
                'athlete_limit' => $discipline->athlete_limit,
                'distance' => $discipline->distance,
                'style' => $discipline->style,
            ]);

            // Copy attributes with their pivot data
            $attributes = $discipline->attributes()
                ->select('evt_attributes.id')
                ->withPivot('custom_value')
                ->get()
                ->mapWithKeys(function ($attribute) {
                    return [$attribute->id => ['custom_value' => $attribute->pivot->custom_value]];
                })->toArray();
            $newDiscipline->attributes()->sync($attributes);

            // Copy licenses
            $licenses = $discipline->licenses()
                ->select('license.id')
                ->pluck('license.id')
                ->toArray();
            $newDiscipline->licenses()->sync($licenses);

            // Copy sport age groups
            $sportAgeGroups = $discipline->sportAgeGroups()
                ->select('evt_sport_age_groups.id')
                ->pluck('evt_sport_age_groups.id')
                ->toArray();
            $newDiscipline->sportAgeGroups()->sync($sportAgeGroups);

            DB::commit();

            return $newDiscipline;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
