<?php

namespace App\View\Components;

use Illuminate\View\Component;

class UxBadgeComponent extends Component
{
    public $color;

    public $status;

    private $colorClasses = [
        'green' => 'green-600',
        'red' => 'red-600',
        'blue' => 'blue-600',
        'yellow' => 'yellow-400',
        'slate' => 'slate-400',
        'gray' => 'gray-400',
        'default' => 'slate-500',
    ];

    public function __construct($status, $color = null)
    {
        $this->status = $status;
        $this->color = $color ?? $this->defaultColorForStatus($status);
    }

    private function defaultColorForStatus($status)
    {
        return $this->colorClasses[$status] ?? $this->colorClasses['default'];
    }

    public function render()
    {
        return view('components.ux-badge-component');
    }
}
