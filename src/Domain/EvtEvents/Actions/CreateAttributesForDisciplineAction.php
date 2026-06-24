<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\Discipline;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateAttributesForDisciplineAction
{
    /**
     * @throws ValidationException
     */
    public function execute(Discipline $discipline, array $attributesData): array
    {
        $createdAttributes = [];

        foreach ($attributesData as $attributeData) {
            $validator = Validator::make($attributeData, [
                'name' => 'required|string|max:255',
                'attribute_type' => 'required|string',
                'default_value' => 'nullable|string',
                'validation_rules' => 'nullable|string',
                'custom_class' => 'nullable|string',
                'fillable_type' => 'required|string',
                'fillable_global' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $attribute = Attribute::create($attributeData);
            $attribute->disciplines()->attach($discipline->id);
            $createdAttributes[] = $attribute;
        }

        return $createdAttributes;
    }
}
