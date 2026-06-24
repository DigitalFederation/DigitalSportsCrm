<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class LicenseRoleMappingController extends Controller
{
    public function index()
    {
        // Check permission
        if (! auth()->user()->can('access users')) {
            abort(403);
        }

        return view('admin.role-mappings.licenses');
    }
}
