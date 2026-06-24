<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Discipline;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateDisciplineAction
{
    public function execute(array $data): Discipline
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'sport_id' => 'required|integer|exists:sports,id',
            'gender' => 'required|in:male,female,mixed',
            'enrollment_type' => 'required|in:Individual,Team,Relay',
            'enrollment_type_value' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Discipline::create($data);
    }
}
