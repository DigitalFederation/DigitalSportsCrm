<?php

namespace App\Http\Controllers\Individual;

use App\Exceptions\CertificationCardGenerationException;
use App\Http\Controllers\Controller;
use App\Models\Country;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\Services\CertificationCardGeneratorService;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificationCardController extends Controller
{
    public function index(): View
    {
        $individualId = auth()->user()->individuals()->first()->id;

        $certifications_attributed = QueryBuilder::for(CertificationAttributed::class)
            ->allowedFilters([
                // REMOVED: filter_committee - National controller only shows SPORT committee
                AllowedFilter::scope('filter_professional'),
                AllowedFilter::scope('filter_certification_category'),
                AllowedFilter::scope('filter_country'),
            ])
            ->where('individual_id', $individualId)
            // CRITICAL: Explicitly exclude international certifications
            // Only show SPORT committee (national) certifications
            ->whereHas('certification', function ($q) {
                $q->whereHas('committee', function ($qq) {
                    $qq->where('is_international', false)
                        ->where('code', 'SPORT');
                });
            })
            ->with('certification.license.sport', 'certification.committee', 'federation.country', 'individual', 'entity', 'mainInstructor')
            ->paginate()
            ->appends(request()->query());

        $user = auth()->user();

        $countries = Country::select('id', 'name')->orderBy('name')->get();

        // List of Professional Roles to use on a filter
        $professional_roles = ProfessionalRole::select('id', 'name')->orderBy('name')->get();

        // Fetch distinct certification categories for the individual
        // Define the custom order mapping
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
            'Coach' => 14,
            'Referee / Judge' => 15,
        ];

        $certification_categories = CertificationAttributed::where('individual_id', $individualId)
            // CRITICAL: Only national certifications for categories
            ->whereHas('certification', function ($q) {
                $q->whereHas('committee', function ($qq) {
                    $qq->where('is_international', false)
                        ->where('code', 'SPORT');
                });
            })
            ->with('certification')
            ->get()
            ->pluck('certification.certification_category')
            ->unique()
            ->values();

        // Apply custom sorting
        $certification_categories = $certification_categories->sortBy(function ($category) use ($orderMapping) {
            return $orderMapping[$category] ?? 999; // Default to 999 if category not found in mapping
        })->values(); // Reset the keys

        // ClassGroup By
        $certifications_attributed = $certifications_attributed
            ->groupBy('certification.certification_category')
            ->sortBy(function ($items, $category) use ($orderMapping) {
                return $orderMapping[$category] ?? 9999;
            });

        return view(
            'web.individual.certification_card.index',
            compact(
                'certifications_attributed',
                'user',
                'professional_roles',
                'certification_categories',
                'countries'
            )
        );
    }

    public function show(string $id): View
    {
        $certification_attributed = CertificationAttributed::with('individual', 'entity', 'mainInstructor')->findOrFail($id);

        return view('web.individual.certification_card.show', compact('certification_attributed'));
    }

    public function download(
        CertificationAttributed $certificationAttributed,
        CertificationCardGeneratorService $cardGenerator
    ): StreamedResponse|RedirectResponse {
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
            Log::error('Unexpected error generating certification card', [
                'error' => $e->getMessage(),
                'certification_id' => $certificationAttributed->id,
            ]);

            return redirect()
                ->back()
                ->with('error', __('Technical error occurred while generating the certification card. Please try again later.'));
        }
    }
}
