<div class="py-6 space-y-6">
    @if (!empty($errorMessages))
        <div class="flex gap-4 bg-red-700 p-4 rounded-md mb-4 items-start">
            <x-heroicon-o-x-circle class="w-6 h-6 text-white mt-1" />
            <div class="text-sm text-white space-y-1">
                @foreach ($errorMessages as $message)
                    <p class="leading-tight">{{ $message }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <div class="space-y-1">
        <label class="block text-sm font-medium text-gray-700">
            {{ __('Select a Discipline to Add') }}
        </label>
        <p class="text-xs text-gray-500 mb-3">
            {{ __('Choose a discipline from the list below and click “Add” to configure its attributes. You can add multiple disciplines to this individual. Each discipline may have unique attributes that you can manage.') }}
        </p>
        <div class="flex space-x-2 items-center">
            <select
                wire:model.live="selectedDiscipline"
                class="form-select w-5/6 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="">{{ __('-- Select Discipline --') }}</option>
                @foreach($availableDisciplines as $discipline)
                    <option value="{{ $discipline->id }}">{{ $discipline->name }}</option>
                @endforeach
            </select>

            <button
                wire:click="addDiscipline"
                type="button"
                class="btn btn-primary w-1/6 flex items-center justify-center"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-50 cursor-not-allowed"
                @if(!$selectedDiscipline)
                    disabled
                @endif
            >
                {{ __('Add') }}
            </button>
        </div>
    </div>

    <div class="space-y-6">
        @foreach($data as $disciplineId => $disciplineData)
            <x-filament::fieldset wire:key="discipline-{{ $disciplineId }}" class="border rounded-lg p-4 shadow-sm">
                <x-slot name="label">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-2 sm:space-y-0">
                        <div class="flex flex-col">
                            <span class="font-semibold text-gray-800">{{ $disciplineData['name'] }}</span>
                            <span class="text-xs text-gray-500">
                                {{ __('Configure attributes specific to this discipline below.') }}
                            </span>
                        </div>

                        <div class="flex space-x-2 items-center">
                            <button wire:click.prevent="remove({{ $disciplineId }})"
                                    class="inline-flex items-center text-xs text-gray-500 hover:text-gray-700 border border-gray-300 rounded px-2 py-1"
                                    title="{{ __('Remove this discipline') }}">
                                <x-heroicon-o-trash class="w-4 h-4 mr-1" />
                                {{ __('Remove') }}
                            </button>
                        </div>
                    </div>
                </x-slot>

                @if(isset($disciplineData['attributes']))
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($disciplineData['attributes'] as $index => $attribute)
                            @php
                                \Log::debug('Attribtues from Loop', ['attribute' => $attribute]);
                            @endphp
                            <div wire:key="attribute-{{ $disciplineId }}-{{ $index }}"
                                 class="">
                                <x-attribute-form-input
                                    :attribute="[
                                        'attribute_data' => [
                                            'id' => $attribute['id'],
                                            'type' => $attribute['type'] ?? 'TEXT',
                                            'name' => $disciplineData['name'],
                                            'required' => false,
                                            'options' => $attribute['options'] ?? []
                                        ]
                                    ]"
                                    :value="$attribute['value']"
                                    :wire="'data.' . $disciplineId . '.attributes.' . $index . '.value'"
                                    :selected="$individual->toArray()"
                                    :options="$attribute['options'] ?? []"
                                />
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-4">
                        <p class="text-sm text-gray-500">
                            {{ __('No attributes are defined for this discipline. You can proceed without additional configuration, or remove this discipline if not needed.') }}
                        </p>
                    </div>
                @endif
            </x-filament::fieldset>
        @endforeach
    </div>

</div>
