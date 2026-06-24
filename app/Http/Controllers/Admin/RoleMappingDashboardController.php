<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class RoleMappingDashboardController extends Controller
{
    public function index()
    {
        // Check permission
        if (! auth()->user()->can('access users')) {
            abort(403);
        }

        // Get statistics
        $stats = [
            'license_mappings' => DB::table('license_roles')->count(),
            'certification_mappings' => DB::table('certification_roles')->count(),
            'federation_mappings' => DB::table('federation_roles')->count(),
            'total_roles' => DB::table('roles')->count(),
            'active_licenses' => DB::table('license')
                ->join('license_roles', 'license.id', '=', 'license_roles.license_id')
                ->distinct('license.id')
                ->count('license.id'),
            'active_certifications' => DB::table('certification')
                ->join('certification_roles', 'certification.id', '=', 'certification_roles.certification_id')
                ->distinct('certification.id')
                ->count('certification.id'),
        ];

        return view('admin.role-mappings.index', compact('stats'));
    }
}
