<?php

namespace App\Http\Controllers\International\Individual;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Contracts\View\View;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * international Individual Certification Attributed Controller
 *
 * Handles INTERNATIONAL certifications ONLY (is_international = true)
 * National certifications are handled in regular Individual namespace
 */
class CertificationAttributedController extends Controller
{
    public function index(): View
    {
        $individualId = auth()->user()->individuals()->first()->id;

        if (! $individualId) {
            abort(403, __('admin.error.no_individual_profile'));
        }

        $certifications = QueryBuilder::for(CertificationAttributed::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_professional'),
                AllowedFilter::scope('filter_certification_category'),
                AllowedFilter::scope('filter_country'),
                AllowedFilter::scope('filter_status', 'status'),
            ])
            ->where('individual_id', $individualId)
            // CRITICAL: Remove global scope to access international certifications
            ->withoutGlobalScope(ExcludeInternationalScope::class)
            // CRITICAL: Only international certifications
            ->whereHas('certification', function ($q) {
                $q->whereHas('committee', function ($qq) {
                    $qq->where('is_international', true)
                        ->whereIn('code', ['DIVING', 'SCIENTIFIC']);
                });
            })
            ->with('certification.committee', 'certification.license.sport', 'federation.country', 'mainInstructor')
            ->orderBy('created_at', 'desc')
            ->paginate()
            ->appends(request()->query());

        $countries = Country::select('id', 'name')->orderBy('name')->get();
        $professional_roles = ProfessionalRole::select('id', 'name')->orderBy('name')->get();

        $filter_status = [
            'active' => ['id' => 'active', 'name' => __('Active')],
            'pending' => ['id' => 'pending', 'name' => __('Pending')],
            'canceled' => ['id' => 'canceled', 'name' => __('Canceled')],
        ];

        return view('web.international.individual.certifications.index', compact(
            'certifications',
            'countries',
            'professional_roles',
            'filter_status'
        ));
    }

    public function show(string $id): View
    {
        $individualId = auth()->user()->individuals()->first()->id;

        $certification = CertificationAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
            ->with('certification.committee', 'certification.license.sport', 'federation.country', 'mainInstructor', 'individual')
            ->where('id', $id)
            ->where('individual_id', $individualId)
            ->whereHas('certification', function ($q) {
                $q->whereHas('committee', fn ($cq) => $cq->where('is_international', true));
            })
            ->firstOrFail();

        return view('web.international.individual.certifications.show', compact('certification'));
    }
}
