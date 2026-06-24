<?php

declare(strict_types=1);

namespace App\Livewire\Entity;

use Domain\Individuals\Models\Individual;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditMemberPhoto extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public Individual $individual;

    public $photo;

    public bool $showEditor = false;

    #[On('toggle-member-photo-editor')]
    public function onToggleEditor(): void
    {
        $this->toggleEditor();
    }

    protected function rules(): array
    {
        return [
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    protected function messages(): array
    {
        return [
            'photo.required' => __('profile.photo_required'),
            'photo.image' => __('profile.photo_must_be_image'),
            'photo.mimes' => __('profile.photo_format'),
            'photo.max' => __('profile.photo_max_size'),
        ];
    }

    public function mount(Individual $individual): void
    {
        $this->authorizeEntityAccess($individual);
        $this->individual = $individual;
    }

    public function updatedPhoto(): void
    {
        $this->validateOnly('photo');
    }

    public function toggleEditor(): void
    {
        $this->showEditor = ! $this->showEditor;
        $this->photo = null;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->authorizeEntityAccess($this->individual);
        $this->validate();

        $this->individual->clearMediaCollection('profile');
        $this->individual->addMedia($this->photo->getRealPath())
            ->usingFileName($this->photo->getClientOriginalName())
            ->toMediaCollection('profile', 'secure-media');

        $this->photo = null;
        $this->showEditor = false;

        Notification::make()
            ->title(__('profile.photo_updated_success'))
            ->success()
            ->send();

        $this->dispatch('profile-photo-updated');
    }

    public function removePhoto(): void
    {
        $this->authorizeEntityAccess($this->individual);

        $this->individual->clearMediaCollection('profile');

        $this->photo = null;
        $this->showEditor = false;

        Notification::make()
            ->title(__('profile.photo_removed_success'))
            ->success()
            ->send();

        $this->dispatch('profile-photo-updated');
    }

    protected function authorizeEntityAccess(Individual $individual): void
    {
        $user = Auth::user();

        if (! $user || ! $user->hasAnyRole(['entity-admin', 'entity-diving-services'])) {
            abort(403, 'Unauthorized action.');
        }

        $entityId = $user->getEntityId();

        if (! $entityId) {
            abort(403, 'User is not associated with any entity.');
        }

        $belongsToEntity = $individual->individualEntities()
            ->where('entity_id', $entityId)
            ->exists();

        if (! $belongsToEntity) {
            abort(403, 'Individual does not belong to your entity.');
        }
    }

    public function render(): View
    {
        return view('livewire.entity.edit-member-photo');
    }
}
