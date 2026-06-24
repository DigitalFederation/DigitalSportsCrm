<?php

namespace App\Livewire;

use Livewire\Component;

class BellNotifications extends Component
{
    public $user;

    public $notifications;

    public $new_notifications = false;

    public function render()
    {
        $this->getNewNotifications();

        if (count($this->notifications) > 0) {
            $this->new_notifications = true;
        } else {
            $this->new_notifications = false;
        }

        return view('livewire.bell-notifications');
    }

    // get new notifications from laravel notifications table
    public function getNewNotifications()
    {
        $this->notifications = $this->user->unreadNotifications;
    }

    public function markAsRead($id): void
    {
        $notification = $this->user->notifications->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
            $this->refreshNotifications(); // Directly call the method to refresh notifications
        }
    }

    public function markAllAsRead(): void
    {
        $this->user->unreadNotifications->markAsRead();
        $this->refreshNotifications(); // Directly call the method to refresh notifications
    }

    public function refreshNotifications(): void
    {
        $this->user->refresh();
    }
}
