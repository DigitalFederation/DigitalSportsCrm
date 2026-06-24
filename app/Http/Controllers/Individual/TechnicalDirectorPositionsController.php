<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use Domain\Diving\Actions\ApproveTechnicalDirectorLicenseAction;
use Domain\Diving\Actions\RejectTechnicalDirectorLicenseAction;
use Domain\Diving\Models\DivingEntityTechnicalDirector;
use Domain\Diving\States\AssignedDivingTechnicalDirectorState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TechnicalDirectorPositionsController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        // Get the individual profile - handle cases where it might not exist
        $individual = $user->individuals()->first();

        if (! $individual) {
            // If no individual profile exists, return empty state
            $technicalDirectorPositions = collect();
            $pendingInvitations = collect();

            return view('web.individual.technical_director_positions.index', compact(
                'technicalDirectorPositions',
                'pendingInvitations',
                'individual'
            ));
        }

        // Get all Technical Director positions (assignments)
        $technicalDirectorPositions = DivingEntityTechnicalDirector::where('individual_id', $individual->id)
            ->where('status_class', AssignedDivingTechnicalDirectorState::class)
            ->with(['entity', 'licenseAttributed.license'])
            ->orderBy('assigned_at', 'desc')
            ->get();

        // No pending invitations in assignment workflow
        $pendingInvitations = collect();

        return view('web.individual.technical_director_positions.index', compact(
            'technicalDirectorPositions',
            'pendingInvitations',
            'individual'
        ));
    }

    public function approve(Request $request, DivingEntityTechnicalDirector $technicalDirector, ApproveTechnicalDirectorLicenseAction $action): JsonResponse
    {
        try {
            // Validate that the current user is this technical director
            $individual = auth()->user()->individuals()->first();
            if (! $individual || $technicalDirector->individual_id !== $individual->id) {
                return response()->json([
                    'success' => false,
                    'message' => __('diving.unauthorized_technical_director_action'),
                ], 403);
            }

            $approvalNotes = $request->input('approval_notes');

            $licenseAttributed = $action->execute($technicalDirector, $approvalNotes);

            return response()->json([
                'success' => true,
                'message' => __('diving.license_approved_successfully'),
                'license_status' => class_basename($licenseAttributed->status_class),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function reject(Request $request, DivingEntityTechnicalDirector $technicalDirector, RejectTechnicalDirectorLicenseAction $action): JsonResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        try {
            // Validate that the current user is this technical director
            $individual = auth()->user()->individuals()->first();
            if (! $individual || $technicalDirector->individual_id !== $individual->id) {
                return response()->json([
                    'success' => false,
                    'message' => __('diving.unauthorized_technical_director_action'),
                ], 403);
            }

            $rejectionReason = $request->input('rejection_reason');

            $licenseAttributed = $action->execute($technicalDirector, $rejectionReason);

            return response()->json([
                'success' => true,
                'message' => __('diving.license_rejected_successfully'),
                'license_status' => class_basename($licenseAttributed->status_class),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
