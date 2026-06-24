<div>
    {{-- Success State --}}
    @if ($showSuccessState)
        <div class="max-w-3xl mx-auto bg-white p-6 sm:p-8 rounded-lg shadow-md">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center h-24 w-24 rounded-full bg-green-100 mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-green-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ __('certifications.certification_attribution_successful') }}</h2>
                <p class="text-lg text-gray-600">
                    {{ __('certifications.your_certification_attribution_processed') }}</p>

                @if (!empty($successMessage))
                    <div class="mt-4 p-4 bg-blue-50 rounded-lg text-left">
                        <p class="text-sm text-blue-800">{{ $successMessage }}</p>
                    </div>
                @endif
            </div>

            <div class="bg-gray-50 rounded-lg p-6 mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('certifications.attribution_summary') }}</h3>
                <div class="space-y-4">
                    <div class="flex justify-between border-b border-gray-200 pb-3">
                        <span class="font-medium text-gray-600">{{ __('certifications.school') }}:</span>
                        <span class="text-gray-900">{{ $attributionSummary['school'] ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-200 pb-3">
                        <span class="font-medium text-gray-600">{{ __('certifications.certification') }}:</span>
                        <span class="text-gray-900">{{ $attributionSummary['certification'] ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-200 pb-3">
                        <span class="font-medium text-gray-600">{{ __('certifications.students') }}:</span>
                        <span class="text-gray-900">{{ $attributionSummary['studentCount'] ?? '0' }}</span>
                    </div>
                    @if ($this->actorType === 'federation')
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600">{{ __('certifications.issue_date') }}:</span>
                        <span class="text-gray-900">{{ $attributionSummary['issueDate'] ?? '-' }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ $this->actorType === 'federation' ? route('federation.certification-attributed.index') : route('entity.certification-attributed.index') }}"
                    class="btn btn-primary px-8 py-3 text-center">
                    {{ __('certifications.view_all_certifications') }}
                </a>
                <button type="button" wire:click="startNewAttribution" class="btn btn-secondary px-8 py-3 text-center">
                    {{ __('certifications.create_another_attribution') }}
                </button>
            </div>
        </div>
    @else
        {{-- TODO: Add Visual Step Indicator Here --}}
        <div class="max-w-7xl mx-auto">
            {{-- Step Content --}}
            <div wire:key="step-{{ $step }}">
                @if ($step === 1)
                    @include('livewire.certifications.wizard.steps.1-context')
                @elseif($step === 2)
                    {{-- Step 2 now includes former step 3 form --}}
                    @include('livewire.certifications.wizard.steps.2-roles')
                @endif
            </div>

            {{-- Wizard Footer Navigation --}}
            <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                <div>
                    @if ($step > 1)
                        <button type="button" wire:click="prevStep" class="btn btn-secondary">
                            {{ __('certifications.back') }}
                        </button>
                    @endif
                </div>
                <div>
                    @if ($step < 2)
                        <button type="button" wire:click="nextStep" class="btn btn-primary"
                            wire:loading.attr="disabled">
                            <span wire:loading wire:target="nextStep" class="mr-2">

                            </span>
                            {{ __('certifications.continue_to_next_step') }}
                        </button>
                    @else
                        <button type="button" wire:click="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading wire:target="submit" class="mr-2">

                            </span>
                            {{ __('certifications.submit_certification_request') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
