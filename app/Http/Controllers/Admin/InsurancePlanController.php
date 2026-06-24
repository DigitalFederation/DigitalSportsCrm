<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InsurancePlanCreateRequest;
use App\Traits\StreamsMediaFromStorage;
use Domain\Insurance\DataTransferObject\InsurancePlanData;
use Domain\Insurance\Models\InsurancePlan;
use Illuminate\Contracts\View\View;

class InsurancePlanController extends Controller
{
    use StreamsMediaFromStorage;
    public function index(): View
    {
        $insurancePlans = InsurancePlan::with('media')->paginate(25);

        return view('web.admin.insurance_plans.index', compact('insurancePlans'));
    }

    public function show($id): View
    {
        $insurance_plan = InsurancePlan::with('media')->findOrFail($id);

        return view('web.admin.insurance_plans.show', compact('insurance_plan'));
    }

    public function create()
    {
        $insurance_plan = new InsurancePlan;

        return view('web.admin.insurance_plans.create', compact('insurance_plan'));
    }

    public function store(InsurancePlanCreateRequest $request)
    {
        $dto = InsurancePlanData::fromRequest($request->validated());

        $insurancePlan = InsurancePlan::create([
            'name' => $dto->name,
            'target_audience' => $dto->targetAudience,
            'type' => $dto->type,
            'individual_fee' => $dto->individualFee,
            'entity_fee' => $dto->entityFee,
            'policy_number' => $dto->policyNumber,
            'policy_number_prefix' => $dto->policyNumberPrefix,
            'policy_number_sequence' => $dto->policyNumberSequence,
            'policy_number_format' => $dto->policyNumberFormat,
            'period' => $dto->period,
            'period_unit' => $dto->periodUnit,
            'description' => $dto->description,
            'insured_activity' => $dto->insuredActivity,
            'territorial_scope' => $dto->territorialScope,
            'cmas_license_code' => $dto->cmasLicenseCode,
            'vat_rate' => $dto->vatRate,
            'requires_official_document' => $dto->requiresOfficialDocument,
            'required_document_type' => $dto->requiredDocumentType,
            'requires_active_affiliation' => $dto->requiresActiveAffiliation,
            'start_date' => $dto->startDate,
            'end_date' => $dto->endDate,
            'insurer_address' => $dto->insurerAddress,
            'insurer_email' => $dto->insurerEmail,
            'insurer_phone' => $dto->insurerPhone,
            'applicable_deductibles' => $dto->applicableDeductibles,
            'coverage_details' => $dto->coverageDetails,
            'insurance_company_name' => $dto->insuranceCompanyName,
            'moloni_reference' => $dto->moloniReference,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file->isValid()) {
                    $insurancePlan->addMedia($file)
                        ->toMediaCollection('insurance_attachments');
                }
            }
        }

        return redirect()->route('admin.insurance-plans.index')
            ->with('success', 'Insurance plan created successfully.');
    }

    public function edit($id)
    {
        $insurance_plan = InsurancePlan::findOrFail($id);

        return view('web.admin.insurance_plans.edit', compact('insurance_plan'));
    }

    public function update(InsurancePlanCreateRequest $request, $id)
    {
        $insurancePlan = InsurancePlan::findOrFail($id);
        $dto = InsurancePlanData::fromRequest($request->validated());

        $insurancePlan->update([
            'name' => $dto->name,
            'target_audience' => $dto->targetAudience,
            'type' => $dto->type,
            'individual_fee' => $dto->individualFee,
            'entity_fee' => $dto->entityFee,
            'policy_number' => $dto->policyNumber,
            'policy_number_prefix' => $dto->policyNumberPrefix,
            'policy_number_sequence' => $dto->policyNumberSequence,
            'policy_number_format' => $dto->policyNumberFormat,
            'period' => $dto->period,
            'period_unit' => $dto->periodUnit,
            'description' => $dto->description,
            'insured_activity' => $dto->insuredActivity,
            'territorial_scope' => $dto->territorialScope,
            'cmas_license_code' => $dto->cmasLicenseCode,
            'vat_rate' => $dto->vatRate,
            'requires_official_document' => $dto->requiresOfficialDocument,
            'required_document_type' => $dto->requiredDocumentType,
            'requires_active_affiliation' => $dto->requiresActiveAffiliation,
            'start_date' => $dto->startDate,
            'end_date' => $dto->endDate,
            'insurer_address' => $dto->insurerAddress,
            'insurer_email' => $dto->insurerEmail,
            'insurer_phone' => $dto->insurerPhone,
            'applicable_deductibles' => $dto->applicableDeductibles,
            'coverage_details' => $dto->coverageDetails,
            'insurance_company_name' => $dto->insuranceCompanyName,
            'moloni_reference' => $dto->moloniReference,
        ]);

        // Handle attachment removal
        $keepAttachments = $request->input('keep_attachments', []);
        $insurancePlan->getMedia('insurance_attachments')->each(function ($media) use ($keepAttachments) {
            if (! in_array($media->id, $keepAttachments)) {
                $media->delete();
            }
        });

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file->isValid()) {
                    $insurancePlan->addMedia($file)
                        ->toMediaCollection('insurance_attachments');
                }
            }
        }

        return redirect()->route('admin.insurance-plans.index')
            ->with('success', 'Insurance plan updated successfully.');
    }

    public function destroy($id)
    {
        $insurancePlan = InsurancePlan::findOrFail($id);
        $insurancePlan->delete();

        return redirect()->route('admin.insurance-plans.index')
            ->with('success', 'Insurance plan deleted successfully.');
    }

    public function downloadAttachment($id, $mediaId)
    {
        $insurancePlan = InsurancePlan::findOrFail($id);
        $media = $insurancePlan->getMedia('insurance_attachments')->where('id', $mediaId)->firstOrFail();

        // Authorization handled by route middleware (user_group:international,ADMIN + permission:access memberships)

        return $this->streamMediaDownload($media, $media->file_name);
    }
}
