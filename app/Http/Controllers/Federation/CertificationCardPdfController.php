<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Domain\Certifications\Models\CertificationAttributed;

class CertificationCardPdfController extends Controller
{
    public function card(string $id)
    {
        $certification_attributed = CertificationAttributed::with('individual')->findOrFail($id);

        // dd($certification_attributed->individual->qrcode_path);
        $pdf = PDF::loadView('web.federation.certification_card.card_pdf', compact('certification_attributed'))
            ->setPaper('a7', 'landscape');

        // Download the generated PDF
        return $pdf->stream($id.'_certification_card.card');
    }
}
