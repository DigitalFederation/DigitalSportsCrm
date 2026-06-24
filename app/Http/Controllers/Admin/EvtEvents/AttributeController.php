<?php

namespace App\Http\Controllers\Admin\EvtEvents;

use App\Enums\EvtAttributeFillableTypeEnum;
use App\Enums\EvtAttributeRuleOperatorsEnum;
use App\Enums\EvtAttributeTypesEnum;
use App\Http\Controllers\Controller;
use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\AttributeRules;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class AttributeController extends Controller
{
    /**
     * Display a listing of the attributes.
     */
    public function index(): View
    {
        $attributes = Attribute::with('attributeGroups')->paginate();

        return view('web.admin.evt_events.attributes.index', compact('attributes'));
    }

    /**
     * Show the form for creating a new attribute.
     */
    public function create(Event $event, $discipline_id = null): View
    {

        $attribute = new Attribute;
        $attribute_types = EvtAttributeTypesEnum::cases();
        $attribute_fillable_types = EvtAttributeFillableTypeEnum::cases();

        $discipline = null;
        if (! empty($discipline_id)) {
            $discipline = Discipline::find($discipline_id);
        }

        return view('web.admin.evt_events.attributes.create', compact(
            'event',
            'discipline',
            'attribute',
            'attribute_types',
            'attribute_fillable_types'
        ));
    }

    /**
     * Store a newly created attribute in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'attribute_type' => 'required|string',
            'default_value' => 'nullable|string',
            'validation_rules' => 'nullable|string',
            'custom_class' => 'nullable|string',
            'fillable_type' => 'required|string',
            'fillable_global' => 'boolean',
            'discipline_id' => 'integer|exists:evt_disciplines,id',
            'attribute_data' => 'array',
            'enrollment_type' => 'nullable|string',
            'required' => 'boolean',
        ]);

        if (empty($data['fillable_global'])) {
            $data['fillable_global'] = 0;
        }

        // Convert required to boolean
        $data['required'] = $request->boolean('required');

        // Create attribute without discipline_id
        $attributeData = Arr::except($data, ['discipline_id']);
        $attribute = Attribute::create($attributeData);

        // If a discipline_id is provided, associate the attribute with the discipline
        if ($request->filled('discipline_id')) {
            $discipline = Discipline::find($request->discipline_id);
            $discipline->attributes()->attach($attribute);
        }

        // Associate the attribute with the event if an event ID is provided
        if ($event = Event::find($request->input('event_id'))) {
            $event->attributes()->attach($attribute);
        }

        // If the attribute type is time, add validation rule
        if ($attribute->attribute_type === EvtAttributeTypesEnum::TIME->name) {
            AttributeRules::create([
                'attribute_id' => $attribute->id,
                'name' => 'Time Format Validation',
                'operator' => EvtAttributeRuleOperatorsEnum::REGEX_MATCH->value,
                'default_value' => '',
                'comparison_value' => '/^([0-5]?\d):([0-5]?\d):([0-5]?\d)(\.\d{1,2})?$/',
                'is_validation' => true,
            ]);
        }

        $routeName = 'admin.evt-events.attributes.index';
        $routeParameters = $request->discipline_id ? ['discipline' => $request->discipline_id] : [];

        return redirect()->route($routeName, $routeParameters)->with('success', 'Attribute created successfully');
    }

    /**
     * Display the specified attribute.
     *
     */
    public function show(Attribute $attribute): View
    {
        return view('web.admin.evt_events.attributes.show', compact('attribute'));
    }

    /**
     * Show the form for editing the specified attribute.
     *
     * @return View
     */
    public function edit(Attribute $attribute): View
    {
        $attribute_types = EvtAttributeTypesEnum::cases();
        $attribute_fillable_types = EvtAttributeFillableTypeEnum::cases();

        return view('web.admin.evt_events.attributes.edit', compact(
            'attribute',
            'attribute_types',
            'attribute_fillable_types'
        ));
    }

    /**
     * Update the specified attribute in storage.
     *
     * @return RedirectResponse
     */
    public function update(Request $request, Attribute $attribute)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'attribute_type' => 'required|string',
            'default_value' => 'nullable|string',
            'validation_rules' => 'nullable|string',
            'custom_class' => 'nullable|string',
            'fillable_type' => 'required|string',
            'fillable_global' => 'nullable|boolean',
            'attribute_data' => 'array',
            'enrollment_type' => 'nullable|string',
            'required' => 'boolean',
        ]);

        // Convert required to boolean
        $data['required'] = $request->boolean('required');

        $attribute->update($data);

        $routeName = 'admin.evt-events.attributes.index';
        $routeParameters = $request->discipline_id ? ['discipline' => $request->discipline_id] : [];

        return redirect()->route($routeName, $routeParameters)->with('success', 'Attribute updated successfully');
    }

    /**
     * Remove the specified attribute from storage.
     */
    public function destroy(Attribute $attribute): RedirectResponse
    {
        // Check if the attribute is associated with any enrollment records
        $hasEnrollments = $attribute->athleteEnrollments()->exists()
            || $attribute->individualEnrollments()->exists()
            || $attribute->coachEnrollments()->exists()
            || $attribute->officialsEnrollments()->exists();

        if ($hasEnrollments) {
            return redirect()->route('admin.evt-events.attributes.index')
                ->with('error', __('events.attribute_cannot_delete_has_enrollments'));
        }

        try {
            \DB::transaction(function () use ($attribute) {
                // Detach from all pivot tables first
                $attribute->disciplines()->detach();
                $attribute->event()->detach();
                $attribute->attributeGroups()->detach();

                // Delete associated rules
                $attribute->rules()->delete();

                // Delete the attribute
                $attribute->delete();
            });

            return redirect()->route('admin.evt-events.attributes.index')
                ->with('success', __('events.attribute_deleted_successfully'));
        } catch (\Exception $e) {
            \Log::error('Failed to delete attribute', [
                'attribute_id' => $attribute->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admin.evt-events.attributes.index')
                ->with('error', __('events.attribute_delete_failed'));
        }
    }
}
