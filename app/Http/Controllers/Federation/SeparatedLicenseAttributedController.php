<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use App\Support\Committees;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $pageTitle = Committees::attributedTitleText('federation', $committee, $holderType);
        $pageSubtitle = Committees::attributedSubtitleText('federation', $committee, $holderType);

        $currentFederation = auth()->user()->federations()->first();

        if (! $currentFederation) {
            abort(403, __('No federation associated with this user'));
        }

        $isDefaultFederation = (bool) $currentFederation->is_default_federation;

        $allowedFilters = [
            AllowedFilter::scope('filter_expiration_end', 'expiration_after'),
            AllowedFilter::scope('filter_expiration_start', 'expiration_before'),
            AllowedFilter::scope('filter_entity', 'entity'),
            AllowedFilter::scope('filter_member_code', 'member_code'),
            AllowedFilter::scope('filter_sport', 'sport'),
            AllowedFilter::scope('filter_category', 'professionalRole'),
            AllowedFilter::scope('filter_name', 'license_name'),
            AllowedFilter::scope('filter_status', 'license_attributed_status'),
            AllowedFilter::scope('filter_professional'),
            AllowedFilter::scope('filter_emission_end', 'emissionAfter'),
            AllowedFilter::scope('filter_emission_start', 'emissionBefore'),
            AllowedFilter::scope('filter_license', 'licenseId'),
            AllowedFilter::scope('filter_first_name', 'individualFirstName'),
            AllowedFilter::scope('filter_surname', 'individualSurname'),
            AllowedFilter::scope('filter_member_number', 'individualMemberNumber'),
            AllowedFilter::scope('filter_payment_status', 'filterPaymentStatus'),
            AllowedFilter::scope('filter_entity_name', 'entityName'),
        ];

        $query = QueryBuilder::for(LicenseAttributed::class)
            ->with(['owner', 'license', 'license.committee', 'license.sport', 'license.professionalRole'])
            ->allowedFilters($allowedFilters);

        // Apply holder type filter (entity or individual)
        $query->where('model_type', $holderType);

        // Apply federation-based visibility rules
        $this->applyFederationFilter($query, $currentFederation);

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

        // Main federation gets payment status eager-loaded
        if ($isDefaultFederation) {
            $query->withPaymentStatus();
        }

        $licenses = $query
            ->allowedSorts('name', 'license_name', 'activated_at')
            ->defaultSort('-created_at')
            ->paginate()
            ->appends(request()->query());

        $sports = Sport::orderBy('name')->get()->map(fn ($sport) => [
            'id' => $sport->id,
            'name' => $sport->translated_name,
        ]);
        $professional_roles = ProfessionalRole::select('id', 'name')->orderBy('name')->get();
        $entities = $this->getEntitiesForFederation($currentFederation);

        $filter_status = [
            'active' => ['id' => 'active', 'name' => __('licenses.state_active')],
            'pending' => ['id' => 'pending', 'name' => __('licenses.state_pending')],
            'canceled' => ['id' => 'canceled', 'name' => __('licenses.state_canceled')],
            'provisional' => ['id' => 'provisional', 'name' => __('licenses.state_provisional')],
            'suspended' => ['id' => 'suspended', 'name' => __('licenses.state_suspended')],
            'waiting_approval' => ['id' => 'waiting_approval', 'name' => __('licenses.state_waiting_approval')],
            'expired' => ['id' => 'expired', 'name' => __('licenses.state_expired')],
        ];

        $viewData = compact(
            'licenses',
            'committee',
            'isInternational',
            'holderType',
            'filter_status',
            'sports',
            'professional_roles',
            'entities',
            'pageTitle',
            'pageSubtitle',
            'currentFederation',
            'isDefaultFederation'
        );

        // Main federation gets extra dropdown data (available licenses, payment status filter)
        if ($isDefaultFederation) {
            $licensesQuery = License::select('id', 'name')
                ->whereHas('committee', function ($q) use ($committee, $isInternational) {
                    $q->where('code', $committee)
                        ->where('is_international', $isInternational);
                });

            if ($isInternational) {
                $licensesQuery->withoutGlobalScope(ExcludeInternationalScope::class);
            }

            $viewData['availableLicenses'] = $licensesQuery->orderBy('name')->get();

            $viewData['filter_payment_status'] = [
                'paid' => ['id' => 'paid', 'name' => __('licenses.payment_status_paid')],
                'pending_payment' => ['id' => 'pending_payment', 'name' => __('licenses.payment_status_pending_payment')],
                'no_document' => ['id' => 'no_document', 'name' => __('licenses.payment_status_no_document')],
            ];
        }

        return view('web.federation.license_attributed.separated', $viewData);
    }

    /**
     * Apply federation-based visibility rules to the query
     */
    private function applyFederationFilter($query, $currentFederation): void
    {
        $currentFederationId = $currentFederation->id;

        // Main federation sees all licenses.
        if ($currentFederation->is_default_federation) {
            // No additional filter - show all
            return;
        }

        // Local/Territorial federations see only licenses of their members
        if ($currentFederation->is_local) {
            $entityIds = DB::table('entity_federation')
                ->where('federation_id', $currentFederationId)
                ->pluck('entity_id');

            $individualIds = DB::table('individual_federation')
                ->where('federation_id', $currentFederationId)
                ->pluck('individual_id');

            $query->where(function ($q) use ($entityIds, $individualIds) {
                $q->where(function ($subQ) use ($entityIds) {
                    $subQ->where('model_type', 'entity')
                        ->whereIn('model_id', $entityIds);
                })->orWhere(function ($subQ) use ($individualIds) {
                    $subQ->where('model_type', 'individual')
                        ->whereIn('model_id', $individualIds);
                });
            });

            return;
        }

        // Other federations (modalidade) - filter by federation_id
        $query->where('federation_id', $currentFederationId);
    }

    /**
     * Get entities associated with the federation for filter dropdown
     */
    private function getEntitiesForFederation($federation): \Illuminate\Support\Collection
    {
        if ($federation->is_default_federation) {
            return Entity::select('id', 'name')->orderBy('name')->get();
        }

        $entityIds = DB::table('entity_federation')
            ->where('federation_id', $federation->id)
            ->pluck('entity_id');

        return Entity::select('id', 'name')
            ->whereIn('id', $entityIds)
            ->orderBy('name')
            ->get();
    }
}
