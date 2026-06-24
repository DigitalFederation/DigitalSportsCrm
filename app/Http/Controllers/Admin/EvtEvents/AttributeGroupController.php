<?php

namespace App\Http\Controllers\Admin\EvtEvents;

use App\Http\Controllers\Controller;
use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\AttributeGroup;
use Illuminate\Http\Request;

class AttributeGroupController extends Controller
{
    public function index()
    {
        $attributes = AttributeGroup::with('attributes')
            ->paginate();

        return view('web.admin.evt_events.attribute_group.index', compact('attributes'));
    }

    public function create()
    {
        $attributes = Attribute::all();

        return view('web.admin.evt_events.attribute_group.create', compact('attributes'));
    }

    public function edit(AttributeGroup $attributeGroup)
    {
        $attributes = Attribute::all();

        return view('web.admin.evt_events.attribute_group.edit', compact('attributeGroup', 'attributes'));
    }

    public function update(Request $request, AttributeGroup $attributeGroup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'attributes' => 'nullable|array',
            'attributes.*' => 'exists:evt_attributes,id',
        ]);

        $attributeGroup->update([
            'name' => $validated['name'],
        ]);

        $attributeGroup->attributes()->sync($validated['attributes'] ?? []);

        return redirect()->route('admin.evt-events.attribute-group.index')
            ->with('success', 'Attribute group updated successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'attributes' => 'nullable|array',
            'attributes.*' => 'exists:evt_attributes,id',
        ]);

        $attributeGroup = AttributeGroup::create([
            'name' => $validated['name'],
        ]);

        if (isset($validated['attributes'])) {
            $attributeGroup->attributes()->sync($validated['attributes']);
        }

        return redirect()->route('admin.evt-events.attribute-group.index')
            ->with('success', 'Attribute group created successfully.');
    }

    public function destroy(AttributeGroup $attributeGroup)
    {
        $attributeGroup->attributes()->detach(); // Detach all attributes from this group
        $attributeGroup->delete(); // Delete the attribute group

        return back()->with('success', 'Attribute group deleted successfully.');
    }
}
