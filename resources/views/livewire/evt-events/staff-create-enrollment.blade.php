<div x-data="{ currentStep: @entangle('currentStep') }">
    <div class="mb-4">
        <a href="{{ $this->backRoute }}" class="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center gap-1">
            <x-heroicon-m-arrow-left class="h-4 w-4" />
            {{ __('events.back_to_event') }}
        </a>
    </div>

    <div class="flex space-x-4 mb-4">
        <button @click="currentStep = 1"
                :class="{ 'bg-blue-500 text-white': currentStep === 1, 'bg-gray-200': currentStep !== 1 }"
                class="px-4 py-2 rounded">
            {{ __('events.step_1_select_staff') }}
        </button>
        <button @click="currentStep = 2"
                :class="{ 'bg-blue-500 text-white': currentStep === 2, 'bg-gray-200': currentStep !== 2 }"
                class="px-4 py-2 rounded"
                :disabled="!@entangle('selectedIndividuals').length">
            {{ __('events.step_2_staff_info') }}
        </button>
    </div>

    @if ($errorMessages)
        <div class="flex gap-4 bg-red-700 p-4 rounded-md mb-4 items-center mt-2">
            <div class="w-max">
                <div class="flex rounded-full text-white">
                    <x-heroicon-m-exclamation-circle class="w-6 h-6" />
                </div>
            </div>
            <div class="text-sm">
                @foreach ($errorMessages as $message)
                    <p class="text-white leading-tight">{{ $message }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <div x-show="currentStep === 1">
        <section class="w-full md:flex md:flex-col">
            @if(count($selectedIndividuals) > 0)
                <div x-data="{ showSelected: true }" class="bg-blue-50 border border-blue-200 rounded-lg mb-4">
                    <div class="flex items-center justify-between p-3 cursor-pointer" @click="showSelected = !showSelected">
                        <div class="flex items-center gap-2">
                            <x-heroicon-s-check-circle class="h-5 w-5 text-blue-600" />
                            <span class="text-sm font-medium text-blue-800">
                                {{ __('events.staff_members_selected', ['count' => count($selectedIndividuals)]) }}
                            </span>
                            <x-heroicon-m-chevron-down class="h-4 w-4 text-blue-600 transition-transform" x-bind:class="{ 'rotate-180': !showSelected }" />
                        </div>
                        <button @click.stop="currentStep = 2" class="inline-flex items-center px-4 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-md transition-colors">
                            {{ __('events.enroll') }}
                        </button>
                    </div>

                    <div x-show="showSelected" x-collapse>
                        <div class="border-t border-blue-200">
                            <table class="min-w-full divide-y divide-blue-200">
                                <thead class="bg-blue-100/50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-blue-700 uppercase">{{ __('events.name') }}</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-blue-700 uppercase">{{ __('events.birthdate') }}</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-blue-700 uppercase">{{ __('events.gender') }}</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-blue-700 uppercase">{{ __('events.member_number') }}</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-blue-700 uppercase">{{ __('events.remove') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-blue-100">
                                    @foreach ($selectedIndividuals as $selected)
                                        <tr wire:key="selected-{{ $selected['id'] }}">
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $selected['name'] }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-600">{{ $selected['birthdate'] }}</td>
                                            <td class="px-4 py-2 text-sm">
                                                @if($selected['gender'] === 'male')
                                                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">M</span>
                                                @elseif($selected['gender'] === 'female')
                                                    <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">F</span>
                                                @else
                                                    <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-600/20">{{ $selected['gender'] }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-600">{{ $selected['member_number'] }}</td>
                                            <td class="px-4 py-2 text-right">
                                                <button wire:click="removeIndividualFromSelection('{{ $selected['id'] }}')" class="text-red-400 hover:text-red-600">
                                                    <x-heroicon-m-x-mark class="h-4 w-4" />
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{ $this->table }}
        </section>
    </div>

    <div x-show="currentStep === 2">
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('events.step_2_staff_info') }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('events.complete_info_for_selected_staff', ['count' => count($selectedIndividuals)]) }}
                </p>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900">{{ __('events.individual_properties') }}</h3>
                </div>

                <div class="divide-y divide-gray-200">
                    @foreach ($selectedIndividuals as $selected)
                        <div class="p-4 hover:bg-gray-50">
                            <div class="flex flex-wrap items-center gap-4">
                                <div class="flex items-center gap-x-3 min-w-[250px]">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-500">
                                                {{ substr($selected['name'], 0, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900">{{ $selected['name'] }}</h4>
                                        <p class="text-xs text-gray-500">{{ __('events.member_number') }}: {{ $selected['member_number'] }}</p>
                                    </div>
                                </div>

                                <div class="flex-1 flex flex-wrap items-center gap-4">
                                    @foreach ($staffAttributes as $attribute)
                                        <div class="justify-end">

                                            <x-attribute-form-input
                                                :attribute="['attribute_data' => $attribute]"
                                                :wire="'attributeValues.' . $selected['id'] . '.' . $attribute['id']"
                                                :value="$attributeValues[$selected['id']][$attribute['id']] ?? null"
                                                :options="$attribute['options'] ?? []" />
                                        </div>
                                    @endforeach
                                </div>

                                <div class="flex-shrink-0">
                                    <button
                                        wire:click="removeIndividualFromSelection('{{ $selected['id'] }}')"
                                        class="text-gray-400 hover:text-red-500 p-1">
                                        <x-heroicon-m-trash class="h-5 w-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-x-3">
                <x-filament::button
                    wire:click="$set('currentStep', 1)"
                    color="gray">
                    {{ __('events.back_to_selection') }}
                </x-filament::button>

                <x-filament::button
                    wire:click="submitEnrollment"
                    color="primary"
                    :disabled="count($selectedIndividuals) < 1">
                    {{ __('events.complete_staff_registration') }}
                </x-filament::button>
            </div>
        </div>
    </div>
</div>
