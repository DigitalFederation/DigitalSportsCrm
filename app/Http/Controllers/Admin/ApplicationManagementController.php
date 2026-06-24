<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManagesApplicationTemplates;
use App\Http\Controllers\Concerns\ManagesEventApplications;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventApplications\StoreApplicationTemplateRequest;
use Domain\EventApplications\Actions\CreateApplicationTemplateAction;
use Domain\EventApplications\Actions\DeleteEventApplicationAction;
use Domain\EventApplications\Actions\UpdateTemplateStateAction;
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
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ApplicationManagementController extends Controller
{
    use ManagesApplicationTemplates;
    use ManagesEventApplications;

    protected function getTemplateRouteNamespace(): string
    {
        return 'admin';
    }

    protected function getTemplateBackRoute(): string
    {
        return 'admin.event-applications.index';
    }

    public function index(): View
    {
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
                AllowedFilter::exact('entity_id'),
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
        $routeNamespace = 'admin';

        return view('web.admin.event-applications.index', compact(
            'applications',
            'templatesList',
            'templates',
            'sports',
            'statuses',
            'routeNamespace'
        ));
    }

    public function show(EventApplication $application): View
    {
        $application->load([
            'entity',
            'sport',
            'template',
            'district',
            'documents',
            'comments.user',
            'stateHistory',
        ]);

        $routeNamespace = 'admin';

        return view('web.admin.event-applications.show', compact('application', 'routeNamespace'));
    }

    public function destroy(
        EventApplication $application,
        DeleteEventApplicationAction $action
    ): RedirectResponse {
        try {
            $action->execute($application);

            return redirect()->route('admin.event-applications.index')
                ->with('success', __('event_applications.application_deleted_success'));
        } catch (Exception $ex) {
            Log::error('Error deleting application: ' . $ex->getMessage(), [
                'exception' => $ex,
                'application_id' => $application->id,
            ]);

            return back()->with('error', __('event_applications.application_deleted_error'));
        }
    }

    public function createTemplate(): View
    {
        return $this->templateCreate();
    }

    public function storeTemplate(StoreApplicationTemplateRequest $request, CreateApplicationTemplateAction $action): RedirectResponse
    {
        return $this->templateStore($request, $action);
    }

    public function showTemplate(ApplicationTemplate $application_template): View
    {
        return $this->templateShow($application_template);
    }

    public function editTemplate(ApplicationTemplate $application_template): View
    {
        return $this->templateEdit($application_template);
    }

    public function updateTemplate(StoreApplicationTemplateRequest $request, ApplicationTemplate $application_template): RedirectResponse
    {
        return $this->templateUpdate($request, $application_template);
    }

    public function destroyTemplate(ApplicationTemplate $application_template): RedirectResponse
    {
        return $this->templateDestroy($application_template);
    }

    public function activateTemplate(ApplicationTemplate $application_template): RedirectResponse
    {
        return $this->templateActivate($application_template);
    }

    public function deactivateTemplate(ApplicationTemplate $application_template): RedirectResponse
    {
        return $this->templateDeactivate($application_template);
    }

    public function updateTemplateState(ApplicationTemplate $application_template, Request $request, UpdateTemplateStateAction $action): RedirectResponse
    {
        return $this->templateUpdateState($application_template, $request, $action);
    }
}
