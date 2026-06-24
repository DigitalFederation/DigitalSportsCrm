<?php

namespace App\Livewire;

use App\Exports\FederationCertificationsExport;
use App\Exports\FederationEntitiesExport;
use App\Exports\FederationIndividualsExport;
use App\Exports\FederationLicensesExport;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class FederationExportButton extends Component
{
    public $isLoading = false;
    public $isExportCompleted = false;
    public $exportFileName = null;
    public $exportType = null;
    public $buttonTitle = null;

    public function __construct()
    {
        if ($this->buttonTitle === null) {
            $this->buttonTitle = str_replace('_', ' ', $this->exportType);
        }
    }

    public function export()
    {
        // Check if the export is allowed based on certain conditions
        if (! $this->isExportAllowed()) {
            return;
        }

        // Check if the user has exceeded the rate limit
        if (! $this->checkRateLimit()) {
            return;
        }

        $this->isLoading = true;
        $this->isExportCompleted = false;

        $this->exportFileName = 'export-' . $this->exportType . '-' . now()->format('Y-m-d-His') . '.xlsx';
        $exportClass = $this->getExportClass();

        // Excel::store(new $exportClass, $this->exportFileName, 'exports');
        return Excel::download($exportClass, $this->exportFileName);

        /*
        $this->isLoading = false;
        $this->isExportCompleted = true;

        if ($this->isExportCompleted) {
            return $this->downloadFile();
        }
        */
    }

    private function getExportClass()
    {

        switch ($this->exportType) {
            case 'individuals':
                return new FederationIndividualsExport;
            case 'entities':
                return new FederationEntitiesExport;
            case 'certifications_diving':
                return new FederationCertificationsExport('diving');
            case 'certifications_sport':
                return new FederationCertificationsExport('sport');
            case 'certifications_scientific':
                return new FederationCertificationsExport('scientific');
            case 'licenses_individual_diving':
                return new FederationLicensesExport('diving', 'individual');
            case 'licenses_individual_sport':
                return new FederationLicensesExport('sport', 'individual');
            case 'licenses_individual_scientific':
                return new FederationLicensesExport('scientific', 'individual');
            case 'licenses_entity_diving':
                return new FederationLicensesExport('diving', 'entity');
            case 'licenses_entity_sport':
                return new FederationLicensesExport('sport', 'entity');
            default:
                throw new \InvalidArgumentException('Invalid export type.');
        }
    }

    private function downloadFile()
    {

        if (Storage::disk('exports')->exists($this->exportFileName)) {
            return Storage::disk('exports')->download($this->exportFileName);
        }

        logger('Error downloading file:'.$this->exportFileName);

        abort(404);
    }

    private function isExportAllowed()
    {
        // Add your custom validation logic here
        // For example, check if the user has the necessary permissions or meets certain criteria
        return true;
    }

    private function checkRateLimit()
    {
        $key = 'export-rate-limit:' . auth()->id();
        $allowedAttempts = 3; // Number of allowed exports within the time frame
        $decayMinutes = 1; // Time frame in minutes

        if (RateLimiter::tooManyAttempts($key, $allowedAttempts)) {
            $this->addError('rate_limit', 'Too many export attempts. Please try again later.');

            return false;
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        return true;
    }

    public function deleteExportFile()
    {
        if (Storage::disk('exports')->exists($this->exportFileName)) {
            Storage::disk('exports')->delete($this->exportFileName);
        }
    }

    public function render()
    {
        return view('livewire.federation-export-button');
    }
}
