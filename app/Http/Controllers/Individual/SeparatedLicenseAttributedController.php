<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use App\Support\Committees;
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
     * Render an individual's licenses-attributed page for a committee.
     *
     * The committee code comes from the route default declared in
     * routes/individual.php, generated from config/committees.php. The
     * international flag is derived from the committee and the page titles from
     * the committee's configured individual licenses-attributed labels.
     */
    public function show(Request $request): View
    {
        return $this->renderLicensesPage($request->route('committeeCode'));
    }

    /**
     * Render the licenses attributed page for the given committee.
     */
    private function renderLicensesPage(string $committee): View
    {
        $individual = Auth::user()->individual;

        if (! $individual) {
            abort(403, __('No individual profile associated with this user'));
        }

        $isInternational = Committees::isInternational($committee);
        $pageTitle = Committees::attributedTitleText('individual', $committee);
        $pageSubtitle = Committees::attributedSubtitleText('individual', $committee);

        $query = QueryBuilder::for(LicenseAttributed::class)
            ->with(['owner', 'license', 'license.committee', 'license.sport', 'license.professionalRole'])
            ->allowedFilters([
                AllowedFilter::scope('filter_expiration_end', 'expiration_after'),
                AllowedFilter::scope('filter_expiration_start', 'expiration_before'),
                AllowedFilter::scope('filter_member_code', 'member_code'),
                AllowedFilter::scope('filter_sport', 'sport'),
                AllowedFilter::scope('filter_category', 'professionalRole'),
                AllowedFilter::scope('filter_name', 'license_name'),
                AllowedFilter::scope('filter_status', 'license_attributed_status'),
            ]);

        // Filter by individual
        $query->where('model_type', 'individual')
            ->where('model_id', $individual->id);

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
            'active' => ['id' => 'active', 'name' => __('licenses.state_active')],
            'pending' => ['id' => 'pending', 'name' => __('licenses.state_pending')],
            'canceled' => ['id' => 'canceled', 'name' => __('licenses.state_canceled')],
        ];

        return view('web.individual.licenses-attributed.separated', compact(
            'licenses',
            'committee',
            'filter_status',
            'sports',
            'professional_roles',
            'isInternational',
            'pageTitle',
            'pageSubtitle'
        ));
    }
}
