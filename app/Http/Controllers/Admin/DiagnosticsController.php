<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class DiagnosticsController extends Controller
{
    /**
     * Display the Eligibility Diagnostic Center.
     */
    public function index(): View
    {
        return view('web.admin.diagnostics.index');
    }
}
