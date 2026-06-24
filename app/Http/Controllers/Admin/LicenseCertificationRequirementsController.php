<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DivingCertificationRequirementService;
use Domain\Licenses\Models\License;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LicenseCertificationRequirementsController extends Controller
{
    public function __construct(
        private DivingCertificationRequirementService $certificationService
    ) {}

    /**
     * Display certification requirements for a license.
     */
    public function show(License $license): View
    {
        $requirements = DB::table('license_required_certifications')
            ->where('license_id', $license->id)
            ->where('requester_type', 'technical_director')
            ->whereNotNull('certification_level')
            ->get();

        $availableComiteeDivingLevels = $this->certificationService->getCertificationLevelDisplayNames();

        return view('web.admin.license-certification-requirements.show', compact(
            'license',
            'requirements',
            'availableComiteeDivingLevels'
        ));
    }

    /**
     * Show the form for editing certification requirements.
     */
    public function edit(License $license): View
    {
        $requirements = DB::table('license_required_certifications')
            ->where('license_id', $license->id)
            ->where('requester_type', 'technical_director')
            ->whereNotNull('certification_level')
            ->pluck('certification_level')
            ->toArray();

        $availableCertificationLevels = $this->certificationService->getCertificationLevelDisplayNames();

        return view('web.admin.license-certification-requirements.edit', compact(
            'license',
            'requirements',
            'availableCertificationLevels'
        ));
    }

    /**
     * Update certification requirements for a license.
     */
    public function update(Request $request, License $license): RedirectResponse
    {
        $validated = $request->validate([
            'certification_levels' => 'nullable|array',
            'certification_levels.*' => [
                'string',
                Rule::in(array_keys($this->certificationService->getCertificationLevelDisplayNames())),
            ],
        ]);

        DB::beginTransaction();

        try {
            // Remove existing certification level requirements for technical directors
            DB::table('license_required_certifications')
                ->where('license_id', $license->id)
                ->where('requester_type', 'technical_director')
                ->whereNotNull('certification_level')
                ->delete();

            // Add new requirements
            if (! empty($validated['certification_levels'])) {
                $requirements = [];
                foreach ($validated['certification_levels'] as $certificationLevel) {
                    $requirements[] = [
                        'license_id' => $license->id,
                        'certification_id' => null,
                        'requester_type' => 'technical_director',
                        'certification_level' => $certificationLevel,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                DB::table('license_required_certifications')->insert($requirements);
            }

            DB::commit();

            return redirect()->route('admin.license-certification-requirements.show', $license)
                ->with('success', __('diving.certification_requirements_updated_successfully'));

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', __('diving.failed_to_update_certification_requirements'));
        }
    }
}
