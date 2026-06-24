{{-- Step 7: Partners & Technical Documentation (Sections 9 + 10) --}}
{{-- Partners Repeater --}}
<div class="mb-6">
    <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-semibold text-slate-700">{{ __('event_applications.wizard.labels.partners') }}</h4>
        <button type="button" wire:click="addRepeaterRow('partners')" class="btn btn-sm btn-secondary">
            <x-heroicon-m-plus class="w-4 h-4 mr-1" />
            {{ __('common.add') }}
        </button>
    </div>

    @forelse($formData['partners'] as $index => $partner)
        <div wire:key="partner-{{ $index }}" class="border border-gray-200 rounded-lg p-4 mb-3">
            <div class="flex items-start justify-between mb-3">
                <span class="text-xs font-medium text-gray-500">#{{ $index + 1 }}</span>
                <button type="button" wire:click="removeRepeaterRow('partners', {{ $index }})"
                        class="text-rose-500 hover:text-rose-700">
                    <x-heroicon-m-trash class="w-4 h-4" />
                </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium mb-1">{{ __('event_applications.wizard.labels.partner_name') }}</label>
                    <input type="text" wire:model="formData.partners.{{ $index }}.name" class="form-input w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1">{{ __('event_applications.wizard.labels.partnership_type') }}</label>
                    <input type="text" wire:model="formData.partners.{{ $index }}.partnership_type" class="form-input w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1">{{ __('event_applications.wizard.labels.partner_email') }}</label>
                    <input type="email" wire:model="formData.partners.{{ $index }}.email" class="form-input w-full text-sm">
                </div>
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500 italic">{{ __('event_applications.wizard.no_entries') }}</p>
    @endforelse
</div>

{{-- Financing Description --}}
<div class="mb-6">
    <label class="block text-sm font-medium mb-1">{{ __('event_applications.wizard.labels.financing_description') }}</label>
    <p class="text-xs text-gray-500 mb-1">{{ __('event_applications.wizard.labels.financing_description_help') }}</p>
    <textarea wire:model="formData.financing_description" rows="3" class="form-textarea w-full"
              placeholder="{{ __('event_applications.wizard.placeholders.financing_description') }}"></textarea>
</div>

<hr class="border-gray-200 my-6">

{{-- Promotion Plan --}}
<div class="mb-6">
    <h4 class="text-sm font-semibold text-slate-700">{{ __('event_applications.wizard.sections.technical_docs') }}</h4>
    <p class="text-xs text-gray-500 mt-1 mb-3">{{ __('event_applications.wizard.sections.technical_docs_description') }}</p>
    <div class="grid grid-cols-1 gap-3">
        @foreach([
            'PDE1' => 'event_applications.wizard.checklist_items.PDE1',
            'PDE2' => 'event_applications.wizard.checklist_items.PDE2',
            'PDE3' => 'event_applications.wizard.checklist_items.PDE3',
            'PDE4' => 'event_applications.wizard.checklist_items.PDE4',
            'PDE5' => 'event_applications.wizard.checklist_items.PDE5',
        ] as $key => $label)
            <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                <input type="checkbox" wire:model="formData.promotion_checklist.{{ $key }}"
                       class="form-checkbox rounded text-primary-600" value="1">
                <span class="text-sm text-gray-700">{{ __($label) }}</span>
            </label>
        @endforeach
    </div>
</div>

{{-- Promotion Description --}}
<div>
    <label class="block text-sm font-medium mb-1">{{ __('event_applications.wizard.labels.technical_documents_description') }}</label>
    <p class="text-xs text-gray-500 mb-1">{{ __('event_applications.wizard.labels.technical_documents_description_help') }}</p>
    <textarea wire:model="formData.technical_documents_description" rows="3" class="form-textarea w-full"
              placeholder="{{ __('event_applications.wizard.placeholders.technical_documents_description') }}"></textarea>
</div>

{{-- Section Comments --}}
@if($application)
    @include('web.entity.event-applications.components.section-comments', [
        'application' => $application,
        'section' => 'partners',
    ])
@endif
