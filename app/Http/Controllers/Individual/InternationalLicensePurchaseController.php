<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use App\Traits\ChecksInternationalLicenseAccess;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Actions\CalculateLicensePriceAction;
use Domain\Licenses\Actions\CreateLicenseAttributedAction;
use Domain\Licenses\Actions\PurchaseLicenseAction;
use Domain\Licenses\Models\License;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Domain\Memberships\Services\ValidationPlanPrivilegeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InternationalLicensePurchaseController extends Controller
{
    use ChecksInternationalLicenseAccess;

    public function index(Request $request): View
    {
        // Get the individual for the current user
        $individual = Auth::user()->individuals()->with('federations.licenses')->first();

        if (! $individual) {
            abort(403, __('No individual profile associated with this user'));
        }

        // Check if individual has access to international licenses through federation
        $this->requireInternationalLicenseAccess($individual);

        $committee = $request->get('filter')['committee'] ?? 'diving';

        return view('web.individual.international-license-purchase.index', [
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
            abort(403, __('Unauthorized'));
        }

        // Check if individual has access to international licenses
        $this->requireInternationalLicenseAccess($individual);

        // Find international license - must explicitly remove scope and filter for international
        $license = License::withoutGlobalScope(ExcludeInternationalScope::class)
            ->whereHas('committee', fn ($q) => $q->where('is_international', true))
            ->where('id', $request->license_id)
            ->first();

        if (! $license) {
            // Check if license exists but is not international
            $nationalLicense = License::find($request->license_id);
            if ($nationalLicense) {
                return back()
                    ->withErrors(['license_id' => __('The selected license is not an international license.')])
                    ->withInput();
            }

            return back()
                ->withErrors(['license_id' => __('The selected international license could not be found.')])
                ->withInput();
        }

        $purchaseAction = new PurchaseLicenseAction(
            new CreateLicenseAttributedAction,
            new CalculateLicensePriceAction,
            new ValidationPlanPrivilegeService,
            new \Domain\Licenses\Actions\CalculateLicenseValidityDatesAction,
            new \Domain\Licenses\Actions\ValidateLicenseDocumentRequirementsAction
        );

        try {
            $licenseAttributed = $purchaseAction($license, $individual);

            return redirect()
                ->route('individual.international-license-purchase.success')
                ->with('success', __('International license purchase initiated. Please complete payment to activate your license.'))
                ->with('license_attributed_id', $licenseAttributed->id);

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function success(): View|RedirectResponse
    {
        $licenseAttributedId = session('license_attributed_id');

        if (! $licenseAttributedId) {
            return redirect()->route('individual.international-license-purchase.index')
                ->with('error', __('No international license purchase found.'));
        }

        $licenseAttributed = \Domain\Licenses\Models\LicenseAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
            ->with(['license.professionalRole', 'license.sport', 'owner'])
            ->find($licenseAttributedId);

        if (! $licenseAttributed) {
            return redirect()->route('individual.international-license-purchase.index')
                ->with('error', __('International license purchase not found.'));
        }

        $license = $licenseAttributed->license;

        // Get the document (invoice) created for this license
        $individual = $licenseAttributed->owner;
        $document = null;

        if ($individual instanceof Individual) {
            // Find document through document_details relationship
            $document = \Domain\Documents\Models\Document::where('owner_type', 'individual')
                ->where('owner_id', $individual->id)
                ->whereHas('type', function ($query) {
                    $query->where('code', 'ORD');
                })
                ->whereHas('details', function ($query) use ($licenseAttributed) {
                    $query->where('owner_type', \Domain\Licenses\Models\LicenseAttributed::class)
                        ->where('owner_id', $licenseAttributed->id);
                })
                ->latest()
                ->first();
        }

        return view('web.individual.international-license-purchase.success', compact('licenseAttributed', 'license', 'document', 'individual'));
    }
}
