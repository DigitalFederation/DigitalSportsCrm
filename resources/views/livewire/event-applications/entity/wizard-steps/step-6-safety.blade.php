{{-- Step 6: Safety & Emergency Plan (Section 7) --}}
{{-- Safety Checklist --}}
<div class="mb-6">
    <p class="text-xs text-gray-500 mb-3">{{ __('event_applications.wizard.sections.safety_plan_description') }}</p>
    <div class="grid grid-cols-1 gap-3">
        @foreach([
            'PSE1' => 'event_applications.wizard.checklist_items.PSE1',
            'PSE2' => 'event_applications.wizard.checklist_items.PSE2',
            'PSE3' => 'event_applications.wizard.checklist_items.PSE3',
            'PSE4' => 'event_applications.wizard.checklist_items.PSE4',
            'PSE5' => 'event_applications.wizard.checklist_items.PSE5',
            'PSE6' => 'event_applications.wizard.checklist_items.PSE6',
            'PSE7' => 'event_applications.wizard.checklist_items.PSE7',
        ] as $key => $label)
            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" wire:model="formData.safety_checklist.{{ $key }}"
                       class="form-checkbox rounded text-primary-600" value="1">
                <span class="text-sm text-gray-700">{{ __($label) }}</span>
            </label>
        @endforeach
    </div>
</div>

{{-- Emergency Team --}}
<div class="mb-6">
    <h4 class="text-sm font-semibold text-slate-700 mb-3">{{ __('event_applications.wizard.labels.emergency_team') }}</h4>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('event_applications.wizard.labels.pse_responsible_name') }}</label>
            <input type="text" wire:model="formData.pse_responsible_name" class="form-input w-full">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('event_applications.wizard.labels.pse_responsible_phone') }}</label>
            <input type="tel" wire:model="formData.pse_responsible_phone" class="form-input w-full">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('event_applications.wizard.labels.pse_responsible_email') }}</label>
            <input type="email" wire:model="formData.pse_responsible_email" class="form-input w-full">
        </div>
    </div>
</div>

{{-- Info Notice --}}
<div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
    <div class="flex gap-3">
        <x-heroicon-m-information-circle class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" />
        <p class="text-sm text-blue-700">{{ __('event_applications.wizard.labels.safety_info_notice') }}</p>
    </div>
</div>

{{-- Section Comments --}}
@if($application)
    @include('web.entity.event-applications.components.section-comments', [
        'application' => $application,
        'section' => 'safety',
    ])
@endif
