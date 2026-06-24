<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Concerns\ManagesEventApplications;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventApplications\StoreApplicationRequest;
use App\Http\Requests\EventApplications\SubmitApplicationRequest;
use App\Http\Requests\EventApplications\UpdateApplicationRequest;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\EventApplications\Actions\CheckDuplicateApplicationAction;
use Domain\EventApplications\Actions\CreateApplicationAction;
use Domain\EventApplications\Actions\SubmitApplicationAction;
use Domain\EventApplications\Models\ApplicationTemplate;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\ApprovedApplicationState;
use Domain\EventApplications\States\DraftApplicationState;
use Domain\EventApplications\States\InValidationApplicationState;
use Domain\EventApplications\States\PublishedApplicationState;
use Domain\EventApplications\States\RejectedApplicationState;
use Domain\EventApplications\States\ReturnedForCorrectionApplicationState;
use Domain\EventApplications\States\SubmittedApplicationState;
use Domain\EvtEvents\Models\Sport;
use Domain\Geographic\Models\District;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class FederationApplicationController extends Controller
{
    use ManagesEventApplications;

    public function index(): View
    {
        $federation = auth()->user()->getFederation();

        if (! $federation) {
            abort(403, 'User is not associated with a federation.');
        }

        if (! $federation->is_default_federation) {
            return $this->territorialIndex($federation);
        }

        $applications = QueryBuilder::for(EventApplication::class)
            ->allowedFilters([
                AllowedFilter::callback('status', function ($query, $value): void {
                    $stateMap = [
                        'draft' => DraftApplicationState::class,
                        'submitted' => SubmittedApplicationState::class,
                        'in_validation' => InValidationApplicationState::class,
                        'returned_for_correction' => ReturnedForCorrectionApplicationState::class,
                        'approved' => ApprovedApplicationState::class,
                        'rejected' => RejectedApplicationState::class,
                        'published' => PublishedApplicationState::class,
                    ];
                    if (isset($stateMap[$value])) {
                        $query->where('status_class', $stateMap[$value]);
                    }
                }),
                AllowedFilter::exact('application_type'),
                AllowedFilter::exact('event_type'),
                AllowedFilter::exact('sport_id'),
                AllowedFilter::exact('template_id'),
                'event_name',
            ])
            ->with(['entity', 'sport', 'template', 'district'])
            ->orderByDesc('created_at')
            ->paginate()
            ->appends(request()->query());

        $templatesList = ApplicationTemplate::withCount('activeApplications')
            ->with('sport')
            ->orderByDesc('created_at')
            ->get();

        $templates = ApplicationTemplate::select('id', 'name')->orderBy('name')->get();
        $sports = Sport::select('id', 'name')->orderBy('name')->get();
        $statuses = config('enum.application_status');
        $routeNamespace = 'federation';
        $showTemplatesTab = true;
        $showCreateButton = false;

        return view('web.admin.event-applications.index', compact(
            'applications',
            'templatesList',
            'templates',
            'sports',
            'statuses',
            'routeNamespace',
            'showTemplatesTab',
            'showCreateButton'
        ));
    }

    private function territorialIndex(mixed $federation): View
    {
        $memberEntityIds = $federation->entities()
            ->wherePivot('status_class', ActiveEntityFederationState::class)
            ->pluck('entity.id');

        $applications = QueryBuilder::for(EventApplication::class)
            ->allowedFilters([
                AllowedFilter::callback('status', function ($query, $value): void {
                    $stateMap = [
                        'draft' => DraftApplicationState::class,
                        'submitted' => SubmittedApplicationState::class,
                        'in_validation' => InValidationApplicationState::class,
                        'returned_for_correction' => ReturnedForCorrectionApplicationState::class,
                        'approved' => ApprovedApplicationState::class,
                        'rejected' => RejectedApplicationState::class,
                        'published' => PublishedApplicationState::class,
                    ];
                    if (isset($stateMap[$value])) {
                        $query->where('status_class', $stateMap[$value]);
                    }
                }),
                AllowedFilter::exact('application_type'),
                'event_name',
            ])
            ->where(function ($q) use ($federation, $memberEntityIds) {
                $q->where(function ($sub) use ($memberEntityIds) {
                    $sub->where('entity_type', 'entity')
                        ->whereIn('entity_id', $memberEntityIds);
                })->orWhere(function ($sub) use ($federation) {
                    $sub->where('entity_type', 'federation')
                        ->where('entity_id', $federation->id);
                });
            })
            ->with(['entity', 'sport', 'template', 'district'])
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
            ->with(['sport', 'createdBy', 'documents'])
            ->orderBy('event_start_date')
            ->get();

        $templates->each(function ($template) use ($federation) {
            $template->hasEntityApplied = $template->hasApplied($federation->id);
            if ($template->hasEntityApplied) {
                $template->existingApplication = $template->getEntityApplication($federation->id);
            }
        });

        $sports = Sport::select('id', 'name')->orderBy('name')->get();
        $routeNamespace = 'federation';

        return view('web.entity.event-applications.index', compact(
            'applications',
            'states',
            'eventTypes',
            'templates',
            'sports',
            'routeNamespace'
        ));
    }

    public function show(EventApplication $application): View
    {
        $federation = auth()->user()->getFederation();

        if (! $federation) {
            abort(403);
        }

        if (! $federation->is_default_federation) {
            $isOwnApplication = $application->entity_type === 'federation'
                && $application->entity_id === $federation->id;

            $isMemberEntity = $application->entity_type === 'entity'
                && $federation->entities()
                    ->wherePivot('status_class', ActiveEntityFederationState::class)
                    ->where('entity.id', $application->entity_id)
                    ->exists();

            if (! $isOwnApplication && ! $isMemberEntity) {
                abort(403);
            }
        }

        $application->load([
            'entity',
            'sport',
            'template',
            'district',
            'documents',
            'comments.user',
            'stateHistory',
        ]);

        $routeNamespace = 'federation';
        $canManageState = $federation->is_default_federation;

        return view('web.admin.event-applications.show', compact('application', 'routeNamespace', 'canManageState'));
    }

    public function createFromTemplate(
        ApplicationTemplate $template,
        CheckDuplicateApplicationAction $checkDuplicate
    ): View|RedirectResponse {
        $federation = auth()->user()->getFederation();

        if (! $federation) {
            abort(403, 'User is not associated with a federation.');
        }

        if (! $template->isOpen()) {
            return redirect()
                ->route('federation.event-applications.available-templates')
                ->with('error', __('event_applications.template_not_open'));
        }

        $isDuplicate = $checkDuplicate->execute($federation->id, $template->id);

        if ($isDuplicate) {
            $existingApplication = $template->getEntityApplication($federation->id);

            return redirect()
                ->route('federation.event-applications.show', $existingApplication)
                ->with('info', __('event_applications.already_applied_to_template'));
        }

        $template->load(['sport', 'createdBy', 'documents']);

        $routeNamespace = 'federation';

        return view('web.entity.event-applications.template-detail', compact(
            'template',
            'federation',
            'routeNamespace'
        ));
    }

    public function createDirect(): View
    {
        $federation = auth()->user()->getFederation();

        if (! $federation) {
            abort(403, 'User is not associated with a federation.');
        }

        $sports = Sport::select('id', 'name')->orderBy('name')->get();
        $districts = District::select('id', 'name')->orderBy('name')->get();
        $eventTypes = config('enum.event_type');

        return view('web.federation.event-applications.create-direct', compact(
            'federation',
            'sports',
            'districts',
            'eventTypes'
        ));
    }

    public function store(StoreApplicationRequest $request, CreateApplicationAction $action): RedirectResponse
    {
        try {
            $federation = auth()->user()->getFederation();

            if (! $federation) {
                abort(403, 'User is not associated with a federation.');
            }

            $data = array_merge($request->validated(), [
                'entity_id' => $federation->id,
                'entity_type' => $federation->getMorphClass(),
            ]);

            $application = $action->execute($data);

            return redirect()
                ->route('federation.event-applications.edit', $application->id)
                ->with('success', __('event_applications.application_created_success'));
        } catch (Exception $ex) {
            Log::error('Error creating application: '.$ex->getMessage(), [
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
        $federation = auth()->user()->getFederation();

        if (! $federation || $application->entity_id !== $federation->id) {
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

        return view('web.federation.event-applications.edit', compact(
            'application',
            'federation',
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
            $federation = auth()->user()->getFederation();

            if (! $federation || $application->entity_id !== $federation->id) {
                abort(403, 'Unauthorized access.');
            }

            $application->update($request->validated());

            return redirect()
                ->route('federation.event-applications.show', $application->id)
                ->with('success', __('event_applications.application_updated_success'));
        } catch (Exception $ex) {
            Log::error('Error updating application: '.$ex->getMessage(), [
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
            $federation = auth()->user()->getFederation();

            if (! $federation || $application->entity_id !== $federation->id) {
                abort(403, 'Unauthorized access.');
            }

            $action->execute($application, auth()->id());

            return redirect()
                ->route('federation.event-applications.show', $application->id)
                ->with('success', __('event_applications.application_submitted_success'));
        } catch (Exception $ex) {
            Log::error('Error submitting application: '.$ex->getMessage(), [
                'exception' => $ex,
                'application_id' => $application->id,
            ]);

            return back()->with('error', __('event_applications.application_submitted_error'));
        }
    }

    public function destroy(EventApplication $application): RedirectResponse
    {
        try {
            $federation = auth()->user()->getFederation();

            if (! $federation || $application->entity_id !== $federation->id) {
                abort(403, 'Unauthorized access.');
            }

            if ($application->status_class !== 'Domain\EventApplications\States\DraftApplicationState') {
                return back()->with('error', __('event_applications.cannot_delete_application'));
            }

            $application->delete();

            return redirect()
                ->route('federation.event-applications.index')
                ->with('success', __('event_applications.application_deleted_success'));
        } catch (Exception $ex) {
            Log::error('Error deleting application: '.$ex->getMessage(), [
                'exception' => $ex,
                'application_id' => $application->id,
            ]);

            return back()->with('error', __('event_applications.application_deleted_error'));
        }
    }

    public function availableTemplates(): View
    {
        $federation = auth()->user()->getFederation();

        if (! $federation) {
            abort(403, 'User is not associated with a federation.');
        }

        $query = ApplicationTemplate::query()
            ->whereIn('state', ['open', 'closed']);

        if ($federation->is_default_federation) {
            $query->whereIn('target_audience', ['federations', 'both']);
        }

        $templates = $query
            ->with(['sport', 'createdBy', 'documents'])
            ->orderBy('submission_end_date')
            ->get();

        $templates->each(function ($template) use ($federation) {
            $template->hasEntityApplied = $template->hasApplied($federation->id);
            if ($template->hasEntityApplied) {
                $template->existingApplication = $template->getEntityApplication($federation->id);
            }
        });

        return view('web.federation.event-applications.available-templates', compact('templates', 'federation'));
    }
}
