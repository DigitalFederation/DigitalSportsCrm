<?php

declare(strict_types=1);

namespace App\Livewire\Admin\EvtEvents;

use Domain\EvtEvents\Actions\UpdateOrganizationEventHeroImageAction;
use Domain\EvtEvents\Actions\UpdateSportHeroImageAction;
use Domain\EvtEvents\Models\Sport;
use Domain\Federations\Models\Federation;
use Filament\Notifications\Notification;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class EventImagesManager extends Component
{
    use WithFileUploads;

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null> */
    public array $sportImages = [];

    public $organizationImage = null;

    public function mount(): void
    {
        $this->sportImages = [];
    }

    public function uploadSportImage(int $sportId, UpdateSportHeroImageAction $action): void
    {
        $this->validate([
            "sportImages.{$sportId}" => ['required', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $sport = Sport::findOrFail($sportId);
        $action->execute($sport, $this->sportImages[$sportId]);
        $this->sportImages[$sportId] = null;

        Notification::make()
            ->title(__('events.image_upload_success'))
            ->success()
            ->send();
    }

    public function removeSportImage(int $sportId): void
    {
        $sport = Sport::findOrFail($sportId);
        $sport->clearMediaCollection('hero-image');

        Notification::make()
            ->title(__('events.image_removed_success'))
            ->success()
            ->send();
    }

    public function uploadOrganizationImage(UpdateOrganizationEventHeroImageAction $action): void
    {
        $this->validate([
            'organizationImage' => ['required', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $action->execute($this->organizationImage);
        $this->organizationImage = null;

        Notification::make()
            ->title(__('events.image_upload_success'))
            ->success()
            ->send();
    }

    public function removeOrganizationImage(): void
    {
        $federation = Federation::where('is_default_federation', true)->firstOrFail();
        $federation->clearMediaCollection('organization-event-hero');

        Notification::make()
            ->title(__('events.image_removed_success'))
            ->success()
            ->send();
    }

    public function render(): View
    {
        $sports = Sport::orderBy('name')->get();

        $sportMedia = [];
        foreach ($sports as $sport) {
            $sportMedia[$sport->id] = $sport->getFirstMediaUrl('hero-image');
        }

        $federation = Federation::where('is_default_federation', true)->first();
        $organizationMediaUrl = $federation?->getFirstMediaUrl('organization-event-hero') ?? '';

        return view('livewire.admin.evt-events.event-images-manager', [
            'sports' => $sports,
            'sportMedia' => $sportMedia,
            'organizationMediaUrl' => $organizationMediaUrl,
        ]);
    }
}
