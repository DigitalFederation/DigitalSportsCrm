<?php

namespace App\Livewire\Federation;

use App\Enums\MembershipTargetType;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Actions\BulkMemberSubscriptionAction;
use Domain\Memberships\Models\MembershipPackage;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class CreateIndividualMembership extends Component
{
    public $federation;
    public $availablePackages;
    public $entities;
    public $isMainFederation = false;

    // Form fields
    public $selectedPackageId = '';
    public $selectedEntityId = '';
    public $selectedIndividuals = [];

    // Dynamic data
    public $selectedPackage = null;
    public $availableIndividuals = [];
    public $loadingIndividuals = false;

    protected $rules = [
        'selectedPackageId' => 'required|exists:membership_packages,id',
        'selectedEntityId' => 'nullable|exists:entity,id',
        'selectedIndividuals' => 'required|array|min:1',
        'selectedIndividuals.*' => 'required|exists:individual,id',
    ];

    // UI state
    public $isSubmitting = false;

    protected function messages()
    {
        return [
            'selectedPackageId.required' => __('federation.individual_memberships.validation.package_required'),
            'selectedPackageId.exists' => __('federation.individual_memberships.validation.package_invalid'),
            'selectedEntityId.required' => __('federation.individual_memberships.validation.entity_required'),
            'selectedEntityId.exists' => __('federation.individual_memberships.validation.entity_invalid'),
            'selectedIndividuals.required' => __('federation.individual_memberships.validation.individuals_required'),
            'selectedIndividuals.min' => __('federation.individual_memberships.validation.individuals_min'),
        ];
    }

    public function mount()
    {
        $user = Auth::user();
        if (! $user || ! $user->isFederation()) {
            abort(403, __('federation.common.unauthorized_action'));
        }

        $this->federation = $user->federations()->first();
        if (! $this->federation) {
            abort(403, __('federation.common.federation_not_found'));
        }

        // Check if this is the main federation
        $this->isMainFederation = $this->federation->is_default_federation ?? false;

        // Get available membership packages (with affiliation plans)
        $this->availablePackages = $this->getAvailablePackagesForIndividuals();

        // Get entities based on federation type
        if ($this->isMainFederation) {
            // Main federation can see all entities
            $this->entities = Entity::orderBy('name')->get();
        } else {
            // Regular federation - only see entities under this federation
            $this->entities = Entity::whereHas('federations', function ($query) {
                $query->select('federation.id')
                    ->where('federation.id', $this->federation->id)
                    ->where('entity_federation.status_class', ActiveEntityFederationState::class);
            })
                ->orderBy('name')
                ->get();
        }
    }

    public function updatedSelectedPackageId($value)
    {
        if ($value) {
            $this->selectedPackage = MembershipPackage::with(['affiliationPlans', 'insurancePlans'])
                ->find($value);
        } else {
            $this->selectedPackage = null;
        }

        // Reset individuals when package changes
        $this->selectedIndividuals = [];

        // Load individuals based on entity selection
        if ($this->selectedEntityId) {
            $this->loadEntityIndividuals();
        } elseif ($value) {
            // If no entity selected but package is selected, load federation individuals
            $this->loadFederationIndividuals();
        }
    }

    public function updatedSelectedEntityId($value)
    {
        if ($this->selectedPackageId) {
            if ($value) {
                $this->loadEntityIndividuals();
            } else {
                $this->loadFederationIndividuals();
            }
        } else {
            $this->availableIndividuals = [];
            $this->selectedIndividuals = [];
        }
    }

    public function loadEntityIndividuals()
    {
        if (! $this->selectedEntityId || ! $this->selectedPackageId) {
            $this->availableIndividuals = [];

            return;
        }

        $this->loadingIndividuals = true;
        $this->selectedIndividuals = [];

        $entity = Entity::find($this->selectedEntityId);
        $package = MembershipPackage::find($this->selectedPackageId);

        if (! $entity || ! $package) {
            $this->availableIndividuals = [];
            $this->loadingIndividuals = false;

            return;
        }

        // Get eligible individuals (those without active subscriptions for this package)
        if ($this->isMainFederation) {
            // Main federation can see all individuals belonging to the entity
            $this->availableIndividuals = Individual::query()
                ->whereHas('entities', function ($query) use ($entity) {
                    $query->where('entity_id', $entity->id);
                })
                ->whereDoesntHave('memberSubscriptions', function ($query) use ($package) {
                    $query->where('membership_package_id', $package->id)
                        ->where('end_date', '>', now());
                })
                ->select(['id', 'name', 'member_code', 'email'])
                ->orderBy('name')
                ->get();
        } else {
            // Regular federation - only see individuals from entities under this federation
            $this->availableIndividuals = Individual::query()
                ->whereHas('entities', function ($query) use ($entity) {
                    $query->where('entity_id', $entity->id);
                })
                ->whereDoesntHave('memberSubscriptions', function ($query) use ($package) {
                    $query->where('membership_package_id', $package->id)
                        ->where('end_date', '>', now());
                })
                ->select(['id', 'name', 'member_code', 'email'])
                ->orderBy('name')
                ->get();
        }

        $this->loadingIndividuals = false;
    }

    public function loadFederationIndividuals()
    {
        if (! $this->selectedPackageId) {
            $this->availableIndividuals = [];

            return;
        }

        $this->loadingIndividuals = true;
        $this->selectedIndividuals = [];

        $package = MembershipPackage::find($this->selectedPackageId);

        if (! $package) {
            $this->availableIndividuals = [];
            $this->loadingIndividuals = false;

            return;
        }

        if ($this->isMainFederation) {
            // Main federation can see ALL individuals in the system
            $this->availableIndividuals = Individual::query()
                ->whereDoesntHave('memberSubscriptions', function ($query) use ($package) {
                    $query->where('membership_package_id', $package->id)
                        ->where('end_date', '>', now());
                })
                ->select(['id', 'name', 'member_code', 'email'])
                ->orderBy('name')
                ->get();
        } else {
            // Regular federation - only see individuals from entities under this federation
            $this->availableIndividuals = Individual::query()
                ->whereHas('entities', function ($query) {
                    $query->whereHas('federations', function ($fedQuery) {
                        $fedQuery->where('federation.id', $this->federation->id)
                            ->where('entity_federation.status_class', ActiveEntityFederationState::class);
                    });
                })
                ->whereDoesntHave('memberSubscriptions', function ($query) use ($package) {
                    $query->where('membership_package_id', $package->id)
                        ->where('end_date', '>', now());
                })
                ->select(['id', 'name', 'member_code', 'email'])
                ->orderBy('name')
                ->get();
        }

        $this->loadingIndividuals = false;
    }

    public function toggleAllIndividuals()
    {
        if (count($this->selectedIndividuals) === count($this->availableIndividuals)) {
            $this->selectedIndividuals = [];
        } else {
            $this->selectedIndividuals = $this->availableIndividuals->pluck('id')->toArray();
        }
    }

    public function submit(BulkMemberSubscriptionAction $action)
    {
        $this->validate();
        $this->isSubmitting = true;

        try {
            DB::beginTransaction();

            $package = MembershipPackage::with(['affiliationPlans', 'insurancePlans'])
                ->findOrFail($this->selectedPackageId);

            // Verify the package has affiliation plans (membership requirement)
            if ($package->affiliationPlans->isEmpty()) {
                throw new \Exception(__('federation.individual_memberships.package_must_have_affiliation_plans'));
            }

            // Determine requester based on entity selection
            if ($this->selectedEntityId) {
                $entity = Entity::findOrFail($this->selectedEntityId);

                // Verify that this entity belongs to this federation
                if (! $entity->federations()->where('federation.id', $this->federation->id)
                    ->where('entity_federation.status_class', ActiveEntityFederationState::class)->exists()) {
                    throw new \Exception(__('federation.individual_memberships.entity_not_in_federation'));
                }

                $requester = $entity;
                $requestType = 'entity_group';
            } else {
                // Federation is facilitating directly for individuals
                $requester = $this->federation;
                $requestType = 'federation_facilitated';
            }

            // Execute the bulk subscription action
            $results = $action->execute(
                $package,
                $this->selectedIndividuals,
                $requester, // Entity or Federation based on selection
                $requestType // entity_group or federation_facilitated
            );

            DB::commit();

            // Handle the results
            $successCount = count($results['success']);
            $failedCount = count($results['failed']);

            if ($successCount > 0) {
                Notification::make()
                    ->title(__('federation.individual_memberships.memberships_created'))
                    ->body($this->selectedEntityId
                        ? __('federation.individual_memberships.success_created_for_entity', ['count' => $successCount, 'entity' => $entity->name])
                        : __('federation.individual_memberships.success_created', ['count' => $successCount]))
                    ->success()
                    ->send();
            }

            if ($failedCount > 0) {
                Log::warning('Some individual membership subscriptions failed to process', [
                    'failed_subscriptions' => $results['failed'],
                    'entity_id' => $this->selectedEntityId,
                    'federation_id' => $this->federation->id,
                    'package_id' => $package->id,
                ]);

                Notification::make()
                    ->title(__('federation.individual_memberships.some_memberships_failed'))
                    ->body(__('federation.individual_memberships.failed_count_admin_notified', ['count' => $failedCount]))
                    ->warning()
                    ->send();
            }

            // Success notification already sent via Filament Notification

            return redirect()->route('federation.individual-affiliations.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process individual membership subscriptions', [
                'error' => $e->getMessage(),
                'entity_id' => $this->selectedEntityId,
                'federation_id' => $this->federation->id,
                'package_id' => $this->selectedPackageId,
            ]);

            Notification::make()
                ->title(__('federation.individual_memberships.error_processing_title'))
                ->body(__('federation.individual_memberships.error_processing_body'))
                ->danger()
                ->send();

            // Error notification already sent via Filament Notification
        }
    }

    private function getAvailablePackagesForIndividuals(): Collection
    {
        return MembershipPackage::where('is_active', true)
            ->whereIn('target_type', [MembershipTargetType::INDIVIDUAL, MembershipTargetType::BOTH])
            ->where(function ($query) {
                $query->whereJsonContains('distribution_methods', 'direct')
                    ->orWhereJsonContains('distribution_methods', 'federation_managed');
            })
            ->whereHas('affiliationPlans') // Must have affiliation plans for memberships
            ->whereHas('federations', function ($query) {
                $query->where('federation.id', $this->federation->id);
            })
            ->with(['affiliationPlans', 'insurancePlans'])
            ->get();
    }

    public function render()
    {
        return view('livewire.federation.create-individual-membership');
    }
}
