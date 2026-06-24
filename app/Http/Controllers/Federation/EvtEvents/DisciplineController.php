<?php

namespace App\Http\Controllers\Federation\EvtEvents;

use App\Http\Controllers\Controller;
use Domain\EvtEvents\Models\Competition;
use Illuminate\View\View;

class DisciplineController extends Controller
{
    public function index(?Competition $competition = null): View
    {
        $competition->load('disciplines');
        $disciplines = $competition->disciplines()->paginate();

        return view('web.federation.evt_event.disciplines.index', compact('disciplines', 'competition'));
    }
}
