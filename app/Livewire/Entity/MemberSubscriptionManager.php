<?php

namespace App\Livewire\Entity;

use App\Enums\MembershipTargetType;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Actions\BulkMemberSubscriptionAction;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Services\ValidationPlanPrivilegeService;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class MemberSubscriptionManager extends Component
{
    use WithPagination;

    public $insurance_filter = false;
    public ?int $selectedPackageId = null;
    public array $selectedIndividuals = [];
    public string $search = '';
    public bool $confirmingSubscription = false;
    public string $statusFilter = 'all';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';
    public bool $hasValidationPlanPrivileges = true;
    public string $validationPlanMessage = '';
    public bool $selectionTrayExpanded = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'page' => ['except' => 1],
    ];

    public function mount(bool $insurance_filter)
    {
        $this->checkEntityAccess();
        $this->checkValidationPlanPrivileges();
        $this->insurance_filter = $insurance_filter;
    }

    #[Computed]
    public function availablePackages(): Collection
    {
        $entity = auth()->user()->getEntity();
        $entityFederationIds = $entity->federations()
            // ->whereNotNull('parent_id') // Exclude main federation
            ->pluck('federation.id')
            ->toArray();

        return MembershipPackage::query()
            ->where('is_active', true)
            ->where('target_type', MembershipTargetType::INDIVIDUAL)
            ->whereJsonContains('distribution_methods', 'entity_managed')
            ->whereHas('federations', function ($query) use ($entityFederationIds) {
                $query->whereIn('federation.id', $entityFederationIds);
            })
            ->when($this->insurance_filter, function ($query) {
                // When insurance-only filter is active, show ONLY packages that have insurance plans
                // and NO affiliation plans (insurance-only packages)
                $query->whereHas('insurancePlans', function ($query) {
                    $query->where(function ($q) {
                        $q->whereNull('start_date')
                            ->orWhere('start_date', '<=', now());
                    })->where(function ($q) {
                        $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', now());
                    });
                })->whereDoesntHave('affiliationPlans');
            }, function ($query) {
                // Original affiliation plans query for regular packages
                $query->whereHas('affiliationPlans', function ($query) {
                    $query->where(function ($q) {
                        // Include plans with type='individual' OR plans with type='entity' that have individual_fee set
                        $q->where('affiliation_plans.type', 'individual')
                            ->orWhere(function ($subQ) {
                                $subQ->where('affiliation_plans.type', 'entity')
                                    ->whereNotNull('affiliation_plans.individual_fee')
                                    ->where('affiliation_plans.individual_fee', '>', 0);
                            });
                    })->where(function ($q) {
                        $q->whereNull('start_date')
                            ->orWhere('start_date', '<=', now());
                    })->where(function ($q) {
                        $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', now());
                    });
                });
            })
            ->with(['affiliationPlans', 'insurancePlans', 'federations'])
            ->get();
    }

    #[Computed]
    public function selectedPackage(): ?MembershipPackage
    {
        return $this->selectedPackageId
            ? MembershipPackage::with(['affiliationPlans', 'insurancePlans'])->find($this->selectedPackageId)
            : null;
    }

    public function getIndividualsProperty()
    {
        return Individual::query()
            ->whereHas('entities', function ($query) {
                $query->where('entity_id', auth()->user()->getEntityId());
            })
            ->when($this->selectedPackageId, function ($query) {
                $package = $this->selectedPackage;

                if ($package) {
                    // Determine package type
                    $hasAffiliationPlans = $package->affiliationPlans->isNotEmpty();
                    $hasInsurancePlans = $package->insurancePlans->isNotEmpty();
                    $hasValidationPlanInPackage = $package->affiliationPlans->contains(fn ($plan) => $plan->is_validation_plan);

                    // PACKAGE-DEPENDENT FILTERING LOGIC:
                    // 1. If package has ONLY validation plans (base membership):
                    //    - SHOW individuals WITHOUT active validation plans (they need a base membership)
                    //    - Later filtering will exclude those who already have ALL plans
                    // 2. If package has validation + non-validation plans (mixed):
                    //    - SHOW all (they might need the non-validation parts even if they have validation)
                    //    - Later filtering will exclude those who already have ALL plans
                    // 3. If package has NO validation plans (add-ons/complementary):
                    //    - SHOW individuals WITH active validation plans (they're eligible for add-ons)
                    //    - HIDE individuals WITHOUT active validation plans (not eligible without base)
                    // 4. If package is insurance-only (no affiliation plans):
                    //    - SHOW individuals WITH active validation plans (required for insurance)
                    //    - HIDE individuals WITHOUT active validation plans (can't get insurance without base)

                    $hasOnlyValidationPlans = $hasValidationPlanInPackage &&
                        $package->affiliationPlans->every(fn ($plan) => $plan->is_validation_plan);

                    if ($hasAffiliationPlans) {
                        if ($hasOnlyValidationPlans) {
                            // Package contains ONLY validation plans - show only those WITHOUT active validation
                            $query->whereDoesntHave('memberSubscriptions.affiliations', function ($q) {
                                $q->where('affiliations.end_date', '>=', now())
                                    ->where('affiliations.status_class', \Domain\Memberships\States\ActiveAffiliationState::class)
                                    ->whereHas('memberSubscription.membershipPackage.affiliationPlans', function ($subQ) {
                                        $subQ->where('is_validation_plan', true);
                                    });
                            });
                        } elseif (! $hasValidationPlanInPackage) {
                            // Package has NO validation plans (add-on/complementary) - show only those WITH active validation
                            $query->whereHas('memberSubscriptions.affiliations', function ($q) {
                                $q->where('affiliations.end_date', '>=', now())
                                    ->where('affiliations.status_class', \Domain\Memberships\States\ActiveAffiliationState::class)
                                    ->whereHas('memberSubscription.membershipPackage.affiliationPlans', function ($subQ) {
                                        $subQ->where('is_validation_plan', true);
                                    });
                            });
                        }
                        // If package has BOTH validation AND non-validation plans: show all
                        // The duplicate plan filtering below will handle exclusions
                    } elseif (! $hasAffiliationPlans && $hasInsurancePlans) {
                        // Insurance-only package - require active validation plan
                        $query->whereHas('memberSubscriptions.affiliations', function ($q) {
                            $q->where('affiliations.end_date', '>=', now())
                                ->where('affiliations.status_class', \Domain\Memberships\States\ActiveAffiliationState::class)
                                ->whereHas('memberSubscription.membershipPackage.affiliationPlans', function ($subQ) {
                                    $subQ->where('is_validation_plan', true);
                                });
                        });
                    }

                    // Exclude individuals who already have ALL affiliation plans in this package
                    // (allow partial overlap - show if they're missing at least one plan)
                    $affiliationPlanIds = $package->affiliationPlans->pluck('id')->toArray();
                    $planCount = count($affiliationPlanIds);

                    if ($planCount > 0) {
                        // Use a subquery to count how many of the package's plans the individual already has
                        // Only exclude if they have ALL plans (count equals total plans in package)
                        $query->where(function ($q) use ($affiliationPlanIds, $planCount) {
                            $q->whereDoesntHave('memberSubscriptions.affiliations', function ($subQ) use ($affiliationPlanIds) {
                                // Individual has NO active affiliations from this package
                                $subQ->where('affiliations.end_date', '>=', now())
                                    ->where('affiliations.status_class', \Domain\Memberships\States\ActiveAffiliationState::class)
                                    ->whereHas('memberSubscription.membershipPackage.affiliationPlans', function ($planQ) use ($affiliationPlanIds) {
                                        $planQ->whereIn('affiliation_plans.id', $affiliationPlanIds);
                                    });
                            })->orWhereRaw(
                                // Individual has SOME but not ALL plans from this package
                                "(SELECT COUNT(DISTINCT ap.id)
                                  FROM affiliations a
                                  JOIN member_subscriptions ms ON a.member_subscription_id = ms.id
                                  JOIN package_affiliation pa ON ms.membership_package_id = pa.package_id
                                  JOIN affiliation_plans ap ON pa.affiliation_id = ap.id
                                  WHERE a.member_type = 'individual'
                                    AND a.member_id = individual.id
                                    AND a.end_date >= NOW()
                                    AND a.status_class = ?
                                    AND ap.id IN (" . implode(',', array_fill(0, count($affiliationPlanIds), '?')) . ')
                                ) < ?',
                                array_merge(
                                    [\Domain\Memberships\States\ActiveAffiliationState::class],
                                    $affiliationPlanIds,
                                    [$planCount]
                                )
                            );
                        });
                    }

                    // NEW: Exclude individuals who already have ANY of the insurance plans in this package
                    $insurancePlanIds = $package->insurancePlans->pluck('id');
                    if ($insurancePlanIds->isNotEmpty()) {
                        // Query the Insurance model directly since Individual doesn't have insurances() relationship
                        $individualsWithInsurance = \Domain\Insurance\Models\Insurance::query()
                            ->where('member_type', 'individual')
                            ->where('end_date', '>=', now())
                            ->whereIn('status_class', [
                                \Domain\Insurance\States\ActiveInsuranceState::class,
                                \Domain\Insurance\States\PendingPaymentInsuranceState::class,
                            ])
                            ->whereIn('insurance_plan_id', $insurancePlanIds)
                            ->pluck('member_id');

                        if ($individualsWithInsurance->isNotEmpty()) {
                            $query->whereNotIn('individual.id', $individualsWithInsurance);
                        }
                    }
                }

                // Exclude individuals who already have an active or pending subscription for the selected package
                $query->whereDoesntHave('memberSubscriptions', function ($q) {
                    $q->where('membership_package_id', $this->selectedPackageId)
                        ->where(function ($subQ) {
                            // Check for active/pending subscriptions that haven't expired
                            $subQ->where('end_date', '>=', now())
                                ->orWhereIn('status_class', [
                                    \Domain\Memberships\States\ActiveMemberSubscriptionState::class,
                                    \Domain\Memberships\States\PendingMemberSubscriptionState::class,
                                    \Domain\Memberships\States\PendingPaymentMemberSubscriptionState::class,
                                ]);
                        });
                });
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('surname', 'like', "%{$this->search}%")
                        ->orWhere('first_name_latin', 'like', "%{$this->search}%")
                        ->orWhere('last_name_latin', 'like', "%{$this->search}%")
                        ->orWhere('member_code', 'like', "%{$this->search}%")
                        ->orWhere('member_number', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter !== 'all', function ($query) {
                if ($this->statusFilter === 'active') {
                    $query->whereHas('memberSubscriptions', function ($q) {
                        $q->where('end_date', '>=', now());
                    });
                } else {
                    $query->whereDoesntHave('memberSubscriptions', function ($q) {
                        $q->where('end_date', '>=', now());
                    });
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    public function toggleSelection($individualId)
    {
        if (in_array($individualId, $this->selectedIndividuals)) {
            $this->selectedIndividuals = array_diff($this->selectedIndividuals, [$individualId]);
        } else {
            $this->selectedIndividuals[] = $individualId;
        }
    }

    public function toggleAll()
    {
        if (count($this->selectedIndividuals) === $this->individuals->count()) {
            $this->selectedIndividuals = [];
        } else {
            $this->selectedIndividuals = $this->individuals->pluck('id')->all();
        }
    }

    public function sort($field)
    {
        if ($field === $this->sortField) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSelectedPackageId()
    {
        // Clear selected individuals when package changes
        $this->selectedIndividuals = [];
        $this->resetPage();
    }

    public function updatedSearch()
    {
        // Reset to first page when search changes
        // Note: We keep selections across searches so users can build a list from multiple searches
        $this->resetPage();
    }

    public function removeSelection(int $individualId): void
    {
        $this->selectedIndividuals = array_values(array_diff($this->selectedIndividuals, [$individualId]));
    }

    public function clearAllSelections(): void
    {
        $this->selectedIndividuals = [];
        $this->selectionTrayExpanded = false;
    }

    public function toggleSelectionTray(): void
    {
        $this->selectionTrayExpanded = ! $this->selectionTrayExpanded;
    }

    #[Computed]
    public function selectedIndividualsDetails(): Collection
    {
        if (empty($this->selectedIndividuals)) {
            return collect();
        }

        return Individual::query()
            ->whereIn('id', $this->selectedIndividuals)
            ->select(['id', 'name', 'surname', 'member_number'])
            ->orderBy('surname')
            ->orderBy('name')
            ->get();
    }

    public function confirmSubscription()
    {
        if (! $this->hasValidationPlanPrivileges) {
            Notification::make()
                ->title(__('memberships.access_restricted'))
                ->body($this->validationPlanMessage)
                ->warning()
                ->persistent()
                ->send();

            return;
        }

        if (empty($this->selectedIndividuals) || ! $this->selectedPackage) {
            return;
        }

        $this->confirmingSubscription = true;
    }

    public function processSubscription(BulkMemberSubscriptionAction $action): void
    {
        if (! $this->hasValidationPlanPrivileges) {
            Notification::make()
                ->title(__('memberships.access_restricted'))
                ->body($this->validationPlanMessage)
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        $this->validate([
            'selectedPackageId' => 'required|exists:membership_packages,id',
            'selectedIndividuals' => 'required|array|min:1',
        ], [
            'selectedPackageId.required' => __('membership.package_required'),
            'selectedPackageId.exists' => __('membership.invalid_package'),
            'selectedIndividuals.required' => __('membership.individuals_required'),
            'selectedIndividuals.min' => __('membership.min_one_individual'),
        ]);

        try {
            $results = $action->execute(
                $this->selectedPackage,
                $this->selectedIndividuals
            );

            $successCount = count($results['success']);
            $failedCount = count($results['failed']);
            $paidSubscriptions = collect($results['success'])->where('requires_payment', true);
            $freeSubscriptions = collect($results['success'])->where('requires_payment', false);

            if ($successCount > 0) {
                $message = __('membership.success_count', ['count' => $successCount]);

                if ($paidSubscriptions->count() > 0) {
                    $message .= ' ' . __('membership.payment_required_count', ['count' => $paidSubscriptions->count()]);
                }

                if ($freeSubscriptions->count() > 0) {
                    $message .= ' ' . __('membership.free_subscriptions_count', ['count' => $freeSubscriptions->count()]);
                }

                Notification::make()
                    ->title(__('membership.subscriptions_created'))
                    ->body($message)
                    ->success()
                    ->duration(8000)
                    ->send();
            }

            if ($failedCount > 0) {
                Notification::make()
                    ->title(__('membership.some_subscriptions_failed'))
                    ->body(__('membership.failed_count', ['count' => $failedCount]))
                    ->warning()
                    ->actions([
                        Action::make('retry')
                            ->label(__('membership.retry_failed'))
                            ->button()
                            ->color('warning')
                            ->action(fn () => $this->retryFailedSubscriptions($results['failed'])),
                    ])
                    ->persistent()
                    ->send();
            }

            $this->reset(['selectedPackageId', 'selectedIndividuals', 'confirmingSubscription']);
            $this->resetPage();

        } catch (\Exception $e) {
            Notification::make()
                ->title(__('membership.error'))
                ->body(__('membership.unexpected_error'))
                ->danger()
                ->persistent()
                ->actions([
                    Action::make('retry')
                        ->label(__('membership.try_again'))
                        ->button()
                        ->color('danger')
                        ->action(fn () => $this->processSubscription($action)),
                ])
                ->send();

            report($e);
        }
    }

    protected function checkEntityAccess()
    {
        if (! auth()->user()->isEntity()) {
            abort(403, 'Unauthorized action.');
        }
    }

    protected function checkValidationPlanPrivileges()
    {
        $entity = auth()->user()->getEntity();

        if (! $entity) {
            $this->hasValidationPlanPrivileges = false;
            $this->validationPlanMessage = __('main.entity_not_found');

            return;
        }

        $validationPlanService = app(ValidationPlanPrivilegeService::class);

        if (! $validationPlanService->canSubscribeMembersToPackages($entity)) {
            $this->hasValidationPlanPrivileges = false;
            $reason = $validationPlanService->getValidationPlanReason($entity, 'entity_member_subscriptions');
            $this->validationPlanMessage = __('memberships.entity_member_subscriptions_not_authorized', ['reason' => $reason]);
        }
    }

    public function render()
    {
        return view('livewire.entity.member-subscription-manager', [
            'packages' => $this->availablePackages,
            'individuals' => $this->individuals,
            'hasValidationPlanPrivileges' => $this->hasValidationPlanPrivileges,
            'validationPlanMessage' => $this->validationPlanMessage,
        ]);
    }
}
