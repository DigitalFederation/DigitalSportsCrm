<?php

namespace App\Http\Controllers\Federation\EvtEvents;

use App\Exports\EventMasterExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventStoreRequest;
use App\Http\Requests\EventUpdateRequest;
use App\Models\Country;
use App\Traits\StreamsMediaFromStorage;
use Domain\Certifications\Models\Certification;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Actions\AssignEventRolesAction;
use Domain\EvtEvents\Actions\AttributeOrganizerToEventAction;
use Domain\EvtEvents\Actions\CreateEventAction;
use Domain\EvtEvents\Actions\FederationAllowedToSeeAction;
use Domain\EvtEvents\Actions\RetrieveFederationIndividualEnrollmentsAction;
use Domain\EvtEvents\Models\AntiDoping;
use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\CompetitionType;
use Domain\EvtEvents\Models\DisciplineTemplate;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\OrganizerDetail;
use Domain\EvtEvents\Models\TechnicalDelegate;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\EvtEvents\States\ArchiveEventState;
use Domain\EvtEvents\States\CanceledEventState;
use Domain\EvtEvents\States\PreparationEventState;
use Domain\Federations\Models\Federation;
use Domain\Geographic\Models\District;
use Domain\Geographic\Models\Zone;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class EventsController extends Controller
{
    use StreamsMediaFromStorage;

    public function index(): View
    {
        $isDefaultFederation = $this->isDefaultFederation();

        return view('web.federation.evt_event.events.index', compact('isDefaultFederation'));
    }

    public function show(
        Event $event,
        FederationAllowedToSeeAction $allowedToSeeAction,
        RetrieveFederationIndividualEnrollmentsAction $retrieveEnrollmentsAction
    ): View {
        $currentFederationId = Auth::user()->federations()->first()->id;
        $isDefaultFederation = $this->isDefaultFederation();

        // Default federation can see all events; others need permission check
        if (! $isDefaultFederation && ! $allowedToSeeAction->execute($event)) {
            abort(403);
        }

        $hasOwnAthleteEnrollments = $event->athleteEnrollments()
            ->where('federation_id', $currentFederationId)
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
                ->orderBy('name', 'ASC')
                ->get();
        });

        $referees = collect();
        $disciplines = collect();
        $federationIndividualEnrollments = collect();
        $competition = null;

        if ($event->isSportEvent()) {
            $referees = $event->competitions->map(function ($competition) {
                return $competition->referees;
            })->flatten();

            $disciplines = $event->competitions->map(function ($competition) {
                return $competition->disciplines;
            })->flatten();

            $competition = $event->competitions->first();
        }

        if ($event->isOrganizationEvent()) {
            $federationIndividualEnrollments = $retrieveEnrollmentsAction->execute($event, $currentFederationId);
        }

        $isOrganizer = $event->organizer()->where('organizable_id', $currentFederationId)->exists();

        $predefinedHeroImageUrl = null;
        if ($event->isSportEvent() && $event->sport) {
            $predefinedHeroImageUrl = $event->sport->getPredefinedImageUrl();
        }
        $isEntity = false;

        return view('web.federation.evt_event.events.show', compact(
            'event',
            'attachments',
            'referees',
            'disciplines',
            'competition',
            'federationIndividualEnrollments',
            'isOrganizer',
            'predefinedHeroImageUrl',
            'isEntity',
            'hasOwnAthleteEnrollments',
            'isDefaultFederation'
        ));
    }

    public function create($category = 'organization'): View
    {
        $event = new Event;

        $federations_list = Federation::query()->pluck('member_code', 'id');
        $entities_list = Entity::query()->orderBy('name')->pluck('name', 'id');
        $sports = \Domain\EvtEvents\Models\Sport::query()->pluck('name', 'id');
        $country_options = Country::query()->pluck('name', 'id');
        $district_options = District::query()->orderBy('name')->pluck('name', 'id');
        $zone_options = Zone::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id');
        $discipline_templates = DisciplineTemplate::all();
        $professional_roles = ProfessionalRole::query()->pluck('name', 'id');
        $attributes = Attribute::all()->groupBy('enrollment_type');
        $anti_doping = new AntiDoping;

        $licenses = License::select('id', 'name')->get()->pluck('name', 'id');
        $certifications = Certification::select('id', 'name')->get()->pluck('name', 'id');

        $referee_attributes = $attributes->get('REFEREE', collect());
        $staff_attributes = $attributes->get('STAFF', collect());
        $coach_attributes = $attributes->get('COACH', collect());
        $official_attributes = $attributes->get('OFFICIAL', collect());
        $member_attributes = $attributes->filter(function ($items, $type) {
            return $type !== 'ATHLETE';
        })->flatten();

        return view('web.federation.evt_event.events.create', compact(
            'event',
            'federations_list',
            'entities_list',
            'country_options',
            'district_options',
            'zone_options',
            'sports',
            'discipline_templates',
            'professional_roles',
            'member_attributes',
            'staff_attributes',
            'referee_attributes',
            'coach_attributes',
            'official_attributes',
            'anti_doping',
            'category',
            'licenses',
            'certifications'
        ));
    }

    public function store(
        EventStoreRequest $request,
        CreateEventAction $createEventAction,
        AttributeOrganizerToEventAction $attributeOrganizerAction
    ): RedirectResponse {
        $validatedData = $request->validated();

        DB::beginTransaction();
        try {
            $validatedData['status_class'] = $this->getStatusClassFromString($validatedData);
            $validatedData['is_visible'] = array_key_exists('is_visible', $validatedData) ? 1 : 0;
            $validatedData['allow_coach_enrollment'] = $validatedData['allow_coach_enrollment'] ?? false;
            $validatedData['allow_referee_enrollment'] = $validatedData['allow_referee_enrollment'] ?? false;
            $validatedData['allow_official_enrollment'] = $validatedData['allow_official_enrollment'] ?? true;
            $validatedData['allow_individual_enrollment'] = $validatedData['allow_individual_enrollment'] ?? false;
            $validatedData['public_athlete_list'] = $validatedData['public_athlete_list'] ?? false;
            $validatedData['public_coach_list'] = $validatedData['public_coach_list'] ?? false;
            $validatedData['public_referee_list'] = $validatedData['public_referee_list'] ?? false;

            $event = $createEventAction->execute($validatedData);

            if ($validatedData['event_category'] === 'competition') {
                $competitionData = $validatedData['competition'];
                $competitionData['required_athlete_licenses'] = ! empty($competitionData['required_athlete_licenses']) ? $competitionData['required_athlete_licenses'] : null;
                $competitionData['required_coach_certifications'] = ! empty($competitionData['required_coach_certifications']) ? $competitionData['required_coach_certifications'] : null;
                $competitionData['required_referee_certifications'] = ! empty($competitionData['required_referee_certifications']) ? $competitionData['required_referee_certifications'] : null;
                $competitionData['required_athlete_documents'] = ! empty($competitionData['required_athlete_documents']) ? $competitionData['required_athlete_documents'] : null;
                $competitionData['required_coach_documents'] = ! empty($competitionData['required_coach_documents']) ? $competitionData['required_coach_documents'] : null;
                $competitionData['required_referee_documents'] = ! empty($competitionData['required_referee_documents']) ? $competitionData['required_referee_documents'] : null;
                $competitionData['required_official_documents'] = ! empty($competitionData['required_official_documents']) ? $competitionData['required_official_documents'] : null;
                $competitionData['requires_athlete_adel'] = $competitionData['requires_athlete_adel'] ?? false;
                $competitionData['requires_coach_adel'] = $competitionData['requires_coach_adel'] ?? false;
                $competitionData['requires_referee_adel'] = $competitionData['requires_referee_adel'] ?? false;
                $competitionData['requires_official_adel'] = $competitionData['requires_official_adel'] ?? false;
                $competitionData['requires_local_federation_affiliation'] = $competitionData['requires_local_federation_affiliation'] ?? false;
                $competitionData['requires_athlete_entity_sport_registration'] = $competitionData['requires_athlete_entity_sport_registration'] ?? true;
                $competitionData['requires_coach_entity_sport_registration'] = $competitionData['requires_coach_entity_sport_registration'] ?? true;

                $competition = new Competition($competitionData);
                $event->competition()->save($competition);

                if (isset($competitionData['required_coach_certifications'])) {
                    $competition->requiredCoachCertifications()->sync($competitionData['required_coach_certifications']);
                }
                if (isset($competitionData['required_referee_certifications'])) {
                    $competition->requiredRefereeCertifications()->sync($competitionData['required_referee_certifications']);
                }

                if (! empty($validatedData['technical_delegate']['name'])) {
                    $technicalDelegateData = $validatedData['technical_delegate'];
                    $technicalDelegateData['competition_id'] = $event->competition->id;
                    TechnicalDelegate::updateOrCreate(
                        ['competition_id' => $event->competition->id],
                        $technicalDelegateData
                    );
                }
            }

            if (! empty($validatedData['organizer_details'])) {
                $organizerDetails = new OrganizerDetail($validatedData['organizer_details']);
                $organizerDetails->event_id = $event->id;
                $event->organizerDetails()->save($organizerDetails);
            }

            if ($validatedData['event_category'] === 'competition' && ! empty($validatedData['anti_doping']) && $event->competition) {
                $antiDopingRecord = new AntiDoping($validatedData['anti_doping']);
                $event->competition->antiDopingRecord()->save($antiDopingRecord);
            }

            if (isset($validatedData['selected_referee_attributes'])) {
                $event->refereeAttributes()->sync($validatedData['selected_referee_attributes']);
            }
            if (isset($validatedData['selected_staff_attributes'])) {
                $event->staffAttributes()->sync($validatedData['selected_staff_attributes']);
            }
            if (isset($validatedData['selected_coach_attributes'])) {
                $event->coachAttributes()->sync($validatedData['selected_coach_attributes']);
            }
            if (isset($validatedData['selected_official_attributes'])) {
                $event->officialAttributes()->sync($validatedData['selected_official_attributes']);
            }
            if (isset($validatedData['selected_attributes'])) {
                $event->attributes()->sync($validatedData['selected_attributes']);
            }
            if (isset($validatedData['selected_attribute_groups'])) {
                $event->attributeGroups()->sync($validatedData['selected_attribute_groups']);
            }
            if (isset($validatedData['organizer_id'])) {
                $attributeOrganizerAction->execute($event->id, $validatedData['organizer_id']);
            }
            if (isset($validatedData['professional_roles'])) {
                $event->professionalRoles()->sync($validatedData['professional_roles']);
            }
            if (isset($validatedData['selected_countries'])) {
                $event->countries()->sync($validatedData['selected_countries']);
            }
            if (isset($validatedData['selected_zones'])) {
                $event->zones()->sync($validatedData['selected_zones']);
            }
            if (isset($validatedData['selected_districts'])) {
                $event->districts()->sync($validatedData['selected_districts']);
            }

            $roles = [
                'technical_delegate_id' => $request->input('technical_delegate_id') ?: null,
                'chief_judge_id' => $request->input('chief_judge_id') ?: null,
                'competition_director_id' => $request->input('competition_director_id') ?: null,
            ];
            $assignRolesAction = app(AssignEventRolesAction::class);
            $assignRolesAction->execute($event, $roles);

            if ($request->hasFile('poster')) {
                $event->addMediaFromRequest('poster')->toMediaCollection('poster');
            }

            DB::commit();

            return redirect()->route('federation.evt-events.events.show', $event->id)->with('success', __('events.event_created_successfully'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            if ($validatedData['event_category'] === 'competition') {
                return redirect()->route('federation.evt-events.events.create', 'competition')->with('error', __('events.error_creating_competition'));
            }

            return redirect()->route('federation.evt-events.events.create', 'organizational')->with('error', __('events.error_creating_event'));
        }
    }

    public function edit(Event $event): View
    {
        // Default federation with manage-events permission gets full edit
        if ($this->canManageEvents()) {
            return $this->fullEdit($event);
        }

        // Otherwise, organizer-only role edit
        $this->authorizeOrganizerAccess($event);

        $event->load(['technicalDelegate.individual', 'chiefJudge.individual', 'competitionDirector.individual']);

        return view('web.federation.evt_event.events.edit', compact('event'));
    }

    public function update(
        Request $request,
        Event $event,
        AttributeOrganizerToEventAction $attributeOrganizerAction
    ): RedirectResponse {
        // Default federation with manage-events permission gets full update
        if ($this->canManageEvents()) {
            return $this->fullUpdate($request, $event, $attributeOrganizerAction);
        }

        // Otherwise, organizer-only role update
        $this->authorizeOrganizerAccess($event);

        $assignRolesAction = app(AssignEventRolesAction::class);

        $validated = $request->validate([
            'technical_delegate_id' => 'nullable|exists:individual,id',
            'chief_judge_id' => 'nullable|exists:individual,id',
            'competition_director_id' => 'nullable|exists:individual,id',
        ]);

        try {
            $assignRolesAction->execute($event, $validated);

            return redirect()
                ->route('federation.evt-events.events.show', $event)
                ->with('success', __('events.officials_updated_successfully'));
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('events.error_updating_event'));
        }
    }

    public function destroy(Event $event): RedirectResponse
    {
        try {
            $event->delete();
        } catch (QueryException $e) {
            $event->status_class = CanceledEventState::class;
            $event->save();
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return redirect()->route('federation.evt-events.events.index')->with('error', __('events.error_deleting_event'));
        }

        return redirect()->route('federation.evt-events.events.index')->with('success', __('events.event_canceled_successfully'));
    }

    public function masterexport()
    {
        $cacheKey = 'event_master_export';
        $cachedFile = Cache::get($cacheKey);
        if ($cachedFile) {
            return response()->download($cachedFile);
        }

        return \Maatwebsite\Excel\Facades\Excel::download(new EventMasterExport, 'event-data.xlsx');
    }

    public function download(Request $request)
    {
        if (empty($request->id)) {
            return back()->with('error', __('events.no_file_selected'));
        }
        $mediaItem = Media::find($request->id);

        if (! $mediaItem) {
            return back()->with('error', __('events.file_not_found'));
        }

        return $this->streamMediaDownload($mediaItem, $mediaItem->file_name);
    }

    public function athletesOverview(Event $event): View
    {
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

        $federationId = Auth::user()->federations()->first()->id;

        return view('web.federation.evt_event.events.overview.athletes', [
            'event' => $event,
            'isEntity' => false,
            'federationId' => $federationId,
        ]);
    }

    protected function fullEdit(Event $event): View
    {
        $event->load([
            'competition' => function ($query) {
                $query->with([
                    'antiDopingRecord',
                    'types',
                    'technicalDelegates',
                    'requiredRefereeCertifications',
                    'requiredCoachCertifications',
                ]);
            },
            'organizer',
            'countries',
            'geoZones',
            'zones',
            'districts',
            'organizerDetails',
            'technicalDelegate',
            'chiefJudge',
            'competitionDirector',
            'coachAttributes',
            'officialAttributes',
        ]);

        $federations_list = Federation::query()->pluck('member_code', 'id');
        $entities_list = Entity::query()->orderBy('name')->pluck('name', 'id');
        $sports = \Domain\EvtEvents\Models\Sport::query()->pluck('name', 'id');
        $country_options = Country::query()->pluck('name', 'id');
        $district_options = District::query()->orderBy('name')->pluck('name', 'id');
        $zone_options = Zone::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id');
        $organizerDetails = $event->organizerDetails;
        $category = $event->event_category;
        $discipline_templates = DisciplineTemplate::all();
        $professional_roles = ProfessionalRole::query()->pluck('name', 'id');

        $anti_doping = $event->competition?->antiDopingRecord ?? new AntiDoping;

        $attributes = Attribute::all()->groupBy('enrollment_type');
        $referee_attributes = $attributes->get('REFEREE', collect());
        $staff_attributes = $attributes->get('STAFF', collect());
        $coach_attributes = $attributes->get('COACH', collect());
        $official_attributes = $attributes->get('OFFICIAL', collect());
        $member_attributes = $attributes->filter(function ($items, $type) {
            return $type !== 'ATHLETE';
        })->flatten();

        $technical_delegate = $event->competition?->technicalDelegates()->first() ?? new TechnicalDelegate;

        $licenses = License::select('id', 'name')->get()->pluck('name', 'id');
        $certifications = Certification::select('id', 'name')->get()->pluck('name', 'id');

        return view('web.federation.evt_event.events.full-edit', compact(
            'event',
            'category',
            'federations_list',
            'entities_list',
            'country_options',
            'district_options',
            'zone_options',
            'sports',
            'organizerDetails',
            'discipline_templates',
            'professional_roles',
            'member_attributes',
            'staff_attributes',
            'referee_attributes',
            'coach_attributes',
            'official_attributes',
            'anti_doping',
            'technical_delegate',
            'licenses',
            'certifications'
        ));
    }

    protected function fullUpdate(
        Request $request,
        Event $event,
        AttributeOrganizerToEventAction $attributeOrganizerAction
    ): RedirectResponse {
        $validatedData = app(EventUpdateRequest::class)->validated();

        if ($validatedData['event_category'] === 'competition') {
            $validatedData['competition']['required_athlete_licenses'] = $validatedData['competition']['required_athlete_licenses'] ?? [];
            $validatedData['competition']['required_coach_certifications'] = $validatedData['competition']['required_coach_certifications'] ?? [];
            $validatedData['competition']['required_referee_certifications'] = $validatedData['competition']['required_referee_certifications'] ?? [];
            $validatedData['competition']['required_athlete_documents'] = $validatedData['competition']['required_athlete_documents'] ?? [];
            $validatedData['competition']['required_coach_documents'] = $validatedData['competition']['required_coach_documents'] ?? [];
            $validatedData['competition']['required_referee_documents'] = $validatedData['competition']['required_referee_documents'] ?? [];
            $validatedData['competition']['required_official_documents'] = $validatedData['competition']['required_official_documents'] ?? [];
        }

        DB::beginTransaction();

        try {
            $validatedData['status_class'] = $this->getStatusClassFromString($validatedData);
            $validatedData['is_visible'] = array_key_exists('is_visible', $validatedData) ? 1 : 0;
            $validatedData['allow_coach_enrollment'] = $validatedData['allow_coach_enrollment'] ?? false;
            $validatedData['allow_referee_enrollment'] = $validatedData['allow_referee_enrollment'] ?? false;
            $validatedData['allow_official_enrollment'] = $validatedData['allow_official_enrollment'] ?? true;
            $validatedData['allow_individual_enrollment'] = $validatedData['allow_individual_enrollment'] ?? false;
            $validatedData['public_athlete_list'] = $validatedData['public_athlete_list'] ?? false;
            $validatedData['public_coach_list'] = $validatedData['public_coach_list'] ?? false;
            $validatedData['public_referee_list'] = $validatedData['public_referee_list'] ?? false;

            $event->update($validatedData);

            if ($validatedData['event_category'] === 'competition') {
                $competitionData = $validatedData['competition'];
                $competitionData['required_athlete_licenses'] = ! empty($competitionData['required_athlete_licenses']) ? $competitionData['required_athlete_licenses'] : null;
                $competitionData['required_coach_certifications'] = ! empty($competitionData['required_coach_certifications']) ? $competitionData['required_coach_certifications'] : null;
                $competitionData['required_referee_certifications'] = ! empty($competitionData['required_referee_certifications']) ? $competitionData['required_referee_certifications'] : null;
                $competitionData['required_athlete_documents'] = ! empty($competitionData['required_athlete_documents']) ? $competitionData['required_athlete_documents'] : null;
                $competitionData['required_coach_documents'] = ! empty($competitionData['required_coach_documents']) ? $competitionData['required_coach_documents'] : null;
                $competitionData['required_referee_documents'] = ! empty($competitionData['required_referee_documents']) ? $competitionData['required_referee_documents'] : null;
                $competitionData['required_official_documents'] = ! empty($competitionData['required_official_documents']) ? $competitionData['required_official_documents'] : null;
                $competitionData['requires_athlete_adel'] = $competitionData['requires_athlete_adel'] ?? false;
                $competitionData['requires_coach_adel'] = $competitionData['requires_coach_adel'] ?? false;
                $competitionData['requires_referee_adel'] = $competitionData['requires_referee_adel'] ?? false;
                $competitionData['requires_official_adel'] = $competitionData['requires_official_adel'] ?? false;
                $competitionData['requires_local_federation_affiliation'] = $competitionData['requires_local_federation_affiliation'] ?? false;
                $competitionData['requires_athlete_entity_sport_registration'] = $competitionData['requires_athlete_entity_sport_registration'] ?? false;
                $competitionData['requires_coach_entity_sport_registration'] = $competitionData['requires_coach_entity_sport_registration'] ?? false;

                if ($event->competition) {
                    $event->competition->update($competitionData);
                    $competition = $event->competition;
                } else {
                    $competition = new Competition($competitionData);
                    $event->competition()->save($competition);
                    $event->refresh();
                }

                $competition->requiredCoachCertifications()->sync($competitionData['required_coach_certifications'] ?? []);
                $competition->requiredRefereeCertifications()->sync($competitionData['required_referee_certifications'] ?? []);

                if (isset($validatedData['anti_doping'])) {
                    $antiDopingRecord = $competition->antiDopingRecord;
                    if ($antiDopingRecord) {
                        $antiDopingRecord->update($validatedData['anti_doping']);
                    } else {
                        $antiDopingRecord = new AntiDoping($validatedData['anti_doping']);
                        $competition->antiDopingRecord()->save($antiDopingRecord);
                    }
                }

                if (! empty($validatedData['technical_delegate']['name'])) {
                    $technicalDelegateData = $validatedData['technical_delegate'];
                    $technicalDelegateData['competition_id'] = $competition->id;
                    TechnicalDelegate::updateOrCreate(
                        ['competition_id' => $competition->id],
                        $technicalDelegateData
                    );
                }
            }

            if ($validatedData['event_category'] === 'competition') {
                CompetitionType::where('competition_id', $competition->id)->delete();
                if (isset($validatedData['competition']['types']) && is_array($validatedData['competition']['types'])) {
                    foreach ($validatedData['competition']['types'] as $type) {
                        CompetitionType::create([
                            'competition_id' => $competition->id,
                            'competition_type' => $type,
                        ]);
                    }
                }
            }

            if (isset($validatedData['organizer_details'])) {
                $organizerDetails = $event->organizerDetails ?: new OrganizerDetail;
                $organizerDetails->fill($validatedData['organizer_details']);
                $event->organizerDetails()->save($organizerDetails);
            }

            if (isset($validatedData['selected_referee_attributes'])) {
                $event->refereeAttributes()->sync($validatedData['selected_referee_attributes']);
            }
            if (isset($validatedData['selected_staff_attributes'])) {
                $event->staffAttributes()->sync($validatedData['selected_staff_attributes']);
            }
            if (isset($validatedData['selected_coach_attributes'])) {
                $event->coachAttributes()->sync($validatedData['selected_coach_attributes']);
            }
            if (isset($validatedData['selected_official_attributes'])) {
                $event->officialAttributes()->sync($validatedData['selected_official_attributes']);
            }
            if (isset($validatedData['selected_attributes'])) {
                $event->attributes()->sync($validatedData['selected_attributes']);
            }
            if (isset($validatedData['selected_attribute_groups'])) {
                $event->attributeGroups()->sync($validatedData['selected_attribute_groups']);
            }
            if (isset($validatedData['organizer_id'])) {
                $attributeOrganizerAction->execute($event->id, $validatedData['organizer_id']);
            }
            if (isset($validatedData['selected_countries'])) {
                $event->countries()->sync($validatedData['selected_countries']);
            }
            if (isset($validatedData['selected_zones'])) {
                $event->zones()->sync($validatedData['selected_zones']);
            }
            if (isset($validatedData['selected_districts'])) {
                $event->districts()->sync($validatedData['selected_districts']);
            }

            $professionalRoleIds = $validatedData['professional_roles'] ?? [];
            $event->professionalRoles()->sync($professionalRoleIds);

            $roles = [
                'technical_delegate_id' => $request->input('technical_delegate_id') ?: null,
                'chief_judge_id' => $request->input('chief_judge_id') ?: null,
                'competition_director_id' => $request->input('competition_director_id') ?: null,
            ];
            $assignRolesAction = app(AssignEventRolesAction::class);
            $assignRolesAction->execute($event, $roles);

            if ($request->boolean('remove_poster')) {
                $event->clearMediaCollection('poster');
            } elseif ($request->hasFile('poster')) {
                $event->clearMediaCollection('poster');
                $event->addMediaFromRequest('poster')->toMediaCollection('poster');
            }

            DB::commit();

            return redirect()->route('federation.evt-events.events.show', $event->id)->with('success', __('events.event_updated_successfully'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()->route('federation.evt-events.events.edit', $event->id)->with('error', __('events.error_updating_event'));
        }
    }

    protected function authorizeOrganizerAccess(Event $event): void
    {
        $federationId = Auth::user()->federations()->first()->id;

        $isOrganizer = $event->organizer()
            ->where('organizable_type', Federation::class)
            ->where('organizable_id', $federationId)
            ->exists();

        if (! $isOrganizer) {
            abort(403);
        }
    }

    protected function isDefaultFederation(): bool
    {
        return Auth::user()->federations->contains(fn ($f) => $f->is_default_federation);
    }

    protected function canManageEvents(): bool
    {
        return $this->isDefaultFederation() && Auth::user()->can('manage-events');
    }

    private function getStatusClassFromString(array $validatedData): string
    {
        return match ($validatedData['status_class']) {
            'PreparationEventState' => PreparationEventState::class,
            'ActiveEventState' => ActiveEventState::class,
            'ArchiveEventState' => ArchiveEventState::class,
            default => $validatedData['status_class'],
        };
    }
}
