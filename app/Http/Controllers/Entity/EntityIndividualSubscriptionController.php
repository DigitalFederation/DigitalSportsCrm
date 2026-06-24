<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Actions\BulkMemberSubscriptionAction;
use Domain\Memberships\Models\MembershipPackage;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EntityIndividualSubscriptionController extends Controller
{
    /**
     * Display the interface for managing individual member subscriptions.
     * This is where entities can subscribe their members to packages.
     */
    public function index(): View
    {
        return view('web.entity.individual-memberships.index');
    }

    /**
     * Show the preview/confirmation page for bulk individual subscriptions.
     */
    public function preview(MembershipPackage $package): View
    {
        $this->authorize('manage-individual-subscriptions', Entity::class);

        $entity = auth()->user()->entity;

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

        return view('entity.individual-subscriptions.preview', [
            'package' => $package,
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
        $this->authorize('manage-individual-subscriptions', Entity::class);

        $validatedData = request()->validate([
            'individuals' => ['required', 'array', 'min:1'],
            'individuals.*' => ['required', 'exists:individual,id'],
        ]);

        try {
            DB::beginTransaction();

            $results = $action->execute($package, $validatedData['individuals']);

            DB::commit();

            // Handle the results
            $successCount = count($results['success']);
            $failedCount = count($results['failed']);

            if ($successCount > 0) {
                Notification::make()
                    ->title('Individual Subscriptions Created')
                    ->body("Successfully created {$successCount} member subscriptions.")
                    ->success()
                    ->send();
            }

            if ($failedCount > 0) {
                Log::warning('Some individual subscriptions failed to process', [
                    'failed_subscriptions' => $results['failed'],
                    'entity_id' => auth()->user()->entity->id,
                    'package_id' => $package->id,
                ]);

                Notification::make()
                    ->title('Some Member Subscriptions Failed')
                    ->body("Failed to create {$failedCount} member subscriptions. The administrator has been notified.")
                    ->warning()
                    ->send();
            }

            return redirect()->route('entity.individual-subscriptions.index')
                ->with('status', 'subscriptions-processed');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process bulk individual subscriptions', [
                'error' => $e->getMessage(),
                'entity_id' => auth()->user()->entity->id,
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
     * Display the subscription history for entity's individuals.
     */
    public function history(): View
    {
        $this->authorize('manage-individual-subscriptions', Entity::class);

        $entity = auth()->user()->entity;

        $subscriptions = $entity->individuals()
            ->with(['memberSubscriptions' => function ($query) {
                $query->with(['membershipPackage'])
                    ->orderBy('created_at', 'desc');
            }])
            ->paginate();

        return view('entity.individual-subscriptions.history', [
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * Show the details of a specific individual's subscription.
     */
    public function show(string $subscriptionId): View
    {
        $this->authorize('manage-individual-subscriptions', Entity::class);

        $subscription = auth()->user()->entity
            ->individuals()
            ->whereHas('memberSubscriptions', function ($query) use ($subscriptionId) {
                $query->where('id', $subscriptionId);
            })
            ->firstOrFail()
            ->memberSubscriptions()
            ->with(['membershipPackage', 'affiliations', 'insurances'])
            ->findOrFail($subscriptionId);

        return view('entity.individual-subscriptions.show', [
            'subscription' => $subscription,
        ]);
    }
}
