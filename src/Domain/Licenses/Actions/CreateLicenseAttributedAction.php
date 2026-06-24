<?php

namespace Domain\Licenses\Actions;

use Domain\Licenses\DataTransferObject\LicenseAttributedData;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Exception;
use Support\UtilityMethods;

class CreateLicenseAttributedAction
{
    /**
     * Handles the creation of a LicenseAttributed record.
     *
     * This action creates a LicenseAttributed record in the database and logs the
     * license attribution activity. Depending on the 'requires_cmas_approval' field
     * of the associated License, the initial state of the LicenseAttributed record
     * is set to either 'WaitingApprovalLicenseAttributedState' or
     * 'PendingLicenseAttributedState'.
     *
     * Usage:
     * $createLicenseAttributedAction = new CreateLicenseAttributedAction();
     * $licenseAttributed = $createLicenseAttributedAction($licenseAttributedDTO);
     *
     * @param  LicenseAttributedData  $dto  Data Transfer Object containing the necessary data
     *                                      for creating a new LicenseAttributed record.
     * @return LicenseAttributed The created LicenseAttributed record.
     *
     * @throws Exception If there is an issue during the creation of the record or logging the activity.
     */
    public function __invoke(LicenseAttributedData $dto): LicenseAttributed
    {
        $licenseAttributedClass = new LicenseAttributed;

        // For international licenses, we need to remove the global scope
        $license = License::withoutGlobalScopes()->find($dto->license_id);

        if (! $license) {
            throw new Exception("License not found with ID: {$dto->license_id}");
        }

        if (! empty($dto->license_code) && ! empty($dto->federation_code)) {
            $dto->license_number = UtilityMethods::generateLicenseCmasInternationalNumber(date('Y'), $dto->license_code, $dto->federation_code);
        }

        // Set default values for new fields if not provided
        if (! isset($dto->request_type)) {
            $dto->request_type = 'direct';
        }

        // Ensure status_class is set (default to Pending for new flow)
        if (! isset($dto->status_class)) {
            $dto->status_class = \Domain\Licenses\States\PendingLicenseAttributedState::class;
        }

        $licenseAttributed = $licenseAttributedClass->create($dto->toArray());

        activity('License')
            ->performedOn($licenseAttributed)
            ->event('attributed')
            ->withProperties($dto->toArray())
            ->log('License attributed');

        return $licenseAttributed;
    }
}
