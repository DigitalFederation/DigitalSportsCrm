<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\Services\CertificationCardGeneratorService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificationCardController extends Controller
{
    public function download(
        CertificationAttributed $certificationAttributed,
        CertificationCardGeneratorService $cardGenerator): StreamedResponse
    {
        $path = $cardGenerator->generate($certificationAttributed);

        return Storage::disk('public')->download($path, "certification_card_{$certificationAttributed->id}.png");
    }
}
