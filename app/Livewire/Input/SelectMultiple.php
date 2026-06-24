<?php

namespace App\Livewire\Input;

use Livewire\Component;

/**
 * Selectmultiple Component.
 *
 * This Livewire component provides an enhanced select input using the Choices.js library.
 * It can be used in multiple instances on a single page by utilizing an identifier to distinguish events.
 */
class SelectMultiple extends Component
{
    /** @var array List of selectable items */
    public $items;

    /** @var string Name attribute for the select input */
    public $inputName;

    /** @var string ID attribute for the select input */
    public $inputId;

    /** @var array Currently selected items */
    public $inputSelected = [];

    /** @var bool Determines if multiple selections are allowed */
    public $multiple = true;

    /** @var array Default options for the Choices.js library */
    public $options = [
        'removeItems' => true,
        'removeItemButton' => true,
        'maxItemCount' => -1,
        'maxItemText' => "You can't add more items",
        'shouldSort' => false,
        'shouldSortItems' => false,
    ];

    /** @var array Extra options that can be merged with the default options */
    public $extraOptions = [];

    /**
     * Identifier for the select input.
     * Useful when multiple instances of this component are used on the same page.
     *
     * @var string
     */
    public $identifier = 'default';

    /**
     * Render the Livewire component.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        // Merge default options with any additional options provided.
        $mergedOptions = array_merge($this->options, $this->extraOptions);

        // Convert merged options to JSON for use with AlpineJS/Choices.js.
        $jsonOptions = json_encode($mergedOptions);

        return view('livewire.input.select-multiple', ['jsonOptions' => $jsonOptions]);
    }

    /**
     * Handle updates to the input selection.
     *
     * Whenever the selected items change, this method dispatches a namespaced event
     * based on the component's identifier. This ensures that each instance of the component
     * on a page can have its events handled independently.
     *
     * @param  $value  updated selection.
     */
    public function updatedInputSelected($value): void
    {
        // Create a namespaced event based on the component's identifier.
        $eventName = 'selectedMultipleUpdatedValue.'.$this->identifier;
        // Dispatch the event with the updated selection.
        $this->dispatch($eventName, values: $this->inputSelected);
    }
}
