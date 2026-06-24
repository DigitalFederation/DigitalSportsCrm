<?php

namespace App\Livewire\Admin\Dashboard;

use Domain\Federations\Models\Federation;
use Livewire\Component;

class AdminWelcomeHeader extends Component
{
    public ?Federation $federation = null;

    public function mount(): void
    {
        $this->federation = Federation::where('is_default_federation', true)->first();
    }

    public function render()
    {
        return view('livewire.admin.dashboard.admin-welcome-header');
    }
}
