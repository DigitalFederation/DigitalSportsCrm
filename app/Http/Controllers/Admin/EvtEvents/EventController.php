<?php

namespace App\Http\Controllers\Admin\EvtEvents;

use App\Exports\EventMasterExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventStoreRequest;
use App\Http\Requests\EventUpdateRequest;
use App\Models\Country;
use App\Models\GeoZone;
use App\Traits\StreamsMediaFromStorage;
use Domain\Certifications\Models\Certification;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Actions\AssignEventRolesAction;
use Domain\EvtEvents\Actions\AttributeOrganizerToEventAction;
use Domain\EvtEvents\Actions\CreateEventAction;
use Domain\EvtEvents\Models\AntiDoping;
use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\CompetitionType;
use Domain\EvtEvents\Models\DisciplineTemplate;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\OrganizerDetail;
use Domain\EvtEvents\Models\Sport;
use Domain\EvtEvents\Models\TechnicalDelegate;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\EvtEvents\States\ArchiveEventState;
use Domain\EvtEvents\States\CanceledEventState;
use Domain\EvtEvents\States\PreparationEventState;
use Domain\Federations\Models\Federation;
use Domain\Geographic\Models\District;
use Domain\Geographic\Models\Zone;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class EventController extends Controller
{
    use StreamsMediaFromStorage;
    /**
     * Display a listing of the events.
     *
     * @return View
     */
    public function index()
    {
        // Show all events paginated excluding archived events by default
        $events = Event::with('organizer.organizable')
            ->where('status_class', '!=', ArchiveEventState::class)
            ->orderByRaw('COALESCE(start_date, DATE("2099-12-31"))')  // This ensures NULL dates go to the end
            ->orderBy('name')  // Secondary sort by name
            ->paginate(50);

        return view('web.admin.evt_events.events.index', compact('events'));
    }

    public function master()
    {
        $events = Event::with([
            'sport',
            'venueCountry',
            'geoZones',
            'subRegions',
            'countries',
            'organizer.organizable',
            'organizerDetails',
            'competition.sport',
            'competition.types',
            'competition.antiDopingRecord',
            'competition.technicalDelegates.federation',
            'competition.venueCountry',
            'competition.disciplineTemplate.disciplines',
        ])
            ->where('status_class', '!=', ArchiveEventState::class)
            ->orderByRaw('COALESCE(start_date, DATE("2099-12-31"))')  // This ensures NULL dates go to the end
            ->orderBy('name')  // Secondary sort by name
            ->paginate(50);

        return view('web.admin.evt_events.events.master', compact('events'));
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

    /**
     * Show the form for creating a new event.
     *
     * @return View
     */
    public function create($category = 'organization')
    {
        $event = new Event;

        $federations_list = Federation::query()->pluck('member_code', 'id');
        $entities_list = Entity::query()->orderBy('name')->pluck('name', 'id');
        $sports = Sport::query()->pluck('name', 'id');
        $country_options = Country::query()->pluck('name', 'id');
        $district_options = District::query()->orderBy('name')->pluck('name', 'id');
        $zone_options = Zone::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id');
        $discipline_templates = DisciplineTemplate::all();
        $professional_roles = ProfessionalRole::query()->pluck('name', 'id');
        $attributes = Attribute::all()->groupBy('enrollment_type');
        $anti_doping = new AntiDoping;
        $availableStates = Event::availableStates();

        // For filtering enrollments purposes
        $licenses = License::query()->pluck('name', 'id');
        $certifications = Certification::query()->pluck('name', 'id');

        // Fetch all attributes in a single query and group them by enrollment type
        $referee_attributes = $attributes->get('REFEREE', collect());
        $staff_attributes = $attributes->get('STAFF', collect());
        $coach_attributes = $attributes->get('COACH', collect());
        $official_attributes = $attributes->get('OFFICIAL', collect());
        $member_attributes = $attributes->filter(function ($items, $type) {
            return $type !== 'ATHLETE';
        })->flatten();

        return view('web.admin.evt_events.events.create', compact(
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

    /**
     * Store a newly created event in storage.
     *
     * @return RedirectResponse
     */
    public function store(
        EventStoreRequest $request,
        CreateEventAction $createEventAction,
        AttributeOrganizerToEventAction $attributeOrganizerAction
    ) {
        $validatedData = $request->validated();

        DB::beginTransaction();
        try {
            // Update Status_class to the correct class AlpineJS sends the class name as a string
            $validatedData['status_class'] = $this->getStatusClassFromString($validatedData);

            // Check if 'is_visible' is present in the request
            $validatedData['is_visible'] = array_key_exists('is_visible', $validatedData) ? 1 : 0;
            // Include the new fields
            $validatedData['allow_coach_enrollment'] = $validatedData['allow_coach_enrollment'] ?? false;
            $validatedData['allow_referee_enrollment'] = $validatedData['allow_referee_enrollment'] ?? false;
            $validatedData['allow_official_enrollment'] = $validatedData['allow_official_enrollment'] ?? true;
            $validatedData['allow_individual_enrollment'] = $validatedData['allow_individual_enrollment'] ?? false;
            $validatedData['public_athlete_list'] = $validatedData['public_athlete_list'] ?? false;
            $validatedData['public_coach_list'] = $validatedData['public_coach_list'] ?? false;
            $validatedData['public_referee_list'] = $validatedData['public_referee_list'] ?? false;

            // Create the event using the action
            $event = $createEventAction->execute($validatedData);

            // Check if it's a competition and handle competition specific fields
            if ($validatedData['event_category'] === 'competition') {
                $competitionData = $validatedData['competition'];

                // Ensure arrays are properly handled (convert empty arrays to null)
                $competitionData['required_athlete_licenses'] = ! empty($competitionData['required_athlete_licenses']) ? $competitionData['required_athlete_licenses'] : null;
                $competitionData['required_coach_certifications'] = ! empty($competitionData['required_coach_certifications']) ? $competitionData['required_coach_certifications'] : null;
                $competitionData['required_referee_certifications'] = ! empty($competitionData['required_referee_certifications']) ? $competitionData['required_referee_certifications'] : null;
                $competitionData['required_athlete_documents'] = ! empty($competitionData['required_athlete_documents']) ? $competitionData['required_athlete_documents'] : null;
                $competitionData['required_coach_documents'] = ! empty($competitionData['required_coach_documents']) ? $competitionData['required_coach_documents'] : null;
                $competitionData['required_referee_documents'] = ! empty($competitionData['required_referee_documents']) ? $competitionData['required_referee_documents'] : null;
                $competitionData['required_official_documents'] = ! empty($competitionData['required_official_documents']) ? $competitionData['required_official_documents'] : null;

                // Handle boolean fields for ADEL requirements
                $competitionData['requires_athlete_adel'] = $competitionData['requires_athlete_adel'] ?? false;
                $competitionData['requires_coach_adel'] = $competitionData['requires_coach_adel'] ?? false;
                $competitionData['requires_referee_adel'] = $competitionData['requires_referee_adel'] ?? false;
                $competitionData['requires_official_adel'] = $competitionData['requires_official_adel'] ?? false;

                // Handle boolean field for local federation affiliation requirement
                $competitionData['requires_local_federation_affiliation'] = $competitionData['requires_local_federation_affiliation'] ?? false;

                // Handle boolean fields for entity sport registration requirements (default to true for new events)
                $competitionData['requires_athlete_entity_sport_registration'] = $competitionData['requires_athlete_entity_sport_registration'] ?? true;
                $competitionData['requires_coach_entity_sport_registration'] = $competitionData['requires_coach_entity_sport_registration'] ?? true;

                $competition = new Competition($competitionData);
                $event->competition()->save($competition);

                // Handle certification requirements after competition is created
                if (isset($competitionData['required_coach_certifications'])) {
                    $competition->requiredCoachCertifications()->sync($competitionData['required_coach_certifications']);
                }
                if (isset($competitionData['required_referee_certifications'])) {
                    $competition->requiredRefereeCertifications()->sync($competitionData['required_referee_certifications']);
                }

                // Save Technical Delegate data
                if (! empty($validatedData['technical_delegate']['name'])) {
                    $technicalDelegateData = $validatedData['technical_delegate'];
                    $technicalDelegateData['competition_id'] = $event->competition->id;
                    TechnicalDelegate::updateOrCreate(
                        ['competition_id' => $event->competition->id],
                        $technicalDelegateData
                    );
                }
            }

            // Check and save organizer details if provided
            if (! empty($validatedData['organizer_details'])) {
                $organizerDetails = new OrganizerDetail($validatedData['organizer_details']);
                $organizerDetails->event_id = $event->id;
                $event->organizerDetails()->save($organizerDetails);
            }

            // Create new AntiDoping record and associate it with the Competition if provided
            if ($validatedData['event_category'] === 'competition' && ! empty($validatedData['anti_doping']) && $event->competition) {
                $antiDopingRecord = new AntiDoping($validatedData['anti_doping']);
                $event->competition->antiDopingRecord()->save($antiDopingRecord);
            }

            // Handle referee attributes
            if (isset($validatedData['selected_referee_attributes'])) {
                $event->refereeAttributes()->sync($validatedData['selected_referee_attributes']);
            }

            // Handle staff attributes
            if (isset($validatedData['selected_staff_attributes'])) {
                $event->staffAttributes()->sync($validatedData['selected_staff_attributes']);
            }

            // Handle coach attributes
            if (isset($validatedData['selected_coach_attributes'])) {
                $event->coachAttributes()->sync($validatedData['selected_coach_attributes']);
            }

            // Handle official attributes
            if (isset($validatedData['selected_official_attributes'])) {
                $event->officialAttributes()->sync($validatedData['selected_official_attributes']);
            }

            // Handle organization/individual attributes
            if (isset($validatedData['selected_attributes'])) {
                $event->attributes()->sync($validatedData['selected_attributes']);
            }

            // Post-creation actions such as syncing relationships
            if (isset($validatedData['selected_attribute_groups'])) {
                $event->attributeGroups()->sync($validatedData['selected_attribute_groups']);
            }

            // Assign organizer to event if applicable
            if (isset($validatedData['organizer_id'])) {
                $attributeOrganizerAction->execute($event->id, $validatedData['organizer_id']);
            }

            $event->professionalRoles()->sync($validatedData['professional_roles'] ?? []);

            // Keep old relationships for backward compatibility if they exist
            if (isset($validatedData['selected_countries'])) {
                $event->countries()->sync($validatedData['selected_countries']);
            }

            // Sync zones and districts for geographic filtering
            $event->zones()->sync($validatedData['selected_zones'] ?? []);
            $event->districts()->sync($validatedData['selected_districts'] ?? []);

            // Assign event roles (Technical Delegate, Chief Judge, Competition Director)
            $roles = [
                'technical_delegate_id' => $request->input('technical_delegate_id') ?: null,
                'chief_judge_id' => $request->input('chief_judge_id') ?: null,
                'competition_director_id' => $request->input('competition_director_id') ?: null,
            ];

            // Always call the action to handle role assignments/removals
            $assignRolesAction = app(AssignEventRolesAction::class);
            $assignRolesAction->execute($event, $roles);

            // Handle poster image upload
            if ($request->hasFile('poster')) {
                $event->addMediaFromRequest('poster')->toMediaCollection('poster');
            }

            DB::commit();

            return redirect()->route('admin.evt-events.events.show', $event->id)->with('success', 'Event created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            if ($validatedData['event_category'] === 'competition') {
                return redirect()->route('admin.evt-events.events.create', 'competition')->with('error', 'Error creating competition: ' . $e->getMessage());
            }

            return redirect()->route('admin.evt-events.events.create', 'organizational')->with('error', 'Error creating event: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified event.
     *
     * @return View
     */
    public function show(Event $event)
    {
        $event->load([
            'competitions.sport',
            'competitions.technicalDelegates',
            'competitions.venueCountry',
            'competitions.disciplineTemplate.disciplines',
            'organizer.organizable.district',
            'organizerDetails',
            'pricing',
            'technicalDelegate.individual',
            'chiefJudge.individual',
            'competitionDirector.individual',
            'athleteEnrollments.individual',
        ]);

        $attachmentsCacheKey = "event_attachments_file_{$event->id}";
        $attachments = Cache::remember($attachmentsCacheKey, now()->addMinutes(10), function () use ($event) {
            return Media::where('model_id', $event->id)
                ->where('collection_name', 'event-attachments')
                ->get();
        });

        return view('web.admin.evt_events.events.show', compact('event', 'attachments'));
    }

    /**
     * Show the form for editing the specified event.
     *
     * @return View
     */
    public function edit(Event $event)
    {
        // load associations
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
        $sports = Sport::query()->pluck('name', 'id');
        $country_options = Country::query()->pluck('name', 'id');
        $district_options = District::query()->orderBy('name')->pluck('name', 'id');
        $zone_options = Zone::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id');
        $geo_zone_options = GeoZone::query()->pluck('name', 'id');
        $organizerDetails = $event->organizerDetails;
        $category = $event->event_category;
        $discipline_templates = DisciplineTemplate::all();
        $professional_roles = ProfessionalRole::query()->pluck('name', 'id');

        $anti_doping = $event->competition?->antiDopingRecord ?? new AntiDoping;

        // Fetch all attributes in a single query and group them by enrollment type
        $attributes = Attribute::all()->groupBy('enrollment_type');

        $referee_attributes = $attributes->get('REFEREE', collect());
        $staff_attributes = $attributes->get('STAFF', collect());
        $coach_attributes = $attributes->get('COACH', collect());
        $official_attributes = $attributes->get('OFFICIAL', collect());
        $member_attributes = $attributes->filter(function ($items, $type) {
            return $type !== 'ATHLETE';
        })->flatten();

        $technical_delegate = $event->competition?->technicalDelegates()->first() ?? new \Domain\EvtEvents\Models\TechnicalDelegate;

        // For filtering enrollments purposes
        $licenses = License::select('id', 'name')->get()->pluck('name', 'id');
        $certifications = Certification::select('id', 'name')->get()->pluck('name', 'id');

        return view('web.admin.evt_events.events.edit', compact(
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

    /**
     * Update the specified event in storage.
     *
     * @return RedirectResponse
     */
    public function update(
        EventUpdateRequest $request,
        Event $event,
        AttributeOrganizerToEventAction $attributeOrganizerAction
    ) {
        $validatedData = $request->validated();

        // Ensure array fields are set to empty array if not present (only for competition events)
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
            // Convert Status_class from string to actual state class
            $validatedData['status_class'] = $this->getStatusClassFromString($validatedData);

            // Check if 'is_visible' is present in the request
            $validatedData['is_visible'] = array_key_exists('is_visible', $validatedData) ? 1 : 0;

            // Include the new fields
            $validatedData['allow_coach_enrollment'] = $validatedData['allow_coach_enrollment'] ?? false;
            $validatedData['allow_referee_enrollment'] = $validatedData['allow_referee_enrollment'] ?? false;
            $validatedData['allow_official_enrollment'] = $validatedData['allow_official_enrollment'] ?? true;
            $validatedData['allow_individual_enrollment'] = $validatedData['allow_individual_enrollment'] ?? false;
            $validatedData['public_athlete_list'] = $validatedData['public_athlete_list'] ?? false;
            $validatedData['public_coach_list'] = $validatedData['public_coach_list'] ?? false;
            $validatedData['public_referee_list'] = $validatedData['public_referee_list'] ?? false;

            // Update the event with the validated data
            $event->update($validatedData);

            // Check if it's a competition and handle competition-specific fields
            if ($validatedData['event_category'] === 'competition') {
                $competitionData = $validatedData['competition'];

                // Ensure arrays are properly handled (convert empty arrays to null)
                $competitionData['required_athlete_licenses'] = ! empty($competitionData['required_athlete_licenses']) ? $competitionData['required_athlete_licenses'] : null;
                $competitionData['required_coach_certifications'] = ! empty($competitionData['required_coach_certifications']) ? $competitionData['required_coach_certifications'] : null;
                $competitionData['required_referee_certifications'] = ! empty($competitionData['required_referee_certifications']) ? $competitionData['required_referee_certifications'] : null;
                $competitionData['required_athlete_documents'] = ! empty($competitionData['required_athlete_documents']) ? $competitionData['required_athlete_documents'] : null;
                $competitionData['required_coach_documents'] = ! empty($competitionData['required_coach_documents']) ? $competitionData['required_coach_documents'] : null;
                $competitionData['required_referee_documents'] = ! empty($competitionData['required_referee_documents']) ? $competitionData['required_referee_documents'] : null;
                $competitionData['required_official_documents'] = ! empty($competitionData['required_official_documents']) ? $competitionData['required_official_documents'] : null;

                // Handle boolean fields for ADEL requirements
                $competitionData['requires_athlete_adel'] = $competitionData['requires_athlete_adel'] ?? false;
                $competitionData['requires_coach_adel'] = $competitionData['requires_coach_adel'] ?? false;
                $competitionData['requires_referee_adel'] = $competitionData['requires_referee_adel'] ?? false;
                $competitionData['requires_official_adel'] = $competitionData['requires_official_adel'] ?? false;

                // Handle boolean field for local federation affiliation requirement
                $competitionData['requires_local_federation_affiliation'] = $competitionData['requires_local_federation_affiliation'] ?? false;

                // Handle boolean fields for entity sport registration requirements
                // For updates, default to false if not provided (checkbox not checked means false)
                $competitionData['requires_athlete_entity_sport_registration'] = $competitionData['requires_athlete_entity_sport_registration'] ?? false;
                $competitionData['requires_coach_entity_sport_registration'] = $competitionData['requires_coach_entity_sport_registration'] ?? false;

                // Create competition if it doesn't exist, otherwise update
                if ($event->competition) {
                    $event->competition->update($competitionData);
                    $competition = $event->competition;
                } else {
                    $competition = new Competition($competitionData);
                    $event->competition()->save($competition);
                    $event->refresh();
                }

                // Handle certification requirements after competition is updated/created
                $competition->requiredCoachCertifications()->sync($competitionData['required_coach_certifications'] ?? []);
                $competition->requiredRefereeCertifications()->sync($competitionData['required_referee_certifications'] ?? []);

                // Check if an AntiDoping record already exists
                if (isset($validatedData['anti_doping'])) {
                    $antiDopingRecord = $competition->antiDopingRecord;
                    if ($antiDopingRecord) {
                        // Update existing AntiDoping record
                        $antiDopingRecord->update($validatedData['anti_doping']);
                    } else {
                        // Create new AntiDoping record and associate it with the Competition
                        $antiDopingRecord = new AntiDoping($validatedData['anti_doping']);
                        $competition->antiDopingRecord()->save($antiDopingRecord);
                    }
                }

                // Save Technical Delegate data
                if (! empty($validatedData['technical_delegate']['name'])) {
                    $technicalDelegateData = $validatedData['technical_delegate'];
                    $technicalDelegateData['competition_id'] = $competition->id;
                    TechnicalDelegate::updateOrCreate(
                        ['competition_id' => $competition->id],
                        $technicalDelegateData
                    );
                }
            }

            // Check if it's a competition and Competition Types are provided
            if ($validatedData['event_category'] === 'competition') {
                // Always clear existing CompetitionTypes for this competition to avoid duplicates or stale data.
                CompetitionType::where('competition_id', $competition->id)->delete();
                // Only re-create CompetitionTypes if types are provided and is an array.
                if (isset($validatedData['competition']['types']) && is_array($validatedData['competition']['types'])) {
                    foreach ($validatedData['competition']['types'] as $type) {
                        CompetitionType::create([
                            'competition_id' => $competition->id,
                            'competition_type' => $type,
                        ]);
                    }
                }
            }

            // Check and update organizer details if provided
            if (isset($validatedData['organizer_details'])) {
                $organizerDetails = $event->organizerDetails ?: new OrganizerDetail;
                $organizerDetails->fill($validatedData['organizer_details']);
                $event->organizerDetails()->save($organizerDetails);
            }

            // Handle referee attributes
            if (isset($validatedData['selected_referee_attributes'])) {
                $event->refereeAttributes()->sync($validatedData['selected_referee_attributes']);
            }

            // Handle staff attributes
            if (isset($validatedData['selected_staff_attributes'])) {
                $event->staffAttributes()->sync($validatedData['selected_staff_attributes']);
            }

            // Handle coach attributes
            if (isset($validatedData['selected_coach_attributes'])) {
                $event->coachAttributes()->sync($validatedData['selected_coach_attributes']);
            }

            // Handle official attributes
            if (isset($validatedData['selected_official_attributes'])) {
                $event->officialAttributes()->sync($validatedData['selected_official_attributes']);
            }

            // Handle organization/individual attributes
            if (isset($validatedData['selected_attributes'])) {
                $event->attributes()->sync($validatedData['selected_attributes']);
            }

            // Post-update actions such as syncing relationships
            if (isset($validatedData['selected_attribute_groups'])) {
                $event->attributeGroups()->sync($validatedData['selected_attribute_groups']);
            }

            // Reassign organizer to event if applicable
            if (isset($validatedData['organizer_id'])) {
                $attributeOrganizerAction->execute($event->id, $validatedData['organizer_id']);
            }

            // Keep old relationships for backward compatibility if needed
            if (isset($validatedData['selected_countries'])) {
                $event->countries()->sync($validatedData['selected_countries']);
            }

            // Sync zones and districts for geographic filtering
            $event->zones()->sync($validatedData['selected_zones'] ?? []);
            $event->districts()->sync($validatedData['selected_districts'] ?? []);

            // Handle professional roles
            $event->professionalRoles()->sync($validatedData['professional_roles'] ?? []);

            // Update event roles (Technical Delegate, Chief Judge, Competition Director)
            $roles = [
                'technical_delegate_id' => $request->input('technical_delegate_id') ?: null,
                'chief_judge_id' => $request->input('chief_judge_id') ?: null,
                'competition_director_id' => $request->input('competition_director_id') ?: null,
            ];

            $assignRolesAction = app(AssignEventRolesAction::class);
            $assignRolesAction->execute($event, $roles);

            // Handle poster image removal or upload
            if ($request->boolean('remove_poster')) {
                $event->clearMediaCollection('poster');
            } elseif ($request->hasFile('poster')) {
                $event->clearMediaCollection('poster');
                $event->addMediaFromRequest('poster')->toMediaCollection('poster');
            }

            DB::commit();

            return redirect()->route('admin.evt-events.events.show', $event->id)->with('success', 'Event updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()->route('admin.evt-events.events.edit', $event->id)->with('error', 'Error updating event: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified event from storage.
     *
     * @return RedirectResponse
     */
    public function destroy(Event $event)
    {
        try {
            $event->delete();
        } catch (QueryException $e) {
            $event->status_class = CanceledEventState::class;
            $event->save();
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return redirect()->route('admin.evt-events.events.index')->with('error', $e->getMessage());
        } finally {
            return redirect()->route('admin.evt-events.events.index')->with('success', 'Event canceled successfully');
        }
    }

    public function download(Request $request)
    {
        if (empty($request->id)) {
            return back()->with('error', 'No file selected');
        }
        $mediaItem = Media::find($request->id);

        if (! $mediaItem) {
            return back()->with('error', 'File not found');
        }

        return $this->streamMediaDownload($mediaItem, $mediaItem->file_name);
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
