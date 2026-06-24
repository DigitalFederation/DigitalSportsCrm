<?php

namespace App\Http\Controllers\International\Federation;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Sport;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * international Federation License Attributed Controller
 *
 * Handles INTERNATIONAL licenses ONLY (is_international = true) for federation management
 * National licenses are handled in regular Federation namespace
 */
class LicenseAttributedController extends Controller
{
    /**
     * Display a listing of international licenses for the federation.
     */
    public function index(): View
    {
        // Retrieve the current federation ID and child federations
        $currentFederationId = auth()->user()->federations()->first()->id;

        $childFederationIds = Federation::where('parent_id', $currentFederationId)
            ->pluck('id')
            ->toArray();

        // Include current federation ID in the list
        $childFederationIds[] = $currentFederationId;

        // Query international licenses only
        $query = QueryBuilder::for(LicenseAttributed::class)
            ->allowedFilters([
                AllowedFilter::scope('committee'),
                AllowedFilter::scope('filter_holder_type', 'holder_type'),
                AllowedFilter::scope('filter_expiration_end', 'expiration_after'),
                AllowedFilter::scope('filter_expiration_start', 'expiration_before'),
                AllowedFilter::scope('filter_entity', 'entity'),
                AllowedFilter::scope('filter_member_code', 'member_code'),
                AllowedFilter::scope('filter_sport', 'sport'),
                AllowedFilter::scope('filter_category', 'professionalRole'),
                AllowedFilter::scope('filter_name', 'license_name'),
                AllowedFilter::scope('filter_status', 'license_attributed_status'),
                AllowedFilter::scope('filter_professional'),
            ])
            ->whereIn('federation_id', $childFederationIds)
            // CRITICAL: Remove global scope to access international licenses
            ->withoutGlobalScope(ExcludeInternationalScope::class)
            // CRITICAL: Only show INTERNATIONAL licenses (committee.is_international = true)
            ->whereHas('license', function ($q) {
                $q->whereHas('committee', function ($qq) {
                    $qq->where('is_international', true);
                });
            });

        $licenses = $query->with('owner', 'license.committee', 'license.sport', 'federation.country')
            ->allowedSorts('name', 'license_name', 'activated_at')
            ->defaultSort('-created_at')
            ->paginate()
            ->appends(request()->query());

        $sports = Sport::select('id', 'name')->orderBy('name')->get();
        $professional_roles = ProfessionalRole::select('id', 'name')->orderBy('name')->get();
        $countries = Country::select('id', 'name')->orderBy('name')->get();

        $filter_status = [
            'active' => ['id' => 'active', 'name' => __('Active')],
            'pending' => ['id' => 'pending', 'name' => __('Pending')],
            'canceled' => ['id' => 'canceled', 'name' => __('Suspended')],
        ];

        // Build dynamic title based on filters
        $committee = request()->input('filter.committee');
        $holderType = request()->input('filter.filter_holder_type', 'individual');
        $professional = request()->input('filter.filter_professional');

        if ($professional) {
            $professionalLabel = match ($professional) {
                'refereejudge' => 'Referee & Judge',
                'instructorleader' => 'Instructor & Leader',
                default => ucwords($professional)
            };
        }

        if ($committee) {
            $title = ucwords($committee);
            if ($professional) {
                $title .= ' ' . $professionalLabel . ' ' . __('admin.licenses');
            } else {
                $title .= ' ' . ucwords($holderType) . ' ' . __('admin.licenses');
            }
        } else {
            $title = ucwords($holderType) . ' ' . __('admin.licenses');
        }

        return view('web.international.federation.licenses-attributed.index', compact(
            'licenses',
            'sports',
            'filter_status',
            'professional_roles',
            'countries',
            'title'
        ));
    }

    /**
     * Display the specified international license.
     */
    public function show(string $id): View|RedirectResponse
    {
        $currentFederationId = auth()->user()->federations()->first()->id;

        $childFederationIds = Federation::where('parent_id', $currentFederationId)
            ->pluck('id')
            ->toArray();
        $childFederationIds[] = $currentFederationId;

        $license = LicenseAttributed::with('owner', 'license.committee', 'license.sport', 'federation.country')
            ->where('id', $id)
            ->whereIn('federation_id', $childFederationIds)
            // CRITICAL: Remove scope and filter for international only (committee.is_international = true)
            ->withoutGlobalScope(ExcludeInternationalScope::class)
            ->whereHas('license', function ($q) {
                $q->whereHas('committee', function ($qq) {
                    $qq->where('is_international', true);
                });
            })
            ->first();

        if (empty($license)) {
            return redirect()->back()->with('error', __('admin.error.license_not_found'));
        }

        return view('web.international.federation.licenses-attributed.show', compact('license'));
    }
}
