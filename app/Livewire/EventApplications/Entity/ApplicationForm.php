<?php

namespace App\Livewire\EventApplications\Entity;

use App\Enums\EventApplicationTypeEnum;
use App\Enums\EvtEventOrganizationCategoryEnum;
use Domain\Entities\Models\Entity;
use Domain\EventApplications\Actions\CheckDuplicateApplicationAction;
use Domain\EventApplications\Actions\CheckForConflictingDirectSubmissionAction;
use Domain\EventApplications\Actions\CreateApplicationAction;
use Domain\EventApplications\Models\ApplicationTemplate;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\DraftApplicationState;
use Domain\EvtEvents\Models\Sport;
use Domain\Geographic\Models\District;
use Filament\Notifications\Notification;
use Livewire\Component;

class ApplicationForm extends Component
{
    public ?ApplicationTemplate $template = null;
    public ?EventApplication $application = null;
    public string $mode = 'create';

    public $event_name = '';
    public $event_type = '';
    public $sport_id = null;
    public $start_date = '';
    public $end_date = '';
    public $district_id = null;
    public $municipality = '';
    public $responsible_name = '';
    public $responsible_phone = '';
    public $target_audience = '';
    public $expected_participants = null;
    public $event_category = null;

    public $districts = [];
    public $sports = [];
    public $eventTypes = [];
    public $eventCategories = [];

    protected function rules(): array
    {
        return [
            'event_name' => 'required|string|max:255',
            'event_type' => 'required|in:organization,competition',
            'event_category' => 'required_if:event_type,organization|nullable|string',
            'sport_id' => 'nullable|exists:sports,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'district_id' => 'nullable|exists:districts,id',
            'municipality' => 'nullable|string|max:255',
            'responsible_name' => 'required|string|max:255',
            'responsible_phone' => 'required|string|max:20',
            'target_audience' => 'nullable|string|max:500',
            'expected_participants' => 'nullable|integer|min:1',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'event_name' => __('event_applications.labels.event_name'),
            'event_type' => __('event_applications.labels.event_type'),
            'event_category' => __('event_applications.labels.event_category'),
            'sport_id' => __('event_applications.labels.sport'),
            'start_date' => __('event_applications.labels.start_date'),
            'end_date' => __('event_applications.labels.end_date'),
            'district_id' => __('event_applications.labels.district'),
            'municipality' => __('event_applications.labels.municipality'),
            'responsible_name' => __('event_applications.labels.responsible_name'),
            'responsible_phone' => __('event_applications.labels.responsible_phone'),
            'target_audience' => __('event_applications.labels.target_audience'),
            'expected_participants' => __('event_applications.labels.expected_participants'),
        ];
    }

    public function mount(): void
    {
        $this->loadFormData();
        $this->loadSelectOptions();
    }

    protected function loadFormData(): void
    {
        if ($this->mode === 'edit' && $this->application) {
            $this->event_name = $this->application->event_name;
            $this->event_type = $this->application->event_type;
            $this->event_category = $this->application->event_category;
            $this->sport_id = $this->application->sport_id;
            $this->start_date = $this->application->start_date?->format('Y-m-d');
            $this->end_date = $this->application->end_date?->format('Y-m-d');
            $this->district_id = $this->application->district_id;
            $this->municipality = $this->application->municipality;
            $this->responsible_name = $this->application->responsible_name;
            $this->responsible_phone = $this->application->responsible_phone;
            $this->target_audience = $this->application->target_audience;
            $this->expected_participants = $this->application->expected_participants;
        } elseif ($this->template) {
            $this->event_name = $this->template->name;
            $this->event_type = $this->template->event_type;
            $this->event_category = $this->template->event_category;
            $this->sport_id = $this->template->sport_id;
        }
    }

    protected function loadSelectOptions(): void
    {
        $this->districts = District::orderBy('name')->pluck('name', 'id')->toArray();
        $this->sports = Sport::orderBy('name')->pluck('name', 'id')->toArray();
        $this->eventTypes = [
            'organization' => __('event_applications.event_types.organization'),
            'competition' => __('event_applications.event_types.competition'),
        ];
        $this->eventCategories = EvtEventOrganizationCategoryEnum::getGroupedOptions();
    }

    public function saveDraft(): void
    {
        $this->validate();

        try {
            $entity = auth()->user()->getEntity();

            if (! $entity) {
                Notification::make()
                    ->title(__('event_applications.messages.unauthorized_action'))
                    ->danger()
                    ->send();

                return;
            }

            $data = $this->getFormData($entity);

            if ($this->mode === 'edit' && $this->application) {
                $this->application->update($data);
                $application = $this->application;
                $message = __('event_applications.application_updated_success');
            } else {
                // Check for duplicate applications
                if ($this->template) {
                    $checkDuplicate = app(CheckDuplicateApplicationAction::class);
                    $duplicate = $checkDuplicate->execute($entity->id, $this->template->id);

                    if ($duplicate) {
                        Notification::make()
                            ->title(__('event_applications.validation.already_applied_to_template'))
                            ->warning()
                            ->send();

                        return;
                    }
                }

                $createAction = app(CreateApplicationAction::class);
                $application = $createAction->execute($data);
                $message = __('event_applications.application_created_success');
            }

            Notification::make()
                ->title($message)
                ->success()
                ->send();

            $this->redirect(route('entity.event-applications.show', $application), navigate: true);

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
        $this->validate();

        try {
            $entity = auth()->user()->getEntity();

            if (! $entity) {
                Notification::make()
                    ->title(__('event_applications.messages.unauthorized_action'))
                    ->danger()
                    ->send();

                return;
            }

            $data = $this->getFormData($entity);
            $data['submitted_at'] = now();
            $data['status_class'] = \Domain\EventApplications\States\SubmittedApplicationState::class;

            if ($this->mode === 'edit' && $this->application) {
                $this->application->update($data);
                $application = $this->application;
            } else {
                // Check for duplicate applications
                if ($this->template) {
                    $checkDuplicate = app(CheckDuplicateApplicationAction::class);
                    $duplicate = $checkDuplicate->execute($entity->id, $this->template->id);

                    if ($duplicate) {
                        Notification::make()
                            ->title(__('event_applications.validation.already_applied_to_template'))
                            ->warning()
                            ->send();

                        return;
                    }
                } else {
                    // Warn about similar direct submissions
                    $checkConflict = app(CheckForConflictingDirectSubmissionAction::class);
                    $conflicts = $checkConflict->execute(
                        $entity->id,
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

                $createAction = app(CreateApplicationAction::class);
                $application = $createAction->execute($data);
            }

            Notification::make()
                ->title(__('event_applications.application_submitted_success'))
                ->success()
                ->send();

            $this->redirect(route('entity.event-applications.show', $application), navigate: true);

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
        $this->redirect(route('entity.event-applications.index'), navigate: true);
    }

    protected function getFormData(Entity $entity): array
    {
        return [
            'application_type' => $this->template
                ? EventApplicationTypeEnum::FederationInitiated->value
                : EventApplicationTypeEnum::DirectSubmission->value,
            'template_id' => $this->template?->id,
            'entity_id' => $entity->id,
            'entity_type' => $entity->getMorphClass(),
            'status_class' => DraftApplicationState::class,
            'event_name' => $this->event_name,
            'event_type' => $this->event_type,
            'event_category' => $this->event_category,
            'sport_id' => $this->sport_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'district_id' => $this->district_id,
            'municipality' => $this->municipality,
            'responsible_name' => $this->responsible_name,
            'responsible_phone' => $this->responsible_phone,
            'target_audience' => $this->target_audience,
            'expected_participants' => $this->expected_participants,
        ];
    }

    public function render()
    {
        return view('livewire.event-applications.entity.application-form');
    }
}
