<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AffiliationPlanRequest;
use App\Traits\StreamsMediaFromStorage;
use Domain\Federations\Models\Federation;
use Domain\Memberships\Actions\CreateAffiliationPlanAction;
use Domain\Memberships\DataTransferObject\AffiliationPlanData;
use Domain\Memberships\Models\AffiliationPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AffiliationPlanController extends Controller
{
    use StreamsMediaFromStorage;
    public function index(): View
    {
        $plans = AffiliationPlan::paginate();

        return view('web.admin.affiliation-plans.index', compact('plans'));
    }

    public function create(): View
    {
        $federations = Federation::all();
        $plan = new AffiliationPlan;

        // Define business scenarios with clear explanations
        $businessScenarios = [
            'direct_individual' => [
                'label' => __('memberships.business_scenarios.direct_individual.label'),
                'description' => __('memberships.business_scenarios.direct_individual.description'),
                'example' => __('memberships.business_scenarios.direct_individual.example'),
                'type' => 'individual',
                'fee_structure' => 'individual_only',
            ],
            'entity_for_individuals' => [
                'label' => __('memberships.business_scenarios.entity_for_individuals.label'),
                'description' => __('memberships.business_scenarios.entity_for_individuals.description'),
                'example' => __('memberships.business_scenarios.entity_for_individuals.example'),
                'type' => 'entity',
                'fee_structure' => 'individual_only',
            ],
            'direct_entity' => [
                'label' => __('memberships.business_scenarios.direct_entity.label'),
                'description' => __('memberships.business_scenarios.direct_entity.description'),
                'example' => __('memberships.business_scenarios.direct_entity.example'),
                'type' => 'entity',
                'fee_structure' => 'entity_only',
            ],
            'flexible' => [
                'label' => __('memberships.business_scenarios.flexible.label'),
                'description' => __('memberships.business_scenarios.flexible.description'),
                'example' => __('memberships.business_scenarios.flexible.example'),
                'type' => 'entity',
                'fee_structure' => 'both',
            ],
        ];

        return view('web.admin.affiliation-plans.create', compact('federations', 'plan', 'businessScenarios'));
    }

    public function store(AffiliationPlanRequest $request, CreateAffiliationPlanAction $action): RedirectResponse
    {
        $validatedData = $request->validated();
        $planData = AffiliationPlanData::fromArray($validatedData);

        $plan = $action($planData);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file->isValid()) {
                    $plan->addMedia($file)
                        ->toMediaCollection('affiliation_attachments');
                }
            }
        }

        return redirect()->route('admin.affiliation-plans.index')->with('success', 'Affiliation plan created successfully.');
    }

    public function edit(AffiliationPlan $plan): View
    {
        $federations = Federation::all();

        // Define business scenarios with clear explanations
        $businessScenarios = [
            'direct_individual' => [
                'label' => __('memberships.business_scenarios.direct_individual.label'),
                'description' => __('memberships.business_scenarios.direct_individual.description'),
                'example' => __('memberships.business_scenarios.direct_individual.example'),
                'type' => 'individual',
                'fee_structure' => 'individual_only',
            ],
            'entity_for_individuals' => [
                'label' => __('memberships.business_scenarios.entity_for_individuals.label'),
                'description' => __('memberships.business_scenarios.entity_for_individuals.description'),
                'example' => __('memberships.business_scenarios.entity_for_individuals.example'),
                'type' => 'entity',
                'fee_structure' => 'individual_only',
            ],
            'direct_entity' => [
                'label' => __('memberships.business_scenarios.direct_entity.label'),
                'description' => __('memberships.business_scenarios.direct_entity.description'),
                'example' => __('memberships.business_scenarios.direct_entity.example'),
                'type' => 'entity',
                'fee_structure' => 'entity_only',
            ],
            'flexible' => [
                'label' => __('memberships.business_scenarios.flexible.label'),
                'description' => __('memberships.business_scenarios.flexible.description'),
                'example' => __('memberships.business_scenarios.flexible.example'),
                'type' => 'entity',
                'fee_structure' => 'both',
            ],
        ];

        return view('web.admin.affiliation-plans.edit', compact('plan', 'federations', 'businessScenarios'));
    }

    public function update(AffiliationPlanRequest $request, AffiliationPlan $plan): RedirectResponse
    {
        $validatedData = $request->validated();
        $plan->update($validatedData);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file->isValid()) {
                    $plan->addMedia($file)
                        ->toMediaCollection('affiliation_attachments');
                }
            }
        }

        return redirect()->route('admin.affiliation-plans.index')
            ->with('success', 'Affiliation plan updated successfully.');
    }

    public function show(AffiliationPlan $plan): View
    {
        return view('web.admin.affiliation-plans.show', compact('plan'));
    }

    public function destroy(AffiliationPlan $plan): RedirectResponse
    {

        try {
            DB::beginTransaction();

            // Check if the plan is associated with any membership packages
            if ($plan->membershipPackages()->exists()) {
                throw new \Exception(__('Cannot delete affiliation plan. It is associated with one or more membership packages.'));
            }

            $plan->delete();

            DB::commit();

            return redirect()->route('admin.affiliation-plans.index')
                ->with('success', __('Affiliation plan deleted successfully.'));
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('admin.affiliation-plans.index')
                ->with('error', $e->getMessage());
        }
    }

    public function downloadAttachment($id, $mediaId)
    {
        $affiliationPlan = AffiliationPlan::findOrFail($id);
        $media = $affiliationPlan->getMedia('affiliation_attachments')->where('id', $mediaId)->firstOrFail();

        // Authorization handled by route middleware (user_group:international,ADMIN + permission:access memberships)

        return $this->streamMediaDownload($media, $media->file_name);
    }
}
