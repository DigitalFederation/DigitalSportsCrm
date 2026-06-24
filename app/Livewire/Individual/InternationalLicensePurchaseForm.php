<?php

namespace App\Livewire\Individual;

use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Actions\CalculateLicensePriceAction;
use Domain\Licenses\Actions\FilterLicensesByCertificationsAndRolesAction;
use Domain\Licenses\Actions\PurchaseLicenseAction;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Domain\Memberships\Services\ValidationPlanPrivilegeService;
use Livewire\Attributes\Url;
use Livewire\Component;

class InternationalLicensePurchaseForm extends Component
{
    public Individual $individual;

    #[Url(as: 'filter[sport]')]
    public $sport; // Sport filter

    public $baseLicenses = [];
    public $selectedLicenseId;
    public $selectedLicense;
    public $calculatedPrice = 0;
    public $search = '';
    public $sortBy = 'name';

    public function mount(Individual $individual, $sport = null)
    {
        $this->individual = $individual;
        $this->sport = $sport;
        $this->loadBaseLicenses();
    }

    public function getIndividualHasActiveAffiliationProperty()
    {
        return $this->individual->hasActiveAffiliation();
    }

    public function getActiveLicenseIdsProperty()
    {
        return LicenseAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
            ->where('model_type', 'individual')
            ->where('model_id', $this->individual->id)
            ->whereHas('license', function ($query) {
                $query->whereHas('committee', fn ($q) => $q->where('is_international', true));
            })
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
        return LicenseAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
            ->where('model_type', 'individual')
            ->where('model_id', $this->individual->id)
            ->whereHas('license', function ($query) {
                $query->whereHas('committee', fn ($q) => $q->where('is_international', true));
            })
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

        if ($license->status_class === 'Domain\\Licenses\\States\\ActiveLicenseAttributedState') {
            return 'active';
        }

        return 'pending';
    }

    public function getLicenseAttributedWithDocumentProperty()
    {
        // Get all international license attributed records for this individual with their documents
        return LicenseAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
            ->where('model_type', 'individual')
            ->where('model_id', $this->individual->id)
            ->whereHas('license', function ($query) {
                $query->whereHas('committee', fn ($q) => $q->where('is_international', true));
            })
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
            $query->where('owner_type', LicenseAttributed::class)
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
        return view('livewire.individual.international-license-purchase-form', [
            'licenses' => $this->getFilteredLicenses(),
            'availableSports' => $this->getAvailableSports(),
        ]);
    }

    public function getAvailableSports()
    {
        // Get sports from international licenses available for individuals
        $licenses = License::query()
            ->withoutGlobalScope(ExcludeInternationalScope::class)
            ->forRequesterType(Individual::class)
            ->where('active', true)
            ->whereHas('committee', fn ($q) => $q->where('is_international', true))  // Only international licenses
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

            // If not found in base licenses, fetch from database
            if (! $this->selectedLicense) {
                $this->selectedLicense = License::withoutGlobalScope(ExcludeInternationalScope::class)
                    ->whereHas('committee', fn ($q) => $q->where('is_international', true))
                    ->find($licenseId);
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
        // Get all federation IDs from individual's federations that have international licenses
        $federationIds = $this->individual->federations()
            ->whereHas('licenses', function ($query) {
                $query->whereHas('committee', fn ($q) => $q->where('is_international', true));
            })
            ->pluck('id')
            ->toArray();

        if (empty($federationIds)) {
            $this->baseLicenses = [];

            return;
        }

        // Get international licenses where requester_model includes Individual
        $query = License::query()
            ->withoutGlobalScope(ExcludeInternationalScope::class)
            ->forRequesterType(Individual::class)
            ->where('active', true)
            ->whereHas('committee', fn ($q) => $q->where('is_international', true)); // Only international licenses

        // Apply sport filter if specified
        if ($this->sport) {
            $query->where('sport_id', $this->sport);
        }

        // Filter by federations that have international licenses
        $query->whereHas('federations', function ($q) use ($federationIds) {
            $q->whereIn('federation_id', $federationIds);
        });

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
            session()->flash('error', __('Please select a valid international license.'));

            return;
        }

        // Find the international license and calculate price
        $license = License::withoutGlobalScope(ExcludeInternationalScope::class)
            ->whereHas('committee', fn ($q) => $q->where('is_international', true))
            ->find($licenseId);

        if (! $license) {
            session()->flash('error', __('International license not found.'));

            return;
        }

        // Calculate price
        $calculatePriceAction = new CalculateLicensePriceAction;
        $calculatedPrice = $calculatePriceAction($license, Individual::class);

        if ($calculatedPrice === null || $calculatedPrice <= 0) {
            session()->flash('error', __('This international license cannot be purchased with this method.'));

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

            session()->flash('success', __('International license purchase initiated. You will receive payment instructions shortly.'));

            // Refresh the component to show the updated status and document link
            $this->loadBaseLicenses();
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            logger('International license purchase error: ' . $e->getMessage());
            session()->flash('error', __('Failed to purchase international license. Please try again.'));
        }
    }

    public function requestFreeLicense($licenseId = null)
    {
        // Use passed licenseId or fall back to selectedLicenseId
        $licenseId = $licenseId ?: $this->selectedLicenseId;

        if (! $licenseId) {
            session()->flash('error', __('Please select a valid international license.'));

            return;
        }

        // Find the international license and calculate price
        $license = License::withoutGlobalScope(ExcludeInternationalScope::class)
            ->whereHas('committee', fn ($q) => $q->where('is_international', true))
            ->find($licenseId);

        if (! $license) {
            session()->flash('error', __('International license not found.'));

            return;
        }

        // Calculate price
        $calculatePriceAction = new CalculateLicensePriceAction;
        $calculatedPrice = $calculatePriceAction($license, Individual::class);

        if ($calculatedPrice !== 0) {
            session()->flash('error', __('This international license is not free.'));

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

            session()->flash('success', __('Free international license requested and activated successfully!'));

            // Refresh the component to show the updated status
            $this->loadBaseLicenses();
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            logger('Free international license request error: ' . $e->getMessage());
            session()->flash('error', __('Failed to request international license. Please try again.'));
        }
    }
}
