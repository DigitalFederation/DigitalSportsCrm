<div>
    {{-- Step Indicator (teleported to sidebar) --}}
    @teleport('#wizard-step-nav')
        <div class="card">
            <h3 class="font-semibold text-slate-800 mb-4">{{ __('event_applications.wizard.navigation') }}</h3>
            <nav aria-label="Progress">
                <ol role="list" class="flex flex-col">
                    @foreach ($this->getSteps() as $index => $step)
                        @php
                            $stepNumber = $step['number'];
                            $isCompleted = $currentStep > $stepNumber;
                            $isCurrent = $currentStep === $stepNumber;
                            $isLast = $index === count($this->getSteps()) - 1;
                        @endphp

                        <li class="relative {{ !$isLast ? 'pb-6' : '' }}">
                            {{-- Vertical connector line --}}
                            @if (!$isLast)
                                <div class="absolute left-4 top-8 -ml-px h-full w-0.5 {{ $isCompleted ? 'bg-primary-600' : 'bg-gray-200' }}" aria-hidden="true"></div>
                            @endif

                            <div class="group relative flex items-center cursor-pointer" wire:click="goToStep({{ $stepNumber }})">
                                @if ($isCompleted)
                                    <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-primary-600">
                                        <x-heroicon-s-check class="h-5 w-5 text-white" />
                                    </span>
                                @elseif ($isCurrent)
                                    <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border-2 border-primary-600 bg-white">
                                        <span class="text-sm font-medium text-primary-600">{{ $stepNumber }}</span>
                                    </span>
                                @else
                                    <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full border-2 border-gray-300 bg-white">
                                        <span class="text-sm font-medium text-gray-500">{{ $stepNumber }}</span>
                                    </span>
                                @endif

                                <span class="ml-3 text-sm font-medium {{ $isCurrent ? 'text-primary-600' : ($isCompleted ? 'text-gray-900' : 'text-gray-500') }}">
                                    {{ $step['title'] }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </nav>
        </div>
    @endteleport

    {{-- Step Content --}}
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">{{ $this->getStepTitle() }}</h2>
            <span class="text-sm text-gray-500">
                {{ __('event_applications.wizard.step_x_of_y', ['current' => $currentStep, 'total' => $totalSteps]) }}
            </span>
        </div>

        @if ($currentStep === 1)
            @include('livewire.event-applications.entity.wizard-steps.step-1-event')
        @elseif ($currentStep === 2)
            @include('livewire.event-applications.entity.wizard-steps.step-2-entity')
        @elseif ($currentStep === 3)
            @include('livewire.event-applications.entity.wizard-steps.step-3-history')
        @elseif ($currentStep === 4)
            @include('livewire.event-applications.entity.wizard-steps.step-4-results')
        @elseif ($currentStep === 5)
            @include('livewire.event-applications.entity.wizard-steps.step-5-logistics')
        @elseif ($currentStep === 6)
            @include('livewire.event-applications.entity.wizard-steps.step-6-safety')
        @elseif ($currentStep === 7)
            @include('livewire.event-applications.entity.wizard-steps.step-7-partners')
        @elseif ($currentStep === 8)
            @include('livewire.event-applications.entity.wizard-steps.step-8-budget')
        @elseif ($currentStep === 9)
            @include('livewire.event-applications.entity.wizard-steps.step-9-documents')
        @elseif ($currentStep === 10)
            @include('livewire.event-applications.entity.wizard-steps.step-10-summary')
        @endif
    </div>

    {{-- Navigation --}}
    <div class="flex flex-wrap items-center justify-between mt-6 gap-3">
        <div>
            @if ($currentStep > 1)
                <button type="button" wire:click="previousStep" class="btn btn-secondary">
                    <x-heroicon-m-arrow-left class="w-4 h-4 mr-1" />
                    {{ __('common.previous') }}
                </button>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" wire:click="cancel" class="btn btn-secondary">
                {{ __('common.cancel') }}
            </button>

            <button type="button" wire:click="saveDraft" class="btn btn-info" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="saveDraft">{{ __('event_applications.actions.save_draft') }}</span>
                <span wire:loading wire:target="saveDraft">{{ __('common.saving') }}...</span>
            </button>

            @if ($currentStep < $totalSteps)
                <button type="button" wire:click="nextStep" class="btn btn-primary">
                    {{ __('common.next') }}
                    <x-heroicon-m-arrow-right class="w-4 h-4 ml-1" />
                </button>
            @else
                <button type="button" wire:click="submitApplication" class="btn btn-success" wire:loading.attr="disabled"
                        onclick="return confirm('{{ __('event_applications.confirmations.submit_application') }}')">
                    <span wire:loading.remove wire:target="submitApplication">{{ __('event_applications.actions.submit_application') }}</span>
                    <span wire:loading wire:target="submitApplication">{{ __('common.submitting') }}...</span>
                </button>
            @endif
        </div>
    </div>
</div>
