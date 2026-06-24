<div class="p-6 bg-white rounded-lg shadow">
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">{{ __('events.images_configuration') }}</h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('events.images_configuration_subtitle') }}
        </p>
    </div>

    {{-- Sport Hero Images --}}
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('events.sport_hero_image') }}</h3>
        <div class="space-y-4">
            @foreach ($sports as $sport)
                <div wire:key="sport-{{ $sport->id }}" class="flex items-center gap-4 p-4 border border-gray-200 rounded-lg">
                    {{-- Sport Name --}}
                    <div class="w-48 flex-shrink-0">
                        <span class="font-medium text-gray-700">{{ $sport->translatedName }}</span>
                    </div>

                    {{-- Current Image Preview --}}
                    <div class="w-24 h-16 flex-shrink-0 bg-gray-100 rounded overflow-hidden">
                        @if (!empty($sportMedia[$sport->id]))
                            <img src="{{ $sportMedia[$sport->id] }}" alt="{{ $sport->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="flex items-center justify-center h-full text-xs text-gray-400">
                                {{ __('events.no_image_uploaded') }}
                            </div>
                        @endif
                    </div>

                    {{-- File Input --}}
                    <div class="flex-1">
                        <input type="file"
                               wire:model="sportImages.{{ $sport->id }}"
                               accept="image/jpeg,image/png,image/webp"
                               class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        @error("sportImages.{$sport->id}")
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror

                        <div wire:loading wire:target="sportImages.{{ $sport->id }}" class="mt-1 text-xs text-blue-600">
                            {{ __('events.uploading') }}...
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-2 flex-shrink-0">
                        @if (isset($sportImages[$sport->id]) && $sportImages[$sport->id])
                            <button wire:click="uploadSportImage({{ $sport->id }})"
                                    wire:loading.attr="disabled"
                                    class="btn btn-sm btn-primary">
                                {{ __('events.upload_image') }}
                            </button>
                        @endif

                        @if (!empty($sportMedia[$sport->id]))
                            <button wire:click="removeSportImage({{ $sport->id }})"
                                    wire:confirm="{{ __('events.confirm_remove_image') }}"
                                    wire:loading.attr="disabled"
                                    class="btn btn-sm btn-danger">
                                {{ __('events.remove_image') }}
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Organization Event Hero Image --}}
    <div>
        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('events.organization_event_image') }}</h3>
        <div class="flex items-center gap-4 p-4 border border-gray-200 rounded-lg">
            {{-- Label --}}
            <div class="w-48 flex-shrink-0">
                <span class="font-medium text-gray-700">{{ __('events.organization_event_image') }}</span>
            </div>

            {{-- Current Image Preview --}}
            <div class="w-24 h-16 flex-shrink-0 bg-gray-100 rounded overflow-hidden">
                @if (!empty($organizationMediaUrl))
                    <img src="{{ $organizationMediaUrl }}" alt="{{ __('events.organization_event') }}" class="w-full h-full object-cover">
                @else
                    <div class="flex items-center justify-center h-full text-xs text-gray-400">
                        {{ __('events.no_image_uploaded') }}
                    </div>
                @endif
            </div>

            {{-- File Input --}}
            <div class="flex-1">
                <input type="file"
                       wire:model="organizationImage"
                       accept="image/jpeg,image/png,image/webp"
                       class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                @error('organizationImage')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror

                <div wire:loading wire:target="organizationImage" class="mt-1 text-xs text-blue-600">
                    {{ __('events.uploading') }}...
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-2 flex-shrink-0">
                @if ($organizationImage)
                    <button wire:click="uploadOrganizationImage"
                            wire:loading.attr="disabled"
                            class="btn btn-sm btn-primary">
                        {{ __('events.upload_image') }}
                    </button>
                @endif

                @if (!empty($organizationMediaUrl))
                    <button wire:click="removeOrganizationImage"
                            wire:confirm="{{ __('events.confirm_remove_image') }}"
                            wire:loading.attr="disabled"
                            class="btn btn-sm btn-danger">
                        {{ __('events.remove_image') }}
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Image Requirements Note --}}
    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
        <p class="text-sm text-blue-700">{{ __('events.image_requirements') }}</p>
    </div>
</div>
