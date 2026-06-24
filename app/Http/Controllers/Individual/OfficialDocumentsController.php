<?php

namespace App\Http\Controllers\Individual;

use App\Enums\OfficialDocumentTypeEnum;
use App\Http\Controllers\Controller;
use Domain\Federations\Models\Federation;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class OfficialDocumentsController extends Controller
{
    /**
     * list of official documents and create form
     */
    public function index(string $role): View
    {
        $roleMap = [
            'instructor-leader' => [
                'title' => __('official_documents.roles.instructor_leader'),
                'types' => [
                    OfficialDocumentTypeEnum::DivingProfessionalCodeOfConduct,
                    OfficialDocumentTypeEnum::MedicalStatement,
                    OfficialDocumentTypeEnum::MedicalFirstAidCprOxygenProvider,
                    OfficialDocumentTypeEnum::ProfessionalLiabilityInsurance,
                ],
                'committee' => ['DIVING', 'SCIENTIFIC'],
            ],
            'diving-professional' => [
                'title' => __('official_documents.roles.diving_professional'),
                'types' => [
                    OfficialDocumentTypeEnum::DivingProfessionalMedicalStatement,
                    OfficialDocumentTypeEnum::DivingProfessionalInsurance,
                    OfficialDocumentTypeEnum::OtherDocument,
                ],
                'committee' => ['DIVING'],
            ],
            'diver' => [
                'title' => __('official_documents.roles.diver'),
                'types' => [
                    OfficialDocumentTypeEnum::MedicalStatement,
                ],
                'committee' => ['DIVING'],
            ],
            'athlete' => [
                'title' => __('official_documents.roles.athlete'),
                'types' => [
                    OfficialDocumentTypeEnum::InternationalAthleteCodeOfConduct,
                    OfficialDocumentTypeEnum::MedicalStatement,
                    OfficialDocumentTypeEnum::InsuranceAthlete,
                    OfficialDocumentTypeEnum::ADELCertificate,
                ],
                'committee' => ['SPORT'],
            ],
            'coach' => [
                'title' => __('official_documents.roles.coach'),
                'types' => [
                    OfficialDocumentTypeEnum::InternationalCoachCodeOfConduct,
                    OfficialDocumentTypeEnum::MedicalStatement,
                    OfficialDocumentTypeEnum::ADELCertificate,
                    OfficialDocumentTypeEnum::ProfessionalLiabilityInsurance,
                    OfficialDocumentTypeEnum::TptdGrauI,
                    OfficialDocumentTypeEnum::TptdGrauII,
                    OfficialDocumentTypeEnum::TptdGrauIII,
                    OfficialDocumentTypeEnum::TptdGrauIV,
                ],
                'committee' => ['SPORT'],
            ],
            'referee-judge' => [
                'title' => __('official_documents.roles.referee_judge'),
                'types' => [
                    OfficialDocumentTypeEnum::InternationalRefereeJudgeCodeOfConduct,
                    OfficialDocumentTypeEnum::MedicalStatement,
                    OfficialDocumentTypeEnum::ADELCertificate,
                    OfficialDocumentTypeEnum::ProfessionalLiabilityInsurance,
                ],
                'committee' => ['SPORT'],
            ],
        ];

        if (! array_key_exists($role, $roleMap)) {
            abort(404);
        }
        $mappedRole = $roleMap[$role];

        // get individual id from user session
        $individual = auth()->user()->individuals()->first();

        $federations = Federation::whereHas('individuals', function ($query) use ($individual) {
            $query->where('individual_id', $individual->id);
        })->get();

        $official_documents = OfficialDocument::whereIn('type', $mappedRole['types'])
            ->with('media', 'country', 'federation')
            ->where('individual_id', $individual->id)
            ->latest()
            ->get();

        $files = $official_documents->map(function ($official_document) {
            return $official_document->getMedia('media');
        });

        return view('web.individual.official_documents.index', [
            'individual' => $individual,
            'official_documents' => $official_documents,
            'official_document_types' => OfficialDocumentTypeEnum::sortedByTranslation($mappedRole['types']),
            'files' => $files,
            'role' => $role,
            'title' => $mappedRole['title'],
            'federations' => $federations,
        ]);
    }

    public function preview(string $id): Response
    {
        $officialDocument = OfficialDocument::with('media')->findOrFail($id);
        $user = Auth::user();

        if ($officialDocument->individual_id != $user->individuals()->first()->id) {
            abort(403, __('official_documents.unauthorized_access'));
        }

        $mediaItem = $officialDocument->media()->first();

        if (! $mediaItem instanceof Media) {
            abort(404, __('official_documents.file_not_found'));
        }

        $disk = Storage::disk($mediaItem->disk);
        $path = $mediaItem->getPathRelativeToRoot();

        if (! $disk->exists($path)) {
            abort(404, __('official_documents.file_not_found'));
        }

        return response($disk->get($path), 200, [
            'Content-Type' => $mediaItem->mime_type,
            'Content-Disposition' => 'inline; filename="' . $mediaItem->file_name . '"',
        ]);
    }

    public function download(string $id)
    {
        $officialDocument = OfficialDocument::with('media', 'individual')->findOrFail($id);
        $user = Auth::user();

        // Check if the user is the owner of the document or has the international role or belongs to the federation
        if ($officialDocument->individual_id == $user->individuals()->first()->id) {
            $mediaItem = $officialDocument->media()->first();

            if (! $mediaItem instanceof Media) {
                return back()->with('error', 'File not found');
            }

            $disk = Storage::disk($mediaItem->disk);
            $path = $mediaItem->getPathRelativeToRoot();

            if (! $disk->exists($path)) {
                return back()->with('error', 'File not found');
            }

            return response()->streamDownload(function () use ($disk, $path) {
                echo $disk->get($path);
            }, $mediaItem->file_name, [
                'Content-Type' => $mediaItem->mime_type,
            ]);
        }

        abort(403, 'Unauthorized access to the file.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $officialDocument = OfficialDocument::with('media')->findOrFail($id);
        $user = Auth::user();

        if ($officialDocument->individual_id != $user->individuals()->first()->id) {
            return back()->with('error', 'Unauthorized to delete this document.');
        }

        try {
            DB::beginTransaction();
            $officialDocument->media->each->delete();
            $officialDocument->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Official document deleted successfully');
    }

}
