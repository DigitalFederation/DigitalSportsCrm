<?php

namespace App\Events;

use Domain\Documents\Models\DocumentDetail;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ActivateAfterPayment
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $models;

    /**
     * Create a new event instance.
     */
    public function __construct(string $document_id)
    {
        $this->models = $this->findModelsToActivateByDocument($document_id);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }

    private function findModelsToActivateByDocument(string $document_id): array
    {
        $models = [];
        $uniqueModelIds = [];

        $details = DocumentDetail::where(compact('document_id'))
            ->with('owner')
            ->get();

        Log::info('ActivateAfterPayment: Processing document details', [
            'document_id' => $document_id,
            'details_count' => $details->count(),
        ]);

        foreach ($details as $detail) {
            Log::info('ActivateAfterPayment: Processing detail', [
                'detail_id' => $detail->id,
                'owner_type' => $detail->owner_type,
                'owner_id' => $detail->owner_id,
                'owner_loaded' => $detail->relationLoaded('owner'),
                'owner_is_null' => is_null($detail->owner),
            ]);

            if (! is_null($detail->owner)) {
                $modelType = get_class($detail->owner);
                $modelId = $detail->owner->id;

                Log::info('ActivateAfterPayment: Owner found', [
                    'detail_id' => $detail->id,
                    'model_type' => $modelType,
                    'model_id' => $modelId,
                ]);

                if (! isset($uniqueModelIds[$modelType]) || ! in_array($modelId, $uniqueModelIds[$modelType])) {
                    $uniqueModelIds[$modelType][] = $modelId;
                    $models[$modelType][] = $detail->owner;
                }
            } else {
                // Try to debug why owner is null
                if ($detail->owner_type && $detail->owner_id) {
                    // Check if the model exists directly
                    $ownerClass = $detail->owner_type;
                    if (class_exists($ownerClass)) {
                        $directLookup = $ownerClass::find($detail->owner_id);
                        $withTrashed = method_exists($ownerClass, 'withTrashed')
                            ? $ownerClass::withTrashed()->find($detail->owner_id)
                            : null;

                        Log::warning('ActivateAfterPayment: Owner NULL but owner_type/owner_id exist', [
                            'detail_id' => $detail->id,
                            'owner_type' => $detail->owner_type,
                            'owner_id' => $detail->owner_id,
                            'direct_lookup_found' => ! is_null($directLookup),
                            'with_trashed_found' => ! is_null($withTrashed),
                            'is_soft_deleted' => $withTrashed && $withTrashed->deleted_at ? true : false,
                        ]);
                    } else {
                        Log::warning('ActivateAfterPayment: Owner class does not exist', [
                            'detail_id' => $detail->id,
                            'owner_type' => $detail->owner_type,
                        ]);
                    }
                }

                Log::info('Manual service entry detected in document detail', ['document_id' => $document_id, 'detail_id' => $detail->id]);
            }
        }

        Log::info('ActivateAfterPayment: Finished processing', [
            'document_id' => $document_id,
            'models_found' => array_keys($models),
            'total_models' => array_sum(array_map('count', $models)),
        ]);

        return $models;
    }
}
