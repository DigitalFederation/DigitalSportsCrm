<?php

namespace App\Livewire;

use Livewire\Component;

class AttributeTypeSelect extends Component
{
    public $options = [];

    public function mount($options)
    {
        $this->options = $options ?? [];
    }

    public function addOption()
    {
        $this->options[] = '';
    }

    public function removeOption($index)
    {
        unset($this->options[$index]);
        $this->options = array_values($this->options);
    }

    public function render()
    {
        return view('livewire.attribute-type-select');
    }
}
