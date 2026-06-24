<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LicenseAttributedCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $licenseAttributed;
    public bool $isSelfRequest;
    /**
     * Create a new event instance.
     */
    public function __construct(array $licenseAttributed, $isSelfRequest)
    {
        $this->licenseAttributed = $licenseAttributed;
        $this->isSelfRequest = $isSelfRequest;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
