<?php

namespace Domain\Licenses\Actions;

use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @mixin \Domain\Licenses\Actions\CreateLicenseAttributedDataAction
 */
class CreateLicenseAttributedDataAction
{
    /**
     * Create a LicenseAttributedData based on the given input parameters.
     *
     * This action is responsible for creating a LicenseAttributedData using the
     * provided input parameters, including the type (Individual or Entity), the
     * ID of the record, the federation ID, and other request data. The
     * LicenseAttributedData can then be used to create a LicenseAttributed record
     * in the database.
     *
     * Usage:
     * $createLicenseAttributedDataAction = new CreateLicenseAttributedDataAction();
     * $licenseAttributedDTO = $createLicenseAttributedDataAction($type, $id, $federationId, $request);
     *
     * @param  string  $type  The type of record, either 'individual' or 'entity'
     * @param  string  $record_id  The ID of the record (Individual or Entity)
     * @param  int  $federationId  The federation ID associated with the record
     * @param  array  $requestData  The request object containing additional data for the LicenseAttributedDTO
     * @return array The created array of LicenseAttributedDTOs containing the necessary data for creating new LicenseAttributed records
     *
     * @throws \Exception If there is an issue during the creation of the DTO
     */
    public function __invoke(
        string $type,
        mixed $record_id,
        int $federationId,
        array $requestData
    ): array {

        $holder_name = '';

        switch ($type) {
            case 'individual':
                $individual = Individual::query()->select('id', 'name', 'surname')->where('id', $record_id)->first();
                $holder_name = $individual->name.' '.$individual->surname;
                break;
            case 'entity':
                $entity = Entity::query()->select('id', 'name')->where('id', $record_id)->first();
                $holder_name = $entity->name;
                break;
        }

        $license = License::query()->select('id', 'name')->where('id', $requestData['license_id'])->first();
        $federationName = Federation::query()->where('id', $federationId)->value('name');
        $currentTermStartsAt = $requestData['current_term_starts_at'] ?? Carbon::today();

        $licenseAttributedData = [
            'id' => Str::uuid()->toString(),
            'model_type' => $type === 'individual' ? 'individual' : 'entity',
            'model_id' => $record_id,
            'federation_id' => $federationId,
            'status_class' => PendingLicenseAttributedState::class,
            'license_id' => $requestData['license_id'],
            'license_name' => $license->name,
            'federation_name' => $federationName,
            'holder_name' => $holder_name,
            'notes' => $requestData['notes'],
            'current_term_starts_at' => $currentTermStartsAt,
            'current_term_ends_at' => Carbon::now()->lastOfMonth()->endOfDay(),
            'created_by' => auth()->user()->id,
            'updated_by' => auth()->user()->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        return [$licenseAttributedData];
    }
}
