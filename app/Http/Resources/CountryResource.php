<?php

namespace App\Http\Resources;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $country = $this->resource;
        assert($country instanceof Country);

        return [
            'id' => $country->id,
            'name' => $country->name,
            'ioc' => $country->ioc,
            'region_name' => $country->region_name,
            'sub_region_name' => $country->sub_region_name,
            'supported' => $country->supported,
            'lat' => $country->lat,
            'lng' => $country->lng,
            'districts' => $this->whenLoaded('districts'),
        ];
    }
}
