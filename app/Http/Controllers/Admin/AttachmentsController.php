<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Models\Country;
use App\Models\Language;
use Domain\Attachments\Models\Attachment;
use Domain\Attachments\Models\AttachmentCategory;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AttachmentsController extends Controller
{
    public function index(?Committee $committee = null): View
    {

        $queryBuilder = QueryBuilder::for(Attachment::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_name'),
                AllowedFilter::scope('filter_category'),
                AllowedFilter::scope('filter_language'),
                AllowedFilter::scope('filter_date_start'),
                AllowedFilter::scope('filter_date_end'),
            ])
            ->with(['category', 'language', 'licenses', 'certifications', 'professionalRoles', 'media', 'owner', 'recipient'])
            ->whereHas('media')
            ->orderBy('name');

        if ($committee) {
            $queryBuilder->where('committee_id', $committee->id);
        } else {
            $queryBuilder->whereNull('committee_id');
        }

        $attachments = $queryBuilder->paginate()->appends(request()->query());

        $categories = AttachmentCategory::all();
        $languages = Language::orderBy('name')->get();

        return view('web.admin.attachments.index', compact('attachments', 'committee', 'categories', 'languages'));
    }

    public function create(?Committee $committee = null)
    {

        $countries = Country::query()->pluck('name', 'id');
        $languages = Language::query()->pluck('name', 'id');
        $categories = AttachmentCategory::query()->pluck('name', 'id');

        $licenses = $certifications = $entity_licenses = $individual_licenses = collect();
        $federations = Federation::query()->pluck('name', 'id');

        if ($committee) {
            $professional_roles = ProfessionalRole::where('committee_id', $committee->id)->pluck('name', 'id');
            $licenses = $committee->licenses()->pluck('name', 'id');
            $certifications = $committee->certifications()->pluck('name', 'id');

            $cacheDuration = 60;

            // Get licenses for Entities based on LicenseType
            $entity_licenses = Cache::remember('entityLicenses_for_committee_' . $committee->id, $cacheDuration, function () use ($committee) {
                return $committee->licenses()
                    ->with('type')
                    ->whereHas('type', function ($query) {
                        $query->where('is_individual', false)
                            ->orWhereNull('is_individual');
                    })
                    ->pluck('name', 'id');
            });

            // Get licenses for Individuals based on LicenseType
            $individual_licenses = Cache::remember('individualLicenses_for_committee_' . $committee->id, $cacheDuration, function () use ($committee) {
                return $committee->licenses()->whereHas('type', function ($query) {
                    $query->where('is_individual', true);
                })
                    ->pluck('name', 'id');
            });
        } else {
            $professional_roles = ProfessionalRole::query()->pluck('name', 'id');
        }

        return view('web.admin.attachments.create', compact(
            'committee',
            'countries',
            'categories',
            'professional_roles',
            'licenses',
            'federations',
            'certifications',
            'entity_licenses',
            'individual_licenses',
            'languages'
        ));
    }

    public function edit(Attachment $attachment): View
    {
        $categories = AttachmentCategory::query()->pluck('name', 'id');
        $languages = Language::orderBy('name')->pluck('name', 'id');

        return view('web.admin.attachments.edit', compact('attachment', 'categories', 'languages'));
    }

    public function update(Request $request, Attachment $attachment): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:attachment_categories,id',
            'language_id' => 'nullable|exists:languages,id',
        ]);

        $attachment->update($validated);

        return redirect()->route('admin.attachments.index')
            ->with('success', __('attachments.updated_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function destroy($id)
    {
        // Find the attachment by its ID
        $attachment = Attachment::findOrFail($id);

        // Delete the attachment
        $attachment->delete();

        return redirect()->route('admin.attachments.index')
            ->with('success', 'Attachment deleted successfully.');
    }
}
