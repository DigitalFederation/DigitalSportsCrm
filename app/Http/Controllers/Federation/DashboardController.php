<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function index(): View
    {
        $federation = Auth::user()?->federations()?->first();

        $isModalidadeAssociation = $federation
            && ! $federation->is_default_federation
            && ! $federation->is_local;

        $showLicenseRevenueCharts = $federation
            && ! $isModalidadeAssociation;

        return view('web.federation.dashboard', compact(
            'isModalidadeAssociation',
            'showLicenseRevenueCharts',
        ));
    }
}
