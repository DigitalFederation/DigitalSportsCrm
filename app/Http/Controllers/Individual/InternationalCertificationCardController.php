<?php

namespace App\Http\Controllers\Individual;

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
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InternationalCertificationCardController extends Controller
{
    public function index(): View
    {
        $individualId = auth()->user()->individuals()->first()->id;

        $certifications_attributed = QueryBuilder::for(CertificationAttributed::class)
            ->allowedFilters([
                AllowedFilter::scope('committee', 'filterCommittee'),
                AllowedFilter::scope('filter_committee'),
                AllowedFilter::scope('filter_professional'),
                AllowedFilter::scope('filter_certification_category'),
                AllowedFilter::scope('filter_country'),
            ])
            ->where('individual_id', $individualId)
            ->withoutGlobalScope(ExcludeInternationalScope::class)
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

        $certification_categories = $certifications_attributed
            ->pluck('certification.certification_category')
            ->unique()
            ->sortBy(function ($category) use ($orderMapping) {
                return $orderMapping[$category] ?? 999;
            })
            ->values();

        $certifications_attributed = $certifications_attributed
            ->groupBy('certification.certification_category')
            ->sortBy(function ($group, $category) use ($orderMapping) {
                return $orderMapping[$category] ?? 999;
            });

        return view('web.individual.international_certification_card.index', compact(
            'certifications_attributed',
            'certification_categories',
            'countries',
            'professional_roles'
        ));
    }

    public function show(string $id): View|RedirectResponse
    {
        $individualId = auth()->user()->individuals()->first()->id;

        $certification_attributed = CertificationAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
            ->select(
                'id',
                'certification_id',
                'individual_id',
                'federation_id',
                'status_class',
                'international_code',
                'activator_id',
                'activator_type',
                'activated_at',
                'created_at',
                'current_term_starts_at',
                'current_term_ends_at',
                'entity_id',
                'national_code'
            )
            ->with([
                'certification:id,name,committee_id,certification_view',
                'individual:id,name,surname,member_code,qrcode_path',
                'individual.media',
                'entity',
                'activator:id,name',
                'mainInstructor',
                'federation:id,member_code,country_id,name',
                'federation.country:id,name,iso,ioc',
            ])
            ->where('id', $id)
            ->where('individual_id', $individualId)
            ->whereHas('certification', function ($q) {
                $q->whereHas('committee', fn ($cq) => $cq->where('is_international', true));
            })
            ->firstOrFail();

        $mainInstructor = $certification_attributed->mainInstructor()->first();
        $assistants = $certification_attributed->assistantInstructors()->get();

        return view('web.individual.international_certification_card.show', [
            'certification' => $certification_attributed,
            'mainInstructor' => $mainInstructor,
            'assistants' => $assistants,
            'showInstructorInfo' => $mainInstructor !== null || $assistants->isNotEmpty(),
        ]);
    }

    public function download(
        string $certificationAttributed,
        CertificationCardGeneratorService $cardGenerator
    ): StreamedResponse|RedirectResponse {
        $individualId = auth()->user()->individuals()->first()->id;

        $certificationAttributed = CertificationAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
            ->where('id', $certificationAttributed)
            ->where('individual_id', $individualId)
            ->firstOrFail();

        try {
            $path = $cardGenerator->generate($certificationAttributed);

            return Storage::disk('public')->download($path, "certification_card_{$certificationAttributed->id}.png");
        } catch (CertificationCardGenerationException $e) {
            $errorMessage = $e->getMessage();
            if ($missingFields = $e->getMissingFields()) {
                $errorMessage .= ': ' . implode(', ', $missingFields);
            }

            return redirect()->back()->with('error', $errorMessage);
        } catch (\Throwable $e) {
            Log::error('Unexpected error generating international certification card', [
                'error' => $e->getMessage(),
                'certification_id' => $certificationAttributed->id,
            ]);

            return redirect()
                ->back()
                ->with('error', __('certification_card.generation_error'));
        }
    }
}
