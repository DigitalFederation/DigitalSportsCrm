<?php

namespace Domain\EventApplications\Actions;

use Domain\EventApplications\Models\ApplicationTemplate;

class UpdateTemplateStateAction
{
    public function execute(ApplicationTemplate $template, string $state): ApplicationTemplate
    {
        $validStates = ['draft', 'open', 'closed', 'archived'];

        if (! in_array($state, $validStates)) {
            throw new \InvalidArgumentException("Invalid state: {$state}. Must be one of: ".implode(', ', $validStates));
        }

        $template->update(['state' => $state]);

        return $template->fresh();
    }
}
