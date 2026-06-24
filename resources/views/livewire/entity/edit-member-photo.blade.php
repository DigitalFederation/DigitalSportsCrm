<div>
    @if($showEditor)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('profile.edit_photo') }}</h3>
                <button type="button" wire:click="toggleEditor" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="flex flex-col items-center gap-4">
                {{-- Photo Preview --}}
                @if($photo)
                    <img src="{{ $photo->temporaryUrl() }}"
                         alt="{{ __('profile.photo_preview') }}"
                         class="w-28 h-28 rounded-full object-cover border-2 border-primary shadow">
                @else
                    <x-secure-profile-image :individual="$individual" size="thumb"
                        class="w-28 h-28 rounded-full object-cover border-2 border-gray-300 shadow" />
                @endif

                {{-- Upload Input --}}
                <div class="flex flex-col items-center gap-2 w-full">
                    <label for="member-photo-{{ $individual->id }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg cursor-pointer hover:bg-primary-dark transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        {{ __('profile.choose_photo') }}
                        <input id="member-photo-{{ $individual->id }}"
                               wire:model="photo"
                               type="file"
                               class="sr-only"
                               accept="image/jpeg,image/png,image/jpg">
                    </label>
                    <p class="text-xs text-gray-500">{{ __('profile.photo_requirements') }}</p>
                    @error('photo')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Loading State --}}
                <div wire:loading wire:target="photo" class="flex items-center gap-2 text-gray-600">
                    <svg class="animate-spin h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm">{{ __('profile.uploading') }}</span>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-3 w-full justify-center">
                    <button type="button"
                            wire:click="save"
                            wire:loading.attr="disabled"
                            wire:target="save"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            {{ !$photo ? 'disabled' : '' }}>
                        <span wire:loading.remove wire:target="save">{{ __('profile.save_photo') }}</span>
                        <span wire:loading wire:target="save" class="flex items-center gap-1">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ __('profile.saving') }}
                        </span>
                    </button>

                    @if($individual->getFirstMediaUrl('profile'))
                        <button type="button"
                                wire:click="removePhoto"
                                wire:confirm="{{ __('profile.confirm_remove_photo') }}"
                                wire:loading.attr="disabled"
                                wire:target="removePhoto"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-700 text-sm font-medium rounded-lg hover:bg-red-100 border border-red-200 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            {{ __('main.remove') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
