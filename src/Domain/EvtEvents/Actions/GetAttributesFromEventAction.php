<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Event;

class GetAttributesFromEventAction
{
    public function execute(int $eventId): array
    {

        $event = Event::with(['attributes.rules'])->find($eventId);

        $data = [];
        if (! empty($event)) {
            foreach ($event->attributes as $attribute) {
                // Check if the attribute is of type 'select' and prepare the options accordingly
                $options = $attribute->attribute_type == 'SELECT' ? ($attribute->attribute_data ?? []) : [];

                $data[$attribute->id] = [
                    'name' => $attribute->name,
                    'type' => $attribute->attribute_type,
                    'default_value' => $attribute->default_value,
                    'validation_rules' => $attribute->validation_rules,
                    'custom_class' => $attribute->custom_class,
                    'fillable_type' => $attribute->fillable_type,
                    'fillable_global' => $attribute->fillable_global,
                    'options' => $options, // Specific for select type attributes
                    'rules' => $attribute->rules->toArray(), // Assuming there are rules defined
                ];
            }
        }

        return $data;
    }
}
