<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Support\Committees;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Actions\CalculateLicensePriceAction;
use Domain\Licenses\Actions\CreateLicenseAttributedAction;
use Domain\Licenses\Actions\PurchaseLicenseAction;
use Domain\Licenses\Models\License;
use Domain\Memberships\Services\ValidationPlanPrivilegeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LicensePurchaseController extends Controller
{
    /**
     * Render an individual's committee license purchase page.
     *
     * The committee code comes from the route default declared in
     * routes/individual.php, generated from config/committees.php. The
     * international flag is derived from the committee and the page titles from
     * the committee's configured individual purchase labels.
     */
    public function show(Request $request)
    {
        return $this->renderPurchasePage($request->route('committeeCode'));
    }

    /**
     * Shared render method for all committee license purchase pages.
     */
    private function renderPurchasePage(string $committee)
    {
        $individual = Auth::user()->individual;

        if (! $individual) {
            abort(403, 'No individual profile associated with this user');
        }

        $committeeModel = Committee::where('code', $committee)->first();
        $isInternational = (bool) $committeeModel?->is_international;

        $federation = Federation::where('is_default_federation', true)->first();

        $page = Committees::purchasePage($committee, 'individual') ?? [];
        $label = $committeeModel?->name ?? $committee;
        $pageTitle = isset($page['title'])
            ? __($page['title'])
            : __('licenses.Purchase :committee Licenses', ['committee' => $label]);
        $pageSubtitle = isset($page['subtitle'])
            ? __($page['subtitle'])
            : __('licenses.Select members and purchase :committee licenses on their behalf', ['committee' => $label]);

        return view('web.individual.license-purchase.index', [
            'individual' => $individual,
            'federation' => $federation,
            'committee' => $committee,
            'isInternational' => $isInternational,
            'pageTitle' => $pageTitle,
            'pageSubtitle' => $pageSubtitle,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'license_id' => 'required|exists:license,id',
            'individual_id' => 'required|exists:individual,id',
        ]);

        // Verify user owns this individual
        $individual = Individual::find($request->individual_id);
        if (! $individual || $individual->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $license = License::find($request->license_id);

        $purchaseAction = app(PurchaseLicenseAction::class);

        try {
            $licenseAttributed = $purchaseAction($license, $individual);

            return redirect()
                ->route('individual.license-purchase.success')
                ->with('success', __('License purchase initiated. Please complete payment to activate your license.'))
                ->with('license_attributed_id', $licenseAttributed->id);

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function success()
    {
        $licenseAttributedId = session('license_attributed_id');

        if (! $licenseAttributedId) {
            return redirect()->route(Committees::defaultPurchaseRouteName('individual'))
                ->with('error', 'No license purchase found.');
        }

        $licenseAttributed = \Domain\Licenses\Models\LicenseAttributed::with(['license.professionalRole', 'license.sport', 'owner'])
            ->find($licenseAttributedId);

        if (! $licenseAttributed) {
            return redirect()->route(Committees::defaultPurchaseRouteName('individual'))
                ->with('error', 'License purchase not found.');
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

        return view('web.individual.license-purchase.success', compact('licenseAttributed', 'license', 'document', 'individual'));
    }
}
