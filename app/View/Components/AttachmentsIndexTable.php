<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AttachmentsIndexTable extends Component
{
    public $attachments;

    /**
     * Create a new component instance.
     */
    public function __construct($attachments)
    {
        $this->attachments = $attachments->map(function ($attachment) {
            $attachment->language_name = $attachment->language_id === null ? 'All Languages' : ($attachment->language?->name ?? 'N/A');

            // Ensure owner is loaded
            if (! $attachment->relationLoaded('owner')) {
                $attachment->load('owner');
            }

            return $attachment;
        });
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.attachments.attachments-index-table');
    }
}
