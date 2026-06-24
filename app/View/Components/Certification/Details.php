<?php

namespace App\View\Components\Certification;

use Illuminate\View\Component;

class Details extends Component
{
    public $certification;

    public function __construct($certification)
    {
        $this->certification = $certification;
    }

    public function render()
    {
        return view('components.certification.details');
    }
}
