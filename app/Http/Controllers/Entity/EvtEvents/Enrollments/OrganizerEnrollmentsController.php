<?php

namespace App\Http\Controllers\Entity\EvtEvents\Enrollments;

use App\Enums\EvtIndividualEnrollmentStatusEnum;
use App\Exports\IndividualEnrollmentsExport;
use App\Http\Controllers\Common\BaseEnrollmentController;
use App\Scopes\IndividualsFromFederationScope;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\IndividualEnrollment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class OrganizerEnrollmentsController extends BaseEnrollmentController
{
    public function index(Request $request, Event $event, string $enrollmentType): View
    {
        $entity = $request->user()->entities()->first();
        $this->authorizeOrganizer($event, $entity);

        try {
            $enrollments = IndividualEnrollment::with([
                'individual' => function ($query) {
                    $query->withoutGlobalScope(IndividualsFromFederationScope::class);
                },
                'enrollment.event',
                'enrollment.enrollable' => function ($query) {
                    $query->withTrashed();
                },
                'attributes.attribute',
            ])
                ->where('event_id', $event->id)
                ->whereIn('status_class', [
                    EvtIndividualEnrollmentStatusEnum::PAID->value,
                    EvtIndividualEnrollmentStatusEnum::COMPLETED->value,
                ])
                ->paginate(75);

            $uniqueAttributes = $enrollments->getCollection()->flatMap->attributes
                ->pluck('attribute.name', 'attribute.id')
                ->unique()
                ->sort()
                ->values();

            return view('web.entity.evt_event.organizer_enrollment.index', compact(
                'event',
                'enrollments',
                'enrollmentType',
                'uniqueAttributes'
            ));
        } catch (\Exception $e) {
            Log::error("Error displaying {$enrollmentType} enrollments: " . $e->getMessage(), [
                'event_id' => $event->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return view('web.entity.evt_event.organizer_enrollment.index', [
                'event' => $event,
                'enrollments' => collect(),
                'enrollmentType' => $enrollmentType,
                'uniqueAttributes' => collect(),
                'error' => __('events.error_retrieving_enrollments'),
            ]);
        }
    }

    public function export(Request $request, Event $event, string $enrollmentType)
    {
        try {
            $entity = $request->user()->entities()->first();
            $this->authorizeOrganizer($event, $entity);

            $exportInstance = new IndividualEnrollmentsExport($event, 'organizer');
            $fileName = $this->generateExportFileName($event, $enrollmentType);

            return Excel::download($exportInstance, $fileName);
        } catch (\Exception $e) {
            Log::error("Error exporting {$enrollmentType} enrollments: " . $e->getMessage(), [
                'event_id' => $event->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', __('events.error_generating_export'));
        }
    }

    private function authorizeOrganizer(Event $event, ?Entity $entity): void
    {
        if (! $entity) {
            abort(403, __('events.entity_not_organizer'));
        }

        $isOrganizer = $event->organizer()
            ->where('organizable_id', $entity->id)
            ->where('organizable_type', Entity::class)
            ->exists();

        if (! $isOrganizer) {
            abort(403, __('events.entity_not_organizer'));
        }
    }
}
