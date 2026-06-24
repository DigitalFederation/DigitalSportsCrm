<?php

namespace App\Livewire;

use Barryvdh\DomPDF\Facade\Pdf;
use Domain\Certifications\Models\CertificationAttributed;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class GenerateIndividualCertificationCardPdf extends Component
{
    public $certificationAttributedId;
    public $loading = false;
    public $individualId;
    public $showDownloadButton = true;

    public function mount(): void
    {
        // Check if certification is from Sport committee - hide download button for Sport
        if ($this->certificationAttributedId) {
            $certification = CertificationAttributed::with('certification.committee')
                ->find($this->certificationAttributedId);

            if ($certification && $certification->certification && $certification->certification->committee) {
                // Hide button for Sport committee (code = 'SPORT')
                $this->showDownloadButton = $certification->certification->committee->code !== 'SPORT';
            }
        }
    }

    public function generatePdf()
    {
        $this->loading = true;
        try {
            $certification_attributed = CertificationAttributed::with('individual', 'federation.media')
                ->findOrFail($this->certificationAttributedId);

            // Authorization check
            if (! $this->canAccessCertification($certification_attributed)) {
                throw new \Exception('Unauthorized access to certification');
            }

            Notification::make()
                ->title('Please wait while PDF is being generated.')
                ->success()
                ->send();

            $pdf = PDF::loadView('web.individual.certification_card.pdf', compact('certification_attributed'));

            // Download the generated PDF
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->stream();
            }, $this->certificationAttributedId . '_certification_card.pdf');

        } catch (\Exception $e) {

            Log::error('Error generating PDF: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => Auth::id(),
                'certification_id' => $this->certificationAttributedId,
            ]);

            Notification::make()
                ->title('Error while generating the PDF. Please try later.')
                ->danger()
                ->send();

            return redirect()->back();
        } finally {
            $this->loading = false;
        }
    }

    private function canAccessCertification(CertificationAttributed $certification): bool
    {
        $user = Auth::user();

        // Allow access when an admin is impersonating another user
        if (session()->has('impersonate_original')) {
            return true;
        }

        if ($user->isAdmin() || $user->isFederation()) {
            return true;
        }

        if ($user->isIndividual()) {
            return $user->individual->id === $certification->individual_id;
        }

        return false;
    }

    public function render()
    {
        return view('livewire.generate-individual-certification-card-pdf');
    }
}
