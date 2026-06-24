{{-- Step 4: Results Forecast (Section 4) --}}
<p class="text-xs text-gray-500 mb-4">{{ __('event_applications.wizard.labels.results_forecast_description') }}</p>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">

    <div>
        <label class="block text-sm font-medium mb-1">{{ __('event_applications.wizard.labels.forecast_total_participants') }}</label>
        <input type="number" wire:model="formData.forecast_total_participants" min="0" class="form-input w-full">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">{{ __('event_applications.wizard.labels.forecast_female_athletes') }}</label>
        <input type="number" wire:model="formData.forecast_female_athletes" min="0" class="form-input w-full">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">{{ __('event_applications.wizard.labels.forecast_male_athletes') }}</label>
        <input type="number" wire:model="formData.forecast_male_athletes" min="0" class="form-input w-full">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">{{ __('event_applications.wizard.labels.forecast_technical_officials') }}</label>
        <input type="number" wire:model="formData.forecast_technical_officials" min="0" class="form-input w-full">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">{{ __('event_applications.wizard.labels.forecast_coaches') }}</label>
        <input type="number" wire:model="formData.forecast_coaches" min="0" class="form-input w-full">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">{{ __('event_applications.wizard.labels.forecast_clubs') }}</label>
        <input type="number" wire:model="formData.forecast_clubs" min="0" class="form-input w-full">
    </div>
</div>

{{-- Planned Actions Repeater --}}
<div class="mb-6">
    <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-semibold text-slate-700">{{ __('event_applications.wizard.labels.planned_actions') }}</h4>
        <button type="button" wire:click="addRepeaterRow('planned_actions')" class="btn btn-sm btn-secondary">
            <x-heroicon-m-plus class="w-4 h-4 mr-1" />
            {{ __('common.add') }}
        </button>
    </div>

    @forelse($formData['planned_actions'] as $index => $action)
        <div wire:key="planned-{{ $index }}" class="border border-gray-200 rounded-lg p-4 mb-3">
            <div class="flex items-start justify-between mb-3">
                <span class="text-xs font-medium text-gray-500">#{{ $index + 1 }}</span>
                <button type="button" wire:click="removeRepeaterRow('planned_actions', {{ $index }})"
                        class="text-rose-500 hover:text-rose-700">
                    <x-heroicon-m-trash class="w-4 h-4" />
                </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium mb-1">{{ __('event_applications.wizard.labels.action') }}</label>
                    <input type="text" wire:model="formData.planned_actions.{{ $index }}.action"
                           class="form-input w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1">{{ __('event_applications.wizard.labels.participants') }}</label>
                    <input type="number" wire:model="formData.planned_actions.{{ $index }}.participants"
                           class="form-input w-full text-sm" min="0">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium mb-1">{{ __('event_applications.wizard.labels.agents') }}</label>
                    <div class="flex flex-wrap gap-3 mt-1">
                        @foreach(__('event_applications.wizard.agent_options') as $key => $label)
                            <label class="inline-flex items-center gap-1.5 text-sm">
                                <input type="checkbox" value="{{ $key }}"
                                       wire:model="formData.planned_actions.{{ $index }}.agents"
                                       class="form-checkbox rounded text-blue-600">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500 italic">{{ __('event_applications.wizard.no_entries') }}</p>
    @endforelse
</div>

{{-- Description Fields --}}
<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium mb-1">{{ __('event_applications.wizard.labels.event_link_description') }}</label>
        <p class="text-xs text-gray-500 mb-1">{{ __('event_applications.wizard.labels.event_link_help') }}</p>
        <textarea wire:model="formData.event_link_description" rows="3" class="form-textarea w-full"
                  placeholder="{{ __('event_applications.wizard.placeholders.event_link_description') }}"></textarea>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">{{ __('event_applications.wizard.labels.event_benefits_description') }}</label>
        <p class="text-xs text-gray-500 mb-1">{{ __('event_applications.wizard.labels.event_benefits_help') }}</p>
        <textarea wire:model="formData.event_benefits_description" rows="3" class="form-textarea w-full"
                  placeholder="{{ __('event_applications.wizard.placeholders.event_benefits_description') }}"></textarea>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">{{ __('event_applications.wizard.labels.event_objectives_description') }}</label>
        <p class="text-xs text-gray-500 mb-1">{{ __('event_applications.wizard.labels.event_objectives_help') }}</p>
        <textarea wire:model="formData.event_objectives_description" rows="3" class="form-textarea w-full"
                  placeholder="{{ __('event_applications.wizard.placeholders.event_objectives_description') }}"></textarea>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">{{ __('event_applications.wizard.labels.event_equipment_description') }}</label>
        <p class="text-xs text-gray-500 mb-1">{{ __('event_applications.wizard.labels.event_equipment_help') }}</p>
        <textarea wire:model="formData.event_equipment_description" rows="3" class="form-textarea w-full"
                  placeholder="{{ __('event_applications.wizard.placeholders.event_equipment_description') }}"></textarea>
    </div>
</div>

{{-- Section Comments --}}
@if($application)
    @include('web.entity.event-applications.components.section-comments', [
        'application' => $application,
        'section' => 'results_forecast',
    ])
@endif
