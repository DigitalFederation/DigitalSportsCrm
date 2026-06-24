<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\GeoZone;
use App\Models\Sport;
use App\Support\Committees;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SeparatedLicenseAttributedController extends Controller
{
    /**
     * Render a licenses-attributed page for a committee + holder type.
     *
     * The committee code and holder ('entity'|'individual') come from the route
     * defaults declared in the route file, generated from config/committees.php.
     * The international flag is derived from the committee and the page titles
     * from the committee's configured labels.
     */
    public function show(Request $request): View
    {
        return $this->renderLicensesPage(
            $request->route('committeeCode'),
            $request->route('holder')
        );
    }

    /**
     * Render the licenses attributed page with fixed committee, international, and holder type parameters
     */
    private function renderLicensesPage(string $committee, string $holderType): View
    {
        $isInternational = Committees::isInternational($committee);
        $pageTitle = Committees::attributedTitleText('admin', $committee, $holderType);
        $pageSubtitle = Committees::attributedSubtitleText('admin', $committee, $holderType);

        $query = QueryBuilder::for(LicenseAttributed::class)
            ->with(['owner', 'license', 'license.committee', 'license.sport', 'license.professionalRole', 'federation.country'])
            ->allowedFilters([
                AllowedFilter::scope('filter_expiration_end', 'expiration_after'),
                AllowedFilter::scope('filter_expiration_start', 'expiration_before'),
                AllowedFilter::scope('filter_emission_end', 'emissionAfter'),
                AllowedFilter::scope('filter_emission_start', 'emissionBefore'),
                AllowedFilter::scope('filter_federation', 'federation'),
                AllowedFilter::scope('filter_entity', 'entity'),
                AllowedFilter::scope('filter_country', 'country'),
                AllowedFilter::scope('filter_member_code', 'member_code'),
                AllowedFilter::scope('filter_sport', 'sport'),
                AllowedFilter::scope('filter_category', 'professionalRole'),
                AllowedFilter::scope('filter_name', 'license_name'),
                AllowedFilter::scope('filter_status', 'license_attributed_status'),
                AllowedFilter::scope('filter_zone'),
                AllowedFilter::scope('filter_professional'),
                AllowedFilter::scope('filter_license', 'licenseId'),
                AllowedFilter::scope('filter_first_name', 'individualFirstName'),
                AllowedFilter::scope('filter_surname', 'individualSurname'),
                AllowedFilter::scope('filter_member_number', 'individualMemberNumber'),
                AllowedFilter::scope('filter_payment_status', 'filterPaymentStatus'),
                AllowedFilter::scope('filter_entity_name', 'entityName'),
            ]);

        // Apply holder type filter (entity or individual)
        $query->where('model_type', $holderType);

        // Apply committee filter
        $query->whereHas('license', function ($q) use ($committee) {
            $q->whereHas('committee', function ($cq) use ($committee) {
                $cq->where('code', $committee);
            });
        });

        // Apply international filter
        if ($isInternational) {
            $query->withoutGlobalScope(ExcludeInternationalScope::class)
                ->whereHas('license', function ($q) {
                    $q->withoutGlobalScope(ExcludeInternationalScope::class)
                        ->whereHas('committee', fn ($cq) => $cq->where('is_international', true));
                });
        } else {
            $query->whereHas('license', function ($q) {
                $q->whereHas('committee', fn ($cq) => $cq->where('is_international', false));
            });
        }

        $licenses = $query
            ->withPaymentStatus()
            ->latest()
            ->paginate()
            ->appends(request()->query());

        $federations = Federation::select('id', 'name')->orderBy('name')->get();
        $countries = Country::select('id', 'name')->orderBy('name')->get();
        $sports = Sport::orderBy('name')->get()->map(fn ($sport) => [
            'id' => $sport->id,
            'name' => $sport->translated_name,
        ]);
        $professional_roles = ProfessionalRole::select('id', 'name')->orderBy('name')->get();
        $cmas_zones = GeoZone::select('id', 'name')->orderBy('name')->get();
        $entities = Entity::select('id', 'name')->orderBy('name')->get();

        // Fetch licenses for the dropdown based on committee and international flag
        $licensesQuery = License::select('id', 'name')
            ->whereHas('committee', function ($q) use ($committee, $isInternational) {
                $q->where('code', $committee)
                    ->where('is_international', $isInternational);
            });

        if ($isInternational) {
            $licensesQuery->withoutGlobalScope(ExcludeInternationalScope::class);
        }

        $availableLicenses = $licensesQuery->orderBy('name')->get();

        $filter_status = [
            'active' => ['id' => 'active', 'name' => __('licenses.state_active')],
            'pending' => ['id' => 'pending', 'name' => __('licenses.state_pending')],
            'canceled' => ['id' => 'canceled', 'name' => __('licenses.state_canceled')],
            'provisional' => ['id' => 'provisional', 'name' => __('licenses.state_provisional')],
            'suspended' => ['id' => 'suspended', 'name' => __('licenses.state_suspended')],
            'waiting_approval' => ['id' => 'waiting_approval', 'name' => __('licenses.state_waiting_approval')],
            'expired' => ['id' => 'expired', 'name' => __('licenses.state_expired')],
        ];

        $filter_payment_status = [
            'paid' => ['id' => 'paid', 'name' => __('licenses.payment_status_paid')],
            'pending_payment' => ['id' => 'pending_payment', 'name' => __('licenses.payment_status_pending_payment')],
            'no_document' => ['id' => 'no_document', 'name' => __('licenses.payment_status_no_document')],
        ];

        return view('web.admin.license_attributed.separated', compact(
            'licenses',
            'committee',
            'isInternational',
            'holderType',
            'filter_status',
            'filter_payment_status',
            'federations',
            'countries',
            'sports',
            'professional_roles',
            'cmas_zones',
            'entities',
            'availableLicenses',
            'pageTitle',
            'pageSubtitle'
        ));
    }
}
