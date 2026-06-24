<?php

namespace App\Http\Controllers\Admin\EvtEvents;

use App\Http\Controllers\Controller;
use Domain\EvtEvents\Models\DisciplineFee;
use Illuminate\Http\Request;

class DisciplineFeeController extends Controller
{
    public function index()
    {
        $fees = DisciplineFee::all();

        return view('web.admin.evt_events.discipline_fees.index', compact('fees'));
    }

    public function create()
    {
        return view('web.admin.evt_events.discipline_fees.create');
    }

    public function store(Request $request)
    {
        $fee = DisciplineFee::create($request->all());

        return redirect()->route('admin.evt_events.discipline_fees.index')->with('message', 'Fee created successfully!');
    }

    public function edit($id)
    {
        $fee = DisciplineFee::findOrFail($id);

        return view('web.admin.evt_events.discipline_fees.edit', compact('fee'));
    }

    public function update(Request $request, $id)
    {
        $fee = DisciplineFee::findOrFail($id);
        $fee->update($request->all());

        return redirect()->route('admin.evt_events.discipline_fees.index')->with('message', 'Fee updated successfully!');
    }

    public function destroy($id)
    {
        $fee = DisciplineFee::findOrFail($id);
        $fee->delete();

        return redirect()->route('admin.evt_events.discipline_fees.index')->with('message', 'Fee deleted successfully!');
    }
}
