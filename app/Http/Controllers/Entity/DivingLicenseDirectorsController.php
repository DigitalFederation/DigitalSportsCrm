<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use Domain\Diving\States\AssignedDivingTechnicalDirectorState;
use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Contracts\View\View;

class DivingLicenseDirectorsController extends Controller
{
    /**
     * Show the directors management page for a license.
     */
    public function index(LicenseAttributed $licenseAttributed): View
    {
        $entity = auth()->user()->entities()->first();

        // Verify ownership
        if ($licenseAttributed->model_id != $entity->id ||
            $licenseAttributed->model_type !== $entity->getMorphClass()) {
            abort(403);
        }

        // Verify it's a diving license
        if ($licenseAttributed->license->committee->code !== 'DIVING') {
            abort(404);
        }

        $licenseAttributed->load([
            'divingTechnicalDirectors.individual',
            'license',
        ]);

        // Get assigned directors
        $activeDirectors = $licenseAttributed->divingTechnicalDirectors()
            ->where('status_class', AssignedDivingTechnicalDirectorState::class)
            ->with('individual')
            ->get();

        // Get all technical directors (for history view)
        $allDirectors = $licenseAttributed->divingTechnicalDirectors()
            ->with('individual')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('web.entity.diving_license_directors.index', compact(
            'licenseAttributed',
            'activeDirectors',
            'allDirectors'
        ));
    }

    /**
     * Note: Technical director assignment is now handled by the Livewire wizard.
     * This controller only shows the management view for existing assignments.
     */
}
