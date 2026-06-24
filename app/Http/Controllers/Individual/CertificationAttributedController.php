<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use App\Models\GeoZone;
use App\Models\Sport;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\DirectorApprovedCertificationAttributedState;
use Domain\Certifications\States\RejectedCertificationAttributedState;
use Domain\Federations\Models\Federation;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CertificationAttributedController extends Controller
{
    public function index(): View
    {
        $certifications_attributed = QueryBuilder::for(CertificationAttributed::class)
            ->allowedFilters([
                AllowedFilter::scope('committee', 'filterCommittee')->ignore(null),
                AllowedFilter::scope('filter_expiration_end', 'expirationAfter')->ignore(null),
                AllowedFilter::scope('filter_expiration_start', 'expirationBefore')->ignore(null),
                AllowedFilter::scope('filter_emission_end', 'emissionAfter')->ignore(null),
                AllowedFilter::scope('filter_emission_start', 'emissionBefore')->ignore(null),
                AllowedFilter::scope('filter_certification', 'certificationId')->ignore(null),
                AllowedFilter::scope('filter_federation', 'federation')->ignore(null),
                AllowedFilter::scope('filter_entity', 'entity')->ignore(null),
                AllowedFilter::scope('filter_status', 'certificationAttributedStatus')->ignore(null),
                AllowedFilter::scope('filter_sport', 'sport')->ignore(null),
                AllowedFilter::scope('filter_director_code', 'director_code')->ignore(null),
                AllowedFilter::scope('filter_member_code', 'member_code')->ignore(null),
                AllowedFilter::scope('filter_zone')->ignore(null),
                AllowedFilter::scope('professional')->ignore(null),
            ])
            ->where('individual_id', auth()->user()->individuals()->first()->id)
            ->with('certification.license.sport', 'federation.country')
            ->orderBy('created_at', 'desc')
            ->paginate()
            ->appends(request()->query());

        $filter_status = [
            'active' => ['id' => 'active', 'name' => __('Active')],
            'pending' => ['id' => 'pending', 'name' => __('Pending')],
            'canceled' => ['id' => 'canceled', 'name' => __('Canceled')],
        ];

        $certifications = Certification::select('id', 'name')->whereHas('committee', function (Builder $query) {
            $query->where('code', Request()->query('filter')['committee'] ?? '');
        })->orderBy('name')->get();

        $federations = Federation::select('id', 'name')->whereHas('individuals', function (Builder $query) {
            $query->where('individual_id', auth()->user()->individuals()->first()->id);
        })->orderBy('name')->get();

        $sports = Sport::select('id', 'name')->orderBy('name')->get();
        $cmas_zones = GeoZone::select('id', 'name')->orderBy('name')->get();

        return view('web.individual.certification_attributed.index', compact('certifications_attributed', 'certifications', 'filter_status', 'federations', 'sports', 'cmas_zones'));
    }

    public function show(string $id): View
    {
        $certification_attributed = Cache::remember("certification_attributed_{$id}", 60, function () use ($id) {
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
                'national_code'
            )
                ->with([
                    'certification:id,name,committee_id,certification_view',
                    'individual:id,name,surname,member_code,qrcode_path',
                    'individual.media',
                    'entity',
                    'activator:id,name',
                    'mainInstructor',
                    'federation:id,member_code,country_id,name',
                    'federation.country:id,name,iso,ioc',
                ])
                ->where('id', $id)
                ->firstOrFail();
        });

        $main_instructor = $certification_attributed->mainInstructor()->first();
        $assistants = $certification_attributed->assistantInstructors()->get();

        return view('web.individual.certification_attributed.show', [
            'certification' => $certification_attributed,
            'mainInstructor' => $main_instructor,
            'assistants' => $assistants,
            'showInstructorInfo' => ! empty($main_instructor) || ! empty($assistants),
        ]);
    }

    /**
     * If the user is Instructor or Director, show the form to create a new validation
     */
    public function activate(
        Request $request,
    ): RedirectResponse {

        try {
            DB::beginTransaction();
            $certification_attributed = CertificationAttributed::where('id', $request->id)->sharedLock()->first();

            $certification_attributed->status_class = DirectorApprovedCertificationAttributedState::class;
            // save the update
            $certification_attributed->save();

            DB::commit();

            // activity log

            activity('Certification')
                ->causedBy(auth()->user())
                ->performedOn($certification_attributed)
                ->event('Director Approved Certification')
                ->withProperties($certification_attributed->toArray())
                ->log('Certification request '.$certification_attributed->certification_name.' approved.');

            // Invalidate the cache
            Cache::forget("certification_attributed_{$request->id}");

            return back()->with('success', 'Certification approved with success.');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    public function cancel(
        Request $request,
    ): RedirectResponse {

        try {
            DB::beginTransaction();

            $certification_attributed = CertificationAttributed::where('id', $request->id)->sharedLock()->first();
            $certification_attributed->status_class = RejectedCertificationAttributedState::class;
            // save the update
            $certification_attributed->save();

            DB::commit();

            // activity log

            activity('Certification')
                ->causedBy(auth()->user())
                ->performedOn($certification_attributed)
                ->event('Director Rejected Certification')
                ->withProperties($certification_attributed->toArray())
                ->log('Certification request '.$certification_attributed->certification_name.' rejected.');

            // Invalidate the cache
            Cache::forget("certification_attributed_{$request->id}");

            return back()->with('success', 'Certification rejected with success.');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    public function grid(): View
    {
        $certifications_attributed = QueryBuilder::for(CertificationAttributed::class)
            ->allowedFilters([
                AllowedFilter::scope('committee', 'filterCommittee')->ignore(null),
                AllowedFilter::scope('filter_expiration_end', 'expirationAfter')->ignore(null),
                AllowedFilter::scope('filter_expiration_start', 'expirationBefore')->ignore(null),
                AllowedFilter::scope('filter_emission_end', 'emissionAfter')->ignore(null),
                AllowedFilter::scope('filter_emission_start', 'emissionBefore')->ignore(null),
                AllowedFilter::scope('filter_certification', 'certificationId')->ignore(null),
                AllowedFilter::scope('filter_federation', 'federation')->ignore(null),
                AllowedFilter::scope('filter_entity', 'entity')->ignore(null),
                AllowedFilter::scope('filter_status', 'certificationAttributedStatus')->ignore(null),
                AllowedFilter::scope('filter_sport', 'sport')->ignore(null),
                AllowedFilter::scope('filter_director_code', 'director_code')->ignore(null),
                AllowedFilter::scope('filter_member_code', 'member_code')->ignore(null),
                AllowedFilter::scope('filter_zone')->ignore(null),
                AllowedFilter::scope('professional')->ignore(null),
            ])
            ->where('individual_id', auth()->user()->individuals()->first()->id)
            ->with('certification.license.sport', 'federation.country')
            ->orderBy('created_at', 'desc')
            ->paginate()
            ->appends(request()->query());

        $filter_status = [
            'active' => ['id' => 'active', 'name' => __('Active')],
            'pending' => ['id' => 'pending', 'name' => __('Pending')],
            'canceled' => ['id' => 'canceled', 'name' => __('Canceled')],
        ];

        $certifications = Certification::select('id', 'name')->whereHas('committee', function (Builder $query) {
            $query->where('code', Request()->query('filter')['committee'] ?? '');
        })->orderBy('name')->get();

        $federations = Federation::select('id', 'name')->whereHas('individuals', function (Builder $query) {
            $query->where('individual_id', auth()->user()->individuals()->first()->id);
        })->orderBy('name')->get();

        $sports = Sport::select('id', 'name')->orderBy('name')->get();
        $cmas_zones = GeoZone::select('id', 'name')->orderBy('name')->get();

        return view('web.individual.certification_attributed.index', compact('certifications_attributed', 'certifications', 'filter_status', 'federations', 'sports', 'cmas_zones'));
    }
}
