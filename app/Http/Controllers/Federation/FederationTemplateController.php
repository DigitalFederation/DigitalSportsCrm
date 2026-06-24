<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Concerns\ManagesApplicationTemplates;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventApplications\StoreApplicationTemplateRequest;
use Domain\EventApplications\Actions\CreateApplicationTemplateAction;
use Domain\EventApplications\Actions\UpdateTemplateStateAction;
use Domain\EventApplications\Models\ApplicationTemplate;
use Domain\EvtEvents\Models\Sport;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class FederationTemplateController extends Controller
{
    use ManagesApplicationTemplates;

    protected function getTemplateRouteNamespace(): string
    {
        return 'federation';
    }

    protected function getTemplateBackRoute(): string
    {
        return 'federation.event-applications.index';
    }

    /**
     * Ensure the authenticated user belongs to the main federation
     */
    protected function ensureMainFederation(): void
    {
        $federation = auth()->user()->federations()->first();

        if (! $federation || ! $federation->is_default_federation) {
            abort(403, 'Only the main federation can manage application templates.');
        }
    }

    public function index(): View
    {
        $this->ensureMainFederation();

        $federation = auth()->user()->federations()->first();

        $templates = QueryBuilder::for(ApplicationTemplate::class)
            ->allowedFilters([
                AllowedFilter::exact('event_type'),
                AllowedFilter::exact('sport_id'),
                AllowedFilter::exact('state'),
                'name',
            ])
            ->with(['sport', 'createdBy'])
            ->withCount('activeApplications')
            ->orderByDesc('created_at')
            ->paginate()
            ->appends(request()->query());

        $sports = Sport::select('id', 'name')->orderBy('name')->get();

        return view('web.federation.application-templates.index', compact('templates', 'sports', 'federation'));
    }

    public function create(): View
    {
        $this->ensureMainFederation();

        return $this->templateCreate();
    }

    public function store(StoreApplicationTemplateRequest $request, CreateApplicationTemplateAction $action): RedirectResponse
    {
        $this->ensureMainFederation();

        return $this->templateStore($request, $action);
    }

    public function show(ApplicationTemplate $application_template): View
    {
        $this->ensureMainFederation();

        return $this->templateShow($application_template);
    }

    public function edit(ApplicationTemplate $application_template): View
    {
        $this->ensureMainFederation();

        return $this->templateEdit($application_template);
    }

    public function update(StoreApplicationTemplateRequest $request, ApplicationTemplate $application_template): RedirectResponse
    {
        $this->ensureMainFederation();

        return $this->templateUpdate($request, $application_template);
    }

    public function destroy(ApplicationTemplate $application_template): RedirectResponse
    {
        $this->ensureMainFederation();

        return $this->templateDestroy($application_template);
    }

    public function activate(ApplicationTemplate $application_template): RedirectResponse
    {
        $this->ensureMainFederation();

        return $this->templateActivate($application_template);
    }

    public function deactivate(ApplicationTemplate $application_template): RedirectResponse
    {
        $this->ensureMainFederation();

        return $this->templateDeactivate($application_template);
    }

    public function updateState(ApplicationTemplate $application_template, Request $request, UpdateTemplateStateAction $action): RedirectResponse
    {
        $this->ensureMainFederation();

        return $this->templateUpdateState($application_template, $request, $action);
    }
}
