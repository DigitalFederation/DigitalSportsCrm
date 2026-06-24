<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ConfirmationModal extends Component
{
    public $title;
    public $content;
    public $action;
    public $isLivewireAction = true;

    /**
     * Create a new component instance.
     */
    public function __construct($action, $title, $content, $isLivewireAction)
    {
        $this->action = $action;
        $this->title = $title;
        $this->content = $content;
        $this->isLivewireAction = $isLivewireAction;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.confirmation-modal');
    }
}
