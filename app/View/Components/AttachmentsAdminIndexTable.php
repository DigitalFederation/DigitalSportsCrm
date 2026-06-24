<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AttachmentsAdminIndexTable extends Component
{
    public $attachments;

    /**
     * Create a new component instance.
     */
    public function __construct($attachments)
    {
        $this->attachments = $attachments;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.attachments.attachments-admin-index-table');
    }
}
