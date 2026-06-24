<?php

namespace App\Http\Controllers\Federation;

use App\Enums\MembershipTargetType;
use App\Http\Controllers\Controller;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Actions\BulkMemberSubscriptionAction;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IndividualMembershipController extends Controller
{
    /**
     * Display a pure listing of existing individual membership subscriptions.
     * No creation forms - just the data table.
     */
    public function index(): View
    {
        $federation = null;

        try {
            // Ensure user is authenticated and is a federation user
            $user = Auth::user();
            if (! $user || ! $user->isFederation()) {
                abort(403, 'Unauthorized action.');
            }
            $federation = $user->federations()->first();
            if (! $federation) {
                Log::error('Federation user has no associated federation.', ['user_id' => $user->id]);
                abort(403, 'Federation association not found.');
            }

            // Check if this is a main federation
            $isMainFederation = $federation->isMainFederation();

            // Build the base query
            $query = MemberSubscription::with([
                'membershipPackage.affiliationPlans',
                'membershipPackage.insurancePlans',
                'member.entities', // The individual with entities
                'affiliations.federation',
                'insurances.insurancePlan',
                'requester', // Add requester like CMAS controller
            ]);

            // Filter for individual subscriptions only (using morph map key)
            $query->where('member_type', 'individual');

            // Only show packages with affiliation plans (membership packages)
            $query->whereHas('membershipPackage.affiliationPlans');

            // If not main federation, filter to only show individuals from entities under this federation
            if (! $isMainFederation) {
                // Since we already filtered for member_type = 'individual', we can use whereHas directly
                $query->whereHas('member', function ($subQuery) use ($federation) {
                    // Ensure we're dealing with Individual model and it has entities
                    $subQuery->whereHas('entities', function ($entityQuery) use ($federation) {
                        $entityQuery->whereHas('federations', function ($fedQuery) use ($federation) {
                            $fedQuery->where('federation.id', $federation->id);
                        });
                    });
                });
            }
            // If main federation, no additional filtering needed - show ALL individual subscriptions

            $subscriptions = $query->orderBy('created_at', 'desc')
                ->paginate(15);

            // Debug logging
            $allMemberTypes = MemberSubscription::distinct()->pluck('member_type')->toArray();
            Log::info('Federation Individual Memberships Index', [
                'federation_id' => $federation->id,
                'federation_name' => $federation->name,
                'is_main_federation' => $isMainFederation,
                'total_results' => $subscriptions->total(),
                'all_member_types_in_db' => $allMemberTypes,
                'query_sql' => $query->toSql(),
                'query_bindings' => $query->getBindings(),
            ]);

            return view('web.federation.individual-memberships.index', compact('subscriptions', 'federation'));

        } catch (\Exception $e) {
            Log::error('Error in IndividualMembershipController@index', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            // Return empty results to avoid breaking the page
            $subscriptions = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);

            return view('web.federation.individual-memberships.index', compact('subscriptions', 'federation'));
        }
    }

    /**
     * Show the form for creating new individual membership subscriptions.
     * The Livewire component handles all the logic.
     */
    public function create(): View
    {
        // Ensure user is authenticated and is a federation user
        $user = Auth::user();
        if (! $user || ! $user->isFederation()) {
            abort(403, 'Unauthorized action.');
        }

        return view('web.federation.individual-memberships.create');
    }

    /**
     * Store a new individual membership subscription from the separate create form.
     */
    public function store(Request $request, BulkMemberSubscriptionAction $action): RedirectResponse
    {
        // Ensure user is authenticated and is a federation user
        $user = Auth::user();
        if (! $user || ! $user->isFederation()) {
            abort(403, 'Unauthorized action.');
        }
        $federation = $user->federations()->first();
        if (! $federation) {
            abort(403, 'Federation not found');
        }

        $validatedData = $request->validate([
            'membership_package_id' => ['required', 'exists:membership_packages,id'],
            'entity_id' => ['required', 'exists:entity,id'],
            'individuals' => ['required', 'array', 'min:1'],
            'individuals.*' => ['required', 'exists:individual,id'],
        ]);

        try {
            DB::beginTransaction();

            $package = MembershipPackage::with(['affiliationPlans', 'insurancePlans'])
                ->findOrFail($validatedData['membership_package_id']);

            $entity = Entity::findOrFail($validatedData['entity_id']);

            // Verify that this entity belongs to this federation
            if (! $entity->federations()->where('federation.id', $federation->id)
                ->where('entity_federation.status_class', ActiveEntityFederationState::class)->exists()) {
                throw new \Exception('Entity does not belong to this federation');
            }

            // Verify the package has affiliation plans (membership requirement)
            if ($package->affiliationPlans->isEmpty()) {
                throw new \Exception('Selected package must contain affiliation plans for membership subscriptions');
            }

            // Override the requester to be the entity (not federation) for payment attribution
            $results = $action->execute(
                $package,
                $validatedData['individuals'],
                $entity, // Pass entity as the requester for payment
                'federation_facilitated' // Request type indicates federation facilitated
            );

            DB::commit();

            // Handle the results
            $successCount = count($results['success']);
            $failedCount = count($results['failed']);

            if ($successCount > 0) {
                Notification::make()
                    ->title('Individual Memberships Created')
                    ->body("Successfully created {$successCount} individual membership subscriptions for {$entity->name}.")
                    ->success()
                    ->send();
            }

            if ($failedCount > 0) {
                Log::warning('Some individual membership subscriptions failed to process', [
                    'failed_subscriptions' => $results['failed'],
                    'entity_id' => $entity->id,
                    'federation_id' => $federation->id,
                    'package_id' => $package->id,
                ]);

                Notification::make()
                    ->title('Some Membership Subscriptions Failed')
                    ->body("Failed to create {$failedCount} membership subscriptions. The administrator has been notified.")
                    ->warning()
                    ->send();
            }

            return redirect()->route('federation.individual-memberships.index')
                ->with('success', 'Individual membership subscriptions processed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process individual membership subscriptions', [
                'error' => $e->getMessage(),
                'entity_id' => $validatedData['entity_id'] ?? null,
                'federation_id' => $federation->id,
                'package_id' => $validatedData['membership_package_id'] ?? null,
            ]);

            Notification::make()
                ->title('Error Processing Membership Subscriptions')
                ->body('An error occurred while processing the membership subscriptions. Please try again.')
                ->danger()
                ->send();

            return back()->withInput()->withErrors(['error' => 'Failed to process membership subscriptions.']);
        }
    }

    /**
     * Show the preview/confirmation page for bulk individual subscriptions.
     */
    public function preview(MembershipPackage $package): View|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        if (! $user || ! $user->isFederation()) {
            abort(403, 'Unauthorized action.');
        }
        $federation = $user->federations()->first();
        if (! $federation) {
            abort(403, 'Federation not found');
        }

        // Get entity ID from request
        $entityId = request('entity_id');
        if (! $entityId) {
            return redirect()->route('federation.individual-memberships.index')
                ->with('error', __('Please select an entity first'));
        }

        $entity = Entity::findOrFail($entityId);

        // Verify that this entity belongs to this federation
        if (! $entity->federations()->where('federation.id', $federation->id)
            ->where('entity_federation.status_class', ActiveEntityFederationState::class)->exists()) {
            abort(403, 'Entity does not belong to this federation');
        }

        // Get eligible individuals (those without active subscriptions for this package)
        $eligibleIndividuals = Individual::query()
            ->whereHas('entities', function ($query) use ($entity) {
                $query->where('entity_id', $entity->id);
            })
            ->whereDoesntHave('memberSubscriptions', function ($query) use ($package) {
                $query->where('membership_package_id', $package->id)
                    ->where('end_date', '>', now());
            })
            ->get();

        return view('web.federation.individual-memberships.preview', [
            'package' => $package,
            'entity' => $entity,
            'eligibleIndividuals' => $eligibleIndividuals,
        ]);
    }

    /**
     * Process the bulk individual subscription request.
     */
    public function process(
        MembershipPackage $package,
        BulkMemberSubscriptionAction $action
    ): RedirectResponse {
        $user = Auth::user();
        if (! $user || ! $user->isFederation()) {
            abort(403, 'Unauthorized action.');
        }
        $federation = $user->federations()->first();
        if (! $federation) {
            abort(403, 'Federation not found');
        }

        $validatedData = request()->validate([
            'entity_id' => ['required', 'exists:entity,id'],
            'individuals' => ['required', 'array', 'min:1'],
            'individuals.*' => ['required', 'exists:individual,id'],
        ]);

        try {
            DB::beginTransaction();

            $entity = Entity::findOrFail($validatedData['entity_id']);

            // Verify that this entity belongs to this federation
            if (! $entity->federations()->where('federation.id', $federation->id)->exists()) {
                throw new \Exception('Entity does not belong to this federation');
            }

            // Override the requester to be the entity (not federation) for payment attribution
            $results = $action->execute(
                $package,
                $validatedData['individuals'],
                $entity, // Pass entity as the requester for payment
                'federation_facilitated' // Request type indicates federation facilitated
            );

            DB::commit();

            // Handle the results
            $successCount = count($results['success']);
            $failedCount = count($results['failed']);

            if ($successCount > 0) {
                Notification::make()
                    ->title('Individual Subscriptions Created')
                    ->body("Successfully created {$successCount} member subscriptions for {$entity->name}.")
                    ->success()
                    ->send();
            }

            if ($failedCount > 0) {
                Log::warning('Some individual subscriptions failed to process', [
                    'failed_subscriptions' => $results['failed'],
                    'entity_id' => $entity->id,
                    'federation_id' => $federation->id,
                    'package_id' => $package->id,
                ]);

                Notification::make()
                    ->title('Some Member Subscriptions Failed')
                    ->body("Failed to create {$failedCount} member subscriptions. The administrator has been notified.")
                    ->warning()
                    ->send();
            }

            return redirect()->route('federation.individual-memberships.index')
                ->with('status', 'subscriptions-processed');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process bulk individual subscriptions', [
                'error' => $e->getMessage(),
                'entity_id' => $validatedData['entity_id'] ?? null,
                'federation_id' => $federation->id,
                'package_id' => $package->id,
            ]);

            Notification::make()
                ->title('Error Processing Member Subscriptions')
                ->body('An error occurred while processing the member subscriptions. Please try again.')
                ->danger()
                ->send();

            return back()->withInput();
        }
    }

    /**
     * Display the subscription history for all entities' individuals.
     */
    public function history(): View
    {
        $user = Auth::user();
        if (! $user || ! $user->isFederation()) {
            abort(403, 'Unauthorized action.');
        }
        $federation = $user->federations()->first();
        if (! $federation) {
            abort(403, 'Federation not found');
        }

        // Get all entities belonging to this federation with their individuals' subscriptions
        $entities = $federation->entities()
            ->with(['individuals.memberSubscriptions' => function ($query) {
                $query->with(['membershipPackage'])
                    ->where('request_type', 'federation_facilitated') // Only federation facilitated ones
                    ->orderBy('created_at', 'desc');
            }])
            ->paginate();

        return view('web.federation.individual-memberships.history', [
            'entities' => $entities,
        ]);
    }

    /**
     * Show the details of a specific individual's subscription.
     */
    public function show(string $subscriptionId): View
    {
        $user = Auth::user();
        if (! $user || ! $user->isFederation()) {
            abort(403, 'Unauthorized action.');
        }
        $federation = $user->federations()->first();
        if (! $federation) {
            abort(403, 'Federation not found');
        }

        // Check if this is a main federation
        $isMainFederation = $federation->isMainFederation();

        // Build the base query
        $query = \Domain\Memberships\Models\MemberSubscription::with([
            'membershipPackage',
            'affiliations',
            'insurances',
            'member', // The individual
            'requester', // Add requester for consistency
        ])
            ->where('id', $subscriptionId)
            ->where('member_type', 'individual'); // Using morph map key

        // If not main federation, verify subscription belongs to an entity under this federation
        if (! $isMainFederation) {
            // Since we already filtered for member_type = 'individual', we can use whereHas directly
            $query->whereHas('member', function ($subQuery) use ($federation) {
                $subQuery->whereHas('entities', function ($entityQuery) use ($federation) {
                    $entityQuery->whereHas('federations', function ($fedQuery) use ($federation) {
                        $fedQuery->where('federation.id', $federation->id);
                    });
                });
            });
        }
        // If main federation, can view any individual subscription

        $subscription = $query->firstOrFail();

        return view('web.federation.individual-memberships.show', [
            'subscription' => $subscription,
        ]);
    }

    /**
     * Get available membership packages for individuals (with affiliation plans).
     * Based on Individual\MemberSubscriptionController pattern.
     */
    private function getAvailablePackagesForIndividuals($federation): Collection
    {
        // DEBUG: Build query step by step to identify issue
        $query = MembershipPackage::with(['affiliationPlans', 'insurancePlans']);

        // DEBUG: Log the SQL query
        Log::info('getAvailablePackagesForIndividuals - Base query', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
        ]);

        $query = $query->where('is_active', true);
        Log::info('getAvailablePackagesForIndividuals - After is_active filter', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'count' => (clone $query)->count(),
        ]);

        $query = $query->whereIn('target_type', [MembershipTargetType::INDIVIDUAL, MembershipTargetType::BOTH]);
        Log::info('getAvailablePackagesForIndividuals - After target_type filter', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'count' => (clone $query)->count(),
            'individual_value' => MembershipTargetType::INDIVIDUAL->value,
            'both_value' => MembershipTargetType::BOTH->value,
        ]);

        // DEBUG: Check target_type values in database
        $targetTypeValues = MembershipPackage::pluck('target_type')->unique()->toArray();
        Log::info('getAvailablePackagesForIndividuals - Existing target_type values in database', [
            'target_types' => $targetTypeValues,
        ]);

        // DEBUG: Check packages for each target type
        $individualTypeCount = MembershipPackage::where('target_type', 'individual')->count();
        $bothTypeCount = MembershipPackage::where('target_type', 'both')->count();
        $entityTypeCount = MembershipPackage::where('target_type', 'entity')->count();
        $individualFromEntityCount = MembershipPackage::where('target_type', 'individual_from_entity')->count();

        Log::info('getAvailablePackagesForIndividuals - Target type counts', [
            'individual' => $individualTypeCount,
            'both' => $bothTypeCount,
            'entity' => $entityTypeCount,
            'individual_from_entity' => $individualFromEntityCount,
        ]);

        $query = $query->whereJsonContains('distribution_methods', 'direct');
        Log::info('getAvailablePackagesForIndividuals - After distribution_methods filter', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'count' => (clone $query)->count(),
        ]);

        $query = $query->whereHas('affiliationPlans');
        Log::info('getAvailablePackagesForIndividuals - After affiliationPlans filter', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'count' => (clone $query)->count(),
        ]);

        $query = $query->whereHas('federations', function ($subQuery) use ($federation) {
            $subQuery->where('federation.id', $federation->id);
        });
        Log::info('getAvailablePackagesForIndividuals - After federations filter', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'count' => (clone $query)->count(),
            'federation_id' => $federation->id,
        ]);

        // DEBUG: Check the federation relationship
        $packagesWithThisFederation = MembershipPackage::whereHas('federations', function ($q) use ($federation) {
            $q->where('federation.id', $federation->id);
        })->pluck('id', 'name')->toArray();

        Log::info('getAvailablePackagesForIndividuals - Packages with this federation', [
            'packages' => $packagesWithThisFederation,
        ]);

        $result = $query->get();

        Log::info('getAvailablePackagesForIndividuals - Final result', [
            'count' => $result->count(),
            'packages' => $result->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'target_type' => $p->target_type,
                    'is_active' => $p->is_active,
                    'distribution_methods' => $p->distribution_methods,
                    'affiliation_plans_count' => $p->affiliationPlans->count(),
                    'insurance_plans_count' => $p->insurancePlans->count(),
                ];
            })->toArray(),
        ]);

        // DEBUG: Test simplified queries to identify the issue
        $testQuery1 = MembershipPackage::where('is_active', true)
            ->whereIn('target_type', ['individual', 'both'])
            ->get();
        Log::info('getAvailablePackagesForIndividuals - Test Query 1 (active + target_type with strings)', [
            'count' => $testQuery1->count(),
        ]);

        $testQuery2 = MembershipPackage::where('is_active', true)
            ->whereIn('target_type', ['individual', 'both'])
            ->whereHas('affiliationPlans')
            ->get();
        Log::info('getAvailablePackagesForIndividuals - Test Query 2 (+ affiliationPlans)', [
            'count' => $testQuery2->count(),
        ]);

        $testQuery3 = MembershipPackage::where('is_active', true)
            ->whereIn('target_type', ['individual', 'both'])
            ->whereHas('affiliationPlans')
            ->whereHas('federations', function ($q) use ($federation) {
                $q->where('federation.id', $federation->id);
            })
            ->get();
        Log::info('getAvailablePackagesForIndividuals - Test Query 3 (+ federation)', [
            'count' => $testQuery3->count(),
        ]);

        // DEBUG: Uncomment to see immediate output and stop execution
        // dd([
        //     'federation_id' => $federation->id,
        //     'final_result_count' => $result->count(),
        //     'test_query_1_count' => $testQuery1->count(),
        //     'test_query_2_count' => $testQuery2->count(),
        //     'test_query_3_count' => $testQuery3->count(),
        //     'target_type_values_in_db' => $targetTypeValues,
        // ]);

        return $result;
    }
}
