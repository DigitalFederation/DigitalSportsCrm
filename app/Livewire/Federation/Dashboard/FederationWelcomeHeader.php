<?php

namespace App\Livewire\Federation\Dashboard;

use Domain\Federations\Models\Federation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class FederationWelcomeHeader extends Component
{
    public ?Federation $federation = null;

    public function mount(): void
    {
        $this->federation = Auth::user()->federations()->first();
    }

    public function render()
    {
        return view('livewire.federation.dashboard.federation-welcome-header');
    }
}
