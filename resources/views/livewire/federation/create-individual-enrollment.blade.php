<div>
    <x-layout.banner_message />

    <!-- Explain how to use this page -->
    <x-information-box
        title="{{ __('Instructions') }}"
        body="{{ __('Choose from the table, the individuals you want to enroll for this event. If a member is not listed, check their role.') }}"></x-information-box>

    <!-- Table -->

    <div class="flex flex-col gap-4">

        <section class="w-full md:flex md:flex-col">

            {{ $this->table }}

        </section>

        <section class=" w-full md:flex md:flex-col">
            <x-filament::section class="flex flex-col h-full">
                <x-slot name="heading">
                    {{ __('Enrollment Selection') }} ({{ count($this->selectedIndividuals) }})
                </x-slot>
                <x-slot name="headerEnd">

                    @if($totalCost > 0)
                        <p class="font-bold text-slate-600">Total: <span class="font-normal">
                                {{ money($totalCost) }}</span>
                        </p>
                    @endif
                </x-slot>
                <x-slot name="description">
                    {{ __('List of selected individuals for registration.') }}
                </x-slot>


                @if(!empty($this->selectedIndividuals))
                    <div class="md:max-h-max overflow-y-auto flex flex-col gap-y-4">

                        @foreach($this->selectedIndividuals as $selected)
                            <div class="border border-slate-200 p-2 flex flex-col gap-y-4">
                                <div class="flex flex-col md:flex-row gap-4 ">
                                    <div class="w-full md:w-1/2">
                                        <label>{{ __('Event Fee') }}</label>
                                        <select wire:model="individualPricingTiers.{{ $selected['id'] }}"
                                                class="form-input rounded-md shadow-sm mt-1 block w-full">
                                            <option value="">{{ __('-- Select Fee -- ') }}</option>
                                            @foreach($pricingTiersOptions as $key => $priceTier)

                                                <option value="{{ $key }}">
                                                    {{ money($priceTier['price']) }} | {{ $priceTier['description'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="w-full md:w-1/2">
                                        <label>{{ __('Name') }}</label>
                                        <input type="text" disabled value="{{ $selected['name'] }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-slate-50">

                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                                    @foreach($this->eventAttributes as $attributeId => $attributeData)
                                        <div>
                                            <label for="attribute_{{ $attributeId }}"
                                                   class="block text-sm font-medium text-gray-700">{{ $attributeData['name'] }}</label>


                                            @if($attributeData['type'] === 'SELECT')
                                                <!-- Check if it's a select type -->
                                                <x-attributes.types.select
                                                    :attributeId="$attributeId"
                                                    :selectedId="$selected['id']"
                                                    :value="$attributeData['default_value'] ?? ''"
                                                    :options="$attributeData['options']"
                                                />
                                            @else
                                                <!-- Fallback to text input -->
                                                <input type="text"
                                                       name="attributes[{{ $selected['id'] }}][{{ $attributeId }}]"
                                                       wire:model="attributeValues.{{ $selected['id'] }}.{{ $attributeId }}"
                                                       id="attribute_{{ $attributeId }}"
                                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                                       value="{{ $attributeData['default_value'] ?? '' }}">
                                            @endif

                                        </div>
                                    @endforeach

                                </div>
                            </div>
                        @endforeach

                    </div>
                @else
                    <p class="text-slate-400 text-sm">{{ __('Use the table and select items with the checkbox.') }}</p>
                @endif

                <div class="w-full flex mt-4 border-t pt-4 border-slate-300">
                    @if(empty($this->showConfirmation))
                        <button
                            wire:click="doShowConfirmation"
                            class="btn btn-primary w-full"
                            type="button"
                            @if(count($this->selectedIndividuals) < 1) disabled @endif>
                            Submit Registration
                        </button>
                    @else
                        <div class="flex flex-col items-center gap-x-2 gap-y-2 w-full">
                            <div class="w-auto block px-2">
                                @if($totalCost > 0)
                                    Are <u>you sure</u> you want to
                                    enroll {{ count($this->selectedIndividuals) }}
                                    members for a total of {{ money($totalCost) }} ?
                                @else
                                    Are <u>you sure</u> you want to
                                    enroll {{ count($this->selectedIndividuals) }}
                                    members?
                                @endif

                            </div>
                            <button wire:click="submitEnrollment" class="btn btn-primary w-full" type="button">
                                Confirm Registration
                            </button>
                        </div>
                    @endif
                </div>
            </x-filament::section>
        </section>
    </div>

</div>
