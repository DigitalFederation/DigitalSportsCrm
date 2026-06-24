<?php

namespace App\Livewire\Profile;

use Illuminate\View\View;
use Livewire\Component;

class UpdateRegistryPrivacyForm extends Component
{
    public bool $visible_in_coach_registry = true;

    public bool $visible_in_technical_official_registry = true;

    public bool $visible_in_diving_professional_registry = true;

    public function mount(): void
    {
        $individual = auth()->user()->individual;

        abort_unless($individual, 403);

        $this->visible_in_coach_registry = (bool) $individual->visible_in_coach_registry;
        $this->visible_in_technical_official_registry = (bool) $individual->visible_in_technical_official_registry;
        $this->visible_in_diving_professional_registry = (bool) $individual->visible_in_diving_professional_registry;
    }

    public function updated(): void
    {
        $this->updateRegistryPrivacy();
    }

    public function updateRegistryPrivacy(): void
    {
        $individual = auth()->user()->individual;

        abort_unless($individual, 403);

        $individual->update([
            'visible_in_coach_registry' => $this->visible_in_coach_registry,
            'visible_in_technical_official_registry' => $this->visible_in_technical_official_registry,
            'visible_in_diving_professional_registry' => $this->visible_in_diving_professional_registry,
        ]);

        $this->dispatch('saved');
    }

    public function render(): View
    {
        return view('profile.update-registry-privacy-form');
    }
}
