<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EventOfficialRolesController extends Controller
{
    /**
     * Show list of events where current user has any official role
     */
    public function index(): View
    {
        $user = Auth::user();

        if (! $user->individual) {
            abort(403, 'No individual profile found');
        }

        return view('web.individual.event_official_roles.index');
    }
}
