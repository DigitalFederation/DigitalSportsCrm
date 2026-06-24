<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Domain\Certifications\Models\CertificationAttributed;
use Illuminate\Support\Facades\Log;

class CertificationCardPdfController extends Controller
{
    public function preview(string $id)
    {

        $individual = auth()->user()->individual;

        $certification_attributed = CertificationAttributed::with('individual', 'federation.media')
            ->where('individual_id', $individual->id)
            ->findOrFail($id);

        return view('web.individual.certification_card.pdf', compact('certification_attributed'));
    }

    public function show(string $id)
    {
        $individual = auth()->user()->individual;
        try {
            $certification_attributed = CertificationAttributed::with('individual', 'federation.media')
                ->where('individual_id', $individual->id)
                ->findOrFail($id);

            $pdf = PDF::loadView('web.individual.certification_card.pdf', compact('certification_attributed'));

            // Download the generated PDF
            return $pdf->download($id . '_certification_card.pdf');
        } catch (\Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage(), ['exception' => $e]);

            // Redirect back with an error message
            return redirect()->back()->with('error', 'Failed to generate PDF');
        }
    }

    public function card(string $id)
    {
        $individual = auth()->user()->individual;
        try {
            $certification_attributed = CertificationAttributed::with('individual')
                ->where('individual_id', $individual->id)
                ->findOrFail($id);

            $pdf = PDF::loadView('web.individual.certification_card.card_pdf', compact('certification_attributed'));

            // Stream the generated PDF
            return $pdf->stream($id . '_certification_card.pdf');
        } catch (\Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage(), ['exception' => $e]);

            // Redirect back with an error message
            return redirect()->back()->with('error', 'Failed to generate PDF');
        }
    }
}
