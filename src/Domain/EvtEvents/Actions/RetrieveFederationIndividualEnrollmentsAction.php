<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Event;
use Domain\Federations\Models\Federation;
use Illuminate\Database\Eloquent\Collection;

class RetrieveFederationIndividualEnrollmentsAction
{
    /**
     * Execute the action and return the individual enrollments for a specific event and federation.
     */
    /**
     * Execute the action and return the processed individual enrollments
     * with attributes for a specific event and federation.
     */
    public function execute(Event $event, int $federationId): Collection
    {

        $federationIndividualEnrollments = $event->enrollments()
            ->whereHas('individualEnrollments')
            ->with([
                'individualEnrollments',
                'individualEnrollments.individual',
                'individualEnrollments.attributes.attribute',
            ])
            ->where('enrollable_type', Federation::class)
            ->where('enrollable_id', $federationId)
            ->get();

        // Process each enrollment to format the attributes data
        $federationIndividualEnrollments->each(function ($enrollment) {
            $enrollment->individualEnrollments->each(function ($individualEnrollment) {
                // Transform attributes into a key-value pair: attribute name => value
                $attributesData = $individualEnrollment->attributes->mapWithKeys(function ($item) {
                    $attributeName = $item->attribute->name ?? 'Unknown';

                    return [$attributeName => $item->value];
                });

                // Add the processed attributes back to the individual enrollment
                $individualEnrollment->processedAttributes = $attributesData;
            });
        });

        return $federationIndividualEnrollments;
    }
}
