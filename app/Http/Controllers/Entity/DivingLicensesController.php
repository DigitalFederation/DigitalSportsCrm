<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use Domain\Diving\Models\DivingEntityTechnicalDirector;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DivingLicensesController extends Controller
{
    public function index(): View
    {
        $entity = auth()->user()->entities()->first();

        // Get all entity's diving licenses (active, pending, pending validation, etc.)
        // Include both DIVING (international) and DIVINGSERVICES (non-international diving services)
        $activeLicenses = $entity->licenses()
            ->whereHas('license', function ($query) {
                $query->whereHas('committee', function ($q) {
                    $q->whereIn('code', ['DIVING', 'DIVINGSERVICES']);
                });
            })
            ->with(['license', 'license.committee'])
            ->paginate(10);

        // Get assigned technical directors
        $assignedDirectors = $entity->divingTechnicalDirectors()
            ->assigned()
            ->with(['individual', 'license', 'licenseAttributed'])
            ->get();

        // No pending invitations in technical director workflow
        $pendingInvitations = collect();

        return view('web.entity.diving_licenses.index', compact(
            'activeLicenses',
            'assignedDirectors',
            'pendingInvitations'
        ));
    }

    /**
     * Show the form to create a new diving license (usually handled by wizard).
     */
    public function create(): RedirectResponse
    {
        // Redirect to the wizard for creating new licenses
        return redirect()->route('entity.diving_licenses.request');
    }

    /**
     * Store a new diving license (usually handled by wizard).
     */
    public function store(Request $request): RedirectResponse
    {
        // This is typically handled by the Livewire wizard component
        // Redirect to the wizard if someone tries to post directly
        return redirect()->route('entity.diving_licenses.request')
            ->with('info', __('diving.use_wizard_to_request_license'));
    }

    public function requestLicense(): View
    {
        // Show the Livewire wizard view
        return view('web.entity.diving_licenses.wizard');
    }

    public function show(string $licenseAttributedId): View
    {
        // Load license without the international scope since diving licenses are international
        $licenseAttributed = LicenseAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
            ->findOrFail($licenseAttributedId);

        $entity = auth()->user()->entities()->first();

        // Verify ownership
        if (
            $licenseAttributed->model_id != $entity->id ||
            $licenseAttributed->model_type !== $entity->getMorphClass()
        ) {
            abort(403);
        }

        // Verify it's a diving license (DIVING or DIVINGSERVICES)
        if (! in_array($licenseAttributed->license->committee->code, ['DIVING', 'DIVINGSERVICES'])) {
            abort(404);
        }

        $licenseAttributed->load([
            'divingTechnicalDirectors.individual',
            'license',
        ]);

        return view('web.entity.diving_licenses.show', compact('licenseAttributed'));
    }

    /**
     * Submit a license request (handled by Livewire wizard).
     */
    public function submitLicenseRequest(Request $request): RedirectResponse
    {
        // This is typically handled by the Livewire wizard component
        // Redirect to the wizard if someone tries to access this directly
        return redirect()->route('entity.diving_licenses.request')
            ->with('info', __('diving.use_wizard_to_request_license'));
    }

    /**
     * Show pending invitations for technical directors.
     */
    public function showInvitations(): View
    {
        $entity = auth()->user()->entities()->first();

        // Get pending invitations
        $pendingInvitations = \Domain\Diving\Models\DivingEntityTechnicalDirector::where('entity_id', $entity->id)
            ->where('status_class', \Domain\Diving\States\AssignedDivingTechnicalDirectorState::class)
            ->with(['individual', 'licenseAttributed.license'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('web.entity.diving_licenses.invitations', compact('pendingInvitations'));
    }

    /**
     * Cancel a pending technical director invitation.
     */
    public function cancelInvitation(\Domain\Diving\Models\DivingEntityTechnicalDirector $invitation): RedirectResponse
    {
        $entity = auth()->user()->entities()->first();

        // Verify ownership
        if ($invitation->entity_id !== $entity->id) {
            abort(403);
        }

        // Verify it's a pending invitation
        if ($invitation->status_class !== \Domain\Diving\States\AssignedDivingTechnicalDirectorState::class) {
            return back()->with('error', __('diving.cannot_cancel_invitation'));
        }

        try {
            $invitation->delete();

            // Log activity
            activity('diving_license')
                ->causedBy(auth()->user())
                ->performedOn($invitation)
                ->withProperties([
                    'individual_id' => $invitation->individual_id,
                    'license_id' => $invitation->license_attributed_id,
                ])
                ->log('Technical director invitation cancelled');

            return back()->with('success', __('diving.invitation_cancelled'));
        } catch (\Exception $e) {
            Log::error('Failed to cancel invitation: ' . $e->getMessage());

            return back()->with('error', __('diving.cancel_invitation_failed'));
        }
    }

    public function showAssignments(): View
    {
        $entity = auth()->user()->entities()->first();

        $assignments = $entity->divingTechnicalDirectors()
            ->with(['individual', 'licenseAttributed.license'])
            ->orderBy('created_at', 'desc')
            ->paginate();

        return view('web.entity.diving_licenses.assignments', compact('assignments'));
    }

    public function removeAssignment(DivingEntityTechnicalDirector $assignment): RedirectResponse
    {
        $entity = auth()->user()->entities()->first();

        if ($assignment->entity_id !== $entity->id) {
            abort(403);
        }

        if (! $assignment->canBeRemoved()) {
            return back()->with('error', __('This technical director cannot be removed.'));
        }

        try {
            $assignment->remove('Removed by entity');

            // Log activity
            activity('diving_license')
                ->causedBy(auth()->user())
                ->performedOn($assignment)
                ->withProperties([
                    'individual_id' => $assignment->individual_id,
                    'license_id' => $assignment->license_attributed_id,
                ])
                ->log('Technical director assignment removed');

            return redirect()->route('entity.diving_licenses.assignments')
                ->with('success', __('Technical director removed successfully.'));
        } catch (\Exception $e) {
            Log::error('Failed to remove technical director: ' . $e->getMessage());

            return back()->with('error', __('Failed to remove technical director.'));
        }
    }

    /**
     * Show the form to invite a technical director for a diving license.
     */
    public function inviteDirector(string $licenseAttributedId): View
    {
        // Load license without the international scope since diving licenses are international
        $licenseAttributed = LicenseAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
            ->findOrFail($licenseAttributedId);

        $entity = auth()->user()->entities()->first();

        // Verify ownership
        if (
            $licenseAttributed->model_id != $entity->id ||
            $licenseAttributed->model_type !== $entity->getMorphClass()
        ) {
            abort(403);
        }

        // Verify it's a diving license (DIVING or DIVINGSERVICES)
        if (! in_array($licenseAttributed->license->committee->code, ['DIVING', 'DIVINGSERVICES'])) {
            abort(404);
        }

        return view('web.entity.diving_licenses.invite-director', compact('licenseAttributed'));
    }

    /**
     * Send an invitation to a technical director.
     */
    public function sendDirectorInvitation(Request $request, string $licenseAttributedId): RedirectResponse
    {
        // Load license without the international scope since diving licenses are international
        $licenseAttributed = LicenseAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
            ->findOrFail($licenseAttributedId);

        $entity = auth()->user()->entities()->first();

        // Verify ownership
        if (
            $licenseAttributed->model_id != $entity->id ||
            $licenseAttributed->model_type !== $entity->getMorphClass()
        ) {
            abort(403);
        }

        // Verify it's a diving license (DIVING or DIVINGSERVICES)
        if (! in_array($licenseAttributed->license->committee->code, ['DIVING', 'DIVINGSERVICES'])) {
            abort(404);
        }

        // Validate the request
        $validated = $request->validate([
            'individual_email' => 'required|email',
            'certification_systems' => 'required|array|min:1',
            'certification_systems.*' => 'required|string',
            'message' => 'nullable|string|max:500',
        ]);

        try {
            // Check if the individual exists
            $individual = \Domain\Individuals\Models\Individual::where('email', $validated['individual_email'])->first();

            if (! $individual) {
                return back()->withInput()->with('error', __('diving.individual_not_found'));
            }

            // Check if this individual is already assigned to this license
            $existingAssignment = $licenseAttributed->divingTechnicalDirectors()
                ->where('individual_id', $individual->id)
                ->where('status_class', \Domain\Diving\States\AssignedDivingTechnicalDirectorState::class)
                ->first();

            if ($existingAssignment) {
                return back()->withInput()->with('error', __('diving.director_already_assigned'));
            }

            // Create the technical director assignment/invitation
            $assignment = $licenseAttributed->divingTechnicalDirectors()->create([
                'entity_id' => $entity->id,
                'individual_id' => $individual->id,
                'license_id' => $licenseAttributed->license_id,
                'certification_systems' => $validated['certification_systems'],
                'status_class' => \Domain\Diving\States\AssignedDivingTechnicalDirectorState::class,
                'assigned_at' => now(),
                'invitation_message' => $validated['message'] ?? null,
            ]);

            // Log activity
            activity('diving_license')
                ->causedBy(auth()->user())
                ->performedOn($assignment)
                ->withProperties([
                    'individual_id' => $individual->id,
                    'license_id' => $licenseAttributed->id,
                    'certification_systems' => $validated['certification_systems'],
                ])
                ->log('Technical director invitation sent');

            // TODO: Send notification email to the individual

            return redirect()->route('entity.diving_licenses.show', $licenseAttributed)
                ->with('success', __('diving.invitation_sent_successfully'));

        } catch (\Exception $e) {
            Log::error('Failed to send technical director invitation: ' . $e->getMessage(), [
                'license_id' => $licenseAttributed->id,
                'entity_id' => $entity->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', __('diving.invitation_failed'));
        }
    }
}
