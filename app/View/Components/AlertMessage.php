<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AlertMessage extends Component
{
    public ?string $bgColor = null;

    public ?string $message = null;

    public function __construct()
    {
        $types = ['success', 'error', 'warning', 'info'];

        foreach ($types as $type) {
            if (session()->has($type)) {
                $this->message = session($type);

                $this->bgColor = match ($type) {
                    'success' => 'bg-green-600',
                    'error' => 'bg-red-600',
                    'warning' => 'bg-yellow-600',
                    default => 'bg-blue-600',
                };

                break;
            }
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.alert-message');
    }
}
