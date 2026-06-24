<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class IndividualInsuranceReportController extends Controller
{
    public function index(): View
    {
        return view('web.admin.insurances.individual-insurance-reports');
    }
}
