<?php

namespace App\Http\Controllers\International\Entity;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Illuminate\Contracts\View\View;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * international Entity Certification Attributed Controller
 *
 * Handles INTERNATIONAL certifications ONLY (is_international = true) for entities' members
 * National certifications are handled in regular Entity namespace
 */
class CertificationAttributedController extends Controller
{
    public function index(): View
    {
        $entity = auth()->user()->entities()->first();

        if (! $entity) {
            abort(403, __('admin.error.no_entity_profile'));
        }

        // Get individuals associated with the entity
        $individualIds = $entity->individuals()
            ->wherePivot('status_class', ActiveIndividualEntityState::class)
            ->pluck('individual_id');

        $certifications = QueryBuilder::for(CertificationAttributed::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_professional'),
                AllowedFilter::scope('filter_certification_category'),
                AllowedFilter::scope('filter_country'),
                AllowedFilter::scope('filter_status', 'status'),
            ])
            ->whereIn('individual_id', $individualIds)
            // CRITICAL: Remove global scope to access international certifications
            ->withoutGlobalScope(ExcludeInternationalScope::class)
            // CRITICAL: Only international certifications
            ->whereHas('certification', function ($q) {
                $q->whereHas('committee', function ($qq) {
                    $qq->where('is_international', true)
                        ->whereIn('code', ['DIVING', 'SCIENTIFIC']);
                });
            })
            ->with('certification.committee', 'certification.license.sport', 'federation.country', 'individual', 'mainInstructor')
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

        return view('web.international.entity.certifications.index', compact(
            'certifications',
            'countries',
            'professional_roles',
            'filter_status',
            'entity'
        ));
    }

    public function show(string $id): View
    {
        $entity = auth()->user()->entities()->first();

        if (! $entity) {
            abort(403, __('admin.error.no_entity_profile'));
        }

        // Get individuals associated with the entity
        $individualIds = $entity->individuals()
            ->wherePivot('status_class', ActiveIndividualEntityState::class)
            ->pluck('individual_id');

        $certification = CertificationAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
            ->with('certification.committee', 'certification.license.sport', 'federation.country', 'individual', 'mainInstructor')
            ->where('id', $id)
            ->whereIn('individual_id', $individualIds)
            ->whereHas('certification', function ($q) {
                $q->whereHas('committee', fn ($cq) => $cq->where('is_international', true));
            })
            ->firstOrFail();

        return view('web.international.entity.certifications.show', compact('certification', 'entity'));
    }
}
