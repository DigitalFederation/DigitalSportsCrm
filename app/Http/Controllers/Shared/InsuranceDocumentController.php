<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Domain\Individuals\Models\Individual;
use Domain\Insurance\Models\Insurance;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InsuranceDocumentController extends Controller
{
    public function show(Insurance $insurance): View|Response
    {
        // Use authorize method which automatically uses the registered policy
        $this->authorize('viewDocument', $insurance);

        try {
            $data = $this->prepareViewData($insurance);

            return view('shared.insurance.document', $data);
        } catch (\InvalidArgumentException $e) {
            Log::error('Error preparing insurance document view data.', ['insurance_id' => $insurance->id, 'error' => $e->getMessage()]);
            abort(400, $e->getMessage());
        }
    }

    public function download(Insurance $insurance): Response
    {
        // Use authorize method
        $this->authorize('downloadDocument', $insurance);

        try {
            $data = $this->prepareViewData($insurance);

            // Render the view to HTML
            $html = view('shared.insurance.document-pdf', $data)->render();

            // Generate PDF using Browsershot
            $browsershot = Browsershot::html($html)
                ->format('A4')
                ->margins(8, 8, 8, 8) // top, right, bottom, left in mm
                ->showBackground()
                ->waitUntilNetworkIdle()
                ->usePipe()
                ->setEnvironmentOptions([
                    'PUPPETEER_DISABLE_CRASH_REPORTING' => 'true',
                ])
                ->addChromiumArguments([
                    'no-sandbox',
                    'disable-crash-reporter',
                    'disable-crashpad',
                    'disable-dev-shm-usage',
                    'disable-gpu',
                ]);

            // Set Chrome path if configured
            if ($chromePath = config('browsershot.chrome_path')) {
                $browsershot->setChromePath($chromePath);
            }

            $pdfContent = $browsershot->pdf();

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="insurance-document-' . $insurance->id . '.pdf"');
        } catch (\InvalidArgumentException $e) {
            Log::error('Error preparing insurance document PDF data.', ['insurance_id' => $insurance->id, 'error' => $e->getMessage()]);
            abort(400, $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Error generating insurance document PDF.', ['insurance_id' => $insurance->id, 'error' => $e->getMessage()]);
            abort(500, 'Failed to generate PDF. Please try again.');
        }
    }

    public function downloadConditions(Insurance $insurance): StreamedResponse
    {
        $this->authorize('viewDocument', $insurance);

        $insurancePlan = $insurance->insurancePlan;
        $media = $insurancePlan->getMedia('insurance_attachments')->first();

        if (! $media) {
            abort(404, __('insurance.conditions_not_available'));
        }

        $disk = Storage::disk($media->disk);
        $path = $media->getPathRelativeToRoot();

        if (! $disk->exists($path)) {
            Log::error('Insurance conditions file not found.', [
                'insurance_id' => $insurance->id,
                'insurance_plan_id' => $insurancePlan->id,
                'media_id' => $media->id,
                'disk' => $media->disk,
                'path' => $path,
            ]);
            abort(404, __('insurance.conditions_not_available'));
        }

        return response()->streamDownload(function () use ($disk, $path) {
            echo $disk->get($path);
        }, $media->file_name, [
            'Content-Type' => $media->mime_type,
        ]);
    }

    /**
     * @throws \InvalidArgumentException If the insurance member is not an Individual.
     */
    private function prepareViewData(Insurance $insurance): array
    {

        // Eager load relationships if not already loaded (belt and suspenders)
        $insurance->loadMissing(['member', 'insurancePlan']);

        // CRITICAL CHECK: Ensure the member is an Individual
        if (! $insurance->member instanceof Individual) {
            throw new \InvalidArgumentException('Insurance document is only applicable to Individual members.');
        }

        // If we pass the check, we know $insurance->member is an Individual
        /** @var Individual $individual */
        $individual = $insurance->member;
        $insurancePlan = $insurance->insurancePlan;

        // Determine policy number (individual or group)
        $policyNumber = $insurance->policy_number ?? $insurancePlan->policy_number;

        // Use real data from InsurancePlan model
        $activity = $insurancePlan->insured_activity ?? __('Não especificado');
        $scope = $insurancePlan->territorial_scope ?? __('Não especificado');

        Log::info('Successfully prepared view data.', ['insurance_id' => $insurance->id, 'individual_id' => $individual->id]);

        return [
            'insurance' => $insurance,
            'individual' => $individual,
            'insurancePlan' => $insurancePlan,
            'plan' => $insurancePlan, // Add alias for template compatibility
            'member' => $individual, // Add alias for template compatibility
            'policyNumber' => $policyNumber,
            'memberId' => $individual->member_number, // "Nº de Filiado" is always member_number
            'address' => $individual->address,
            'postalCode' => $individual->postal_code,
            'district' => $individual->location, // Use location field as district
            'insuredActivity' => $activity, // Derived/placeholder
            'territorialScope' => $scope, // Derived/placeholder
            'startDateFormatted' => $insurance->start_date->format('d/m/Y'),
            'endDateFormatted' => $insurance->end_date->format('d/m/Y') . ' - 23h59',
        ];
    }
}
