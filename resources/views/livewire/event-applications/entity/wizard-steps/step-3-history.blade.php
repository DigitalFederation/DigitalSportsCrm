{{-- Step 3: Previous Editions (Section 3) --}}
{{-- Previous Editions Repeater --}}
<div class="mb-6">
    <div class="flex items-center justify-between mb-3">
        <div>
            <h4 class="text-sm font-semibold text-slate-700">{{ __('event_applications.wizard.labels.previous_editions') }}</h4>
            <p class="text-xs text-gray-500 mt-1">{{ __('event_applications.wizard.labels.previous_editions_description') }}</p>
        </div>
        <button type="button" wire:click="addRepeaterRow('previous_editions')" class="btn btn-sm btn-secondary">
            <x-heroicon-m-plus class="w-4 h-4 mr-1" />
            {{ __('common.add') }}
        </button>
    </div>

    @forelse($formData['previous_editions'] as $index => $edition)
        <div wire:key="edition-{{ $index }}" class="border border-gray-200 rounded-lg p-4 mb-3">
            <div class="flex items-start justify-between mb-3">
                <span class="text-xs font-medium text-gray-500">#{{ $index + 1 }}</span>
                <button type="button" wire:click="removeRepeaterRow('previous_editions', {{ $index }})"
                        class="text-rose-500 hover:text-rose-700">
                    <x-heroicon-m-trash class="w-4 h-4" />
                </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium mb-1">{{ __('event_applications.wizard.labels.year') }}</label>
                    <input type="number" wire:model="formData.previous_editions.{{ $index }}.year"
                           class="form-input w-full text-sm" min="1900" max="2099">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1">{{ __('event_applications.wizard.labels.edition_location') }}</label>
                    <input type="text" wire:model="formData.previous_editions.{{ $index }}.location"
                           class="form-input w-full text-sm">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium mb-1">{{ __('event_applications.wizard.labels.edition_name') }}</label>
                    <input type="text" wire:model="formData.previous_editions.{{ $index }}.name"
                           class="form-input w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1">{{ __('event_applications.wizard.labels.participants_count') }}</label>
                    <input type="number" wire:model="formData.previous_editions.{{ $index }}.athletes"
                           class="form-input w-full text-sm" min="0">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1">{{ __('event_applications.wizard.labels.clubs_count') }}</label>
                    <input type="number" wire:model="formData.previous_editions.{{ $index }}.clubs"
                           class="form-input w-full text-sm" min="0">
                </div>
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500 italic">{{ __('event_applications.wizard.no_entries') }}</p>
    @endforelse
</div>

{{-- Previous Actions Repeater --}}
<div class="mb-6">
    <div class="flex items-center justify-between mb-3">
        <div>
            <h4 class="text-sm font-semibold text-slate-700">{{ __('event_applications.wizard.labels.previous_actions') }}</h4>
            <p class="text-xs text-gray-500 mt-1">{{ __('event_applications.wizard.labels.previous_actions_description') }}</p>
        </div>
        <button type="button" wire:click="addRepeaterRow('previous_actions')" class="btn btn-sm btn-secondary">
            <x-heroicon-m-plus class="w-4 h-4 mr-1" />
            {{ __('common.add') }}
        </button>
    </div>

    @forelse($formData['previous_actions'] as $index => $action)
        <div wire:key="action-{{ $index }}" class="border border-gray-200 rounded-lg p-4 mb-3">
            <div class="flex items-start justify-between mb-3">
                <span class="text-xs font-medium text-gray-500">#{{ $index + 1 }}</span>
                <button type="button" wire:click="removeRepeaterRow('previous_actions', {{ $index }})"
                        class="text-rose-500 hover:text-rose-700">
                    <x-heroicon-m-trash class="w-4 h-4" />
                </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium mb-1">{{ __('event_applications.wizard.labels.action') }}</label>
                    <input type="text" wire:model="formData.previous_actions.{{ $index }}.action"
                           class="form-input w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1">{{ __('event_applications.wizard.labels.participants') }}</label>
                    <input type="number" wire:model="formData.previous_actions.{{ $index }}.participants"
                           class="form-input w-full text-sm" min="0">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium mb-1">{{ __('event_applications.wizard.labels.agents') }}</label>
                    <div class="flex flex-wrap gap-3 mt-1">
                        @foreach(__('event_applications.wizard.agent_options') as $key => $label)
                            <label class="inline-flex items-center gap-1.5 text-sm">
                                <input type="checkbox" value="{{ $key }}"
                                       wire:model="formData.previous_actions.{{ $index }}.agents"
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

{{-- Section Comments --}}
@if($application)
    @include('web.entity.event-applications.components.section-comments', [
        'application' => $application,
        'section' => 'previous_editions',
    ])
@endif

