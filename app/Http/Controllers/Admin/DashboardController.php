<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function index(): View
    {
        return view('web.admin.dashboard');
    }
}
