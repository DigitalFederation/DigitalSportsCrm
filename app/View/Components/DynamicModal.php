<?php

namespace App\View\Components;

use Illuminate\View\Component;

/**
 * Dynamic Modal Component
 *
 * To use this component, you can include it in your Blade templates as follows:
 *
 * ```php
 * <x-dynamic-modal
 *     :viewName="'your-view-name'"
 *     :params="['key' => 'value']"
 *     buttonLabel="Open Modal"
 *     buttonClass="btn btn-primary"
 *     :isLivewire="true/false"
 *     animation="transition ease-in duration-200"
 * />
 * ```
 *
 * @param  string  $viewName  The Blade view name or Livewire component name to load in the modal
 * @param  array  $params  Parameters to pass to the view or Livewire component
 * @param  string  $buttonLabel  Label for the modal-opening button
 * @param  string  $buttonClass  CSS classes for the modal-opening button
 * @param  bool  $isLivewire  Whether the view is a Livewire component
 * @param  string  $animation  AlpineJS transition classes for modal animation
 */
class DynamicModal extends Component
{
    public $viewName;

    public $params;

    public $buttonLabel;

    public $buttonClass;

    public $isLivewire;

    public $animation;

    public $headerView;

    public $headerTitle;

    public $iconComponent;

    public function __construct(
        $viewName,
        $params = [],
        $buttonLabel = 'Open Modal',
        $buttonClass = 'btn btn-outline btn-xs cursor-pointer',
        $isLivewire = false,
        $animation = 'transition ease-in duration-200',
        $headerView = true,
        $headerTitle = '',
        $iconComponent = 'svg.input-cursor-text' // Default icon component
    ) {
        $this->viewName = $viewName;
        $this->params = $params;
        $this->buttonLabel = $buttonLabel;
        $this->buttonClass = $buttonClass;
        $this->isLivewire = $isLivewire;
        $this->animation = $animation;
        $this->headerView = $headerView;
        $this->headerTitle = $headerTitle;
        $this->iconComponent = $iconComponent;
    }

    public function render()
    {
        return view('components.dynamic-modal', [
            'content' => $this->isLivewire ? null : view($this->viewName, $this->params)->render(),
        ]);
    }
}
