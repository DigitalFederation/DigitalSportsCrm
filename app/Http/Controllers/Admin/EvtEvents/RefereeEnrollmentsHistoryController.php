<?php

namespace App\Http\Controllers\Admin\EvtEvents;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class RefereeEnrollmentsHistoryController extends Controller
{
    public function index(): View
    {
        return view('web.admin.evt_events.referee_enrollments_history.index');
    }
}
