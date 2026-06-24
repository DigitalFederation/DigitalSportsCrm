<?php

namespace App\Http\Controllers\International\Entity;

use App\Http\Controllers\Controller;
use App\Traits\ChecksInternationalLicenseAccess;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Actions\CalculateLicensePriceAction;
use Domain\Licenses\Actions\CalculateLicenseValidityDatesAction;
use Domain\Licenses\Actions\CreateLicenseAttributedAction;
use Domain\Licenses\Actions\PurchaseLicenseAction;
use Domain\Licenses\Actions\PurchaseLicenseForGroupAction;
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
 * international Entity License Purchase Controller
 *
 * Handles INTERNATIONAL license purchases ONLY (is_international = true)
 * National license purchases are handled in regular Entity namespace
 */
class LicensePurchaseController extends Controller
{
    use ChecksInternationalLicenseAccess;

    public function index(Request $request): View
    {
        // Get the entity for the current user
        $entity = Auth::user()->getEntity();

        if (! $entity) {
            abort(403, __('admin.error.no_entity_profile'));
        }

        // Check if entity has access to international licenses
        $this->requireInternationalLicenseAccess($entity);

        $federation = $entity->federations()
            ->whereHas('licenses', function ($query) {
                $query->whereHas('committee', fn ($cq) => $cq->where('is_international', true));
            })
            ->first();

        // Validate and sanitize committee filter - only allow international committees
        $requestedCommittee = $request->get('committee') ?? $request->get('filter')['committee'] ?? 'diving';
        $allowedCommittees = ['diving', 'scientific'];
        $committee = in_array(strtolower($requestedCommittee), $allowedCommittees)
            ? strtolower($requestedCommittee)
            : 'diving';

        return view('web.international.entity.license-purchase.index', [
            'entity' => $entity,
            'federation' => $federation,
            'committee' => $committee,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'license_id' => 'required|exists:license,id',
            'entity_id' => 'required|exists:entity,id',
            'license_type' => 'required|in:entity,members',
            'individual_ids' => 'required_if:license_type,members|array|min:1',
            'individual_ids.*' => 'exists:individual,id',
        ]);

        // Verify user owns this entity
        $entity = Entity::find($request->entity_id);
        if (! $entity || ! $entity->users()->where('user_id', Auth::id())->exists()) {
            abort(403, __('admin.error.unauthorized'));
        }

        // Check if entity has access to international licenses
        $this->requireInternationalLicenseAccess($entity);

        // Get entity's active federations with international licenses
        $federationIds = $entity->federations()
            ->where('entity_federation.status_class', 'Domain\\Entities\\States\\ActiveEntityFederationState')
            ->whereHas('licenses', function ($query) {
                $query->whereHas('committee', fn ($cq) => $cq->where('is_international', true));
            })
            ->pluck('federation_id');

        // CRITICAL: Find international license
        $license = License::withoutGlobalScope(ExcludeInternationalScope::class)
            ->whereHas('committee', fn ($q) => $q->where('is_international', true))
            ->where('id', $request->license_id)
            ->forFederationEntities($federationIds)
            ->with(['committee', 'type', 'professionalRole', 'sport', 'federations'])
            ->first();

        if (! $license) {
            \Log::error('CMAS License not found', [
                'license_id' => $request->license_id,
                'entity_id' => $entity->id,
                'federation_ids' => $federationIds->toArray(),
            ]);

            return back()->withErrors(['license_id' => __('admin.error.license_not_found')])->withInput();
        }

        try {
            $calculatePriceAction = new CalculateLicensePriceAction;

            if ($request->license_type === 'entity') {
                // Entity purchasing license for itself
                $purchaseAction = new PurchaseLicenseAction(
                    new CreateLicenseAttributedAction,
                    $calculatePriceAction,
                    new ValidationPlanPrivilegeService,
                    new CalculateLicenseValidityDatesAction,
                    new ValidateLicenseDocumentRequirementsAction
                );

                $licenseAttributed = $purchaseAction($license, $entity);
                $totalPrice = $calculatePriceAction($license, Entity::class);

                \Log::info('CMAS License Purchase: Entity license created', [
                    'license_attributed_id' => $licenseAttributed->id,
                    'entity_id' => $entity->id,
                    'license_name' => $license->name,
                    'total_amount' => $totalPrice,
                ]);

                return redirect()
                    ->route('international.entity.license-purchase.success')
                    ->with('license_attributed_id', $licenseAttributed->id)
                    ->with('purchase_type', 'entity')
                    ->with('license_name', $license->name)
                    ->with('total_amount', $totalPrice)
                    ->with('purchase_timestamp', now()->timestamp);

            } else {
                // Member licenses - purchase for selected individuals
                $individuals = Individual::whereIn('id', $request->individual_ids)->get();

                $groupPurchaseAction = new PurchaseLicenseForGroupAction(
                    new CreateLicenseAttributedAction,
                    $calculatePriceAction,
                    new ValidationPlanPrivilegeService,
                    new ValidateLicenseDocumentRequirementsAction,
                    new CalculateLicenseValidityDatesAction
                );

                $licenses = $groupPurchaseAction($license, $entity, $individuals);

                if (! $licenses || $licenses->isEmpty()) {
                    throw new \Exception(__('admin.error.no_licenses_created'));
                }

                $pricePerLicense = $calculatePriceAction($license, Entity::class);
                $totalPrice = $pricePerLicense * $individuals->count();
                $firstLicenseAttributedId = $licenses->first()?->id;

                \Log::info('CMAS License Purchase: Member licenses created', [
                    'first_license_attributed_id' => $firstLicenseAttributedId,
                    'license_count' => $licenses->count(),
                    'entity_id' => $entity->id,
                    'license_name' => $license->name,
                    'total_amount' => $totalPrice,
                ]);

                return redirect()
                    ->route('international.entity.license-purchase.success')
                    ->with('success', __('admin.success.member_licenses_purchased', ['count' => $licenses->count()]))
                    ->with('license_attributed_id', $firstLicenseAttributedId)
                    ->with('license_count', $licenses->count())
                    ->with('purchase_type', 'members')
                    ->with('license_name', $license->name)
                    ->with('total_amount', $totalPrice)
                    ->with('member_count', $individuals->count())
                    ->with('purchase_timestamp', now()->timestamp);
            }

        } catch (\Exception $e) {
            \Log::error('CMAS License Purchase Failed', [
                'entity_id' => $entity->id,
                'license_id' => $request->license_id,
                'license_type' => $request->license_type,
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
        $licenseCount = session('license_count');
        $purchaseType = session('purchase_type');
        $licenseName = session('license_name');
        $totalAmount = session('total_amount');
        $memberCount = session('member_count');
        $purchaseTimestamp = session('purchase_timestamp');

        if (! $licenseAttributedId && ! $licenseCount) {
            return redirect()->route('international.entity.license-purchase.index')
                ->with('error', __('admin.error.no_license_purchase'));
        }

        $licenseAttributed = null;
        if ($licenseAttributedId) {
            $licenseAttributed = LicenseAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
                ->with(['license', 'owner'])
                ->find($licenseAttributedId);
        }

        // Find document created by the purchase event
        $entity = Auth::user()->getEntity();
        $document = null;

        if ($entity && $licenseAttributedId) {
            $document = \Domain\Documents\Models\Document::where('owner_type', 'entity')
                ->where('owner_id', $entity->id)
                ->whereHas('type', function ($query) {
                    $query->where('code', 'ORD');
                })
                ->whereHas('details', function ($query) use ($licenseAttributedId) {
                    $query->where('owner_type', LicenseAttributed::class)
                        ->where('owner_id', $licenseAttributedId);
                })
                ->latest()
                ->first();
        }

        $license = (object) ['name' => $licenseName];

        return view('web.international.entity.license-purchase.success', compact(
            'licenseAttributed',
            'licenseCount',
            'purchaseType',
            'license',
            'totalAmount',
            'memberCount',
            'document'
        ));
    }
}
