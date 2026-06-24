<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use App\Support\Committees;
use Domain\Individuals\Models\IndividualEntity;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SeparatedLicenseAttributedController extends Controller
{
    /**
     * Render an entity's licenses-attributed page for a committee + holder type.
     *
     * The committee code and holder ('entity'|'members') come from the route
     * defaults declared in routes/entity.php, generated from config/committees.php.
     * The international flag is derived from the committee and the page title from
     * the committee's configured entity-portal labels.
     */
    public function show(Request $request): View
    {
        return $this->renderLicensesPage(
            $request->route('holder'),
            $request->route('committeeCode')
        );
    }

    /**
     * Render the licenses attributed page for the given holder type + committee.
     */
    private function renderLicensesPage(string $type, string $committee): View
    {
        $entity = Auth::user()->getEntity();

        if (! $entity) {
            abort(403, __('No entity associated with this user'));
        }

        $isInternational = Committees::isInternational($committee);
        $pageTitle = Committees::attributedTitleText('entity', $committee, $type);

        $query = QueryBuilder::for(LicenseAttributed::class)
            ->with(['owner', 'license', 'license.committee', 'license.sport', 'license.professionalRole'])
            ->allowedFilters([
                AllowedFilter::scope('filter_holder_type', 'holder_type'),
                AllowedFilter::scope('filter_expiration_end', 'expiration_after'),
                AllowedFilter::scope('filter_expiration_start', 'expiration_before'),
                AllowedFilter::scope('filter_member_code', 'member_code'),
                AllowedFilter::scope('filter_sport', 'sport'),
                AllowedFilter::scope('filter_category', 'professionalRole'),
                AllowedFilter::scope('filter_name', 'license_name'),
                AllowedFilter::scope('filter_status', 'license_attributed_status'),
                AllowedFilter::scope('filter_professional'),
            ]);

        if ($type === 'entity') {
            // Entity licenses
            $query->where('model_type', 'entity')
                ->where('model_id', $entity->id);
        } else {
            // Member licenses - get individuals associated with entity
            $individualIds = IndividualEntity::where('entity_id', $entity->id)
                ->pluck('individual_id');

            $query->where('model_type', 'individual')
                ->whereIn('model_id', $individualIds);
        }

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

        $licenses = $query->paginate()
            ->appends(request()->query());

        $sports = Sport::select('id', 'name')->orderBy('name')->get();
        $professional_roles = ProfessionalRole::select('id', 'name')->orderBy('name')->get();

        $filter_status = [
            'active' => ['id' => 'active', 'name' => __('Active')],
            'pending' => ['id' => 'pending', 'name' => __('Pending')],
            'canceled' => ['id' => 'canceled', 'name' => __('Suspended')],
        ];

        // Set the holder type for the view
        $filterHolderType = $type === 'entity' ? 'entity' : 'individual';

        return view('web.entity.license_attributed.index', compact(
            'licenses',
            'committee',
            'filter_status',
            'sports',
            'professional_roles',
            'filterHolderType',
            'isInternational',
            'type',
            'pageTitle'
        ));
    }
}
