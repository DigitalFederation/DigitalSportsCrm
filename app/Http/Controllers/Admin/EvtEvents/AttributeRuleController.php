<?php

namespace App\Http\Controllers\Admin\EvtEvents;

use App\Enums\EvtAttributeRuleOperatorsEnum;
use App\Http\Controllers\Controller;
use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\AttributeRules;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AttributeRuleController extends Controller
{
    /**
     * Display a listing of the attribute rules.
     */
    public function index(?Attribute $attribute = null): View
    {
        $attributeRules = AttributeRules::where('attribute_id', $attribute->id)->paginate();

        return view('web.admin.evt_events.attribute-rules.index', compact('attributeRules', 'attribute'));
    }

    /**
     * Show the form for creating a new attribute rule.
     */
    public function create(?Attribute $attribute = null): View
    {
        $attributeRule = new AttributeRules;
        $attribute_rules_operator = EvtAttributeRuleOperatorsEnum::cases();

        return view('web.admin.evt_events.attribute-rules.create', compact('attribute', 'attribute_rules_operator', 'attributeRule'));
    }

    /**
     * Store a newly created attribute rule in storage.
     */
    public function store(Request $request, ?Attribute $attribute = null): RedirectResponse
    {

        // Assign the default value for attribute_id from the passed $attribute object
        $request->merge(['attribute_id' => $attribute->id]);

        $data = $request->validate([
            'attribute_id' => 'required|exists:evt_attributes,id',
            'name' => 'required|string|max:255',
            'operator' => 'required|string|max:255',
            'default_value' => 'required|string|max:255',
            'comparison_field' => 'nullable|string|max:255',
        ]);

        AttributeRules::create($data);

        return redirect()->route('admin.evt-events.attributes.index')
            ->with('success', 'Attribute rule created successfully.');
    }

    /**
     * Show the form for editing the specified attribute rule.
     */
    public function edit(Attribute $attribute, AttributeRules $attributeRule): View
    {
        $attribute_rules_operator = EvtAttributeRuleOperatorsEnum::cases();

        return view('web.admin.evt_events.attribute-rules.edit', compact('attributeRule', 'attribute', 'attribute_rules_operator'));
    }

    /**
     * Update the specified attribute rule in storage.
     */
    public function update(Request $request, Attribute $attribute, AttributeRules $attributeRule): RedirectResponse
    {

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'operator' => 'required|string|max:255',
            'default_value' => 'required|string|max:255',
            'comparison_field' => 'nullable|string|max:255',
        ]);

        $attributeRule->update($data);

        return redirect()->route('admin.evt-events.attributes.index')
            ->with('success', 'Attribute rule updated successfully.');
    }

    /**
     * Remove the specified attribute rule from storage.
     */
    public function destroy(Attribute $attribute, AttributeRules $attributeRule): RedirectResponse
    {
        $attributeRule->delete();

        return redirect()->route('admin.evt-events.attributes.index')
            ->with('success', 'Attribute rule deleted successfully.');
    }

}
