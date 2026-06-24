<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Common\BaseLicenseAttributedController;
use App\Models\Sport;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\IndividualEntity;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Contracts\View\View;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndividualLicenseAttributedController extends BaseLicenseAttributedController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {

        // Entity
        $licenses = QueryBuilder::for(LicenseAttributed::class)
            ->with(['owner'])
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
            ->where(['model_type' => 'individual'])
            ->whereIn('model_id', IndividualEntity::where('entity_id', auth()->user()->entities()->first()->id)->pluck('individual_id'))
            ->paginate()
            ->appends(request()->query());

        $sports = Sport::select('id', 'name')->orderBy('name')->get();

        $professional_roles = ProfessionalRole::select('id', 'name')->orderBy('name')->get();

        $filter_status = [
            'active' => ['id' => 'active', 'name' => __('Active')],
            'pending' => ['id' => 'pending', 'name' => __('Pending')],
            'canceled' => ['id' => 'canceled', 'name' => __('Suspended')],
        ];

        $committee = request()->filter['committee'] ?? null;

        // Set filterHolderType to 'individual' to ensure proper routing for purchase button
        $filterHolderType = 'individual';

        return view('web.entity.license_attributed.index', compact('licenses', 'sports', 'filter_status', 'professional_roles', 'committee', 'filterHolderType'));
    }
}
