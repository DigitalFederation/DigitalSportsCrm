{{-- Step 2: Promoting Entity (Section 2) --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

    {{-- Entity Name (auto-filled) --}}
    <div>
        <label class="block text-sm font-medium mb-1">
            {{ __('event_applications.wizard.labels.entity_name') }} <span class="text-rose-500">*</span>
        </label>
        <input type="text" wire:model="formData.entity_name"
               class="form-input w-full bg-gray-50 @error('formData.entity_name') border-rose-300 @enderror"
               readonly>
        @error('formData.entity_name')
            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
        @enderror
    </div>

    {{-- Primary federation number (auto-filled) --}}
    <div>
        <label class="block text-sm font-medium mb-1">
            {{ __('event_applications.wizard.labels.national_federation_number') }}
        </label>
        <input type="text" wire:model="formData.national_federation_number"
               class="form-input w-full bg-gray-50" readonly>
    </div>

    {{-- Entity Address (auto-filled) --}}
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1">
            {{ __('event_applications.wizard.labels.entity_address') }}
        </label>
        <input type="text" wire:model="formData.entity_address"
               class="form-input w-full bg-gray-50" readonly>
    </div>

    {{-- Entity Postal Code (auto-filled) --}}
    <div>
        <label class="block text-sm font-medium mb-1">
            {{ __('event_applications.wizard.labels.entity_postal_code') }}
        </label>
        <input type="text" wire:model="formData.entity_postal_code"
               class="form-input w-full bg-gray-50" readonly>
    </div>

    {{-- Entity Location (auto-filled) --}}
    <div>
        <label class="block text-sm font-medium mb-1">
            {{ __('event_applications.wizard.labels.entity_location') }}
        </label>
        <input type="text" wire:model="formData.entity_location"
               class="form-input w-full bg-gray-50" readonly>
    </div>

    {{-- Entity NIPC (auto-filled) --}}
    <div>
        <label class="block text-sm font-medium mb-1">
            {{ __('event_applications.wizard.labels.entity_nipc') }}
        </label>
        <input type="text" wire:model="formData.entity_nipc"
               class="form-input w-full bg-gray-50" readonly>
    </div>

    {{-- Entity Phone (auto-filled) --}}
    <div>
        <label class="block text-sm font-medium mb-1">
            {{ __('event_applications.wizard.labels.entity_phone') }}
        </label>
        <input type="text" wire:model="formData.entity_phone"
               class="form-input w-full bg-gray-50" readonly>
    </div>

    {{-- Entity Email (auto-filled) --}}
    <div>
        <label class="block text-sm font-medium mb-1">
            {{ __('event_applications.wizard.labels.entity_email') }}
        </label>
        <input type="text" wire:model="formData.entity_email"
               class="form-input w-full bg-gray-50" readonly>
    </div>

    <div class="sm:col-span-2">
        <hr class="border-gray-200 my-2">
        <h4 class="text-sm font-semibold text-slate-700 mt-2">{{ __('event_applications.wizard.sections.event_director') }}</h4>
        <p class="text-xs text-gray-500 mt-1">{{ __('event_applications.wizard.labels.event_director_description') }}</p>
    </div>

    {{-- Event Director Name --}}
    <div>
        <label class="block text-sm font-medium mb-1">
            {{ __('event_applications.wizard.labels.event_director_name') }}
        </label>
        <input type="text" wire:model="formData.event_director_name"
               class="form-input w-full @error('formData.event_director_name') border-rose-300 @enderror"
               placeholder="{{ __('event_applications.wizard.placeholders.event_director_name') }}">
        @error('formData.event_director_name')
            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
        @enderror
    </div>

    {{-- Event Director Phone --}}
    <div>
        <label class="block text-sm font-medium mb-1">
            {{ __('event_applications.wizard.labels.event_director_phone') }}
        </label>
        <input type="tel" wire:model="formData.event_director_phone"
               class="form-input w-full"
               placeholder="{{ __('event_applications.wizard.placeholders.event_director_phone') }}">
    </div>

    {{-- Event Director Email --}}
    <div>
        <label class="block text-sm font-medium mb-1">
            {{ __('event_applications.wizard.labels.event_director_email') }}
        </label>
        <input type="email" wire:model="formData.event_director_email"
               class="form-input w-full @error('formData.event_director_email') border-rose-300 @enderror"
               placeholder="{{ __('event_applications.wizard.placeholders.event_director_email') }}">
        @error('formData.event_director_email')
            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
        @enderror
    </div>

    {{-- Section Comments --}}
    @if($application)
        <div class="sm:col-span-2">
            @include('web.entity.event-applications.components.section-comments', [
                'application' => $application,
                'section' => 'promoting_entity',
            ])
        </div>
    @endif
</div>
