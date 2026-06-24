<?php

namespace App\Livewire\Profile;

use Illuminate\View\View;
use Livewire\Component;

class UpdateEntityRegistryPrivacyForm extends Component
{
    public bool $visible_in_club_registry = true;

    public bool $visible_in_diving_service_provider_registry = true;

    public bool $visible_in_map = true;

    public function mount(): void
    {
        $entity = auth()->user()->getEntity();

        abort_unless($entity, 403);

        $this->visible_in_club_registry = (bool) $entity->visible_in_club_registry;
        $this->visible_in_diving_service_provider_registry = (bool) $entity->visible_in_diving_service_provider_registry;
        $this->visible_in_map = (bool) $entity->visible_in_map;
    }

    public function updated(): void
    {
        $this->updateEntityRegistryPrivacy();
    }

    public function updateEntityRegistryPrivacy(): void
    {
        $entity = auth()->user()->getEntity();

        abort_unless($entity, 403);

        $entity->update([
            'visible_in_club_registry' => $this->visible_in_club_registry,
            'visible_in_diving_service_provider_registry' => $this->visible_in_diving_service_provider_registry,
            'visible_in_map' => $this->visible_in_map,
        ]);

        cache()->forget('user:' . auth()->id() . ':primary_entity');

        $this->dispatch('saved');
    }

    public function render(): View
    {
        return view('profile.update-entity-registry-privacy-form');
    }
}
