<?php

namespace App\Livewire\Individual;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class CompleteProfilePhoto extends Component
{
    use WithFileUploads;

    public $photo;

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

    public function updatedPhoto(): void
    {
        $this->validateOnly('photo');
    }

    public function save(): void
    {
        $this->validate();

        $user = auth()->user();
        $individual = $user->individual;

        if (! $individual) {
            session()->flash('error', __('profile.no_individual_profile'));

            return;
        }

        // Clear existing profile photo and add the new one
        $individual->clearMediaCollection('profile');
        $individual->addMedia($this->photo->getRealPath())
            ->usingFileName($this->photo->getClientOriginalName())
            ->toMediaCollection('profile', 'secure-media');

        session()->flash('success', __('profile.photo_uploaded_success'));

        // Redirect to the dashboard after successful upload
        $this->redirect(route('individual.dashboard'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.individual.complete-profile-photo')
            ->layout('layouts.guest');
    }
}
