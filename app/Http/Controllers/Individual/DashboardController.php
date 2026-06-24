<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function index(): View
    {
        $individual = auth()->user()->individuals()->firstOrFail();

        // Preload relationships for profile tabs
        $individual->load([
            'country',
            'individualFederations.federation',
            'individualEntities.entity',
            'certificationsDivingAttributed.certification',
            'certificationsScientificAttributed.certification',
            'certificationsSportAttributed.certification',
            'licensesDivingAttributed.license',
            'licensesScientificAttributed.license',
            'licensesSportAttributed.license',
            'memberSubscriptions.insurances.insurancePlan',
            'memberSubscriptions.insurances.member',
            'memberSubscriptions.affiliations.federation',
            'memberSubscriptions.membershipPackage.affiliationPlans',
        ]);

        return view('web.individual.dashboard', compact('individual'));
    }
}
