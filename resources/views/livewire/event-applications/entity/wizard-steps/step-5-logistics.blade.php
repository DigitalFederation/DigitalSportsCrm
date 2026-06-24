{{-- Step 5: Logistics - Facilities, Accommodations, Transport, Food (Sections 5 + 6) --}}
{{-- Facilities Checklist --}}
<div class="mb-6">
    <h4 class="text-sm font-semibold text-slate-700">{{ __('event_applications.wizard.sections.facilities') }}</h4>
    <p class="text-xs text-gray-500 mt-1 mb-3">{{ __('event_applications.wizard.sections.facilities_description') }}</p>
    <div class="grid grid-cols-1 gap-3">
        @foreach([
            'ILE1' => 'event_applications.wizard.checklist_items.ILE1',
            'ILE2' => 'event_applications.wizard.checklist_items.ILE2',
            'ILE3' => 'event_applications.wizard.checklist_items.ILE3',
            'ILE4' => 'event_applications.wizard.checklist_items.ILE4',
            'ILE5' => 'event_applications.wizard.checklist_items.ILE5',
            'ILE6' => 'event_applications.wizard.checklist_items.ILE6',
            'ILE7' => 'event_applications.wizard.checklist_items.ILE7',
            'ILE8' => 'event_applications.wizard.checklist_items.ILE8',
            'ILE9' => 'event_applications.wizard.checklist_items.ILE9',
            'ILE10' => 'event_applications.wizard.checklist_items.ILE10',
        ] as $key => $label)
            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" wire:model="formData.facilities_checklist.{{ $key }}"
                       class="form-checkbox rounded text-primary-600" value="1">
                <span class="text-sm text-gray-700">{{ __($label) }}</span>
            </label>
        @endforeach
    </div>
</div>

{{-- Other Facilities --}}
<div class="mb-6">
    <label class="block text-sm font-medium mb-1">{{ __('event_applications.wizard.labels.other_facilities') }}</label>
    <textarea wire:model="formData.other_facilities" rows="2" class="form-textarea w-full"
              placeholder="{{ __('event_applications.wizard.placeholders.other_facilities') }}"></textarea>
</div>

<hr class="border-gray-200 my-6">

{{-- Accommodation Checklist --}}
<div class="mb-6">
    <h4 class="text-sm font-semibold text-slate-700">{{ __('event_applications.wizard.sections.accommodations') }}</h4>
    <p class="text-xs text-gray-500 mt-1 mb-3">{{ __('event_applications.wizard.sections.accommodations_description') }}</p>
    <div class="grid grid-cols-1 gap-3">
        @foreach([
            'ATA1' => 'event_applications.wizard.checklist_items.ATA1',
            'ATA2' => 'event_applications.wizard.checklist_items.ATA2',
            'ATA3' => 'event_applications.wizard.checklist_items.ATA3',
            'ATA4' => 'event_applications.wizard.checklist_items.ATA4',
        ] as $key => $label)
            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" wire:model="formData.logistics_checklist.{{ $key }}"
                       class="form-checkbox rounded text-primary-600" value="1">
                <span class="text-sm text-gray-700">{{ __($label) }}</span>
            </label>
        @endforeach
    </div>
</div>

{{-- Transport Checklist --}}
<div class="mb-6">
    <h4 class="text-sm font-semibold text-slate-700">{{ __('event_applications.wizard.sections.transport') }}</h4>
    <p class="text-xs text-gray-500 mt-1 mb-3">{{ __('event_applications.wizard.sections.transport_description') }}</p>
    <div class="grid grid-cols-1 gap-3">
        @foreach([
            'TRA1' => 'event_applications.wizard.checklist_items.TRA1',
            'TRA2' => 'event_applications.wizard.checklist_items.TRA2',
            'TRA3' => 'event_applications.wizard.checklist_items.TRA3',
            'TRA4' => 'event_applications.wizard.checklist_items.TRA4',
        ] as $key => $label)
            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" wire:model="formData.logistics_checklist.{{ $key }}"
                       class="form-checkbox rounded text-primary-600" value="1">
                <span class="text-sm text-gray-700">{{ __($label) }}</span>
            </label>
        @endforeach
    </div>
</div>

{{-- Food Checklist --}}
<div>
    <h4 class="text-sm font-semibold text-slate-700">{{ __('event_applications.wizard.sections.food') }}</h4>
    <p class="text-xs text-gray-500 mt-1 mb-3">{{ __('event_applications.wizard.sections.food_description') }}</p>
    <div class="grid grid-cols-1 gap-3">
        @foreach([
            'ALI1' => 'event_applications.wizard.checklist_items.ALI1',
            'ALI2' => 'event_applications.wizard.checklist_items.ALI2',
        ] as $key => $label)
            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" wire:model="formData.logistics_checklist.{{ $key }}"
                       class="form-checkbox rounded text-primary-600" value="1">
                <span class="text-sm text-gray-700">{{ __($label) }}</span>
            </label>
        @endforeach
    </div>
</div>

{{-- Section Comments --}}
@if($application)
    @include('web.entity.event-applications.components.section-comments', [
        'application' => $application,
        'section' => 'logistics',
    ])
@endif
