<?php

namespace App\Livewire;

use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\AttributeRules;
use Livewire\Component;
use Livewire\WithPagination;

class AttributeRulesIndexTable extends Component
{
    use WithPagination;

    public $attributeId = null;

    public function mount(?Attribute $attribute = null)
    {
        $this->attributeId = optional($attribute)->id;
    }

    public function render()
    {
        $attributeRules = AttributeRules::where('attribute_id', $this->attributeId)->paginate();
        $attribute = Attribute::find($this->attributeId);

        return view('livewire.attribute-rules-index-table', compact('attributeRules', 'attribute'));
    }
}
