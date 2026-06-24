<x-form-section submit="updateEntityRegistryPrivacy">
    <x-slot name="title">
        {{ __('profile.entity_privacy_settings') }}
    </x-slot>

    <x-slot name="description">
        {{ __('profile.entity_privacy_settings_description') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6">
            <div class="space-y-4">
                <label class="flex items-center">
                    <x-checkbox wire:model.live="visible_in_club_registry" />
                    <span class="ml-2 text-sm text-gray-600">{{ __('profile.visible_in_club_registry') }}</span>
                </label>

                <label class="flex items-center">
                    <x-checkbox wire:model.live="visible_in_diving_service_provider_registry" />
                    <span class="ml-2 text-sm text-gray-600">{{ __('profile.visible_in_diving_service_provider_registry') }}</span>
                </label>

                <label class="flex items-center">
                    <x-checkbox wire:model.live="visible_in_map" />
                    <span class="ml-2 text-sm text-gray-600">{{ __('profile.visible_in_map') }}</span>
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
