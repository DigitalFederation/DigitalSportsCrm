<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class DivingLicensePdfController extends Controller
{
    /**
     * Download the PDF
     */
    public function show(string $id)
    {
        try {
            $licenseAttributed = LicenseAttributed::with([
                'license.committee',
                'owner',
                'divingTechnicalDirectors.individual',
            ])->findOrFail($id);

            // Check if the license is active
            if ($licenseAttributed->status_class !== \Domain\Licenses\States\ActiveLicenseAttributedState::class) {
                Log::warning('PDF access denied for non-active license', [
                    'user_id' => auth()->id(),
                    'license_id' => $id,
                    'status' => class_basename($licenseAttributed->status_class),
                ]);

                return redirect()->back()->with('error', __('diving.pdf_only_active_licenses'));
            }

            // Use policy for authorization
            Gate::authorize('viewPdf', $licenseAttributed);

            $pdf = PDF::loadView('web.entity.diving_licenses.pdf', compact('licenseAttributed'))
                ->setPaper('a4', 'landscape');

            // Download the generated PDF
            return $pdf->download($licenseAttributed->id . '_diving_license.pdf');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized PDF access attempt', [
                'user_id' => auth()->id(),
                'license_id' => $id,
            ]);

            abort(403, __('diving.unauthorized_access'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('PDF requested for non-existent license', [
                'user_id' => auth()->id(),
                'license_id' => $id,
            ]);

            abort(404, __('diving.license_not_found'));
        } catch (\Exception $e) {
            Log::error('PDF generation failed', [
                'user_id' => auth()->id(),
                'license_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', __('diving.failed_generate_pdf'));
        }
    }

    /**
     * Stream the PDF in browser
     */
    public function stream(string $id)
    {
        try {
            $licenseAttributed = LicenseAttributed::with([
                'license.committee',
                'owner',
                'divingTechnicalDirectors.individual',
            ])->findOrFail($id);

            // Check if the license is active
            if ($licenseAttributed->status_class !== \Domain\Licenses\States\ActiveLicenseAttributedState::class) {
                Log::warning('PDF stream denied for non-active license', [
                    'user_id' => auth()->id(),
                    'license_id' => $id,
                    'status' => class_basename($licenseAttributed->status_class),
                ]);

                return redirect()->back()->with('error', __('diving.pdf_only_active_licenses'));
            }

            // Use policy for authorization
            Gate::authorize('viewPdf', $licenseAttributed);

            $pdf = PDF::loadView('web.entity.diving_licenses.pdf', compact('licenseAttributed'))
                ->setPaper('a4', 'landscape');

            // Stream the generated PDF
            return $pdf->stream($licenseAttributed->id . '_diving_license.pdf');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized PDF stream attempt', [
                'user_id' => auth()->id(),
                'license_id' => $id,
            ]);

            abort(403, __('diving.unauthorized_access'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('PDF stream requested for non-existent license', [
                'user_id' => auth()->id(),
                'license_id' => $id,
            ]);

            abort(404, __('diving.license_not_found'));
        } catch (\Exception $e) {
            Log::error('PDF streaming failed', [
                'user_id' => auth()->id(),
                'license_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', __('diving.failed_generate_pdf'));
        }
    }

    /**
     * Preview the PDF in browser
     */
    public function preview(string $id)
    {
        try {
            $licenseAttributed = LicenseAttributed::with([
                'license.committee',
                'owner',
                'divingTechnicalDirectors.individual',
            ])->findOrFail($id);

            // Check if the license is active
            if ($licenseAttributed->status_class !== \Domain\Licenses\States\ActiveLicenseAttributedState::class) {
                Log::warning('PDF preview denied for non-active license', [
                    'user_id' => auth()->id(),
                    'license_id' => $id,
                    'status' => class_basename($licenseAttributed->status_class),
                ]);

                abort(403, __('diving.pdf_only_active_licenses'));
            }

            // Use policy for authorization
            Gate::authorize('viewPdf', $licenseAttributed);

            return view('web.entity.diving_licenses.pdf', compact('licenseAttributed'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized PDF preview attempt', [
                'user_id' => auth()->id(),
                'license_id' => $id,
            ]);

            abort(403, __('diving.unauthorized_access'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('PDF preview requested for non-existent license', [
                'user_id' => auth()->id(),
                'license_id' => $id,
            ]);

            abort(404, __('diving.license_not_found'));
        } catch (\Exception $e) {
            Log::error('PDF preview failed', [
                'user_id' => auth()->id(),
                'license_id' => $id,
            ]);

            return redirect()->back()->with('error', __('diving.failed_generate_pdf'));
        }
    }
}
