<?php

namespace App\Http\Controllers\Individual\EvtEvents;

use App\Http\Controllers\Controller;
use Domain\EvtEvents\Models\Competition;
use Illuminate\View\View;

class DisciplineController extends Controller
{
    public function index(?Competition $competition = null): View
    {
        $competition->load('disciplines');
        $disciplines = $competition->disciplines()->paginate();

        return view('web.individual.evt_events.disciplines.index', compact('disciplines', 'competition'));
    }
}
