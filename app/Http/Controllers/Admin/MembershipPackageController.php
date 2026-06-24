<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MembershipPackageRequest;
use Domain\Federations\Models\Federation;
use Domain\Insurance\Models\InsurancePlan;
use Domain\Memberships\Actions\CreateMembershipPackageAction;
use Domain\Memberships\Actions\UpdateMembershipPackageAction;
use Domain\Memberships\DataTransferObject\MembershipPackageData;
use Domain\Memberships\Models\AffiliationPlan;
use Domain\Memberships\Models\MembershipPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MembershipPackageController extends Controller
{
    public function index(): View
    {
        $packages = MembershipPackage::with('affiliationPlans', 'insurancePlans')->paginate();

        return view('web.admin.membership-packages.index', compact('packages'));
    }

    public function create(): View
    {
        $package = new MembershipPackage;
        $affiliationPlans = $this->formatAffiliationPlansForSelect(AffiliationPlan::all());
        $insurancePlans = InsurancePlan::all();
        $availableFederations = Federation::all();

        return view('web.admin.membership-packages.create', compact(
            'package',
            'affiliationPlans',
            'insurancePlans',
            'availableFederations'
        ));
    }

    public function store(
        MembershipPackageRequest $request,
        CreateMembershipPackageAction $action
    ): RedirectResponse {

        $validatedData = $request->validated();

        $validatedData['federation_ids'] = $validatedData['federation_ids'] ?? [];
        $validatedData['is_active'] = $request->has('is_active');
        // Ensure distribution_methods is set even if no checkboxes are selected
        $validatedData['distribution_methods'] = $validatedData['distribution_methods'] ?? [];

        $packageData = MembershipPackageData::fromArray($validatedData);
        $package = $action($packageData);

        return redirect()->route('admin.membership-packages.index')
            ->with('success', __('Membership package created successfully.'));
    }

    public function show(MembershipPackage $package): View
    {
        return view('web.admin.membership-packages.show', compact('package'));
    }

    public function edit(MembershipPackage $package): View
    {
        $affiliationPlans = $this->formatAffiliationPlansForSelect(AffiliationPlan::all());
        $insurancePlans = InsurancePlan::all();
        $availableFederations = Federation::all();

        return view('web.admin.membership-packages.edit', compact(
            'package',
            'affiliationPlans',
            'insurancePlans',
            'availableFederations'
        ));
    }

    public function update(
        MembershipPackageRequest $request,
        MembershipPackage $package,
        UpdateMembershipPackageAction $action
    ): RedirectResponse {
        $user = Auth::user();
        $federationId = $user->getFederationId();

        $validatedData = $request->validated();

        $validatedData['federation_ids'] = $validatedData['federation_ids'] ?? [];
        $validatedData['is_active'] = $request->has('is_active');
        // Ensure distribution_methods is set even if no checkboxes are selected
        $validatedData['distribution_methods'] = $validatedData['distribution_methods'] ?? [];

        $packageData = MembershipPackageData::fromArray($validatedData);
        $action($package, $packageData);

        return redirect()->route('admin.membership-packages.index')
            ->with('success', __('Membership package updated successfully.'));
    }

    public function destroy(
        MembershipPackage $package
    ): RedirectResponse {

        try {
            DB::beginTransaction();

            // Check if the package is associated with any member subscriptions
            if ($package->memberSubscriptions()->exists()) {
                throw new \Exception(__('Cannot delete membership package. It is associated with one or more member subscriptions.'));
            }

            // Detach related plans and licenses
            $package->affiliationPlans()->detach();
            $package->insurancePlans()->detach();

            $package->delete();

            DB::commit();

            return redirect()->route('admin.membership-packages.index')
                ->with('success', __('Membership package deleted successfully.'));
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('admin.membership-packages.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Format affiliation plans for select dropdown with comprehensive business logic display
     */
    private function formatAffiliationPlansForSelect($affiliationPlans)
    {
        return $affiliationPlans->mapWithKeys(function ($plan) {
            // Determine the business logic type based on type and fee structure
            $businessType = $this->determineBusinessType($plan);

            // Format pricing information
            $priceInfo = $this->formatPriceInfo($plan);

            // Create comprehensive label
            $label = sprintf(
                '%s (%s) %s',
                $plan->name,
                $businessType,
                $priceInfo
            );

            return [$plan->id => $label];
        });
    }

    /**
     * Determine the business type based on plan type and fee structure
     */
    private function determineBusinessType($plan): string
    {
        if ($plan->type === 'individual') {
            return __('Direct Individual Subscription');
        }

        if ($plan->type === 'entity') {
            // Check if it has individual fee - this indicates it's for entity to subscribe for individuals
            if ($plan->individual_fee !== null && $plan->entity_fee === null) {
                return __('Entity subscribes for Individuals');
            }

            // Check if it has entity fee - this indicates it's for the entity itself
            if ($plan->entity_fee !== null && $plan->individual_fee === null) {
                return __('Direct Entity Subscription');
            }

            // If both fees exist, it's flexible
            if ($plan->individual_fee !== null && $plan->entity_fee !== null) {
                return __('Flexible (Entity or Individual)');
            }

            // Fallback
            return __('Entity Plan');
        }

        return __('Unknown');
    }

    /**
     * Format price information for display
     */
    private function formatPriceInfo($plan): string
    {
        $currencySymbol = config('squidflex.currency_symbol', '€');
        $parts = [];

        if ($plan->individual_fee !== null) {
            $parts[] = __('Individual') . ': ' . $currencySymbol . number_format($plan->individual_fee, 2);
        }

        if ($plan->entity_fee !== null) {
            $parts[] = __('Entity') . ': ' . $currencySymbol . number_format($plan->entity_fee, 2);
        }

        if (empty($parts)) {
            return '(' . __('Free') . ')';
        }

        return '- ' . implode(', ', $parts);
    }
}
