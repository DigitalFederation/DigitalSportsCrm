<?php

namespace App\Listeners;

use App\Events\LicenseAttributedCreatedEvent;
use Domain\Documents\Actions\CreateDocumentWithDetailsAction;
use Domain\Federations\Models\Federation;
use Domain\Licenses\Actions\BuildLicenseDocumentDetailAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * CreateLicenseAttributedDocumentListener is a listener responsible for creating
 * a new document with associated details when a license is attributed to an entity.
 * The listener is triggered by the LicenseAttributedCreatedEvent.
 *
 * It utilizes CreateDocumentWithDetailsAction to create the document and
 * BuildLicenseDocumentDetailAction to build the document details based on
 * the LicenseAttributed model provided by the event.
 *
 * The created document stores information about the attributed license,
 * such as the license holder, license type, and associated fees.
 */
class CreateLicenseAttributedDocumentListener
{
    public function handle(LicenseAttributedCreatedEvent $event): void
    {
        Log::info('CreateLicenseAttributedDocumentListener: Event received', [
            'licenses_count' => count($event->licenseAttributed),
            'is_self_request' => $event->isSelfRequest,
            'first_license_id' => ! empty($event->licenseAttributed) ? $event->licenseAttributed[0]->id : null,
        ]);

        DB::beginTransaction();

        try {
            $licensesAttributed = $event->licenseAttributed;
            $isSelfRequest = $event->isSelfRequest;

            $allDetails = $this->buildAllDetails($licensesAttributed);

            Log::info('CreateLicenseAttributedDocumentListener: Details built', [
                'details_count' => count($allDetails),
                'has_details' => ! empty($allDetails),
            ]);

            if (! empty($allDetails)) {
                $this->createDocument($licensesAttributed, $isSelfRequest, $allDetails);
                Log::info('CreateLicenseAttributedDocumentListener: Document created successfully');
            } else {
                Log::warning('CreateLicenseAttributedDocumentListener: No details to create document');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CreateLicenseAttributedDocumentListener: Error creating document', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

    }

    private function buildAllDetails(array $licensesAttributed): array
    {
        $buildLicenseDocumentAction = new BuildLicenseDocumentDetailAction;
        $allDetails = [];

        foreach ($licensesAttributed as $licenseAttributed) {
            Log::info('CreateLicenseAttributedDocumentListener: Processing license attributed', [
                'license_attributed_id' => $licenseAttributed->id,
                'total_value' => $licenseAttributed->total_value,
                'requester_model_type' => $licenseAttributed->requester_model_type,
                'license_id' => $licenseAttributed->license_id,
                'has_license_relation' => $licenseAttributed->relationLoaded('license'),
            ]);

            $buildDetails = $buildLicenseDocumentAction([$licenseAttributed]);
            if (! empty($buildDetails)) {
                $allDetails = array_merge($allDetails, $buildDetails);
                Log::info('CreateLicenseAttributedDocumentListener: Details added', [
                    'detail_count' => count($buildDetails),
                ]);
            } else {
                Log::warning('CreateLicenseAttributedDocumentListener: No details returned for license attributed', [
                    'license_attributed_id' => $licenseAttributed->id,
                ]);
            }
        }

        return $allDetails;
    }

    private function createDocument(array $licensesAttributed, bool $isSelfRequest, array $allDetails): void
    {
        $ownerClass = $licensesAttributed[0]->model_type;

        // When it's a self request, the document owner is whoever holds the license
        if ($isSelfRequest) {
            $documentOwnerClass = $ownerClass;
            $ownerId = $licensesAttributed[0]->model_id;
        } else {
            // When it's not a self request, check who made the request
            $requesterModelType = $licensesAttributed[0]->requester_model_type;

            // If there's a requester, use it as the document owner (Entity purchasing for members)
            if ($requesterModelType) {
                $documentOwnerClass = $requesterModelType;
                $ownerId = $licensesAttributed[0]->requested_by_id;
            } else {
                // Fallback to Federation (backward compatibility)
                $documentOwnerClass = Federation::class;
                $ownerId = $licensesAttributed[0]->federation_id;
            }
        }

        Log::info('CreateLicenseAttributedDocumentListener: Creating document', [
            'document_owner_class' => $documentOwnerClass,
            'owner_id' => $ownerId,
            'document_type' => 'ORD',
            'details_count' => count($allDetails),
            'is_self_request' => $isSelfRequest,
        ]);

        $createDocumentAction = new CreateDocumentWithDetailsAction;
        $document = $createDocumentAction($allDetails, 'ORD', $ownerId, $documentOwnerClass);

        if ($document === null) {
            Log::info('CreateLicenseAttributedDocumentListener: No document created - total value is zero', [
                'owner_type' => $documentOwnerClass,
                'owner_id' => $ownerId,
                'license_attributed_ids' => array_map(function ($la) {
                    return $la->id;
                }, $licensesAttributed),
            ]);

            return;
        }

        Log::info('CreateLicenseAttributedDocumentListener: Document created', [
            'document_id' => $document->id ?? null,
            'document_number' => $document->number ?? null,
            'document_reference' => $document->reference ?? null,
            'document_type_code' => $document->document_type_code ?? null,
            'owner_type' => $documentOwnerClass,
            'owner_id' => $ownerId,
            'license_attributed_ids' => array_map(function ($la) {
                return $la->id;
            }, $licensesAttributed),
            'created_at' => $document->created_at ?? null,
        ]);
    }
}
