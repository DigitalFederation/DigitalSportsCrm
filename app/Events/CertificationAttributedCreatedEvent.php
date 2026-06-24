<?php

namespace App\Events;

use Domain\Certifications\Models\CertificationAttributed;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CertificationAttributedCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public CertificationAttributed $certificationAttributed;
    public float $price;

    /**
     * Create a new event instance.
     */
    public function __construct(CertificationAttributed $certificationAttributed, float $price)
    {
        $this->certificationAttributed = $certificationAttributed;
        $this->price = $price;
    }
}
