<?php

namespace App\Http\Controllers\Entity\EvtEvents;

use App\Enums\EvtEventEnrollmentTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Sport;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Actions\EntityAllowedToSeeAction;
use Domain\EvtEvents\Actions\RetrieveFederationIndividualEnrollmentsAction;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\ArchiveEventState;
use Domain\EvtEvents\States\PreparationEventState;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class EventsController extends Controller
{
    /**
     * Display a listing of organization events.
     */
    public function index(Request $request): View
    {
        $events = $this->getEventsQuery($request, 'organization')->paginate();

        return view('web.entity.evt_event.events.index', compact('events'));
    }

    /**
     * Display a listing of competition events.
     */
    public function competitionsIndex(Request $request): View
    {
        $sports = Sport::all();

        $query = $this->getEventsQuery($request, 'competition');

        // Apply sport filter for competitions
        if ($request->filled('sport_id')) {
            $query->whereHas('competition', function ($q) use ($request) {
                $q->where('sport_id', $request->input('sport_id'));
            });
        }

        $competitions = $query->paginate();

        return view('web.entity.evt_event.competitions.index', compact('competitions', 'sports'));
    }

    /**
     * Build base query for events with common filters.
     */
    private function getEventsQuery(Request $request, string $eventCategory): Builder
    {
        $entityId = auth()->user()->getEntityId();
        $excludedStates = [
            ArchiveEventState::class,
        ];

        $query = Event::with([
            'sport',
            'organizer.organizable',
            'competition',
            'competition.sport',
        ])
            ->where('event_category', $eventCategory)
            ->where(function ($query) use ($entityId) {
                $query->where('is_visible', true)
                    ->orWhere(function ($q) use ($entityId) {
                        $q->where('status_class', PreparationEventState::class)
                            ->whereHas('organizer', function ($subQuery) use ($entityId) {
                                $subQuery->where('organizable_id', $entityId)
                                    ->where('organizable_type', Entity::class);
                            });
                    });
            })
            ->whereNotIn('status_class', $excludedStates);

        // Apply date range filter
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

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by end date and enrollment type
        $query->where(function (Builder $query) {
            $query->whereDate('end_date', '>=', now()->format('Y-m-d'))
                ->orWhereNull('end_date');
        })
            ->whereIn('enrollment_type', [
                EvtEventEnrollmentTypeEnum::only_entities->name,
                EvtEventEnrollmentTypeEnum::only_federations_and_entities->name,
                EvtEventEnrollmentTypeEnum::all->name,
            ])
            ->orderByDesc('start_date');

        return $query;
    }

    /**
     * Display the specified event.
     *
     * @return \Illuminate\View\View
     */
    public function show(
        Event $event,
        EntityAllowedToSeeAction $allowedToSeeAction,
        RetrieveFederationIndividualEnrollmentsAction $retrieveEnrollmentsAction
    ): View {
        $currentEntity = auth()->user()->entities()->first();

        $currentEntityId = $currentEntity->id;

        $isEntity = $currentEntity instanceof Entity;

        if (! $allowedToSeeAction->execute($event)) {
            abort(403);
        }

        // Add hasOwnAthleteEnrollments check
        $hasOwnAthleteEnrollments = $event->athleteEnrollments()
            ->when($isEntity, function ($query) use ($currentEntityId) {
                return $query->where('entity_id', $currentEntityId);
            })
            ->exists();

        $event->load([
            'competitions.sport',
            'competitions.technicalDelegates',
            'competitions.venueCountry',
            'organizer.organizable',
            'pricing',
        ]);

        $attachmentsCacheKey = "event_attachments_{$event->id}";
        $attachments = Cache::remember($attachmentsCacheKey, now()->addMinutes(10), function () use ($event) {
            return Media::where('model_id', $event->id)
                ->where('collection_name', 'event-general-attachments')
                ->get();
        });

        // Initialize empty collections
        $referees = collect();
        $disciplines = collect();
        $federationIndividualEnrollments = collect();
        $competition = null;

        if ($event->isSportEvent()) {
            // Get all referees from all competitions in the event
            $referees = $event->competitions->map(function ($competition) {
                return $competition->referees;
            })->flatten();

            // Get all disciplines from all competitions in the event
            $disciplines = $event->competitions->map(function ($competition) {
                return $competition->disciplines;
            })->flatten();

            // Get the first competition associated with the event
            $competition = $event->competitions->first();
        }

        $federationIndividualEnrollments = collect(); // Default to an empty collection
        // Fetch individual enrollments only if needed
        if ($event->isOrganizationEvent()) {
            $federationIndividualEnrollments = $retrieveEnrollmentsAction->execute($event, $currentEntityId);
        }

        // Detect if the federation is the Organizer for the event
        $isOrganizer = $event->organizer()->where('organizable_id', $currentEntityId)->exists();

        $predefinedHeroImageUrl = null;
        if ($event->isSportEvent() && $event->sport) {
            $predefinedHeroImageUrl = $event->sport->getPredefinedImageUrl();
        }

        return view('web.entity.evt_event.events.show', compact(
            'event',
            'attachments',
            'referees',
            'disciplines',
            'competition',
            'federationIndividualEnrollments',
            'isOrganizer',
            'predefinedHeroImageUrl',
            'isEntity',
            'hasOwnAthleteEnrollments'
        ));
    }

    public function athletesOverview(Event $event): View
    {
        // Eager load required relationships
        $event->load([
            'competitions.disciplines',
            'athleteEnrollments' => function ($query) {
                $query->with([
                    'individual:id,name,surname,member_code,gender',
                    'discipline:id,name',
                ])->orderBy('created_at', 'desc');
            },
            'organizer.organizable',
        ]);

        $currentEntity = auth()->user()->entities()->first();

        return view('web.entity.evt_event.events.overview.athletes', [
            'event' => $event,
            'isEntity' => true,
            'entity' => $currentEntity,
        ]);
    }
}
