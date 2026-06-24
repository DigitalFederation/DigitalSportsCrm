<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DynamicTableButtons extends Component
{
    public $type;

    public $route;

    public $method;

    public $classes;

    public $svg;

    public $confirmText;

    public $svgClass;

    public $target;

    public function __construct($type, $route, $method = 'GET', $classes = null, $svg = null, $confirmText = null, $svgClass = null, $target = null)
    {
        $this->type = $type;
        $this->route = $route;
        $this->method = strtoupper($method);
        $this->classes = $classes;
        $this->svg = $svg;
        $this->confirmText = $confirmText ?? __('main.confirm_action');
        $this->svgClass = $svgClass ?? 'h-5 w-5';
        $this->target = $target ?? '_self';
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.dynamic-table-buttons');
    }

    public function getClassForType($type)
    {
        $classMap = [
            'show' => 'text-slate-500 hover:text-slate-800',
            'accept' => 'text-green-500 hover:text-green-800',
            'reject' => 'text-red-500 hover:text-red-800',
            'edit' => 'text-slate-500 hover:text-slate-800',
            'delete' => 'text-red-500 hover:text-red-800',
            'disassociate' => 'text-red-500 hover:text-red-800',
            'files' => 'text-purple-500 hover:text-purple-800',
            'document' => 'text-slate-500 hover:text-slate-800',
            'document.pdf' => 'text-slate-500 hover:text-slate-800',
            'document.moloni' => 'text-blue-500 hover:text-blue-800',
            'download' => 'text-slate-500 hover:text-slate-800',
            'duplicate' => 'text-slate-500 hover:text-slate-800',
            'copy' => 'text-slate-500 hover:text-slate-800',
            'permissions' => 'text-emerald-600 hover:text-emerald-900',
        ];

        return $classMap[$type] ?? 'text-slate-500 hover:text-slate-800';
    }

    public function getDefaultSvg($type)
    {
        $svgs = [
            'show' => 'components.svg.eye',
            'accept' => 'components.svg.check',
            'reject' => 'components.svg.x-circle',
            'edit' => 'components.svg.edit',
            'delete' => 'components.svg.trash',
            'disassociate' => 'components.svg.user-minus',
            'files' => 'components.svg.files',
            'document' => 'components.svg.filetype-all',
            'document.pdf' => 'components.svg.filetype-pdf',
            'document.moloni' => 'components.svg.receipt',
            'download' => 'components.svg.box-arrow-down',
            'duplicate' => 'components.svg.duplicate',
            'copy' => 'components.svg.clipboard',
            'permissions' => 'components.svg.key',
        ];

        return $svgs[$type] ?? 'components.svg.eye';
    }
}
