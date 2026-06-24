<?php

namespace App\View\Components\Entity;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class EntityIndividualSelector extends Component
{
    public $inputId;
    public $entityId;
    public $wireModel;

    public function __construct($inputId, $entityId, $wireModel = null)
    {
        $this->inputId = $inputId;
        $this->entityId = $entityId;
        $this->wireModel = $wireModel;
    }

    public function render(): View
    {
        return view('components.entity.entity-individual-selector');
    }
}
