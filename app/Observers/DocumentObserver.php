<?php

namespace App\Observers;

use App\Services\DashboardCacheService;
use Domain\Documents\Models\Document;
use Domain\Documents\States\PaidDocumentState;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\States\ActiveAffiliationState;
use Domain\Memberships\States\ActiveMemberSubscriptionState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DocumentObserver
{
    /**
     * Handle the Document "created" event.
     */
    public function created(Document $document): void
    {
        if ($document->status_class === PaidDocumentState::class) {
            $year = $document->created_at?->year ?? now()->year;

            DB::afterCommit(function () use ($year) {
                app(DashboardCacheService::class)->invalidatePaymentCaches($year);
            });
        }
    }

    /**
     * Handle the Document "updated" event.
     */
    public function updated(Document $document): void
    {
        if ($document->isDirty('status_class') && $document->status_class === PaidDocumentState::class) {
            $year = $document->created_at?->year ?? now()->year;

            DB::afterCommit(function () use ($year) {
                app(DashboardCacheService::class)->invalidatePaymentCaches($year);
            });

            $this->handlePaidDocument($document);
        }
    }

    /**
     * Handle actions when a document is marked as paid
     */
    private function handlePaidDocument(Document $document): void
    {
        // Check if this document is related to a member subscription
        if ($document->owner_type === MemberSubscription::class) {
            $subscription = MemberSubscription::find($document->owner_id);

            if ($subscription && $subscription->status_class !== ActiveMemberSubscriptionState::class) {
                Log::info(__('memberships.activating_subscription_after_payment'), [
                    'subscription_id' => $subscription->id,
                    'document_id' => $document->id,
                ]);

                // Activate the subscription
                $subscription->update([
                    'status_class' => ActiveMemberSubscriptionState::class,
                ]);

                // Activate all related affiliations
                $subscription->affiliations()->each(function ($affiliation) {
                    $affiliation->update([
                        'status_class' => ActiveAffiliationState::class,
                    ]);

                    Log::info('Affiliation activated after payment', [
                        'affiliation_id' => $affiliation->id,
                        'federation_id' => $affiliation->federation_id,
                    ]);
                });

                Log::info(__('memberships.subscription_activated'), [
                    'subscription_id' => $subscription->id,
                ]);
            }
        }
    }
}
