<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtEventEnrollmentTypeEnum;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\ArchiveEventState;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GetIndividualEventsAction
{
    public function execute($eventId = null, ?string $eventCategory = null, ?Request $request = null)
    {
        $individual = auth()->user()->individuals()->first();
        $professionalRoleIds = ProfessionalRole::whereHas('individuals', function (Builder $query) use ($individual) {
            return $query->where('individual.id', $individual->id);
        })->pluck('id');

        $query = Event::with(['competition', 'competition.sport', 'enrollments'])
            ->where(function (Builder $query) use ($individual, $professionalRoleIds) {
                // Events available for individual enrollment
                $query->where(function (Builder $subQuery) use ($individual, $professionalRoleIds) {
                    $subQuery->whereIn('enrollment_type', [
                        EvtEventEnrollmentTypeEnum::only_individuals->name,
                        EvtEventEnrollmentTypeEnum::only_federations_and_individuals->name,
                        EvtEventEnrollmentTypeEnum::all->name,
                    ])
                        ->where(function (Builder $geoQuery) use ($individual) {
                            $federation = $individual?->federations()->first();
                            $country = $federation?->country;

                            // Geographic filtering: match zones/districts OR no restrictions
                            return $geoQuery->whereHas('geoZones', function (Builder $query) use ($country) {
                                return $query->where('geo_zones.id', $country->geo_zone_id ?? 0);
                            })->orWhereHas('districts', function (Builder $query) use ($country) {
                                return $query->where('districts.id', $country->district_id ?? 0);
                            })->orWhere(function (Builder $noGeoQuery) {
                                // Include events with no geographic restrictions
                                $noGeoQuery->whereDoesntHave('geoZones')->whereDoesntHave('districts');
                            });
                        })
                        ->where(function (Builder $roleQuery) use ($professionalRoleIds) {
                            return $roleQuery->whereDoesntHave('professionalRoles') // Include events with no professional roles
                                ->orWhere(function (Builder $subRoleQuery) use ($professionalRoleIds) {
                                    return $subRoleQuery->whereHas('professionalRoles', function (Builder $query) use ($professionalRoleIds) {
                                        return $query->whereIn('professional_roles.id', $professionalRoleIds);
                                    });
                                });
                        });
                })
                // OR events where individual has management roles
                    ->orWhereHas('eventRoles', function (Builder $roleQuery) use ($individual) {
                        return $roleQuery->where('individual_id', $individual->id);
                    });
            })
            ->where('end_date', '>=', now())
            ->where('status_class', '!=', ArchiveEventState::class)
            ->where('is_visible', true);

        // Filter by event category if provided
        if ($eventCategory) {
            $query->where('event_category', $eventCategory);
        }

        // Apply request filters if provided
        if ($request) {
            if ($request->filled('date_range')) {
                if ($request->input('date_range') === 'upcoming') {
                    $query->where(function ($q) {
                        $q->whereDate('start_date', '>=', now())
                            ->orWhereNull('start_date');
                    });
                } elseif ($request->input('date_range') === 'past') {
                    $query->whereDate('end_date', '<', now());
                }
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }
        }

        if (! is_null($eventId)) {
            // Check if the individual can access this specific event
            return $query->where('id', $eventId)->exists();
        }

        return $query->orderByDesc('start_date')->paginate();
    }
}
