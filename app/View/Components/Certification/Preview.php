<?php

namespace App\View\Components\Certification;

use Illuminate\View\Component;

class Preview extends Component
{
    public $certification;
    public $showFront = true;
    public $canFlip = true;

    public function __construct($certification, $canFlip = true)
    {
        $this->certification = $certification;
        $this->canFlip = $canFlip;
    }

    public function render()
    {
        return view('components.certification.preview');
    }
}
