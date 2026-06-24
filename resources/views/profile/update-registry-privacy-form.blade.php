<x-form-section submit="updateRegistryPrivacy">
    <x-slot name="title">
        {{ __('profile.privacy_settings') }}
    </x-slot>

    <x-slot name="description">
        {{ __('profile.privacy_settings_description') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6">
            <div class="space-y-4">
                <label class="flex items-center">
                    <x-checkbox wire:model.live="visible_in_coach_registry" />
                    <span class="ml-2 text-sm text-gray-600">{{ __('profile.visible_in_coach_registry') }}</span>
                </label>

                <label class="flex items-center">
                    <x-checkbox wire:model.live="visible_in_technical_official_registry" />
                    <span class="ml-2 text-sm text-gray-600">{{ __('profile.visible_in_technical_official_registry') }}</span>
                </label>

                <label class="flex items-center">
                    <x-checkbox wire:model.live="visible_in_diving_professional_registry" />
                    <span class="ml-2 text-sm text-gray-600">{{ __('profile.visible_in_diving_professional_registry') }}</span>
                </label>
            </div>
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button>
            {{ __('Save') }}
        </x-button>
    </x-slot>
</x-form-section>
