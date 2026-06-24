<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LivewireConfirmationModal extends Component
{
    public $isOpen;

    public $title;

    public $message;

    public $confirmMethod;

    public $cancelMethod;

    public $confirmText;

    public $cancelText;

    public $buttonColor;

    /**
     * Create a new component instance.
     */
    public function __construct($isOpen, $title, $message, $confirmMethod, $cancelMethod, $confirmText, $cancelText, $buttonColor = 'bg-red-500')
    {
        $this->isOpen = $isOpen;
        $this->title = $title;
        $this->message = $message;
        $this->confirmMethod = $confirmMethod;
        $this->cancelMethod = $cancelMethod;
        $this->confirmText = $confirmText;
        $this->cancelText = $cancelText;
        $this->buttonColor = $buttonColor;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.livewire-confirmation-modal');
    }
}
