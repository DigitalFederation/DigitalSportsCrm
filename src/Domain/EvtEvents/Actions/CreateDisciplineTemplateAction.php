<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\DisciplineTemplate;

class CreateDisciplineTemplateAction
{
    /**
     * Execute the action and create a new DisciplineTemplate.
     */
    public function execute(string $name, string $description, array $disciplineIds): DisciplineTemplate
    {
        // Validate the input parameters here, if necessary

        // Create the DisciplineTemplate
        $disciplineTemplate = new DisciplineTemplate;
        $disciplineTemplate->name = $name;
        $disciplineTemplate->description = $description;
        $disciplineTemplate->save();

        // Associate the disciplines with the newly created template
        if (! empty($disciplineIds)) {
            $disciplineTemplate->disciplines()->sync($disciplineIds);
        }

        // Return the newly created template
        return $disciplineTemplate;
    }
}
