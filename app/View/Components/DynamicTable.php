<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class DynamicTable extends Component
{
    public array $headers;
    public bool $displayableHeaders;
    public array $sortableFields;
    public string $currentSortField;
    public string $currentSortDirection;

    public function __construct(array $headers, bool $displayableHeaders = true)
    {
        $this->displayableHeaders = $displayableHeaders;
        $this->headers = array_map(function ($header) {
            if (is_string($header)) {
                // Convert simple string headers to the full array format, assuming non-sortable by default
                return [
                    'text' => $header,
                    'field' => Str::slug($header, '_'),
                    'sortable' => false,
                    'alignment' => 'text-left',
                ];

            } elseif (is_array($header)) {
                // Ensure all keys exist with default values if not provided
                $defaults = [
                    'text' => '',
                    'field' => $header['field'] ?? Str::slug($header['text'] ?? '', '_'), // Use text for field if not provided
                    'sortable' => false,
                    'alignment' => 'text-left',
                ];

                return array_merge($defaults, $header);
            }
        }, $headers);

        $this->sortableFields = array_filter($this->headers, fn ($header) => $header['sortable']);
        // Determine the current sort field and direction from the request
        $this->currentSortField = ltrim(request('sort', ''), '-');
        $this->currentSortDirection = Str::startsWith(request('sort', ''), '-') ? 'desc' : 'asc';

    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.dynamic-table');
    }
}
