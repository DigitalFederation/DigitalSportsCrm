<?php

namespace App\Http\Controllers\International\Individual;

use App\Http\Controllers\Common\BaseLicenseAttributedController;
use App\Models\Country;
use App\Models\GeoZone;
use App\Models\Sport;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * international Individual License Attributed Controller
 *
 * Handles INTERNATIONAL licenses ONLY (is_international = true)
 * National licenses are handled in the regular Individual namespace
 */
class LicenseAttributedController extends BaseLicenseAttributedController
{
    /**
     * Display a listing of international licenses for the individual.
     */
    public function index(): View
    {
        $individual = auth()->user()->individuals()->first();

        if (! $individual) {
            abort(403, __('admin.error.no_individual_profile'));
        }

        // Permission check handled by route middleware
        // Active affiliation check
        if (! $individual->hasActiveAffiliation()) {
            abort(403, __('admin.error.no_active_affiliation'));
        }

        $query = QueryBuilder::for(LicenseAttributed::class)
            ->allowedFilters([
                AllowedFilter::scope('committee'),
                AllowedFilter::scope('filter_holder_type', 'holder_type'),
                AllowedFilter::scope('filter_expiration_end', 'expiration_after'),
                AllowedFilter::scope('filter_expiration_start', 'expiration_before'),
                AllowedFilter::scope('filter_federation', 'federation'),
                AllowedFilter::scope('filter_entity', 'entity'),
                AllowedFilter::scope('filter_country', 'country'),
                AllowedFilter::scope('filter_sport', 'sport'),
                AllowedFilter::scope('filter_category', 'professionalRole'),
                AllowedFilter::scope('filter_name', 'license_name'),
                AllowedFilter::scope('filter_status', 'license_attributed_status'),
            ])
            ->where(['model_type' => 'individual', 'model_id' => $individual->id])
            // CRITICAL: Remove global scope to access international licenses
            ->withoutGlobalScope(ExcludeInternationalScope::class)
            // CRITICAL: Only show INTERNATIONAL licenses
            ->whereHas('license', function ($q) {
                $q->whereHas('committee', fn ($cq) => $cq->where('is_international', true));
            });

        $licenses = $query->with('federation.country', 'owner', 'license.committee', 'license.sport')
            ->paginate()
            ->appends(request()->query());

        // Filter data for international licenses only
        $federations = Federation::select('id', 'name')
            ->whereHas('licenses', function ($query) {
                $query->whereHas('committee', fn ($cq) => $cq->where('is_international', true));
            })
            ->orderBy('name')
            ->get();

        $countries = Country::select('id', 'name')->orderBy('name')->get();
        $sports = Sport::select('id', 'name')->orderBy('name')->get();
        $professional_roles = ProfessionalRole::select('id', 'name')->orderBy('name')->get();
        $cmas_zones = GeoZone::select('id', 'name')->orderBy('name')->get();

        $filter_status = [
            'active' => ['id' => 'active', 'name' => __('Active')],
            'pending' => ['id' => 'pending', 'name' => __('Pending')],
            'canceled' => ['id' => 'canceled', 'name' => __('Suspended')],
        ];

        $title = __('admin.licenses.title');

        return view('web.international.individual.licenses-attributed.index', compact(
            'licenses',
            'federations',
            'countries',
            'sports',
            'filter_status',
            'title',
            'professional_roles',
            'cmas_zones'
        ));
    }

    /**
     * Display the specified international license.
     */
    public function show(string $id): View|RedirectResponse
    {
        $individual = auth()->user()->individuals()->first();

        if (! $individual) {
            abort(403, __('admin.error.no_individual_profile'));
        }

        $query = LicenseAttributed::with('owner', 'license.committee', 'license.sport', 'federation')
            ->where('id', $id)
            ->where('model_type', 'individual')
            ->where('model_id', $individual->id)
            // CRITICAL: Remove scope and filter for international only
            ->withoutGlobalScope(ExcludeInternationalScope::class)
            ->whereHas('license', function ($q) {
                $q->whereHas('committee', fn ($cq) => $cq->where('is_international', true));
            });

        $license = $query->firstOrFail();

        return view('web.international.individual.licenses-attributed.show', compact('license'));
    }

    /**
     * Remove the specified international license.
     */
    public function destroy(LicenseAttributed $license_attributed): RedirectResponse
    {
        $individual = auth()->user()->individuals()->first();

        // Verify ownership and international status
        if ($license_attributed->model_type !== 'individual' ||
            $license_attributed->model_id !== $individual->id ||
            ! $license_attributed->license->is_international) {
            abort(403, __('admin.error.unauthorized'));
        }

        // Check if license can be deleted (business rules)
        if (! $license_attributed->canBeDeleted()) {
            return redirect()->back()->with('error', __('admin.error.cannot_delete_license'));
        }

        $license_attributed->delete();

        return redirect()->route('international.individual.licenses-attributed.index')
            ->with('success', __('admin.success.license_deleted'));
    }
}
