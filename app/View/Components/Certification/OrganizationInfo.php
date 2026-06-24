<?php

namespace App\View\Components\Certification;

use Illuminate\View\Component;

class OrganizationInfo extends Component
{
    public $certification;
    public $federation;
    public $country;

    public function __construct($certification)
    {
        $this->certification = $certification;
        $this->federation = $certification->federation;
        $this->country = $certification->federation?->country;
    }

    public function render()
    {
        return view('components.certification.organization-info');
    }
}
