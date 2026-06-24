<?php

namespace Domain\Licenses\Transitions;

use App\Events\LicenseAttributedCreatedEvent;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Domain\Licenses\States\PendingValidationLicenseAttributedState;
use Illuminate\Support\Facades\Log;

class PendingValidationToPendingTransition
{
    protected LicenseAttributed $licenseAttributed;

    public function __construct(LicenseAttributed $licenseAttributed)
    {
        $this->licenseAttributed = $licenseAttributed;
    }

    public function handle(): LicenseAttributed
    {
        // Validate current state
        if ($this->licenseAttributed->status_class !== PendingValidationLicenseAttributedState::class) {
            throw new \Exception('License must be in pending validation state to transition to pending');
        }

        $this->licenseAttributed->status_class = PendingLicenseAttributedState::class;
        $this->licenseAttributed->save();

        activity('license_attributed')
            ->performedOn($this->licenseAttributed)
            ->causedBy(auth()->user())
            ->withProperties([
                'transition' => 'pending_validation_to_pending',
                'from' => PendingValidationLicenseAttributedState::class,
                'to' => PendingLicenseAttributedState::class,
            ])
            ->log('License approved and pending payment');

        // Create payment document for the approved license
        // Load the license relationship before firing the event
        $this->licenseAttributed->load('license');

        Log::info('PendingValidationToPendingTransition: Firing LicenseAttributedCreatedEvent', [
            'license_attributed_id' => $this->licenseAttributed->id,
            'license_id' => $this->licenseAttributed->license_id,
            'license_loaded' => $this->licenseAttributed->relationLoaded('license'),
            'license_name' => $this->licenseAttributed->license?->name,
            'total_value' => $this->licenseAttributed->total_value,
            'model_type' => $this->licenseAttributed->model_type,
            'requester_model_type' => $this->licenseAttributed->requester_model_type,
        ]);

        // The document will be created by the existing listener
        event(new LicenseAttributedCreatedEvent([$this->licenseAttributed], true));

        Log::info('PendingValidationToPendingTransition: Event dispatched successfully', [
            'license_attributed_id' => $this->licenseAttributed->id,
        ]);

        return $this->licenseAttributed;
    }
}
