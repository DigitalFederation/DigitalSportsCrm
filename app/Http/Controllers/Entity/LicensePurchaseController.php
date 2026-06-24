<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Support\Committees;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Actions\CalculateLicensePriceAction;
use Domain\Licenses\Actions\CalculateLicenseValidityDatesAction;
use Domain\Licenses\Actions\CreateLicenseAttributedAction;
use Domain\Licenses\Actions\PurchaseLicenseAction;
use Domain\Licenses\Actions\PurchaseLicenseForGroupAction;
use Domain\Licenses\Actions\ValidateLicenseDocumentRequirementsAction;
use Domain\Licenses\Models\License;
use Domain\Memberships\Services\ValidationPlanPrivilegeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LicensePurchaseController extends Controller
{
    /**
     * Render a committee's license purchase page.
     *
     * The committee code and purchase type ('entity' | 'members') come from the
     * route defaults declared in routes/entity.php, which are generated from
     * config/committees.php. The international flag is derived from the committee
     * and the page titles from the committee's configured labels.
     */
    public function show(Request $request)
    {
        return $this->renderPurchasePage(
            $request->route('purchaseType'),
            $request->route('committeeCode')
        );
    }

    /**
     * Render the shared license purchase view for the given committee + type.
     */
    private function renderPurchasePage(string $type, string $committee)
    {
        $entity = Auth::user()->getEntity();

        if (! $entity) {
            abort(403, 'No entity associated with this user');
        }

        $committeeModel = Committee::where('code', $committee)->first();
        $isInternational = (bool) $committeeModel?->is_international;

        $federation = $entity->federations()->first();

        // If requesting member licenses, entity must first have an active entity license
        if ($type === 'members' && ! $entity->hasActiveEntityLicense()) {
            return redirect()
                ->route(Committees::memberEntityRedirectRouteName($committee))
                ->with('error', __('licenses.entity_license_required_for_members'));
        }

        [$pageTitle, $pageSubtitle] = $this->purchaseLabels($committee, $type, $committeeModel);

        return view('web.entity.license-purchase.index', [
            'entity' => $entity,
            'federation' => $federation,
            'committee' => $committee,
            'isInternational' => $isInternational,
            'type' => $type,
            'pageTitle' => $pageTitle,
            'pageSubtitle' => $pageSubtitle,
        ]);
    }

    /**
     * Resolve the page title/subtitle for a committee purchase page, preferring
     * the committee's configured translation keys and falling back to generic,
     * committee-label-driven titles for deployments that don't set them.
     */
    private function purchaseLabels(string $committee, string $type, ?Committee $committeeModel): array
    {
        $page = Committees::purchasePage($committee, $type) ?? [];
        $label = $committeeModel?->name ?? $committee;

        if ($type === 'entity') {
            $title = isset($page['title'])
                ? __($page['title'])
                : __('licenses.Purchase :committee Entity License', ['committee' => $label]);
            $subtitle = isset($page['subtitle'])
                ? __($page['subtitle'])
                : __('licenses.Purchase a :committee license for your entity', ['committee' => $label]);
        } else {
            $title = isset($page['title'])
                ? __($page['title'])
                : __('licenses.Purchase :committee Licenses', ['committee' => $label]);
            $subtitle = isset($page['subtitle'])
                ? __($page['subtitle'])
                : __('licenses.Select members and purchase :committee licenses on their behalf', ['committee' => $label]);
        }

        return [$title, $subtitle];
    }

    public function store(Request $request)
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
            abort(403, 'Unauthorized');
        }

        // Get entity's active federations for license filtering
        $federationIds = $entity->federations()
            ->where('entity_federation.status_class', 'Domain\\Entities\\States\\ActiveEntityFederationState')
            ->pluck('federation_id');

        // Find license with appropriate scope handling
        $committee = $request->get('committee') ?? null;
        $licenseQuery = License::query();

        // Debug logging
        \Log::info('License Purchase Debug', [
            'license_id' => $request->license_id,
            'committee' => $committee,
            'license_type' => $request->license_type,
            'all_request_data' => $request->all(),
        ]);

        // Apply the same filters as in the Livewire component, keyed on the committee's
        // international flag rather than specific committee codes.
        $committeeUpper = $committee ? strtoupper($committee) : null;
        $committeeModel = $committeeUpper ? Committee::where('code', $committeeUpper)->first() : null;
        if ($committeeModel && ! $committeeModel->is_international) {
            // National committee - only national licenses
            $licenseQuery->whereHas('committee', fn ($q) => $q->where('is_international', false));
            \Log::info('License query path: national committee - national only', ['committee' => $committee]);
        } elseif ($committeeModel && $committeeModel->is_international) {
            // International committee - only international licenses
            $licenseQuery->withoutGlobalScope(\Domain\Licenses\Scopes\ExcludeInternationalScope::class)
                ->whereHas('committee', fn ($q) => $q->where('is_international', true));
            \Log::info('License query path: international committee - international only', ['committee' => $committee]);
        } else {
            // No committee specified - for entity license purchases, we need to check
            // the license details to determine if we should remove the scope
            $tempLicense = License::withoutGlobalScopes()->find($request->license_id);
            \Log::info('License query path: no committee - checking license', [
                'temp_license_found' => $tempLicense ? true : false,
                'is_international' => $tempLicense?->isInternationalLicense(),
            ]);
            if ($tempLicense && $tempLicense->isInternationalLicense()) {
                // This is an international license, so we need to remove the scope to find it
                $licenseQuery->withoutGlobalScope(\Domain\Licenses\Scopes\ExcludeInternationalScope::class);
                \Log::info('Removed ExcludeInternationalScope for international license');
            }
            // For national licenses, the default scope behavior is fine
        }

        // Apply federation filtering to ensure entity can only purchase licenses from their federations
        $licenseQuery->forFederationEntities($federationIds);

        // Log the SQL query
        \Log::info('License Query SQL', [
            'sql' => $licenseQuery->toSql(),
            'bindings' => $licenseQuery->getBindings(),
            'federation_ids' => $federationIds->toArray(),
        ]);

        $license = $licenseQuery->with(['committee', 'type', 'professionalRole', 'sport', 'federations'])->find($request->license_id);

        if (! $license) {
            // Try to find without any filters to debug
            $licenseExists = License::withoutGlobalScopes()->find($request->license_id);
            \Log::error('License not found', [
                'license_exists_without_scopes' => $licenseExists ? true : false,
                'license_data' => $licenseExists ? [
                    'id' => $licenseExists->id,
                    'name' => $licenseExists->name,
                    'is_international' => $licenseExists->isInternationalLicense(),
                    'committee_id' => $licenseExists->committee_id,
                ] : null,
            ]);

            return back()->withErrors(['license_id' => 'The selected license could not be found.'])->withInput();
        }

        // Log successful license retrieval
        \Log::info('License found', [
            'license_id' => $license->id,
            'license_name' => $license->name,
            'license_code' => $license->license_code,
            'is_international' => $license->isInternationalLicense(),
        ]);

        // For member licenses, validate entity has active entity license for the sport
        if ($request->license_type === 'members') {
            if (! $entity->hasActiveEntityLicenseForSport($license->sport_id)) {
                \Log::warning('Entity attempted to purchase member license without sport entity license', [
                    'entity_id' => $entity->id,
                    'license_id' => $license->id,
                    'sport_id' => $license->sport_id,
                ]);

                return back()
                    ->withErrors(['error' => __('licenses.entity_sport_license_required')])
                    ->withInput();
            }
        }

        try {
            // Extra safety check
            if (! $license) {
                \Log::error('License is null before PurchaseLicenseAction');
                throw new \Exception('License not found');
            }

            // Ensure license has all required properties
            if (! $license->id || ! $license->license_code) {
                \Log::error('License is missing required properties', [
                    'license' => $license ? $license->toArray() : null,
                    'license_id' => $license ? $license->id : null,
                    'license_code' => $license ? $license->license_code : null,
                ]);
                throw new \Exception('License is missing required properties');
            }

            \Log::info('About to execute PurchaseLicenseAction', [
                'license_id' => $license->id,
                'license_code' => $license->license_code,
                'entity_id' => $entity->id,
            ]);

            $calculatePriceAction = new CalculateLicensePriceAction;
            $document = null;

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

                // Calculate total price for display
                $totalPrice = $calculatePriceAction($license, Entity::class);

                // Document creation is handled automatically by the PurchaseLicenseAction event

                \Log::info('License Purchase: Entity license created', [
                    'license_attributed_id' => $licenseAttributed->id,
                    'entity_id' => $entity->id,
                    'license_name' => $license->name,
                    'total_amount' => $totalPrice,
                ]);

                return redirect()
                    ->route('entity.license-purchase.success')
                  // ->with('success', __('Entity license purchase initiated. Please complete payment to activate the license.'))
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

                // Validate that licenses were created successfully
                if (! $licenses || $licenses->isEmpty()) {
                    throw new \Exception('No licenses were created during the purchase process.');
                }

                // Calculate total price for display
                // Use Entity pricing since entity is purchasing for members
                $pricePerLicense = $calculatePriceAction($license, Entity::class);
                $totalPrice = $pricePerLicense * $individuals->count();

                // Document creation is handled automatically by the PurchaseLicenseForGroupAction event

                // Store the first license attributed ID for document tracking
                $firstLicense = $licenses->first();
                $firstLicenseAttributedId = $firstLicense ? $firstLicense->id : null;

                \Log::debug('License Purchase: First license extraction', [
                    'licenses_type' => get_class($licenses),
                    'licenses_count' => $licenses->count(),
                    'first_license_type' => $firstLicense ? get_class($firstLicense) : 'null',
                    'first_license_id' => $firstLicenseAttributedId,
                ]);

                \Log::info('License Purchase: Member licenses created', [
                    'first_license_attributed_id' => $firstLicenseAttributedId,
                    'license_count' => $licenses->count(),
                    'entity_id' => $entity->id,
                    'license_name' => $license->name,
                    'total_amount' => $totalPrice,
                ]);

                return redirect()
                    ->route('entity.license-purchase.success')
                    ->with('success', __('License purchase initiated for :count member(s). Please complete payment to activate licenses.', ['count' => $licenses->count()]))
                    ->with('license_attributed_id', $firstLicenseAttributedId)
                    ->with('license_count', $licenses->count())
                    ->with('purchase_type', 'members')
                    ->with('license_name', $license->name)
                    ->with('total_amount', $totalPrice)
                    ->with('member_count', $individuals->count())
                    ->with('purchase_timestamp', now()->timestamp);
            }
        } catch (\Exception $e) {
            \Log::error('License Purchase Failed', [
                'entity_id' => $entity->id,
                'license_id' => $request->license_id,
                'license_type' => $request->license_type,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    public function success()
    {
        $licenseAttributedId = session('license_attributed_id');
        $licenseCount = session('license_count');
        $purchaseType = session('purchase_type');
        $licenseName = session('license_name');
        $totalAmount = session('total_amount');
        $memberCount = session('member_count');
        $purchaseTimestamp = session('purchase_timestamp');

        if (! $licenseAttributedId && ! $licenseCount) {
            return redirect()->route(Committees::defaultEntityPurchaseRouteName())
                ->with('error', __('licenses.no_license_purchase_found'));
        }

        $licenseAttributed = null;
        if ($licenseAttributedId) {
            $licenseAttributed = \Domain\Licenses\Models\LicenseAttributed::with(['license', 'owner'])
                ->find($licenseAttributedId);
        }

        // Find document created by the purchase event
        $entity = Auth::user()->getEntity();
        $document = null;

        if ($entity && $licenseAttributedId) {
            // First try to find document through document_details relationship
            $document = \Domain\Documents\Models\Document::where('owner_type', 'entity')
                ->where('owner_id', $entity->id)
                ->whereHas('type', function ($query) {
                    $query->where('code', 'ORD');
                }) // Order documents only
                ->whereHas('details', function ($query) use ($licenseAttributedId) {
                    $query->where('owner_type', \Domain\Licenses\Models\LicenseAttributed::class)
                        ->where('owner_id', $licenseAttributedId);
                })
                ->latest()
                ->first();

            \Log::info('License Purchase Success: Looking for document with license_attributed relationship', [
                'entity_id' => $entity->id,
                'license_attributed_id' => $licenseAttributedId,
                'document_found' => $document ? true : false,
                'document_id' => $document ? $document->id : null,
            ]);

            // If not found through relationship, try by recent creation time
            if (! $document && $purchaseTimestamp) {
                // Look for documents created within 2 minutes of the purchase
                $purchaseTime = \Carbon\Carbon::createFromTimestamp($purchaseTimestamp);
                $document = \Domain\Documents\Models\Document::where('owner_type', 'entity')
                    ->where('owner_id', $entity->id)
                    ->whereHas('type', function ($query) {
                        $query->where('code', 'ORD');
                    })
                    ->where('created_at', '>=', $purchaseTime->subMinutes(1))
                    ->where('created_at', '<=', $purchaseTime->addMinutes(2))
                    ->latest()
                    ->first();

                \Log::info('License Purchase Success: Looking for document by timestamp', [
                    'entity_id' => $entity->id,
                    'purchase_timestamp' => $purchaseTimestamp,
                    'document_found' => $document ? true : false,
                    'document_id' => $document ? $document->id : null,
                ]);
            }

            // Last resort: get the most recent ORD document (but log a warning)
            if (! $document) {
                $document = \Domain\Documents\Models\Document::where('owner_type', 'entity')
                    ->where('owner_id', $entity->id)
                    ->whereHas('type', function ($query) {
                        $query->where('code', 'ORD');
                    })
                    ->latest()
                    ->first();

                \Log::warning('License Purchase Success: Using fallback - latest ORD document', [
                    'entity_id' => $entity->id,
                    'license_attributed_id' => $licenseAttributedId,
                    'document_found' => $document ? true : false,
                    'document_id' => $document ? $document->id : null,
                    'document_created_at' => $document ? $document->created_at : null,
                ]);
            }
        }

        // Create a license object for the view
        $license = (object) ['name' => $licenseName];

        return view('web.entity.license-purchase.success', compact(
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
