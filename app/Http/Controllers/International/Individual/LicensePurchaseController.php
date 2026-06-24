<?php

namespace App\Http\Controllers\International\Individual;

use App\Http\Controllers\Controller;
use App\Traits\ChecksInternationalLicenseAccess;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Actions\CalculateLicensePriceAction;
use Domain\Licenses\Actions\CalculateLicenseValidityDatesAction;
use Domain\Licenses\Actions\CreateLicenseAttributedAction;
use Domain\Licenses\Actions\PurchaseLicenseAction;
use Domain\Licenses\Actions\ValidateLicenseDocumentRequirementsAction;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Domain\Memberships\Services\ValidationPlanPrivilegeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * international Individual License Purchase Controller
 *
 * Handles INTERNATIONAL license purchases ONLY (is_international = true)
 * National license purchases are handled in the regular Individual namespace
 */
class LicensePurchaseController extends Controller
{
    use ChecksInternationalLicenseAccess;

    public function index(Request $request): View
    {
        // Get the individual for the current user
        $individual = Auth::user()->individuals()->with('federations.licenses')->first();

        if (! $individual) {
            abort(403, __('admin.error.no_individual_profile'));
        }

        // Check if individual has access to international licenses through federation
        // Permission is also checked by route middleware, but this provides detailed message
        $this->requireInternationalLicenseAccess($individual);

        // Validate and sanitize committee filter - only allow international committees
        $requestedCommittee = $request->get('filter')['committee'] ?? 'diving';
        $allowedCommittees = ['diving', 'scientific'];
        $committee = in_array(strtolower($requestedCommittee), $allowedCommittees)
            ? strtolower($requestedCommittee)
            : 'diving';

        return view('web.international.individual.license-purchase.index', [
            'individual' => $individual,
            'committee' => $committee,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'license_id' => 'required|exists:license,id',
            'individual_id' => 'required|exists:individual,id',
        ]);

        // Verify user owns this individual
        $individual = Individual::find($request->individual_id);
        if (! $individual || $individual->user_id !== Auth::id()) {
            abort(403, __('admin.error.unauthorized'));
        }

        // Check if individual has access to international licenses
        $this->requireInternationalLicenseAccess($individual);

        // CRITICAL: Find international license - must explicitly remove scope and filter for international
        $license = License::withoutGlobalScope(ExcludeInternationalScope::class)
            ->whereHas('committee', fn ($q) => $q->where('is_international', true))
            ->where('id', $request->license_id)
            ->first();

        if (! $license) {
            // Check if license exists but is not international
            $nationalLicense = License::find($request->license_id);
            if ($nationalLicense) {
                return back()
                    ->withErrors(['license_id' => __('admin.error.not_international_license')])
                    ->withInput();
            }

            return back()
                ->withErrors(['license_id' => __('admin.error.license_not_found')])
                ->withInput();
        }

        $purchaseAction = new PurchaseLicenseAction(
            new CreateLicenseAttributedAction,
            new CalculateLicensePriceAction,
            new ValidationPlanPrivilegeService,
            new CalculateLicenseValidityDatesAction,
            new ValidateLicenseDocumentRequirementsAction
        );

        try {
            $licenseAttributed = $purchaseAction($license, $individual);

            return redirect()
                ->route('international.individual.license-purchase.success')
                ->with('success', __('admin.success.license_purchase_initiated'))
                ->with('license_attributed_id', $licenseAttributed->id);

        } catch (\Exception $e) {
            \Log::error('CMAS License Purchase Failed', [
                'individual_id' => $individual->id,
                'license_id' => $request->license_id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function success(): View|RedirectResponse
    {
        $licenseAttributedId = session('license_attributed_id');

        if (! $licenseAttributedId) {
            return redirect()->route('international.individual.license-purchase.index')
                ->with('error', __('admin.error.no_license_purchase'));
        }

        // CRITICAL: Remove scope to access international license
        $licenseAttributed = LicenseAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
            ->with(['license.professionalRole', 'license.sport', 'license.committee', 'owner'])
            ->find($licenseAttributedId);

        if (! $licenseAttributed) {
            return redirect()->route('international.individual.license-purchase.index')
                ->with('error', __('admin.error.license_purchase_not_found'));
        }

        $license = $licenseAttributed->license;
        $individual = $licenseAttributed->owner;
        $document = null;

        // Get the document (invoice) created for this license
        if ($individual instanceof Individual) {
            // Find document through document_details relationship
            $document = \Domain\Documents\Models\Document::where('owner_type', 'individual')
                ->where('owner_id', $individual->id)
                ->whereHas('type', function ($query) {
                    $query->where('code', 'ORD');
                })
                ->whereHas('details', function ($query) use ($licenseAttributed) {
                    $query->where('owner_type', LicenseAttributed::class)
                        ->where('owner_id', $licenseAttributed->id);
                })
                ->latest()
                ->first();
        }

        return view('web.international.individual.license-purchase.success', compact(
            'licenseAttributed',
            'license',
            'document',
            'individual'
        ));
    }
}
