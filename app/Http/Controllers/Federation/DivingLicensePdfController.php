<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class DivingLicensePdfController extends Controller
{
    /**
     * Download the PDF for federation
     */
    public function show(string $id)
    {
        try {
            $licenseAttributed = LicenseAttributed::with([
                'license.committee',
                'owner',
                'divingTechnicalDirectors.individual',
            ])->findOrFail($id);

            // Use policy for federation context
            Gate::authorize('viewPdf', $licenseAttributed);

            $pdf = PDF::loadView('web.entity.diving_licenses.pdf', compact('licenseAttributed'))
                ->setPaper('a4', 'landscape');

            // Download the generated PDF
            return $pdf->download($licenseAttributed->id . '_diving_license.pdf');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized federation PDF access attempt', [
                'user_id' => auth()->id(),
                'license_id' => $id,
            ]);

            abort(403, __('diving.unauthorized_access'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Federation PDF requested for non-existent license', [
                'user_id' => auth()->id(),
                'license_id' => $id,
            ]);

            abort(404, __('diving.license_not_found'));
        } catch (\Exception $e) {
            Log::error('Federation PDF generation failed', [
                'user_id' => auth()->id(),
                'license_id' => $id,
            ]);

            return redirect()->back()->with('error', __('diving.failed_generate_pdf'));
        }
    }
}
