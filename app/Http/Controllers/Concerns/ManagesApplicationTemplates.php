<?php

namespace App\Http\Controllers\Concerns;

use App\Http\Requests\EventApplications\StoreApplicationTemplateRequest;
use Domain\EventApplications\Actions\CreateApplicationTemplateAction;
use Domain\EventApplications\Actions\UpdateTemplateStateAction;
use Domain\EventApplications\Models\ApplicationTemplate;
use Domain\EvtEvents\Models\Sport;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

trait ManagesApplicationTemplates
{
    abstract protected function getTemplateRouteNamespace(): string;

    abstract protected function getTemplateBackRoute(): string;

    protected function templateCreate(): View
    {
        $sports = Sport::select('id', 'name')->orderBy('name')->get();
        $eventTypes = config('enum.event_type');
        $routeNamespace = $this->getTemplateRouteNamespace();
        $backRoute = $this->getTemplateBackRoute();

        return view('web.federation.application-templates.create', compact('sports', 'eventTypes', 'routeNamespace', 'backRoute'));
    }

    protected function templateStore(StoreApplicationTemplateRequest $request, CreateApplicationTemplateAction $action): RedirectResponse
    {
        $routeNamespace = $this->getTemplateRouteNamespace();

        try {
            $data = array_merge($request->validated(), [
                'created_by' => auth()->id(),
            ]);

            $template = $action->execute($data);
            $template->refresh();

            return redirect()
                ->route($routeNamespace . '.application-templates.show', $template)
                ->with('success', __('event_applications.template_created_success'));
        } catch (Exception $ex) {
            Log::error('Error creating application template: ' . $ex->getMessage(), [
                'exception' => $ex,
                'data' => $request->validated(),
            ]);

            return back()
                ->withInput()
                ->with('error', __('event_applications.template_created_error'));
        }
    }

    protected function templateShow(ApplicationTemplate $application_template): View
    {
        $application_template->load(['sport', 'createdBy', 'documents']);

        $applications = $application_template->applications()
            ->with(['entity', 'sport'])
            ->latest()
            ->paginate(15);

        $routeNamespace = $this->getTemplateRouteNamespace();
        $backRoute = $this->getTemplateBackRoute();

        return view('web.federation.application-templates.show', compact('application_template', 'applications', 'routeNamespace', 'backRoute'));
    }

    protected function templateEdit(ApplicationTemplate $application_template): View
    {
        $sports = Sport::select('id', 'name')->orderBy('name')->get();
        $eventTypes = config('enum.event_type');
        $application_template->loadCount('applications');
        $routeNamespace = $this->getTemplateRouteNamespace();
        $backRoute = $this->getTemplateBackRoute();

        return view('web.federation.application-templates.edit', compact('application_template', 'sports', 'eventTypes', 'routeNamespace', 'backRoute'));
    }

    protected function templateUpdate(StoreApplicationTemplateRequest $request, ApplicationTemplate $application_template): RedirectResponse
    {
        $routeNamespace = $this->getTemplateRouteNamespace();

        try {
            $application_template->update($request->validated());

            return redirect()
                ->route($routeNamespace . '.application-templates.show', $application_template)
                ->with('success', __('event_applications.template_updated_success'));
        } catch (Exception $ex) {
            Log::error('Error updating application template: ' . $ex->getMessage(), [
                'exception' => $ex,
                'template_id' => $application_template->id,
                'data' => $request->validated(),
            ]);

            return back()
                ->withInput()
                ->with('error', __('event_applications.template_updated_error'));
        }
    }

    protected function templateDestroy(ApplicationTemplate $application_template): RedirectResponse
    {
        $backRoute = $this->getTemplateBackRoute();

        try {
            if ($application_template->activeApplications()->count() > 0) {
                return back()->with('error', __('event_applications.template_has_applications'));
            }

            $application_template->delete();

            return redirect()
                ->route($backRoute)
                ->with('success', __('event_applications.template_deleted_success'));
        } catch (Exception $ex) {
            Log::error('Error deleting application template: ' . $ex->getMessage(), [
                'exception' => $ex,
                'template_id' => $application_template->id,
            ]);

            return back()->with('error', __('event_applications.template_deleted_error'));
        }
    }

    protected function templateActivate(ApplicationTemplate $application_template): RedirectResponse
    {
        try {
            $application_template->update(['state' => 'open']);

            return back()->with('success', __('event_applications.template_activated_success'));
        } catch (Exception $ex) {
            Log::error('Error activating application template: ' . $ex->getMessage(), [
                'exception' => $ex,
                'template_id' => $application_template->id,
            ]);

            return back()->with('error', __('event_applications.template_activated_error'));
        }
    }

    protected function templateDeactivate(ApplicationTemplate $application_template): RedirectResponse
    {
        try {
            $application_template->update(['state' => 'closed']);

            return back()->with('success', __('event_applications.template_deactivated_success'));
        } catch (Exception $ex) {
            Log::error('Error deactivating application template: ' . $ex->getMessage(), [
                'exception' => $ex,
                'template_id' => $application_template->id,
            ]);

            return back()->with('error', __('event_applications.template_deactivated_error'));
        }
    }

    protected function templateUpdateState(ApplicationTemplate $application_template, Request $request, UpdateTemplateStateAction $action): RedirectResponse
    {
        try {
            $action->execute($application_template, $request->input('state'));

            return back()->with('success', __('event_applications.template_state_updated_success'));
        } catch (Exception $ex) {
            Log::error('Error updating template state: ' . $ex->getMessage(), [
                'exception' => $ex,
                'template_id' => $application_template->id,
                'state' => $request->input('state'),
            ]);

            return back()->with('error', __('event_applications.template_state_updated_error'));
        }
    }
}
