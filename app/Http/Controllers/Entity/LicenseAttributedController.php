<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Common\BaseLicenseAttributedController;
use App\Models\Sport;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LicenseAttributedController extends BaseLicenseAttributedController
{
    public function __construct()
    {
        // No middleware here - we'll handle permissions in the methods
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = auth()->user();
        $entity = $user->entities()->first();

        // Check if this is a sport committee request (from filter parameter)
        $committee = request()->filter['committee'] ?? null;
        $isSportCommittee = $committee === 'sport';

        // Entity
        $query = QueryBuilder::for(LicenseAttributed::class)
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
            ->where(['model_type' => 'entity', 'model_id' => $entity->id]);

        // Apply committee-specific filtering
        // Normalize committee to uppercase for comparison
        $committeeUpper = $committee ? strtoupper($committee) : null;
        if ($committeeUpper === 'DIVING' || $committeeUpper === 'SCIENTIFIC') {
            // For DIVING and SCIENTIFIC committees, show only international licenses for entities
            $query->withoutGlobalScope(ExcludeInternationalScope::class)
                ->whereHas('license', function ($q) {
                    $q->whereHas('committee', fn ($cq) => $cq->where('is_international', true));
                });
        } else {
            // For SPORT, DIVINGSERVICES and other committees, show only national licenses
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

        return view('web.entity.license_attributed.index', compact('licenses', 'committee', 'filter_status', 'sports', 'professional_roles'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View|RedirectResponse
    {
        $user = auth()->user();

        // Check if this is a sport committee request (from referrer or session)
        $referrer = request()->headers->get('referer');
        $isSportCommittee = str_contains($referrer, 'filter[committee]=sport');
        $isDivingCommittee = str_contains($referrer, 'filter[committee]=diving');

        // If not sport committee context, require entity-admin role
        if (! $isSportCommittee && ! $user->hasRole('entity-admin')) {
            abort(403, 'Unauthorized access to license details');
        }

        $query = LicenseAttributed::with('owner')
            ->where(compact('id'));

        // Apply committee-specific filtering
        if ($isDivingCommittee) {
            // For diving committee, allow international licenses
            $query->withoutGlobalScope(ExcludeInternationalScope::class)
                ->whereHas('license', function ($q) {
                    $q->whereHas('committee', fn ($cq) => $cq->where('is_international', true));
                });
        } else {
            // For sport and other committees, show only national licenses
            $query->whereHas('license', function ($q) {
                $q->whereHas('committee', fn ($cq) => $cq->where('is_international', false));
            });
        }

        $license = $query->firstOrFail();

        return view('web.entity.license_attributed.show', compact('license'));
    }
}
