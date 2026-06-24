<?php

namespace App\Http\Controllers\International\Federation;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Sport;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Domain\Federations\Models\Federation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * international Federation Certification Attributed Controller
 *
 * Handles INTERNATIONAL certifications ONLY (is_international = true) for federation management
 * Covers DIVING and SCIENTIFIC committees only
 * National certifications are handled in regular Federation namespace
 */
class CertificationAttributedController extends Controller
{
    /**
     * Display a listing of international certifications for the federation.
     */
    public function index(): View
    {
        // Retrieve IDs of current federation and all child federations
        $currentFederationId = Auth::user()->getFederationId();
        $childFederationIds = Federation::where('parent_id', $currentFederationId)
            ->pluck('id')
            ->toArray();

        // Include current federation ID in the list
        $childFederationIds[] = $currentFederationId;

        $certifications_attributed = QueryBuilder::for(CertificationAttributed::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_expiration_end', 'expirationAfter'),
                AllowedFilter::scope('filter_expiration_start', 'expirationBefore'),
                AllowedFilter::scope('filter_emission_end', 'emissionAfter'),
                AllowedFilter::scope('filter_emission_start', 'emissionBefore'),
                AllowedFilter::scope('filter_certification', 'certificationId'),
                AllowedFilter::scope('filter_federation', 'federation'),
                AllowedFilter::scope('filter_entity', 'entity'),
                AllowedFilter::scope('filter_status', 'certificationAttributedStatus'),
                AllowedFilter::scope('filter_sport', 'sport'),
                AllowedFilter::scope('filter_director_code', 'director_code'),
                AllowedFilter::scope('filter_member_code', 'member_code'),
            ])
            ->whereIn('federation_id', $childFederationIds)
            // CRITICAL: Remove global scope to access international certifications
            ->withoutGlobalScope(ExcludeInternationalScope::class)
            // CRITICAL: Only international certifications (committee.is_international = true)
            ->whereHas('certification', function ($q) {
                $q->whereHas('committee', function ($qq) {
                    $qq->where('is_international', true);
                });
            })
            ->with('certification.committee', 'certification.license.sport', 'federation.country', 'individual', 'entity')
            ->orderBy('created_at', 'desc')
            ->paginate()
            ->appends(request()->query());

        $filter_status = [
            'active' => ['id' => 'active', 'name' => __('Active')],
            'pending' => ['id' => 'pending', 'name' => __('Pending')],
            'canceled' => ['id' => 'canceled', 'name' => __('Canceled')],
            'waiting_director' => ['id' => 'waiting_director', 'name' => __('Waiting Director')],
            'waiting_nf' => ['id' => 'waiting_nf', 'name' => __('Waiting NF')],
            'expired' => ['id' => 'expired', 'name' => __('Expired')],
            'provisional' => ['id' => 'provisional', 'name' => __('Provisional')],
            'rejected' => ['id' => 'rejected', 'name' => __('Rejected')],
            'suspended' => ['id' => 'suspended', 'name' => __('Suspended')],
        ];

        // Get international certifications for filter dropdown (committee.is_international = true)
        $certifications = Certification::select('id', 'name')
            ->whereHas('committee', function ($q) {
                $q->where('is_international', true);
            })
            ->orderBy('name')
            ->get();

        $federations = Federation::select('id', 'name')->orderBy('name')->get();
        $sports = Sport::select('id', 'name')->orderBy('name')->get();
        $countries = Country::select('id', 'name')->orderBy('name')->get();

        return view('web.international.federation.certifications-attributed.index', compact(
            'certifications_attributed',
            'certifications',
            'filter_status',
            'sports',
            'federations',
            'countries'
        ));
    }

    /**
     * Display the specified international certification.
     */
    public function show(string $id): View|RedirectResponse
    {
        $currentFederationId = Auth::user()->getFederationId();
        $childFederationIds = Federation::where('parent_id', $currentFederationId)
            ->pluck('id')
            ->toArray();
        $childFederationIds[] = $currentFederationId;

        // Use cache to improve performance
        $certification_attributed = Cache::remember("cmas_certification_attributed_{$id}", 60, function () use ($id, $childFederationIds) {
            return CertificationAttributed::select(
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
                'national_code',
                'holder_name'
            )
                ->with([
                    'certification:id,name,committee_id,certification_view,is_international',
                    'certification.committee',
                    'individual:id,name,surname,member_code,qrcode_path',
                    'individual.media',
                    'entity',
                    'activator:id,name',
                    'mainInstructor',
                    'federation:id,member_code,country_id,name',
                    'federation.country:id,name,iso,ioc',
                ])
                ->whereIn('federation_id', $childFederationIds)
                // CRITICAL: Remove scope and filter for international only (committee.is_international = true)
                ->withoutGlobalScope(ExcludeInternationalScope::class)
                ->whereHas('certification', function ($q) {
                    $q->whereHas('committee', function ($qq) {
                        $qq->where('is_international', true);
                    });
                })
                ->where('id', $id)
                ->first();
        });

        if (empty($certification_attributed)) {
            return redirect()->back()->with('error', __('admin.error.certification_not_found'));
        }

        $main_instructor = $certification_attributed->mainInstructor()->first();
        $assistants = $certification_attributed->assistantInstructors()->get();

        return view('web.international.federation.certifications-attributed.show', [
            'certification' => $certification_attributed,
            'mainInstructor' => $main_instructor,
            'assistants' => $assistants,
            'showInstructorInfo' => ! empty($main_instructor) || ! empty($assistants),
        ]);
    }
}
