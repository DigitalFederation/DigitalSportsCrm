<?php

declare(strict_types=1);

namespace App\Livewire\Entity\PublicPage;

use Domain\Entities\Actions\UpdateEntityPublicSettingsAction;
use Domain\Entities\Models\Entity;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManagePublicPage extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public Entity $entity;

    public string $activeTab = 'general';

    public ?string $publicDescription = '';

    public $entityBackground = null;

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user) {
            abort(403, 'User not authenticated.');
        }

        $hasRole = $user->hasAnyRole(['entity-admin', 'entity-diving-services']);

        if (! $hasRole) {
            abort(403, 'Unauthorized action.');
        }

        $entity = $user->entities()->first();

        if (! $entity) {
            abort(403, 'User is not associated with any entity.');
        }

        $this->entity = $entity;
        $this->publicDescription = $entity->public_description ?? '';
    }

    public function saveGeneralSettings(UpdateEntityPublicSettingsAction $action): void
    {
        $this->authorizeEntityAccess();

        $this->validate([
            'publicDescription' => ['nullable', 'string', 'max:65535'],
            'entityBackground' => ['nullable', 'image', 'max:2048'],
        ]);

        $data = [
            'public_description' => $this->publicDescription,
        ];

        if ($this->entityBackground) {
            $data['entity_background'] = $this->entityBackground;
        }

        $action($this->entity, $data);

        $this->entityBackground = null;

        Notification::make()
            ->title(__('entity.public_page.settings_saved'))
            ->success()
            ->send();
    }

    public function removeBackgroundImage(): void
    {
        $this->authorizeEntityAccess();

        $this->entity->clearMediaCollection('entity-background');

        Notification::make()
            ->title(__('entity.public_page.background_removed'))
            ->success()
            ->send();
    }

    protected function authorizeEntityAccess(): void
    {
        $user = Auth::user();

        if (! $user || ! $user->hasAnyRole(['entity-admin', 'entity-diving-services'])) {
            abort(403, 'Unauthorized action.');
        }

        if (! $user->entities()->where('entity.id', $this->entity->id)->exists()) {
            abort(403, 'User is not associated with this entity.');
        }
    }

    public function render(): View
    {
        return view('livewire.entity.public-page.manage-public-page', [
            'currentBackgroundUrl' => $this->entity->getFirstMediaUrl('entity-background', 'thumb'),
        ]);
    }
}
