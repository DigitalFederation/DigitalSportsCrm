<?php

namespace App\Http\Controllers\Admin\EvtEvents;

use App\Http\Controllers\Controller;
use Domain\EvtEvents\Models\EventPin;
use Illuminate\Http\Request;

class AntiDopingController extends Controller
{
    public function create()
    {
        // Since you're using a modal, you might not need to define this method.
        // If you do need to return some data to the view where the modal is included,
        // you should do it here.
    }

    public function store(Request $request)
    {
        $request->validate(['pin' => 'required|string|unique:event_pins,pin']);
        EventPin::create(['pin' => $request->input('pin')]);

        return back()->with('status', 'PIN created successfully.');
    }

    public function destroy($id)
    {
        $eventPin = EventPin::findOrFail($id);
        $eventPin->delete();

        return back()->with('status', 'PIN removed successfully.');
    }
}
