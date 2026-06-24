<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CardFormStatusMessage extends Component
{
    public $message_type;

    public $message_title;

    public $message_body;

    /**
     * Create a new component instance.
     */
    public function __construct($message_type, $message_title, $message_body)
    {
        $this->message_type = $message_type;
        $this->message_title = $message_title;
        $this->message_body = $message_body;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.forms.card-form-message');
    }
}
