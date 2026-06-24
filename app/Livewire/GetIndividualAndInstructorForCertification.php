<?php

namespace App\Livewire;

use App\Models\Committee;
use Domain\Certifications\Actions\GetCertificationsFromInstructorAction;
use Domain\Certifications\Models\Certification;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Individuals\Actions\DetectIfIndividualIsInstructorAction;
use Domain\Individuals\Models\Individual;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class GetIndividualAndInstructorForCertification extends Component
{
    public $federationId;
    public $entityId;
    public $federations = [];
    public $entities = [];
    public $selectedEntity;
    public $selectedFederation = null;
    public bool $is_federation = false;
    public bool $is_admin = false;
    public bool $is_entity = false;
    public bool $showdivindividual = false;
    public bool $showdivinstructor = false;
    public bool $showdivassistantinstructor = false;
    public bool $federationApprove = false;
    public string $codeIndividual = '';
    public string $codeInstructor = '';
    public string $codeAssistantInstructor = '';
    public $individual = [];
    public $instructor = null;
    public $assistantInstructor = [];
    public string $selected_license_error_individual = '';
    public string $selected_license_error_instructor = '';
    public string $selected_license_error_assistant_instructor = '';
    public $certifications = [];
    public $passedCertifications = [];
    public ?string $committee_code = '';
    public $showIndividualSelectorModal = false;
    protected $listeners = ['individualSelected' => 'onIndividualSelected'];

    public function mount($certifications = [])
    {
        $this->passedCertifications = $certifications;
        $this->initializeFederation();
    }

    public function render(): View
    {
        $this->handleUserAssociations();
        $this->loadCertificationsIfNeeded();
        $this->handleFederationApproval();

        return view('livewire.get-individual-and-instructor-for-certification');
    }

    private function handleUserAssociations(): void
    {
        $user = auth()->user();
        if ($user->federations()->exists() && ! $user->entities()->exists()) {
            $this->selectedFederation = $this->selectedFederation ?: $user->federations()->first()->id;
            $this->searchEntitiesFromFederation();
        }

        if ($user->entities()->exists()) {
            $this->selectedEntity = $user->entities()->first()->id;
        }
    }

    private function loadCertificationsIfNeeded(): void
    {
        if (! empty($this->selectedFederation) && ! empty($this->instructor)) {
            $this->searchLicensesToInstructors();
        }
    }

    private function handleFederationApproval(): void
    {
        if ($this->federationApprove) {
            // For admin, use all certifications passed from controller
            if ($this->is_admin && ! empty($this->passedCertifications)) {
                $this->certifications = $this->passedCertifications;
            } else {
                $this->certifications = $this->getCertificationsForCommittee();
            }
        }
    }

    private function getCertificationsForCommittee(): Collection
    {
        $query = Certification::query();

        // Filter by committee if provided
        if (! empty($this->committee_code)) {
            $committeeId = Committee::where('code', $this->committee_code)->value('id');
            if ($committeeId) {
                $query->where('committee_id', $committeeId);
            }
        }

        // Filter by federation's allowed licenses
        if ($this->selectedFederation) {
            $allowedLicenseIds = \Domain\Federations\Models\Federation::find($this->selectedFederation)
                ->licenses()
                ->pluck('license_id')
                ->toArray();

            if (! empty($allowedLicenseIds)) {
                $query->whereIn('license_id', $allowedLicenseIds);
            }
        }

        return $query->orderBy('name')->get(['id', 'name']);
    }

    public function searchEntitiesFromFederation(): void
    {
        if (auth()->user()->group()->first()->code !== 'FEDERATION') {
            return;
        }

        $committeeId = Committee::where('code', $this->committee_code)->value('id');
        $cacheKey = "entities_federation_{$this->selectedFederation}_committee_{$committeeId}";

        $this->entities = Cache::remember($cacheKey, now()->addMinutes(1), function () use ($committeeId) {
            return Entity::select('id', 'name')
                ->whereHas('federations', function (Builder $query) {
                    $query->where('federation_id', $this->selectedFederation)
                        ->where('entity_federation.status_class', ActiveEntityFederationState::class);
                })
                ->whereHas('licenses', function (Builder $query) use ($committeeId) {
                    $query->whereHas('license', function (Builder $query) use ($committeeId) {
                        $query->where('committee_id', $committeeId);
                    });
                })
                ->orderBy('name')
                ->get();
        });
    }

    private function searchLicensesToInstructors(): void
    {
        if (empty($this->selectedFederation)) {
            $this->selected_license_error_instructor = __('You need to choose a Federation first');

            return;
        }

        // For admin, use all certifications passed from controller
        if ($this->is_admin && ! empty($this->passedCertifications)) {
            $this->certifications = $this->passedCertifications;

            return;
        }

        $instructorCertifications = new GetCertificationsFromInstructorAction;
        $certifications = $instructorCertifications($this->instructor, $this->selectedFederation, $this->committee_code);

        if (empty($certifications) || $certifications->isEmpty()) {
            $this->certifications = collect();

            return;
        }

        $this->filterCertificationsByCommittee($certifications);
    }

    private function filterCertificationsByCommittee($certifications): void
    {
        // Filter by committee if provided
        if (! empty($this->committee_code)) {
            $committeeId = Committee::where('code', $this->committee_code)->value('id');
            $certifications = $committeeId ? $certifications->where('committee_id', $committeeId) : collect();
        }

        // Filter by federation's allowed licenses
        if ($this->selectedFederation) {
            $allowedLicenseIds = \Domain\Federations\Models\Federation::find($this->selectedFederation)
                ->licenses()
                ->pluck('license_id')
                ->toArray();

            if (! empty($allowedLicenseIds)) {
                $certifications = $certifications->filter(function ($certification) use ($allowedLicenseIds) {
                    return in_array($certification->license_id, $allowedLicenseIds);
                });
            }
        }

        $this->certifications = $certifications;
    }

    public function searchIndividual(): void
    {
        if (empty($this->codeIndividual)) {
            return;
        }

        $individualSearch = Individual::where('member_code', $this->codeIndividual)
            ->individualsFromEntity()
            ->first(['id', 'member_code', 'name', 'surname']);

        if (! $individualSearch) {
            $this->selected_license_error_individual = "Can't find a student with that code.";

            return;
        }

        if ($this->isIndividualAlreadySelected($individualSearch)) {
            $this->selected_license_error_individual = 'This student is already selected.';

            return;
        }

        $this->individual[] = $individualSearch->toArray();
        $this->selected_license_error_individual = '';
        $this->codeIndividual = '';
    }

    private function isIndividualAlreadySelected($individualSearch): bool
    {
        return collect($this->individual)->contains('id', $individualSearch->id);
    }

    public function searchInstructor(DetectIfIndividualIsInstructorAction $checkInstructor): void
    {
        $this->initializeFederation();

        if (empty($this->selectedFederation)) {
            $this->selected_license_error_instructor = __('Need to choose a Federation first');

            return;
        }

        if (empty($this->codeInstructor)) {
            $this->selected_license_error_instructor = __('Need to choose an Instructor first');

            return;
        }

        $entityId = $this->getEntityId();
        $individualQuery = $this->buildInstructorQuery($entityId);

        if (! $individualQuery->exists() || ! $checkInstructor($individualQuery)) {
            $this->selected_license_error_instructor = __('The individual member associated with this international code does not hold an instructor certification, does not hold an active instructor licence or does not belong to a Diving entity.');

            return;
        }

        $instructor = $individualQuery->first();

        if ($this->isInstructorAlreadySelected($instructor)) {
            $this->selected_license_error_instructor = __('This instructor is already selected');

            return;
        }

        $this->instructor = $instructor;
        $this->selected_license_error_instructor = '';

        if (! $this->federationApprove) {
            $this->searchLicensesToInstructors();
        }

        $this->codeInstructor = '';
    }

    private function getEntityId(): ?int
    {
        return $this->selectedEntity ?: (auth()->user()->entities()->exists() ? auth()->user()->entities()->first()->id : null);
    }

    private function buildInstructorQuery(?int $entityId): Builder
    {
        $query = Individual::where('member_code', $this->codeInstructor)
            ->IndividualFromFederation($this->selectedFederation)
            ->with('licenses');

        if (! empty($entityId)) {
            $query->whereHas('professionalRoleEntities', function (Builder $query) use ($entityId) {
                $query->where('entity_id', $entityId)
                    ->where('status_class', ActiveEntityProfessionalRoleState::class)
                    ->whereHas('professionalRole', function (Builder $query) {
                        $query->where('role', 'like', '%INSTRUCTOR%');
                    });
            });
        }

        return $query;
    }

    private function isInstructorAlreadySelected($instructor): bool
    {
        return isset($this->instructor) && $this->instructor->id == $instructor->id;
    }

    public function searchAssistantInstructor(DetectIfIndividualIsInstructorAction $checkInstructor): void
    {
        if (empty($this->codeAssistantInstructor)) {
            return;
        }

        if ($this->isAssistantSameAsDirector()) {
            $this->selected_license_error_assistant_instructor = __("The Director Instructor can't be Assistant Instructor too");

            return;
        }

        $individualQuery = $this->buildAssistantInstructorQuery();

        if (! $individualQuery->exists() || ! $checkInstructor($individualQuery)) {
            $this->selected_license_error_assistant_instructor = __('The individual member associated with this international code does not hold an instructor certification, does not hold an active instructor licence or does not belong to a Diving entity.');

            return;
        }

        $assistant = $individualQuery->first();

        if ($this->isAssistantAlreadySelected($assistant)) {
            $this->selected_license_error_assistant_instructor = __('This assistant instructor is already selected');

            return;
        }
        // Ensure $this->assistantInstructor is an array before adding to it
        if (! is_array($this->assistantInstructor)) {
            $this->assistantInstructor = [];
        }

        $this->assistantInstructor[] = $assistant->only(['member_code', 'name', 'surname', 'id']);
        $this->selected_license_error_assistant_instructor = '';
        $this->codeAssistantInstructor = '';
    }

    private function isAssistantSameAsDirector(): bool
    {
        return $this->instructor && $this->codeAssistantInstructor == $this->instructor->member_code;
    }

    private function buildAssistantInstructorQuery(): Builder
    {
        return Individual::where('member_code', $this->codeAssistantInstructor)
            ->individualsFromEntity()
            ->with('licenses');
    }

    private function isAssistantAlreadySelected($assistant): bool
    {
        return in_array($assistant->id, array_column($this->assistantInstructor, 'id'));
    }

    private function initializeFederation(): void
    {
        $user = auth()->user();
        if ($user->federations()->exists() && empty($this->selectedFederation)) {
            $this->selectedFederation = $user->federations()->first()->id;
        }
        if ($user->entities()->exists() && empty($this->selectedFederation)) {
            $this->selectedFederation = $user->entities()->first()->federations()->first()->id;
        }
    }

    public function removeItem(string $type, $index = null): void
    {
        switch ($type) {
            case 'INDIVIDUAL':
                if (isset($index)) {
                    unset($this->individual[$index]);
                    $this->individual = array_values($this->individual);
                }
                break;
            case 'INSTRUCTOR':
                $this->instructor = null;
                break;
            case 'ASSISTANT':
                if (isset($index)) {
                    unset($this->assistantInstructor[$index]);
                    $this->assistantInstructor = array_values($this->assistantInstructor);
                }
                break;
        }
    }
}
