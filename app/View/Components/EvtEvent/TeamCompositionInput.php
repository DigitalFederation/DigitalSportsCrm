<?php

namespace App\View\Components\EvtEvent;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TeamCompositionInput extends Component
{
    public array $initialValue;

    public function __construct($value = null)
    {
        $this->initialValue = is_string($value) ? json_decode($value, true) ?? [] : ($value ?? []);
    }

    public function render(): View|Closure|string
    {
        return view('components.evt-event.team-composition-input', [
            'value' => $this->initialValue,
        ]);
    }
}
