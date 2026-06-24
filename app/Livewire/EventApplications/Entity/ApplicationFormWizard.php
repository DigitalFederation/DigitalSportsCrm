<?php

namespace App\Livewire\EventApplications\Entity;

use App\Enums\EventApplicationTypeEnum;
use Domain\Entities\Models\Entity;
use Domain\EventApplications\Actions\CheckDuplicateApplicationAction;
use Domain\EventApplications\Actions\CheckForConflictingDirectSubmissionAction;
use Domain\EventApplications\Actions\CreateApplicationAction;
use Domain\EventApplications\Models\ApplicationTemplate;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\DraftApplicationState;
use Domain\EventApplications\States\SubmittedApplicationState;
use Domain\EvtEvents\Models\Sport;
use Domain\Federations\Models\Federation;
use Domain\Geographic\Models\District;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class ApplicationFormWizard extends Component
{
    public int $currentStep = 1;

    public int $totalSteps = 10;

    public ?ApplicationTemplate $template = null;

    public ?EventApplication $application = null;

    public string $mode = 'create';

    public string $routeNamespace = 'entity';

    // Step 1 - Event (Section 0 + 1)
    public $event_name = '';

    public $event_type = 'competition';

    public $sport_id = null;

    public $start_date = '';

    public $end_date = '';

    public $district_id = null;

    public $municipality = '';

    public $event_category = null;

    public $category = null;

    public $target_audience = '';

    public $expected_participants = null;

    // Form data JSON (Sections 0-13)
    public array $formData = [];

    // Select options
    public $districts = [];

    public $sports = [];

    public function mount(): void
    {
        $this->initializeFormData();
        $this->loadFormFields();
        $this->loadSelectOptions();
    }

    protected function initializeFormData(): void
    {
        $defaults = $this->getDefaultFormData();

        if ($this->mode === 'edit' && $this->application) {
            $this->formData = array_replace_recursive($defaults, $this->application->form_data ?? []);
        } else {
            $this->formData = $defaults;
        }
    }

    protected function getDefaultFormData(): array
    {
        return [
            // Section 0 - Budget summary
            'budget' => null,
            'expected_revenue' => null,

            // Section 1 - Event characterization
            'registration_type' => null,
            'age_groups' => [],
            'location' => '',
            'address' => '',
            'postal_code' => '',

            // Section 2 - Entity
            'entity_name' => '',
            'national_federation_number' => '',
            'entity_address' => '',
            'entity_postal_code' => '',
            'entity_location' => '',
            'entity_nipc' => '',
            'entity_phone' => '',
            'entity_email' => '',
            'event_director_name' => '',
            'event_director_phone' => '',
            'event_director_email' => '',

            // Section 3 - Previous editions
            'previous_editions' => [],
            'previous_actions' => [],

            // Section 4 - Results forecast
            'forecast_total_participants' => null,
            'forecast_female_athletes' => null,
            'forecast_male_athletes' => null,
            'forecast_technical_officials' => null,
            'forecast_coaches' => null,
            'forecast_clubs' => null,
            'planned_actions' => [],
            'event_link_description' => '',
            'event_benefits_description' => '',
            'event_objectives_description' => '',
            'event_equipment_description' => '',

            // Section 5 - Facilities
            'facilities_checklist' => [],
            'other_facilities' => '',

            // Section 6 - Accommodation
            'logistics_checklist' => [],

            // Section 7 - Safety
            'safety_checklist' => [],
            'pse_responsible_name' => '',
            'pse_responsible_phone' => '',
            'pse_responsible_email' => '',
            'insurances' => [],

            // Section 8 - Promotion
            'promotion_checklist' => [],
            'promotion_description' => '',

            // Section 9 - Partners
            'partners' => [],
            'financing_description' => '',

            // Section 10 - Technical docs
            'technical_documents_description' => '',

            // Section 11 - Expenses
            'expenses' => [
                'infrastructure' => [
                    'installations' => ['qty' => null, 'value' => null],
                    'licenses' => ['qty' => null, 'value' => null],
                    'audiovisual' => ['qty' => null, 'value' => null],
                    'other' => ['qty' => null, 'value' => null],
                ],
                'human_resources' => [
                    'technical_delegate' => ['qty' => null, 'value' => null],
                    'technical_officials' => ['qty' => null, 'value' => null],
                    'chief_technical_officials' => ['qty' => null, 'value' => null],
                    'event_director' => ['qty' => null, 'value' => null],
                    'safety_emergency_manager' => ['qty' => null, 'value' => null],
                    'specialized_technicians' => ['qty' => null, 'value' => null],
                    'other' => ['qty' => null, 'value' => null],
                ],
                'travel' => [
                    'fuel' => ['qty' => null, 'value' => null],
                    'tolls' => ['qty' => null, 'value' => null],
                    'other' => ['qty' => null, 'value' => null],
                ],
                'prizes' => [
                    'medals' => ['qty' => null, 'value' => null],
                    'trophies' => ['qty' => null, 'value' => null],
                    'diplomas' => ['qty' => null, 'value' => null],
                    'other' => ['qty' => null, 'value' => null],
                ],
                'accommodation_food' => [
                    'food' => ['qty' => null, 'value' => null],
                    'accommodation' => ['qty' => null, 'value' => null],
                ],
                'other_expenses' => [
                    'consumables' => ['qty' => null, 'value' => null],
                    'merchandise' => ['qty' => null, 'value' => null],
                    'streaming' => ['qty' => null, 'value' => null],
                    'promotion_plan' => ['qty' => null, 'value' => null],
                ],
            ],

            // Section 12 - Revenue
            'revenue' => [
                'partners' => [],
                'registrations' => [
                    'clubs' => ['qty' => null, 'value' => null],
                    'participants' => ['qty' => null, 'value' => null],
                ],
                'sales' => [
                    'equipment' => ['qty' => null, 'value' => null],
                    'merch' => ['qty' => null, 'value' => null],
                    'stand_rental' => ['qty' => null, 'value' => null],
                    'other' => ['qty' => null, 'value' => null],
                ],
                'other_revenue' => [
                    'meals' => ['qty' => null, 'value' => null],
                    'accommodation' => ['qty' => null, 'value' => null],
                    'equipment_rental' => ['qty' => null, 'value' => null],
                    'other' => ['qty' => null, 'value' => null],
                ],
            ],
        ];
    }

    protected function loadFormFields(): void
    {
        if ($this->mode === 'edit' && $this->application) {
            $this->event_name = $this->application->event_name;
            $this->event_type = 'competition';
            $this->event_category = $this->application->event_category;
            $this->category = $this->application->category;
            $this->sport_id = $this->application->sport_id;
            $this->start_date = $this->application->start_date?->format('Y-m-d') ?? '';
            $this->end_date = $this->application->end_date?->format('Y-m-d') ?? '';
            $this->district_id = $this->application->district_id;
            $this->municipality = $this->application->municipality ?? '';
            $this->target_audience = $this->application->target_audience ?? '';
            $this->expected_participants = $this->application->expected_participants;
        } elseif ($this->template) {
            $this->event_name = $this->template->name;
            $this->event_type = 'competition';
            $this->event_category = $this->template->event_category;
            $this->category = $this->template->category;
            $this->sport_id = $this->template->sport_id;
        }

        // Auto-fill owner data (entity or federation)
        $owner = $this->getOwner();
        if ($owner && empty($this->formData['entity_name'])) {
            $this->formData['entity_name'] = $owner->name;
            $this->formData['entity_address'] = $owner->address ?? '';
            $this->formData['entity_location'] = $owner->location ?? '';
            $this->formData['entity_nipc'] = $owner->vat_number ?? '';
            $this->formData['entity_phone'] = $owner->phone ?? '';
            $this->formData['entity_email'] = $owner->email ?? '';

            if ($owner instanceof Entity) {
                $this->formData['national_federation_number'] = $owner->getNationalFederationNumber() ?? '';
                $this->formData['entity_postal_code'] = $owner->postal_code ?? '';
            } else {
                $this->formData['national_federation_number'] = '';
                $this->formData['entity_postal_code'] = $owner->zip_code ?? '';
            }
        }
    }

    protected function loadSelectOptions(): void
    {
        $this->districts = District::orderBy('name')->pluck('name', 'id')->toArray();
        $this->sports = Sport::orderBy('name')->get()->pluck('translated_name', 'id')->toArray();
    }

    protected function getOwner(): Entity|Federation|null
    {
        if ($this->routeNamespace === 'federation') {
            return auth()->user()?->getFederation();
        }

        return auth()->user()?->getEntity();
    }

    // -- Step Navigation --

    public function nextStep(): void
    {
        $this->resetErrorBag();

        if ($this->validateCurrentStep()) {
            if ($this->currentStep < $this->totalSteps) {
                // Auto-save draft before entering documents or summary step
                if (in_array($this->currentStep + 1, [9, 10]) && ! $this->application) {
                    $this->saveDraft();

                    if (! $this->application) {
                        return;
                    }
                }

                $this->currentStep++;
            }
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function goToStep(int $step): void
    {
        if ($step < 1 || $step > $this->totalSteps) {
            return;
        }

        if ($step < $this->currentStep) {
            $this->currentStep = $step;
        } elseif ($step > $this->currentStep) {
            if ($this->validateCurrentStep()) {
                $this->currentStep = $step;
            }
        }
    }

    // -- Repeaters --

    public function addRepeaterRow(string $section): void
    {
        $templates = [
            'previous_editions' => ['year' => '', 'location' => '', 'name' => '', 'athletes' => null, 'clubs' => null],
            'previous_actions' => ['action' => '', 'agents' => [], 'participants' => null],
            'planned_actions' => ['action' => '', 'agents' => [], 'participants' => null],
            'insurances' => ['type' => '', 'insurer' => '', 'policy_number' => ''],
            'partners' => ['name' => '', 'partnership_type' => '', 'email' => ''],
            'revenue_partners' => ['entity' => '', 'qty' => null, 'value' => null],
        ];

        if ($section === 'revenue_partners') {
            $this->formData['revenue']['partners'][] = $templates['revenue_partners'];
        } elseif (isset($templates[$section])) {
            $this->formData[$section][] = $templates[$section];
        }
    }

    public function removeRepeaterRow(string $section, int $index): void
    {
        if ($section === 'revenue_partners') {
            unset($this->formData['revenue']['partners'][$index]);
            $this->formData['revenue']['partners'] = array_values($this->formData['revenue']['partners']);
        } elseif (isset($this->formData[$section])) {
            unset($this->formData[$section][$index]);
            $this->formData[$section] = array_values($this->formData[$section]);
        }
    }

    // -- Validation --

    protected function validateCurrentStep(): bool
    {
        $rules = $this->rulesForStep($this->currentStep);

        if (! empty($rules)) {
            $this->validate($rules);
        }

        return true;
    }

    protected function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => [
                'event_name' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ],
            2 => [
                'formData.entity_name' => 'required|string|max:255',
                'formData.event_director_name' => 'nullable|string|max:255',
                'formData.event_director_email' => 'nullable|email|max:255',
            ],
            default => [],
        };
    }

    protected function validationAttributes(): array
    {
        return [
            'event_name' => __('event_applications.labels.event_name'),
            'start_date' => __('event_applications.labels.proposed_start_date'),
            'end_date' => __('event_applications.labels.proposed_end_date'),
            'formData.entity_name' => __('event_applications.wizard.labels.entity_name'),
            'formData.event_director_name' => __('event_applications.wizard.labels.event_director_name'),
            'formData.event_director_email' => __('event_applications.wizard.labels.event_director_email'),
        ];
    }

    // -- Save & Submit --

    public function saveDraft(): void
    {
        try {
            $owner = $this->getOwner();

            if (! $owner) {
                Notification::make()
                    ->title(__('event_applications.messages.unauthorized_action'))
                    ->danger()
                    ->send();

                return;
            }

            $data = $this->buildApplicationData($owner);

            if ($this->mode === 'edit' && $this->application) {
                $this->application->update($data);
                $application = $this->application;
                $message = __('event_applications.application_updated_success');
            } else {
                if ($this->template) {
                    $duplicate = app(CheckDuplicateApplicationAction::class)->execute($owner->id, $this->template->id);
                    if ($duplicate) {
                        Notification::make()
                            ->title(__('event_applications.validation.already_applied_to_template'))
                            ->warning()
                            ->send();

                        return;
                    }
                }

                $application = app(CreateApplicationAction::class)->execute($data);
                $this->application = $application;
                $this->mode = 'edit';
                $message = __('event_applications.application_created_success');
            }

            Notification::make()
                ->title($message)
                ->success()
                ->send();
        } catch (\Exception $e) {
            report($e);

            Notification::make()
                ->title(__('event_applications.application_created_error'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function submitApplication(): void
    {
        $this->resetErrorBag();

        // Validate step 1 at minimum
        $this->validate($this->rulesForStep(1));

        try {
            $owner = $this->getOwner();

            if (! $owner) {
                Notification::make()
                    ->title(__('event_applications.messages.unauthorized_action'))
                    ->danger()
                    ->send();

                return;
            }

            $data = $this->buildApplicationData($owner);
            $data['submitted_at'] = now();
            $data['status_class'] = SubmittedApplicationState::class;

            if ($this->mode === 'edit' && $this->application) {
                $this->application->update($data);
                $application = $this->application;
            } else {
                if ($this->template) {
                    $duplicate = app(CheckDuplicateApplicationAction::class)->execute($owner->id, $this->template->id);
                    if ($duplicate) {
                        Notification::make()
                            ->title(__('event_applications.validation.already_applied_to_template'))
                            ->warning()
                            ->send();

                        return;
                    }
                } else {
                    $conflicts = app(CheckForConflictingDirectSubmissionAction::class)->execute(
                        $owner->id,
                        [
                            'event_name' => $this->event_name,
                            'event_type' => $this->event_type,
                            'start_date' => $this->start_date,
                        ]
                    );

                    if ($conflicts->isNotEmpty()) {
                        Notification::make()
                            ->title(__('event_applications.messages.similar_submission_warning'))
                            ->warning()
                            ->persistent()
                            ->send();
                    }
                }

                $application = app(CreateApplicationAction::class)->execute($data);
            }

            Notification::make()
                ->title(__('event_applications.application_submitted_success'))
                ->success()
                ->send();

            $this->redirect(route($this->routeNamespace . '.event-applications.show', $application), navigate: true);
        } catch (\Exception $e) {
            report($e);

            Notification::make()
                ->title(__('event_applications.application_submitted_error'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function cancel(): void
    {
        $this->redirect(route($this->routeNamespace . '.event-applications.index'), navigate: true);
    }

    protected function buildApplicationData(Model $owner): array
    {
        return [
            'application_type' => $this->template
                ? EventApplicationTypeEnum::FederationInitiated->value
                : EventApplicationTypeEnum::DirectSubmission->value,
            'template_id' => $this->template?->id,
            'entity_id' => $owner->id,
            'entity_type' => $owner->getMorphClass(),
            'status_class' => DraftApplicationState::class,
            'event_name' => $this->event_name,
            'event_type' => $this->event_type,
            'event_category' => $this->event_category,
            'category' => $this->category,
            'sport_id' => $this->sport_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'district_id' => $this->district_id,
            'municipality' => $this->municipality,
            'responsible_name' => $this->formData['event_director_name'] ?? '',
            'responsible_phone' => $this->formData['event_director_phone'] ?? '',
            'target_audience' => $this->target_audience,
            'expected_participants' => $this->expected_participants,
            'form_data' => $this->formData,
        ];
    }

    public function getStepTitle(): string
    {
        return match ($this->currentStep) {
            1 => __('event_applications.wizard.steps.event'),
            2 => __('event_applications.wizard.steps.entity'),
            3 => __('event_applications.wizard.steps.history'),
            4 => __('event_applications.wizard.steps.results'),
            5 => __('event_applications.wizard.steps.logistics'),
            6 => __('event_applications.wizard.steps.safety'),
            7 => __('event_applications.wizard.steps.partners'),
            8 => __('event_applications.wizard.steps.budget'),
            9 => __('event_applications.wizard.steps.documents'),
            10 => __('event_applications.wizard.steps.summary'),
            default => '',
        };
    }

    public function getSteps(): array
    {
        return [
            ['number' => 1, 'title' => __('event_applications.wizard.steps.event')],
            ['number' => 2, 'title' => __('event_applications.wizard.steps.entity')],
            ['number' => 3, 'title' => __('event_applications.wizard.steps.history')],
            ['number' => 4, 'title' => __('event_applications.wizard.steps.results')],
            ['number' => 5, 'title' => __('event_applications.wizard.steps.logistics')],
            ['number' => 6, 'title' => __('event_applications.wizard.steps.safety')],
            ['number' => 7, 'title' => __('event_applications.wizard.steps.partners')],
            ['number' => 8, 'title' => __('event_applications.wizard.steps.budget')],
            ['number' => 9, 'title' => __('event_applications.wizard.steps.documents')],
            ['number' => 10, 'title' => __('event_applications.wizard.steps.summary')],
        ];
    }

    public function render()
    {
        if ($this->application) {
            $this->application->load('comments.user');
        }

        return view('livewire.event-applications.entity.application-form-wizard');
    }
}
