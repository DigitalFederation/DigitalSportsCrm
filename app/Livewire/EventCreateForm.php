<?php

namespace App\Livewire;

use App\Enums\EvtCompetitionTypeEnum;
use App\Enums\EvtEventCategoryTypeEnum;
use App\Enums\EvtEventEnrollmentTypeEnum;
use App\Enums\EvtEventFeeTypeEnum;
use App\Enums\EvtEventGeographicalCoverageEnum;
use App\Enums\EvtEventOrganizationCategoryEnum;
use App\Enums\EvtEventTypeEnum;
use App\Models\Country;
use App\Models\GeoZone;
use App\Models\SubRegion;
use Carbon\Carbon;
use Domain\EvtEvents\Actions\AttributeOrganizerToEventAction;
use Domain\EvtEvents\Actions\CreateEventAction;
use Domain\EvtEvents\Models\AttributeGroup;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\CompetitionType;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\EventGeographic;
use Domain\EvtEvents\Models\Sport;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\ProfessionalRole;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class EventCreateForm extends Component
{
    use WithFileUploads;

    public $name;

    public $event;

    public $event_type;

    public $event_geographical_coverage;

    public $organization_type;
    public $organizer_options;
    public $organizer_id;

    public $status_class;

    public $enrollment_type;

    public $location;
    public $event_organization_types;
    public $event_enrollment_types;

    public $notes;

    public $start_date;

    public $end_date;
    public $start_registration_date;
    public $end_registration_date;

    public $category_selected = '';
    public $status_class_selected = '';
    public $sport_options;

    public $sport_id;

    public $event_fee_type;
    public $event_fee_type_options;
    public $event_categories_options;
    public $event_organization_types_options;
    public $event_type_options;
    public $event_enrollment_types_options;
    public $event_geographical_coverage_options;
    public $professional_roles_options;

    public $edit = false;

    // Geography
    public $country_options;
    public $geo_zone_options;
    public $sub_region_options;
    public $selected_countries = [];
    public $selected_sub_regions = [];
    public $selected_geo_zones = [];
    public $competition_types = [];
    public $competition_type_options;
    public $selected_referee_certifications = [];
    public $selected_coach_certifications = [];
    public $selected_professional_roles = [];
    public $selected_attribute_groups = [];
    public $all_attribute_groups; // Holds all available attribute groups
    public $referee_certifications_options = [];
    public $coach_certifications_options = [];

    public $external_url;

    public $regulations_url;

    public $moloni_reference;

    public $venue_country;
    public $venue_city;
    public $venue;
    public $venue_address;

    public function mount()
    {

        $this->fillInitialOptions();

        if (empty($this->event)) {
            $this->event = new Event;
            $this->start_date = null; // Explicitly setting to null
            $this->end_date = null; // Explicitly setting to null

        } else {
            $this->name = $this->event->name;
            $this->notes = $this->event->notes;
            $this->event_geographical_coverage = $this->event->event_geographical_coverage;
            $this->event_type = $this->event->event_type;
            $this->organization_type = $this->event->organization_type;
            $this->enrollment_type = $this->event->enrollment_type;
            $this->event_fee_type = $this->event->event_fee_type;
            $this->location = $this->event->location;
            $this->event_organization_types = $this->event->event_organization_types;
            $this->event_enrollment_types = $this->event->event_enrollment_types;
            $this->category_selected = $this->event->event_category;
            $this->status_class_selected = $this->event->status_class;
            $this->start_date = $this->event->start_date ? Carbon::parse($this->event->start_date)->format('Y-m-d') : null;
            $this->end_date = $this->event->end_date ? Carbon::parse($this->event->end_date)->format('Y-m-d') : null;
            $this->start_registration_date = Carbon::parse($this->event->start_registration_date)->format('Y-m-d');
            $this->end_registration_date = Carbon::parse($this->event->end_registration_date)->format('Y-m-d');

            $this->competition_types = $this->event->competitions->first()?->types()->pluck('competition_type')->toArray();
            $this->sport_id = $this->event->competitions->first()?->sport_id;
            $this->selected_referee_certifications = $this->event->competitions->first()?->requiredRefereeCertifications->pluck('id')->toArray();
            $this->selected_coach_certifications = $this->event->competitions->first()?->requiredCoachCertifications->pluck('id')->toArray();
            $this->selected_professional_roles = $this->event->professionalRoles->pluck('id')->toArray();

            $this->venue = $this->event->venue;
            $this->venue_address = $this->event->venue_address;
            $this->venue_city = $this->event->venue_city;
            $this->moloni_reference = $this->event->moloni_reference;

        }

        $this->all_attribute_groups = AttributeGroup::all();

        if ($this->event->exists) {
            $this->selected_attribute_groups = $this->event->attributeGroups->pluck('id')->toArray();

            $this->selected_countries = $this->event->countries->pluck('id')->toArray();
            $this->selected_sub_regions = $this->event->subRegions->pluck('id')->toArray();
            $this->selected_geo_zones = $this->event->geoZones->pluck('id')->toArray();
        }

    }

    public function fillInitialOptions()
    {
        $this->sport_options = Sport::all()->pluck('name', 'id');
        $this->competition_type_options = $this->competitionTypesFillList();
        $this->event_categories_options = EvtEventCategoryTypeEnum::cases();
        $this->event_organization_types_options = EvtEventOrganizationCategoryEnum::getGroupedOptions();
        $this->event_type_options = EvtEventTypeEnum::cases();
        $this->event_enrollment_types_options = EvtEventEnrollmentTypeEnum::cases();
        $this->event_geographical_coverage_options = EvtEventGeographicalCoverageEnum::cases();
        $this->organizer_options = Federation::all()->pluck('member_code', 'id');

        $this->sport_options = Sport::select('id', 'name')->get()->pluck('name', 'id');
        $this->country_options = Country::select('id', 'name')->get()->pluck('name', 'id');
        $this->geo_zone_options = GeoZone::select('id', 'name')->get()->pluck('name', 'id');
        $this->sub_region_options = SubRegion::select('id', 'name')->get()->pluck('name', 'id');
        $this->professional_roles_options = ProfessionalRole::select('id', 'name')->get()->pluck('name', 'id');

        // Fetch all technical official roles with their certifications
        $technicalOfficialRolesWithCertifications = ProfessionalRole::technicalOfficialRelatedCertifications()->get();
        $technicalOfficialCertifications = collect();
        foreach ($technicalOfficialRolesWithCertifications as $role) {
            $technicalOfficialCertifications = $technicalOfficialCertifications->concat($role->certifications);
        }
        $uniqueTechnicalOfficialCertifications = $technicalOfficialCertifications->unique('id');
        $this->referee_certifications_options = $uniqueTechnicalOfficialCertifications->pluck('name', 'id');

        // Fetch all coach roles with their certifications
        $coachRolesWithCertifications = ProfessionalRole::coachRelatedCertifications()->get();
        $coachCertifications = collect();
        foreach ($coachRolesWithCertifications as $role) {
            $coachCertifications = $coachCertifications->concat($role->certifications);
        }
        $uniqueCoachCertifications = $coachCertifications->unique('id');
        $this->coach_certifications_options = $uniqueCoachCertifications->pluck('name', 'id');

        // Populate event_fee_type_options using enum keys
        $this->event_fee_type_options = array_combine(
            array_map(fn ($case) => $case->name, EvtEventFeeTypeEnum::cases()), // Extract the enum keys
            array_map(fn ($case) => EvtEventFeeTypeEnum::toString($case->name), EvtEventFeeTypeEnum::cases()) // Get the string values
        );

    }

    #[On('selectedMultipleUpdatedValue.competition_types')]
    public function updatedCompetitionTypes($values): void
    {
        $this->competition_types = $values;
    }

    #[On('selectedMultipleUpdatedValue.referee_certifications')]
    public function updatedRefereeCertifications($values): void
    {
        $this->selected_referee_certifications = $values;
    }

    #[On('selectedMultipleUpdatedValue.coach_certifications')]
    public function updatedCoachCertifications($values): void
    {
        $this->selected_coach_certifications = $values;
    }

    #[On('selectedMultipleUpdatedValue.geo_zones')]
    public function updatedGeoZones($values): void
    {
        $this->selected_geo_zones = $values;
    }

    #[On('selectedMultipleUpdatedValue.countries')]
    public function updatedCountries($values): void
    {
        $this->selected_countries = $values;
    }

    #[On('selectedMultipleUpdatedValue.professional_roles')]
    public function updatedProfessionalRoles($values): void
    {
        $this->selected_professional_roles = $values;
    }

    public function competitionTypesFillList(): array
    {
        $list = [];
        foreach (EvtCompetitionTypeEnum::cases() as $type) {
            $list[$type->name] = $type->value;
        }

        return $list;
    }

    public function submitForm(
        CreateEventAction $createEventAction,
        AttributeOrganizerToEventAction $attributeOrganizerAction)
    {
        $this->external_url = filled($this->external_url) ? $this->external_url : null;
        $this->regulations_url = filled($this->regulations_url) ? $this->regulations_url : null;

        // Validation
        $rules = [
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'location' => 'nullable|string',
            'start_date' => 'nullable|date|before_or_equal:end_date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'category_selected' => 'required',
            'event_type' => 'required_if:category_selected,competition',
            'event_geographical_coverage' => 'required_if:category_selected,competition',
            'organization_type' => 'required_if:category_selected,organization',
            'status_class_selected' => 'required|string',
            'enrollment_type' => 'required|string',
            'external_url' => 'nullable|url|max:500',
            'regulations_url' => 'nullable|url|max:500',
            'moloni_reference' => 'nullable|string|max:50',
        ];

        // Add validation for organizer_id if the event state is Active
        if ($this->status_class_selected == \Domain\EvtEvents\States\ActiveEventState::class) {
            $rules['organizer_id'] = 'nullable|exists:federation,id';
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'notes' => $this->notes,
            'location' => $this->location,
            'start_date' => $this->start_date ? Carbon::parse($this->start_date)->format('Y-m-d') : null,
            'end_date' => $this->end_date ? Carbon::parse($this->end_date)->format('Y-m-d') : null,
            'event_type' => $this->event_type,
            'event_category' => $this->category_selected,
            'event_geographical_coverage' => $this->event_geographical_coverage,
            'organization_type' => $this->organization_type,
            'status_class' => $this->status_class_selected,
            'enrollment_type' => $this->enrollment_type,
            'external_url' => $this->external_url,
            'regulations_url' => $this->regulations_url,
            'moloni_reference' => $this->moloni_reference,
            'venue' => $this->venue,
            'venue_address' => $this->venue_address,
            'venue_city' => $this->venue_city,
        ];

        // Data only available when "organization" is selected
        if ($this->category_selected !== 'competition') {
            $data['event_fee_type'] = $this->event_fee_type;
        } else {
            $data['event_fee_type'] = null;
        }

        try {
            DB::beginTransaction();

            // Create event action
            if (! $this->edit) {
                $event = $createEventAction->execute($data);
                // Create the Competition if Sport is selected
                if ($this->category_selected === 'competition') {
                    // Create the competition alongside the event
                    $competitionData = [
                        'event_id' => $event->id,
                        'sport_id' => $this->sport_id,
                        'full_name' => $this->name,  // Using event name as competition name
                    ];
                    $competition = Competition::create($competitionData);
                }

            } else {
                $event = Event::find($this->event->id);
                $event->update($data);
            }

            if ($this->category_selected == 'competition' && ! empty($this->selected_referee_certifications)) {
                $event->competitions->first()->requiredRefereeCertifications()->sync($this->selected_referee_certifications);
            }
            if ($this->category_selected == 'competition' && ! empty($this->selected_coach_certifications)) {
                $event->competitions->first()->requiredCoachCertifications()->sync($this->selected_coach_certifications);
            }
            // Guard clause to ensure competition is created
            if ($this->category_selected == 'competition' && ! $event->competitions->first()) {
                throw new Exception('Failed to create associated competition for the sport event.');
            }

            // Syncing the countries, sub-regions, and geo-zones with the event
            $event->countries()->sync($this->selected_countries);

            // After creating or updating the event:
            if ($this->category_selected === 'organization') {
                $event->attributeGroups()->sync($this->selected_attribute_groups);
            }

            EventGeographic::where(['event_id' => $event->id, 'geo_entity_type' => 'geo_zone'])
                ->delete();

            foreach ($this->selected_geo_zones as $geo_zone) {
                EventGeographic::create([
                    'event_id' => $event->id,
                    'geo_entity_id' => $geo_zone,
                    'geo_entity_type' => 'geo_zone',
                ]);
            }

            if ($this->category_selected == 'competition') {
                CompetitionType::where('competition_id', $event->competitions()->first()->id)->delete();
                foreach ($this->competition_types as $type) {
                    CompetitionType::create([
                        'competition_id' => $event->competitions->first()->id,
                        'competition_type' => $type,
                    ]);
                }
            }

            if ($this->category_selected !== 'competition') {
                $event->professionalRoles()->sync($this->selected_professional_roles);
            }

            // Assigning the organizer if the event is Active and an organizer is selected
            if ($this->status_class_selected == \Domain\EvtEvents\States\ActiveEventState::class && $this->organizer_id) {
                $attributeOrganizerAction->execute($event->id, $this->organizer_id);
            }

            DB::commit();

            $this->dispatch('eventCreated', $event->id);

            return redirect()->route('admin.evt-events.events.show', $event->id)->with('success', sprintf('Event %s successfully', $this->edit ? 'updated' : 'created'));
        } catch (Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());

            return redirect()->route('admin.evt-events.events.index')->with('error', "Error: {$e->getMessage()}");
        }
    }

    public function render()
    {
        return view('livewire.event-create-form');
    }
}
