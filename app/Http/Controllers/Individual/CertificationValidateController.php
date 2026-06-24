<?php

namespace App\Http\Controllers\Individual;

use App\Exports\InstructorCertificationsExport;
use App\Http\Controllers\Controller;
use App\Models\GeoZone;
use App\Models\Sport;
use Barryvdh\DomPDF\Facade\Pdf;
use Domain\Certifications\Actions\ApproveCertificationByDirectorAction;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Certifications\States\RejectedCertificationAttributedState;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CertificationValidateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $individualId = auth()->user()->individuals()->first()->id;

        $certifications_validate = QueryBuilder::for(CertificationAttributed::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_emission_end', 'emission_after'),
                AllowedFilter::scope('filter_emission_start', 'emission_before'),
                AllowedFilter::scope('filter_expiration_start', 'expiration_before'),
                AllowedFilter::scope('filter_certification', 'certification_id'),
                AllowedFilter::scope('filter_entity', 'entity_name'),
                AllowedFilter::scope('filter_individual', 'individual_name'),
                AllowedFilter::scope('filter_status', 'certification_attributed_status'),
                AllowedFilter::scope('filter_committee'),
            ])
            ->with('mainInstructor', 'federation.country')
            ->whereHas('mainInstructor', function (Builder $query) use ($individualId) {
                return $query->where('individual.id', $individualId);
            })
            ->when((! empty(request()->filter) && request()->filter['filter_status'] != 'active') || empty(request()->filter), function (Builder $query) {
                $query->whereNot('status_class', ActiveCertificationAttributedState::class);
            })
            ->paginate()
            ->appends(request()->query());

        $filter_status = [
            'active' => ['id' => 'active', 'name' => __('Active')],
            'suspended' => ['id' => 'suspended', 'name' => __('Suspended')],
        ];

        $certifications = Certification::select('id', 'name')
            ->whereIn('id',
                CertificationAttributed::whereHas('mainInstructor', fn (Builder $q) => $q->where('individual.id', $individualId))
                    ->distinct()
                    ->pluck('certification_id')
            )
            ->when(
                Request()->query('filter')['committee'] ?? null,
                fn (Builder $q, string $committee) => $q->whereHas('committee', fn (Builder $q) => $q->where('code', $committee))
            )
            ->orderBy('name')
            ->get();
        $sports = Sport::select('id', 'name')->orderBy('name')->get();
        $cmas_zones = GeoZone::select('id', 'name')->orderBy('name')->get();
        $filter_comittee = [
            'diving' => __('Mergulho'),
            'scientific' => __('Científico'),
        ];

        $allCertifications = CertificationAttributed::whereHas('allInstructors', fn (Builder $q) => $q->where('individual.id', $individualId));
        $totalCertifications = $allCertifications->count();
        $totalLastFiveYears = (clone $allCertifications)->whereDate('activated_at', '>=', now()->subYears(5))->count();
        $totalAsDirector = CertificationAttributed::whereHas('mainInstructor', fn (Builder $q) => $q->where('individual.id', $individualId))->count();
        $totalAsAssistant = CertificationAttributed::whereHas('assistantInstructors', fn (Builder $q) => $q->where('individual.id', $individualId))->count();

        return view('web.individual.certification_validate.index', compact(
            'certifications_validate',
            'certifications',
            'filter_status',
            'sports',
            'cmas_zones',
            'filter_comittee',
            'totalCertifications',
            'totalLastFiveYears',
            'totalAsDirector',
            'totalAsAssistant'
        ));
    }

    public function exportExcel(): BinaryFileResponse
    {
        $individualId = auth()->user()->individuals()->first()->id;
        $filters = request()->input('filter', []);

        return (new InstructorCertificationsExport($individualId, $filters))
            ->download('certifications_instructor.xlsx');
    }

    public function exportPdf(): \Illuminate\Http\Response
    {
        $individual = auth()->user()->individuals()->first();
        $individualId = $individual->id;
        $filters = request()->input('filter', []);

        $query = CertificationAttributed::query()
            ->select('certification_attributed.*')
            ->join('certifications_attributed_instructors', 'certification_attributed.id', '=', 'certifications_attributed_instructors.attributed_id')
            ->where('certifications_attributed_instructors.individual_id', $individualId)
            ->addSelect('certifications_attributed_instructors.is_main');

        $this->applyExportFilters($query, $filters);

        $certifications = $query->orderByDesc('activated_at')->get();

        $photoBase64 = null;
        if ($individual->hasProfileImage()) {
            $media = $individual->getFirstMedia('profile');
            $disk = Storage::disk($media->disk);
            $path = $media->getPathRelativeToRoot();
            if ($disk->exists($path)) {
                $mimeType = $media->mime_type ?? 'image/jpeg';
                $photoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($disk->get($path));
            }
        }

        $pdf = Pdf::loadView('pdf.individual.certification-validate-report', [
            'certifications' => $certifications,
            'individual' => $individual,
            'photoBase64' => $photoBase64,
            'instructorName' => $individual->name . ' ' . $individual->surname,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('certifications_instructor.pdf');
    }

    protected function applyExportFilters(Builder $query, array $filters): void
    {
        if (empty($filters)) {
            return;
        }

        if (! empty($filters['filter_status'])) {
            $query->certificationAttributedStatus($filters['filter_status']);
        } else {
            $query->whereNot('status_class', ActiveCertificationAttributedState::class);
        }

        if (! empty($filters['filter_certification'])) {
            $query->certificationId($filters['filter_certification']);
        }

        if (! empty($filters['filter_entity'])) {
            $query->entityName($filters['filter_entity']);
        }

        if (! empty($filters['filter_individual'])) {
            $query->individualName($filters['filter_individual']);
        }

        if (! empty($filters['filter_emission_start'])) {
            $query->emissionBefore($filters['filter_emission_start']);
        }

        if (! empty($filters['filter_emission_end'])) {
            $query->emissionAfter($filters['filter_emission_end']);
        }

        if (! empty($filters['filter_committee'])) {
            $query->filterCommittee($filters['filter_committee']);
        }
    }

    /**
     * Display the specified resource.
     */
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
                ->whereHas('mainInstructor', function (Builder $query) {
                    return $query->where('individual.id', auth()->id());
                })
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
     * Activate the Certification based on permission rules
     * Permissions: international, FEDERATION, INDIVIDUAL
     */
    public function activate(
        CertificationAttributed $certificationAttributed,
        ApproveCertificationByDirectorAction $approveCertification
    ): RedirectResponse {
        try {
            DB::beginTransaction();

            // verify current state? Is it already active?
            if (! $certificationAttributed->isActive()) {

                $certificationApproved = $approveCertification($certificationAttributed);

                DB::commit();

                // Clear cache so the updated state is shown
                Cache::forget("certification_attributed_{$certificationAttributed->id}");

                return back()->with('success', 'Certification approved with success.');
            }

            DB::rollBack();

            return back()->with('error', 'Federation doesn\'t have enough certifications to validate.');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getCode() . ': ' . $exception->getMessage());

            return back()->with('error', 'Error approving certification');
        }
    }

    public function reject(
        CertificationAttributed $certificationAttributed
    ): RedirectResponse {
        try {
            DB::beginTransaction();

            // Be sure SCOPE is valid. Only to this Federation.
            // Using route model binding, the certificationAttributed is already loaded.
            // We just need to verify the instructor scope.
            $scopedCertificationAttributed = CertificationAttributed::where('id', $certificationAttributed->id)
                ->whereHas('mainInstructor', function (Builder $query) {
                    return $query->where('individual.id', auth()->id());
                })
                ->sharedLock()
                ->first();

            if (empty($scopedCertificationAttributed)) {
                DB::rollBack();

                return back()->with('error', 'Certification not found or you don\'t have permission to reject it.');
            }

            $scopedCertificationAttributed->status_class = RejectedCertificationAttributedState::class;
            $scopedCertificationAttributed->save();

            DB::commit();

            // Clear cache so the updated state is shown
            Cache::forget("certification_attributed_{$certificationAttributed->id}");

            return back()->with('success', 'Certification request rejected successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getCode() . ': ' . $exception->getMessage());

            return back()->with('error', 'Error rejecting certification');
        }
    }
}
