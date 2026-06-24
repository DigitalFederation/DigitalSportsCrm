<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class GlobalAttributeFormInput extends Component
{
    public array $globalAttributes;
    public array $values;

    public function __construct(array $globalAttributes = [], array $values = [])
    {
        $this->globalAttributes = $globalAttributes;
        $this->values = $values;
    }

    public function render(): View|Closure|string
    {
        return view('components.global-attribute-form-input');
    }
}
