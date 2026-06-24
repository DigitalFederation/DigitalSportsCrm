<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EventCoachHistoryController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        if (! $user->individual) {
            abort(403, 'No individual profile found');
        }

        return view('web.individual.event_coach_history.index');
    }
}
