<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Models\Country;
use App\Models\Language;
use Domain\Attachments\Models\Attachment;
use Domain\Attachments\Models\AttachmentCategory;
use Domain\Certifications\Models\Certification;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseType;
use Domain\Users\Actions\GetUserTypeAction;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AttachmentsSentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Committee $committee, GetUserTypeAction $getUserType): View
    {
        $federationId = $getUserType::execute(auth()->user())->id;
        $cacheKey = "attachments_from_federation_{$federationId}_committee_{$committee->id}";

        $attachments = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($federationId, $committee) {
            $baseQuery = Attachment::query()
                ->where(function (Builder $query) use ($federationId) {
                    $query->where('owner_type', Federation::class)
                        ->where('owner_id', $federationId);
                })->where('committee_id', $committee->id);

            // Use QueryBuilder for additional filters
            return QueryBuilder::for($baseQuery)
                ->allowedFilters([
                    AllowedFilter::scope('filter_name'),
                    AllowedFilter::scope('filter_category'),
                    AllowedFilter::scope('filter_language'),
                    AllowedFilter::scope('filter_date_start'),
                    AllowedFilter::scope('filter_date_end'),
                ])
                ->with(['category', 'language', 'licenses', 'certifications', 'owner', 'media'])
                ->whereHas('media')
                ->orderBy('name')
                ->paginate()
                ->appends(request()->query());
        });

        $categories = Cache::remember('attachment_categories', now()->addDays(1), function () {
            return AttachmentCategory::all();
        });
        $languages = Cache::remember('attachment_languages', now()->addDays(1), function () {
            return Language::orderBy('name')->get();
        });

        return view('web.federation.attachments_sent.index', compact('attachments', 'committee', 'categories', 'languages'));
    }

    public function create(?Committee $committee = null)
    {
        $federation_id = auth()->user()->federations()->first()->id;

        $countries = Country::query()->pluck('name', 'id');

        $categories = AttachmentCategory::query()->pluck('name', 'id');

        $licenses = $certifications = $entity_licenses = $individual_licenses = collect();
        $license_type_entity = Cache::remember('license_type_entity'.$federation_id, 120, function () {
            return LicenseType::where('name', 'entity')->first();
        });
        $license_type_individual = Cache::remember('license_type_individual'.$federation_id, 120, function () {
            return LicenseType::where('name', 'individual')->first();
        });

        if ($committee) {
            $professional_roles = ProfessionalRole::where('committee_id', $committee->id)->pluck('name', 'id');
            $licenses = $committee->licenses()->get()->pluck('name', 'id');
            $certifications = $committee->certifications()->get()->pluck('name', 'id');

            // Get licenses for Entities based on LicenseType
            if ($license_type_entity) {
                $entity_licenses = Cache::remember('entityLicenses_for_committee_' . $committee->id.'_federation_'.$federation_id, 60, function () use ($committee, $license_type_entity) {
                    return $committee->licenses()
                        ->with('type')
                        ->where('type_id', $license_type_entity->id)
                        ->pluck('name', 'id');
                });
            }

            // Get licenses for Individuals based on LicenseType
            $individual_licenses = Cache::remember('individualLicenses_for_committee_' . $committee->id, 120, function () use ($committee) {
                return $committee->licenses()->whereHas('type', function ($query) {
                    $query->where('is_individual', true);
                })
                    ->pluck('name', 'id');
            });
        } else {
            $professional_roles = ProfessionalRole::query()->pluck('name', 'id');
            // Get licenses for Entities based on LicenseType
            if ($license_type_entity) {
                $entity_licenses = Cache::remember('entityLicenses_for_federation_'.$federation_id, 120, function () use ($license_type_entity) {
                    return License::with('type')
                        ->where('type_id', $license_type_entity->id)
                        ->pluck('name', 'id');
                });
            }
            // Get licenses for Individuals based on LicenseType
            if ($license_type_individual) {
                $individual_licenses = Cache::remember('individualLicenses_for_committee_' .$federation_id, 120, function () use ($license_type_individual) {
                    return License::with('type')
                        ->where('type_id', $license_type_individual->id)
                        ->pluck('name', 'id');
                });
            }
            $certifications = Certification::get()->pluck('name', 'id');
        }

        return view('web.federation.attachments_sent.create', compact(
            'committee',
            'countries',
            'categories',
            'professional_roles',
            'licenses',
            'certifications',
            'entity_licenses',
            'individual_licenses',
            'federation_id',
        ));
    }

    public function destroy(Attachment $attachments_sent)
    {
        // Delete the attachment
        $attachments_sent->delete();

        return redirect()->back()->with('success', 'Attachment deleted successfully.');
    }
}
