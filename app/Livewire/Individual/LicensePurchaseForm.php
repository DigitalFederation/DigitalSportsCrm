<?php

namespace App\Livewire\Individual;

use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Actions\CalculateLicensePriceAction;
use Domain\Licenses\Actions\FilterLicensesByCertificationsAndRolesAction;
use Domain\Licenses\Actions\PurchaseLicenseAction;
use Domain\Licenses\Models\License;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Domain\Memberships\Services\ValidationPlanPrivilegeService;
use Livewire\Attributes\Url;
use Livewire\Component;

class LicensePurchaseForm extends Component
{
    public Individual $individual;

    #[Url(as: 'filter[sport]')]
    public $sport; // Sport filter

    public $committee = null; // Committee filter (sport, diving, scientific)
    public $isInternational = null; // International license filter

    public $baseLicenses = [];
    public $selectedLicenseId;
    public $selectedLicense;
    public $calculatedPrice = 0;
    public $search = '';
    public $sortBy = 'name';

    public function mount(Individual $individual, $sport = null, $committee = null, $isInternational = null)
    {
        $this->individual = $individual;
        $this->sport = $sport;
        $this->committee = $committee;
        $this->isInternational = $isInternational;
        $this->loadBaseLicenses();
    }

    public function getIndividualHasActiveAffiliationProperty()
    {
        return $this->individual->hasActiveAffiliation();
    }

    public function getHasActiveDivingServicesCertificationProperty(): bool
    {
        // DIVINGSERVICES certifications (Mergulhador Nivel 3, Instrutor Nivel 1/2/3, etc.)
        // are stored in diving_professional_certifications table, not certification_attributed
        return $this->individual->divingProfessionalCertifications()
            ->active()
            ->exists();
    }

    public function getActiveLicenseIdsProperty()
    {
        return \Domain\Licenses\Models\LicenseAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
            ->where('model_type', 'individual')
            ->where('model_id', $this->individual->id)
            ->where('status_class', 'Domain\\Licenses\\States\\ActiveLicenseAttributedState')
            ->pluck('license_id')
            ->toArray();
    }

    public function hasActiveLicense($licenseId)
    {
        return in_array($licenseId, $this->activeLicenseIds);
    }

    public function getExistingLicensesProperty()
    {
        return \Domain\Licenses\Models\LicenseAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
            ->where('model_type', 'individual')
            ->where('model_id', $this->individual->id)
            ->whereIn('status_class', [
                'Domain\\Licenses\\States\\ActiveLicenseAttributedState',
                'Domain\\Licenses\\States\\PendingLicenseAttributedState',
                'Domain\\Licenses\\States\\PendingTechnicalDirectorApprovalLicenseAttributedState',
                'Domain\\Licenses\\States\\PendingValidationLicenseAttributedState',
            ])
            ->get()
            ->keyBy('license_id');
    }

    public function hasExistingLicense($licenseId)
    {
        return $this->existingLicenses->has($licenseId);
    }

    public function getLicenseStatus($licenseId)
    {
        $license = $this->existingLicenses->get($licenseId);
        if (! $license) {
            return null;
        }

        return match ($license->status_class) {
            'Domain\\Licenses\\States\\ActiveLicenseAttributedState' => 'active',
            'Domain\\Licenses\\States\\PendingLicenseAttributedState' => 'pending_payment',
            'Domain\\Licenses\\States\\PendingValidationLicenseAttributedState' => 'pending_validation',
            'Domain\\Licenses\\States\\PendingTechnicalDirectorApprovalLicenseAttributedState' => 'pending_td_approval',
            default => 'pending',
        };
    }

    public function getLicenseAttributedWithDocumentProperty()
    {
        // Get all license attributed records for this individual with their documents
        return \Domain\Licenses\Models\LicenseAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
            ->where('model_type', 'individual')
            ->where('model_id', $this->individual->id)
            ->with(['license'])
            ->get()
            ->keyBy('license_id');
    }

    public function getDocumentForLicense($licenseId)
    {
        $licenseAttributed = $this->licenseAttributedWithDocument[$licenseId] ?? null;

        if (! $licenseAttributed) {
            return null;
        }

        // Find document through document_detail relationship
        $document = \Domain\Documents\Models\Document::whereHas('details', function ($query) use ($licenseAttributed) {
            $query->where('owner_type', \Domain\Licenses\Models\LicenseAttributed::class)
                ->where('owner_id', $licenseAttributed->id);
        })
            ->whereHas('type', function ($query) {
                $query->where('code', 'ORD'); // Order/Invoice type
            })
            ->first();

        return $document;
    }

    public function render()
    {
        return view('livewire.individual.license-purchase-form', [
            'licenses' => $this->getFilteredLicenses(),
            'availableSports' => $this->getAvailableSports(),
        ]);
    }

    public function getAvailableSports()
    {
        // Get sports from licenses available for individuals (excluding international)
        $licenses = License::query()
            ->forRequesterType(Individual::class)
            ->where('active', true)
            ->whereHas('committee', fn ($q) => $q->where('is_international', false))  // Exclude international licenses
            ->whereNotNull('sport_id')
            ->with('sport')
            ->get();

        $sports = $licenses->pluck('sport')->filter()->unique('id');

        return $sports->map(function ($sport) {
            return [
                'id' => $sport->id,
                'name' => $sport->name,
            ];
        })->sortBy('name')->values()->all();
    }

    public function getFilteredLicenses()
    {
        $licenses = collect($this->baseLicenses);

        // Apply search filter
        if ($this->search) {
            $licenses = $licenses->filter(function ($license) {
                return str_contains(strtolower($license->name), strtolower($this->search)) ||
                    str_contains(strtolower($license->license_code ?? ''), strtolower($this->search)) ||
                    str_contains(strtolower($license->description ?? ''), strtolower($this->search)) ||
                    ($license->sport && str_contains(strtolower($license->sport->name), strtolower($this->search))) ||
                    ($license->professionalRole && str_contains(strtolower($license->professionalRole->name), strtolower($this->search)));
            });
        }

        // Apply sorting
        $licenses = $licenses->sortBy(function ($license) {
            switch ($this->sortBy) {
                case 'price':
                    return $license->calculated_price ?? 0;
                case 'sport':
                    return $license->sport->name ?? 'zzz';
                case 'name':
                default:
                    return $license->name;
            }
        });

        return $licenses->values()->all();
    }

    public function updatedSelectedLicenseId($licenseId)
    {
        $this->calculatedPrice = 0;
        $this->selectedLicense = null;

        if ($licenseId) {
            // Find license from baseLicenses first (for cached calculated_price)
            $this->selectedLicense = collect($this->baseLicenses)->firstWhere('id', $licenseId);

            // If not found in base licenses, fetch from database (bypass ExcludeInternationalScope)
            if (! $this->selectedLicense) {
                $this->selectedLicense = License::withoutGlobalScope(ExcludeInternationalScope::class)->find($licenseId);
            }

            if ($this->selectedLicense) {
                // Use cached price if available and > 0, otherwise calculate
                if (isset($this->selectedLicense->calculated_price) && $this->selectedLicense->calculated_price >= 0) {
                    $this->calculatedPrice = $this->selectedLicense->calculated_price;
                } else {
                    $calculatePriceAction = new CalculateLicensePriceAction;
                    $result = $calculatePriceAction($this->selectedLicense, Individual::class);
                    $this->calculatedPrice = $result ?? 0;
                }
            }
        }
    }

    public function updatedSearch()
    {
        $this->resetSelection();
    }

    public function updatedSortBy()
    {
        $this->resetSelection();
    }

    public function updatedSport()
    {
        $this->loadBaseLicenses();
        $this->resetSelection();
    }

    private function resetSelection()
    {
        $this->selectedLicenseId = null;
        $this->selectedLicense = null;
        $this->calculatedPrice = 0;
    }

    private function loadBaseLicenses()
    {
        // Get all federation IDs from individual's federations
        $federationIds = $this->individual->federations->pluck('id')->toArray();

        if (empty($federationIds)) {
            $this->baseLicenses = [];

            return;
        }

        // Get licenses where requester_model includes Individual
        // These are licenses that individuals can request for themselves
        $query = License::query()
            ->forRequesterType(Individual::class)
            ->where('active', true);

        // Apply sport filter if specified
        if ($this->sport) {
            $query->where('sport_id', $this->sport);
        }

        // Apply committee filter if specified
        if ($this->committee) {
            $query->whereHas('committee', function ($q) {
                $q->where('code', $this->committee);
            });
        }

        // Handle international scope based on isInternational property
        if ($this->isInternational !== null) {
            if ($this->isInternational) {
                // Include international licenses
                $query->withoutGlobalScope(ExcludeInternationalScope::class)
                    ->whereHas('committee', fn ($q) => $q->where('is_international', true));
            } else {
                // Only national licenses
                $query->whereHas('committee', fn ($q) => $q->where('is_international', false));
            }
        } else {
            // Default behavior: exclude international licenses
            $query->whereHas('committee', fn ($q) => $q->where('is_international', false));
        }

        $licenses = $query->with(['sport', 'committee', 'professionalRole', 'type', 'federations'])->get();

        // Apply certification and role filters
        $filterLicensesAction = new FilterLicensesByCertificationsAndRolesAction;
        $filteredLicenses = $filterLicensesAction($licenses, $this->individual);

        // Calculate price for each license using Individual pricing
        $calculatePriceAction = new CalculateLicensePriceAction;
        foreach ($filteredLicenses as $license) {
            $license->calculated_price = $calculatePriceAction($license, Individual::class);
        }

        // Filter out licenses with null prices (improperly configured licenses)
        $filteredLicenses = $filteredLicenses->filter(function ($license) {
            return $license->calculated_price !== null;
        });

        $this->baseLicenses = $filteredLicenses->values()->all();
    }

    public function canPurchase()
    {
        // Check if individual has active affiliation
        if (! $this->individualHasActiveAffiliation) {
            return false;
        }

        // Check validation plan privileges
        $validationPlanService = resolve(ValidationPlanPrivilegeService::class);
        if (! $validationPlanService->canRequestLicense($this->individual)) {
            return false;
        }

        // For DIVINGSERVICES licenses, require active certification
        if ($this->committee === 'DIVINGSERVICES' && ! $this->hasActiveDivingServicesCertification) {
            return false;
        }

        return true;
    }

    public function getValidationPlanMessageProperty()
    {
        $validationPlanService = resolve(ValidationPlanPrivilegeService::class);

        return $validationPlanService->getValidationPlanReason($this->individual, 'license');
    }

    public function purchaseLicense($licenseId = null)
    {
        // Use passed licenseId or fall back to selectedLicenseId
        $licenseId = $licenseId ?: $this->selectedLicenseId;

        if (! $licenseId) {
            session()->flash('error', __('Please select a valid license.'));

            return;
        }

        // Find the license and calculate price (bypass ExcludeInternationalScope for international licenses)
        $license = License::withoutGlobalScope(ExcludeInternationalScope::class)->find($licenseId);
        if (! $license) {
            session()->flash('error', __('License not found.'));

            return;
        }

        // Calculate price
        $calculatePriceAction = new CalculateLicensePriceAction;
        $calculatedPrice = $calculatePriceAction($license, Individual::class);

        if ($calculatedPrice === null || $calculatedPrice <= 0) {
            session()->flash('error', __('licenses.This license cannot be purchased with this method'));

            return;
        }

        try {
            $purchaseAction = new PurchaseLicenseAction(
                new \Domain\Licenses\Actions\CreateLicenseAttributedAction,
                new CalculateLicensePriceAction,
                new ValidationPlanPrivilegeService,
                new \Domain\Licenses\Actions\CalculateLicenseValidityDatesAction,
                new \Domain\Licenses\Actions\ValidateLicenseDocumentRequirementsAction
            );

            $purchaseAction($license, $this->individual);

            session()->flash('success', __('License purchase initiated. You will receive payment instructions shortly.'));

            // Refresh the component to show the updated status and document link
            $this->loadBaseLicenses();
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            logger('License purchase error: ' . $e->getMessage());
            session()->flash('error', $e->getMessage() ?: __('Failed to purchase license. Please try again.'));
        }
    }

    public function requestFreeLicense($licenseId = null)
    {
        // Use passed licenseId or fall back to selectedLicenseId
        $licenseId = $licenseId ?: $this->selectedLicenseId;

        if (! $licenseId) {
            session()->flash('error', __('Please select a valid license.'));

            return;
        }

        // Find the license and calculate price (bypass ExcludeInternationalScope for international licenses)
        $license = License::withoutGlobalScope(ExcludeInternationalScope::class)->find($licenseId);
        if (! $license) {
            session()->flash('error', __('License not found.'));

            return;
        }

        // Calculate price
        $calculatePriceAction = new CalculateLicensePriceAction;
        $calculatedPrice = $calculatePriceAction($license, Individual::class);

        if ($calculatedPrice != 0) {
            session()->flash('error', __('licenses.This license is not free'));

            return;
        }

        try {
            $purchaseAction = new PurchaseLicenseAction(
                new \Domain\Licenses\Actions\CreateLicenseAttributedAction,
                new CalculateLicensePriceAction,
                new ValidationPlanPrivilegeService,
                new \Domain\Licenses\Actions\CalculateLicenseValidityDatesAction,
                new \Domain\Licenses\Actions\ValidateLicenseDocumentRequirementsAction
            );

            $purchaseAction($license, $this->individual);

            session()->flash('success', __('Free license requested and activated successfully!'));

            // Refresh the component to show the updated status
            $this->loadBaseLicenses();
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            logger('Free license request error: ' . $e->getMessage());
            session()->flash('error', $e->getMessage() ?: __('Failed to request license. Please try again.'));
        }
    }
}
