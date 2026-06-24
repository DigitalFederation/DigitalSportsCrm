<?php

namespace App\Http\Controllers\Admin\EvtEvents\Enrollments;

use App\Exports\IndividualEnrollmentsExport;
use App\Http\Controllers\Common\BaseEnrollmentController;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\IndividualEnrollment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class IndividualEnrollmentController extends BaseEnrollmentController
{
    public function index(Event $event): RedirectResponse|View
    {

        $enrollments = $event->individualEnrollments()
            ->with('individual', 'enrollment.event', 'attributes.attribute', 'federation:id,name,member_code')
            ->select('evt_individuals_enrollment.*')
            ->selectSub(function ($query) {
                // Check if at least one document exists in DocumentDetail
                $query->from('document_detail')
                    ->join('document', 'document.id', '=', 'document_detail.document_id')
                    ->select(\Illuminate\Support\Facades\DB::raw('1'))
                    ->whereColumn('document_detail.owner_id', 'evt_individuals_enrollment.enrollment_id')
                    ->where('document_detail.owner_type', '=', 'Domain\\EvtEvents\\Models\\Enrollment')
                    ->whereNull('document.deleted_at')
                    ->limit(1);
            }, 'hasDocument')
            ->selectSub(function ($query) {
                // Subquery to fetch the latest document ID through DocumentDetail
                $query->from('document_detail')
                    ->join('document', 'document.id', '=', 'document_detail.document_id')
                    ->select('document.id')
                    ->whereColumn('document_detail.owner_id', 'evt_individuals_enrollment.enrollment_id')
                    ->where('document_detail.owner_type', '=', 'Domain\\EvtEvents\\Models\\Enrollment')
                    ->orderByDesc('document.created_at')
                    ->limit(1);
            }, 'document_id')
            ->paginate(500);

        // When we have enrollments, load document details
        if ($enrollments->isNotEmpty()) {
            // Get all document IDs from the results
            $documentIds = $enrollments->pluck('document_id')->filter()->unique()->values();

            // If we have document IDs, eager load the documents
            if ($documentIds->isNotEmpty()) {
                $documents = \Domain\Documents\Models\Document::whereIn('id', $documentIds)
                    ->get()
                    ->keyBy('id');

                // Attach the documents to the enrollments
                foreach ($enrollments as $enrollment) {
                    if ($enrollment->document_id) {
                        $enrollment->document = $documents->get($enrollment->document_id);
                    }
                }
            }
        }

        $uniqueAttributes = collect();
        if ($enrollments->isNotEmpty()) {
            foreach ($enrollments as $enrollment) {
                foreach ($enrollment->attributes as $attribute) {
                    $uniqueAttributes->put($attribute->attribute_id, $attribute->attribute->name);
                }
            }
            $uniqueAttributes = $uniqueAttributes->sort()->values();
        }

        // Get status options for the dropdown
        $statusOptions = [
            \App\Enums\EvtIndividualEnrollmentStatusEnum::PENDING->value => __('Pending Payment'),
            \App\Enums\EvtIndividualEnrollmentStatusEnum::REGISTERED->value => __('Registered'),
            \App\Enums\EvtIndividualEnrollmentStatusEnum::PAID->value => __('Paid'),
            \App\Enums\EvtIndividualEnrollmentStatusEnum::COMPLETED->value => __('Completed'),
        ];

        return view('web.admin.evt_events.individual_enrollment.index', compact('event', 'enrollments', 'uniqueAttributes', 'statusOptions'));
    }

    public function export(Request $request, Event $event)
    {
        $enrollments = $event->individualEnrollments()
            ->with('individual', 'enrollment.event', 'attributes.attribute', 'federation:id,name,member_code')
            ->get();

        $uniqueAttributes = collect();
        if ($enrollments->isNotEmpty()) {
            foreach ($enrollments as $enrollment) {
                foreach ($enrollment->attributes as $attribute) {
                    $uniqueAttributes->put($attribute->attribute_id, $attribute->attribute->name);
                }
            }
            $uniqueAttributes = $uniqueAttributes->sort()->values();
        }

        return $this->processExport($request, IndividualEnrollmentsExport::class, $event, 'member', $uniqueAttributes);
    }

    /**
     * Remove an individual enrollment from the event.
     */
    public function destroy(
        Event $event,
        IndividualEnrollment $individualEnrollment
    ): RedirectResponse {
        // Ensure only admin users can delete enrollments
        if (Auth::user()->group->code !== 'ADMIN') {
            abort(403, 'Unauthorized action.');
        }

        if ((int) $individualEnrollment->event_id !== (int) $event->id) {
            abort(404);
        }

        try {
            DB::beginTransaction();

            $individualEnrollment->loadMissing(['individual', 'federation', 'attributes.attribute']);

            // Capture important details before deletion for logging
            $enrollmentDetails = [
                'id' => $individualEnrollment->id,
                'event_id' => $event->id,
                'event_name' => $event->name,
                'individual_id' => $individualEnrollment->individual_id,
                'individual_name' => $individualEnrollment->individual->full_name ?? 'Unknown',
                'individual_member_code' => $individualEnrollment->individual->member_code ?? 'N/A',
                'federation_id' => $individualEnrollment->federation_id,
                'federation_name' => $individualEnrollment->federation->name ?? 'N/A',
                'entity_id' => $individualEnrollment->entity_id,
                'status_class' => $individualEnrollment->status_class,
                'enrollment_id' => $individualEnrollment->enrollment_id,
                'attribute_count' => $individualEnrollment->attributes->count(),
                'price' => $individualEnrollment->price,
                'created_at' => $individualEnrollment->created_at,
            ];

            // Log the attributes separately to provide detailed information
            $attributeDetails = $individualEnrollment->attributes->map(function ($attr) {
                return [
                    'id' => $attr->id,
                    'attribute_name' => $attr->attribute->name ?? 'Unknown',
                    'value' => $attr->value,
                ];
            })->toArray();

            // Delete related attributes first
            $individualEnrollment->attributes()->delete();

            // Then delete the individual enrollment
            $individualEnrollment->delete();

            // Log the activity with detailed information
            activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'enrollment' => $enrollmentDetails,
                    'attributes' => $attributeDetails,
                    'deleted_by' => [
                        'id' => Auth::id(),
                        'name' => Auth::user()->name,
                        'email' => Auth::user()->email,
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->log('Individual enrollment deleted from event');

            DB::commit();

            return redirect()->route('admin.evt-events.events.enrollments.individual.index', $event)
                ->with('success', 'Individual enrollment deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting individual enrollment: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'enrollment_id' => $individualEnrollment->id,
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);

            return redirect()->route('admin.evt-events.events.enrollments.individual.index', $event)
                ->with('error', 'An error occurred while deleting the individual enrollment.');
        }
    }

    /**
     * Update the status of an individual enrollment.
     */
    public function updateStatus(
        Event $event,
        IndividualEnrollment $individualEnrollment,
        Request $request
    ): RedirectResponse {
        // Ensure only admin users can update enrollment status
        if (Auth::user()->group->code !== 'ADMIN') {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            // Validate the status
            $validStatusValues = [
                \App\Enums\EvtIndividualEnrollmentStatusEnum::PENDING->value,
                \App\Enums\EvtIndividualEnrollmentStatusEnum::REGISTERED->value,
                \App\Enums\EvtIndividualEnrollmentStatusEnum::PAID->value,
                \App\Enums\EvtIndividualEnrollmentStatusEnum::COMPLETED->value,
            ];

            $request->validate([
                'status' => ['required', 'string', Rule::in($validStatusValues)],
            ]);

            // Store old status for logging
            $oldStatus = $individualEnrollment->status_class;
            $newStatus = $request->input('status');

            // Update the status
            $individualEnrollment->status_class = $newStatus;
            $individualEnrollment->save();

            // Log the activity
            activity()
                ->causedBy(Auth::user())
                ->performedOn($individualEnrollment)
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'enrollment_id' => $individualEnrollment->id,
                    'event_id' => $event->id,
                    'individual_id' => $individualEnrollment->individual_id,
                    'updated_by' => [
                        'id' => Auth::id(),
                        'name' => Auth::user()->name,
                        'email' => Auth::user()->email,
                    ],
                ])
                ->log('Individual enrollment status updated');

            DB::commit();

            return redirect()->route('admin.evt-events.events.enrollments.individual.index', $event)
                ->with('success', 'Enrollment status updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating individual enrollment status: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'enrollment_id' => $individualEnrollment->id,
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);

            return redirect()->route('admin.evt-events.events.enrollments.individual.index', $event)
                ->with('error', 'An error occurred while updating the enrollment status.');
        }
    }
}
