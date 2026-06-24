<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventApplications\StoreApplicationRequest;
use App\Http\Requests\EventApplications\SubmitApplicationRequest;
use App\Http\Requests\EventApplications\UpdateApplicationRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Domain\EventApplications\Actions\CheckDuplicateApplicationAction;
use Domain\EventApplications\Actions\CreateApplicationAction;
use Domain\EventApplications\Actions\SubmitApplicationAction;
use Domain\EventApplications\Models\ApplicationTemplate;
use Domain\EventApplications\Models\EventApplication;
use Domain\EvtEvents\Models\Sport;
use Domain\Geographic\Models\District;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EntityApplicationController extends Controller
{
    public function index(): View
    {
        $entity = Auth::user()->getEntity();

        if (! $entity) {
            abort(403, 'User is not associated with an entity.');
        }

        $applications = QueryBuilder::for(EventApplication::class)
            ->allowedFilters([
                AllowedFilter::exact('status_class'),
                AllowedFilter::exact('application_type'),
                'event_name',
            ])
            ->where('entity_id', $entity->id)
            ->where('entity_type', $entity->getMorphClass())
            ->with(['sport', 'template', 'district'])
            ->orderByDesc('created_at')
            ->paginate()
            ->appends(request()->query());

        $states = [
            'Domain\EventApplications\States\DraftApplicationState' => __('event_applications.states.draft'),
            'Domain\EventApplications\States\SubmittedApplicationState' => __('event_applications.states.submitted'),
            'Domain\EventApplications\States\InValidationApplicationState' => __('event_applications.states.in_validation'),
            'Domain\EventApplications\States\ReturnedForCorrectionApplicationState' => __('event_applications.states.returned_for_correction'),
            'Domain\EventApplications\States\ApprovedApplicationState' => __('event_applications.states.approved'),
            'Domain\EventApplications\States\RejectedApplicationState' => __('event_applications.states.rejected'),
            'Domain\EventApplications\States\PublishedApplicationState' => __('event_applications.states.published'),
        ];

        $eventTypes = [
            'organization' => __('event_applications.event_types.organization'),
            'competition' => __('event_applications.event_types.competition'),
        ];

        $templates = ApplicationTemplate::query()
            ->whereIn('state', ['open', 'closed'])
            ->whereIn('target_audience', ['entities', 'both'])
            ->with(['sport', 'createdBy', 'documents'])
            ->orderBy('event_start_date')
            ->get();

        $templates->each(function (ApplicationTemplate $template) use ($entity) {
            $template->hasEntityApplied = $template->hasApplied($entity->id);
            if ($template->hasEntityApplied) {
                $template->existingApplication = $template->getEntityApplication($entity->id);
            }
        });

        $sports = Sport::select('id', 'name')->orderBy('name')->get();

        return view('web.entity.event-applications.index', compact(
            'applications',
            'entity',
            'states',
            'eventTypes',
            'templates',
            'sports'
        ));
    }

    public function createFromTemplate(
        ApplicationTemplate $template,
        CheckDuplicateApplicationAction $checkDuplicate
    ): View|RedirectResponse {
        $entity = Auth::user()->getEntity();

        if (! $entity) {
            abort(403, 'User is not associated with an entity.');
        }

        if (! $template->isOpen()) {
            return redirect()
                ->route('entity.event-applications.available-templates')
                ->with('error', __('event_applications.template_not_open'));
        }

        // Check if already applied
        $isDuplicate = $checkDuplicate->execute($entity->id, $template->id);

        if ($isDuplicate) {
            $existingApplication = $template->getEntityApplication($entity->id);

            return redirect()
                ->route('entity.event-applications.show', $existingApplication)
                ->with('info', __('event_applications.already_applied_to_template'));
        }

        // Load template with relationships
        $template->load(['sport', 'createdBy', 'documents']);

        return view('web.entity.event-applications.template-detail', compact(
            'template',
            'entity'
        ));
    }

    public function createDirect(): View
    {
        $entity = Auth::user()->getEntity();

        if (! $entity) {
            abort(403, 'User is not associated with an entity.');
        }

        $sports = Sport::select('id', 'name')->orderBy('name')->get();
        $districts = District::select('id', 'name')->orderBy('name')->get();
        $eventTypes = config('enum.event_type');

        return view('web.entity.event-applications.create-direct', compact(
            'entity',
            'sports',
            'districts',
            'eventTypes'
        ));
    }

    public function store(StoreApplicationRequest $request, CreateApplicationAction $action): RedirectResponse
    {
        try {
            $entity = Auth::user()->getEntity();

            if (! $entity) {
                abort(403, 'User is not associated with an entity.');
            }

            $data = array_merge($request->validated(), [
                'entity_id' => $entity->id,
                'entity_type' => $entity->getMorphClass(),
            ]);

            $application = $action->execute($data);

            return redirect()
                ->route('entity.event-applications.edit', $application->id)
                ->with('success', __('event_applications.application_created_success'));
        } catch (Exception $ex) {
            Log::error('Error creating application: ' . $ex->getMessage(), [
                'exception' => $ex,
                'data' => $request->validated(),
            ]);

            return back()
                ->withInput()
                ->with('error', __('event_applications.application_created_error'));
        }
    }

    public function edit(EventApplication $application): View|RedirectResponse
    {
        $entity = Auth::user()->getEntity();

        if (! $entity || $application->entity_id !== $entity->id) {
            abort(403, 'Unauthorized access.');
        }

        if (! in_array($application->status_class, [
            'Domain\EventApplications\States\DraftApplicationState',
            'Domain\EventApplications\States\ReturnedForCorrectionApplicationState',
        ])) {
            return back()->with('error', __('event_applications.cannot_edit_application'));
        }

        if ($application->template?->isArchived()) {
            return back()->with('error', __('event_applications.template_archived_cannot_edit'));
        }

        $sports = Sport::select('id', 'name')->orderBy('name')->get();
        $districts = District::select('id', 'name')->orderBy('name')->get();
        $eventTypes = config('enum.event_type');

        return view('web.entity.event-applications.edit', compact(
            'application',
            'entity',
            'sports',
            'districts',
            'eventTypes'
        ));
    }

    public function update(
        UpdateApplicationRequest $request,
        EventApplication $application
    ): RedirectResponse {
        try {
            $entity = Auth::user()->getEntity();

            if (! $entity || $application->entity_id !== $entity->id) {
                abort(403, 'Unauthorized access.');
            }

            $application->update($request->validated());

            return redirect()
                ->route('entity.event-applications.show', $application->id)
                ->with('success', __('event_applications.application_updated_success'));
        } catch (Exception $ex) {
            Log::error('Error updating application: ' . $ex->getMessage(), [
                'exception' => $ex,
                'application_id' => $application->id,
                'data' => $request->validated(),
            ]);

            return back()
                ->withInput()
                ->with('error', __('event_applications.application_updated_error'));
        }
    }

    public function submit(
        EventApplication $application,
        SubmitApplicationRequest $request,
        SubmitApplicationAction $action
    ): RedirectResponse {
        try {
            $entity = Auth::user()->getEntity();

            if (! $entity || $application->entity_id !== $entity->id) {
                abort(403, 'Unauthorized access.');
            }

            $action->execute($application, auth()->id());

            return redirect()
                ->route('entity.event-applications.show', $application->id)
                ->with('success', __('event_applications.application_submitted_success'));
        } catch (Exception $ex) {
            Log::error('Error submitting application: ' . $ex->getMessage(), [
                'exception' => $ex,
                'application_id' => $application->id,
            ]);

            return back()->with('error', __('event_applications.application_submitted_error'));
        }
    }

    public function show(EventApplication $application): View
    {
        $entity = Auth::user()->getEntity();

        if (! $entity || $application->entity_id !== $entity->id) {
            abort(403, 'Unauthorized access.');
        }

        $application->load(['sport', 'template', 'district', 'documents', 'comments.user']);

        return view('web.entity.event-applications.show', compact('application', 'entity'));
    }

    public function destroy(EventApplication $application): RedirectResponse
    {
        try {
            $entity = Auth::user()->getEntity();

            if (! $entity || $application->entity_id !== $entity->id) {
                abort(403, 'Unauthorized access.');
            }

            if ($application->status_class !== 'Domain\EventApplications\States\DraftApplicationState') {
                return back()->with('error', __('event_applications.cannot_delete_application'));
            }

            $application->delete();

            return redirect()
                ->route('entity.event-applications.index')
                ->with('success', __('event_applications.application_deleted_success'));
        } catch (Exception $ex) {
            Log::error('Error deleting application: ' . $ex->getMessage(), [
                'exception' => $ex,
                'application_id' => $application->id,
            ]);

            return back()->with('error', __('event_applications.application_deleted_error'));
        }
    }

    public function exportPdf(EventApplication $application): Response|RedirectResponse
    {
        try {
            $entity = Auth::user()->getEntity();

            if (! $entity || $application->entity_id !== $entity->id) {
                abort(403, 'Unauthorized access.');
            }

            $application->load(['sport', 'template', 'district', 'documents']);

            $pdf = Pdf::loadView('pdf.event-applications.application-summary', [
                'application' => $application,
                'entity' => $entity,
                'generatedAt' => now(),
            ])->setPaper('a4', 'portrait');

            $filename = 'candidatura_' . $application->id . '_' . now()->format('Y-m-d_His') . '.pdf';

            return $pdf->download($filename);
        } catch (Exception $e) {
            Log::error('Error exporting application PDF: ' . $e->getMessage(), [
                'exception' => $e,
                'application_id' => $application->id,
            ]);

            return redirect()->back()->with('error', __('event_applications.messages.application_not_found'));
        }
    }

    public function availableTemplates(): View
    {
        $entity = Auth::user()->getEntity();

        if (! $entity) {
            abort(403, 'User is not associated with an entity.');
        }

        $templates = QueryBuilder::for(ApplicationTemplate::class)
            ->allowedFilters([
                AllowedFilter::exact('event_type'),
                AllowedFilter::exact('sport_id'),
            ])
            ->whereIn('state', ['open', 'closed'])
            ->whereIn('target_audience', ['entities', 'both'])
            ->with(['sport', 'createdBy', 'documents'])
            ->orderBy('event_start_date')
            ->get();

        // Check if entity has applied to each template
        $templates->each(function (ApplicationTemplate $template) use ($entity) {
            $template->hasEntityApplied = $template->hasApplied($entity->id);
            if ($template->hasEntityApplied) {
                $template->existingApplication = $template->getEntityApplication($entity->id);
            }
        });

        $sports = Sport::select('id', 'name')->orderBy('name')->get();

        return view('web.entity.event-applications.available-templates', compact('templates', 'entity', 'sports'));
    }
}
