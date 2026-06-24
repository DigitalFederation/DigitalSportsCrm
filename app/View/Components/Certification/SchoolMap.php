<?php

namespace App\View\Components\Certification;

use Illuminate\View\Component;

class SchoolMap extends Component
{
    public $latitude;
    public $longitude;
    public $name;
    public $address;
    public $zoom;

    public function __construct($entity)
    {
        $this->latitude = $entity->lat;
        $this->longitude = $entity->lng;
        $this->name = $entity->name;
        $this->address = $entity->address;
        $this->zoom = 13; // Good zoom level for a school location
    }

    public function shouldRender(): bool
    {
        return $this->latitude && $this->longitude;
    }

    public function render()
    {
        return view('components.certification.school-map');
    }
}
