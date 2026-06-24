<?php

namespace App\Http\Controllers\International\Individual;

use App\Exceptions\CertificationCardGenerationException;
use App\Http\Controllers\Controller;
use App\Models\Country;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Domain\Certifications\Services\CertificationCardGeneratorService;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * international Individual Certification Card Controller
 *
 * Handles INTERNATIONAL certification cards ONLY (is_international = true)
 * Covers DIVING and SCIENTIFIC committees only
 * National (Sport) certification cards are handled in regular Individual namespace
 */
class CertificationCardController extends Controller
{
    public function index(): View
    {
        $individualId = auth()->user()->individuals()->first()->id;

        // CRITICAL: Filter for international certifications (Diving + Scientific committees)
        $certifications_attributed = QueryBuilder::for(CertificationAttributed::class)
            ->allowedFilters([
                // REMOVED: AllowedFilter::scope('filter_committee'), - not needed, always Diving/Scientific
                AllowedFilter::scope('filter_professional'),
                AllowedFilter::scope('filter_certification_category'),
                AllowedFilter::scope('filter_country'),
            ])
            ->where('individual_id', $individualId)
            // CRITICAL: Remove global scope to access international certifications
            ->withoutGlobalScope(ExcludeInternationalScope::class)
            // CRITICAL: Only international certifications from Diving/Scientific committees
            ->whereHas('certification', function ($q) {
                $q->whereHas('committee', function ($qq) {
                    $qq->where('is_international', true)
                        ->whereIn('code', ['DIVING', 'SCIENTIFIC']);
                });
            })
            ->with('certification.license.sport', 'certification.committee', 'federation.country', 'individual', 'entity', 'mainInstructor')
            ->paginate()
            ->appends(request()->query());

        $countries = Country::select('id', 'name')->orderBy('name')->get();
        $professional_roles = ProfessionalRole::select('id', 'name')->orderBy('name')->get();

        // Fetch distinct certification categories for the individual (international only)
        $orderMapping = [
            'Diver Career' => 1,
            'Recreational Diver Speciality' => 2,
            'Technical Diver Speciality' => 3,
            'Instructor Career' => 4,
            'Recreational Instructor Speciality' => 5,
            'Technical Instructor Speciality' => 6,
            'Scientific Diver Career' => 7,
            'Scientific Speciality Diver' => 8,
            'Scientific Speciality Instructor' => 9,
            'Scientific Instructor Career' => 11,
            'Freediver' => 12,
            'Freediving Instructor' => 13,
        ];

        $certification_categories = CertificationAttributed::where('individual_id', $individualId)
            ->withoutGlobalScope(ExcludeInternationalScope::class)
            ->whereHas('certification', function ($q) {
                $q->whereHas('committee', function ($qq) {
                    $qq->where('is_international', true)
                        ->whereIn('code', ['DIVING', 'SCIENTIFIC']);
                });
            })
            ->with('certification')
            ->get()
            ->pluck('certification.certification_category')
            ->unique()
            ->values();

        // Apply custom sorting
        $certification_categories = $certification_categories->sortBy(function ($category) use ($orderMapping) {
            return $orderMapping[$category] ?? 999;
        })->values();

        // Group by certification category
        $certifications_attributed = $certifications_attributed
            ->groupBy('certification.certification_category')
            ->sortBy(function ($group, $category) use ($orderMapping) {
                return $orderMapping[$category] ?? 999;
            });

        return view('web.international.individual.certification-card.index', compact(
            'certifications_attributed',
            'certification_categories',
            'countries',
            'professional_roles'
        ));
    }

    public function show(string $id): View|RedirectResponse
    {
        $individualId = auth()->user()->individuals()->first()->id;

        $certificationAttributed = CertificationAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
            ->with('certification.license.sport', 'certification.committee', 'federation.country', 'individual', 'entity', 'mainInstructor')
            ->where('id', $id)
            ->where('individual_id', $individualId)
            ->whereHas('certification', function ($q) {
                $q->whereHas('committee', fn ($cq) => $cq->where('is_international', true));
            })
            ->firstOrFail();

        return view('web.international.individual.certification-card.show', compact('certificationAttributed'));
    }

    public function download(CertificationAttributed $certificationAttributed): BinaryFileResponse
    {
        // Verify ownership and international status
        $individualId = auth()->user()->individuals()->first()->id;

        if ($certificationAttributed->individual_id !== $individualId ||
            ! $certificationAttributed->certification->is_international) {
            abort(403, __('admin.error.unauthorized'));
        }

        try {
            $generatorService = new CertificationCardGeneratorService;
            $pdfPath = $generatorService->generateCard($certificationAttributed);

            return response()->download($pdfPath, "certification-card-{$certificationAttributed->id}.pdf")
                ->deleteFileAfterSend(true);

        } catch (CertificationCardGenerationException $e) {
            Log::error('CMAS Certification card generation failed', [
                'certification_attributed_id' => $certificationAttributed->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', __('admin.error.card_generation_failed'));
        }
    }
}
