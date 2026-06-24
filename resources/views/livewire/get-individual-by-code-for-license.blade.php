<div class="w-full md:flex gap-x-4">
    <section class="card {{ empty($entities) ? 'sm:w-1/3' : 'sm:w-full' }}">

        @if(!empty($entity))
            <input type="hidden" name="entity_id" value="{{ $entity }}">
        @endif

        @if(!empty($federations))
            <div class="flex flex-row items-end mb-4">
                <div class="w-full">
                    <label for="federation_id" class="block text-sm font-medium mb-1">{{ __('Federation') }}
                        <span class="text-rose-500">*</span>
                    </label>


                    <select name="federation_id" wire:model.live="federation" id="federation_id"
                            class="form-select w-full" required>
                        <option value="null" hidden disabled>{{ __('Select one...') }}</option>
                        @foreach($federations as $federation)
                            <option value="{{ $federation->id }}">{{ $federation->name }}</option>
                        @endforeach
                    </select>

                    @if($errors->has('federation_id'))
                        <div class="text-xs mt-1 text-rose-500 h-2">
                            {{ $errors->first('federation_id') }}
                        </div>
                    @endif
                    <div class="text-xs mt-1">{{ __('Choose the federation to be attributed on this request') }}</div>
                </div>
            </div>
        @else
            <input type="hidden" name="federation_id" value="{{ $federation }}">
        @endif

        <div class="flex flex-row items-end mb-4">
            <div class="w-full">
                <label class="block text-sm font-medium mb-1" for="license_id"> {{ __('License') }} <span
                        class="text-rose-500">*</span></label>

                <select wire:model.live="selected_license" name="license_id" id="license_id" class="form-select w-full"
                        required @if(!empty($records)) disabled @endif>
                    <option value="null" hidden disabled> {{ __('Select a License...') }} </option>
                    @foreach($licenses as $license)
                        <option value="{{ $license['id'] }}"
                                @if(old('license_id')==$license['id']) selected @endif>{{ $license['name']}}</option>
                    @endforeach
                </select>

                @if($errors->has('license_id'))
                    <div class="text-xs mt-1 text-rose-500 h-2">
                        {{ $errors->first('license_id') }}
                    </div>
                @endif
                <div class="text-xs mt-1">{{ __('Choose the license to be attributed on this request') }}</div>
            </div>
        </div>

        @if(empty($entities))
            <div class="flex flex-row items-end relative">
                <div class="w-full">
                    <label class="block text-sm font-medium" for="name">
                        {{ __('main.Member Code') }}
                        <span class="text-rose-500">*</span>
                    </label>

                    <div class="flex">
                        <input
                            type="text"
                            wire:model.live="code"
                            class="form-input w-full rounded-r-none"
                            id="individual_code">

                        <x-federation-individual-selector input-id="individual_code" />

                        <button
                            type="button"
                            class="btn bg-slate-200 rounded-l-none btn-info"
                            wire:click="searchResult"> {{ __('Insert') }} </button>
                    </div>
                </div>


            </div>
            <div
                class="text-xs mt-1">{{ __('Add multiple individuals for licensing by entering each Nº Filiado and clicking Insert.') }}</div>
        @else
            <div class="flex flex-row items-end">
                <div class="w-full">
                    <label for="entity_id" class="block text-sm font-medium mb-1">{{ __('Entity') }} <span
                            class="text-rose-500">*</span></label>
                    <select name="entity_id" id="entity_id" wire:model.live="entity" class="form-select w-full"
                            required>
                        <option hidden selected disabled>{{ __('Choose a entity') }}</option>
                        @foreach($entities as $entity)
                            <option value="{{ $entity->id }}">{{ $entity->name }}</option>
                        @endforeach
                    </select>

                    @if($errors->has('entity_id'))
                        <div class="text-xs mt-1 text-rose-500 h-2">
                            {{ $errors->first('entity_id') }}
                        </div>
                    @endif
                    <div class="text-xs mt-1">{{ __('Choose the entity to be attributed on this request') }}</div>
                </div>
            </div>
        @endif

        <!-- Search result list -->
        @if(empty($records) && !empty($showdiv) || !empty($errorMessage))
            <p class="text-sm text-rose-500">{{ $errorMessage }}</p>
        @endif

        @if(!empty($selected_license_error))
            <p class="text-sm text-rose-500">{{$selected_license_error}}</p>
        @endif

    </section>

    @if(empty($entities))
        <section class="card h-auto sm:w-2/3 mt-4 md:mt-0">
            <div class="space-y-4">
                @if(!empty($records))
                    <div class="flex justify-between items-center border-b border-gray-200 pb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Selected Individual(s)') }}</h3>
                            <p class="text-sm text-gray-500">
                                {{ __('The following individuals will be attributed to the selected license.') }}
                            </p>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-medium bg-blue-50 text-blue-700 rounded-full">
                            {{ count($records) }} {{ __('Selected') }}
                        </span>
                    </div>

                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                        {{ __('Name') }}
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                        {{ __('main.Member Code') }}
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                        {{ __('Country') }}
                                    </th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                        <span class="sr-only">{{ __('Actions') }}</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach($records as $key => $record)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150 ease-in-out">
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 flex-shrink-0">
                                                    @if($record['photo'])
                                                        <img class="h-10 w-10 rounded-full object-cover"
                                                             src="{{ $record['photo'] }}"
                                                             alt="{{ $record['name'] }}">
                                                    @else
                                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                            <span class="text-sm font-medium text-gray-500">
                                                                {{ substr($record['name'], 0, 2) }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="ml-4">
                                                    <p
                                                       class="font-medium text-gray-900 hover:text-blue-600">
                                                        {{ $record['name'] }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ $record['member_code'] }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <span>{{ $record['country']['name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <input name="individual[]" value="{{ $record['id'] }}" type="hidden">
                                            <div class="flex items-center justify-end gap-2">

                                                <button type="button"
                                                        wire:click="removeResult({{ $key }})"
                                                        class="text-red-600 hover:text-red-900 transition-colors duration-150">
                                                    <span class="sr-only">{{ __('Remove') }} {{ $record['name'] }}</span>
                                                    <x-heroicon-m-x-circle class="h-5 w-5" />
                                                </button>

                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="rounded-lg bg-white px-6 py-8 text-center">
                        <div class="mx-auto h-12 w-12 text-gray-400">
                            <x-heroicon-o-user-group class="h-12 w-12" />
                        </div>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900">{{ __('No individuals selected') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('Choose a license and one or more Individuals, by entering their Nº Filiado.') }}
                        </p>
                    </div>

                    @if($errors->has('individual'))
                        <div class="mt-2 text-sm text-red-600">
                            {{ $errors->first('individual') }}
                        </div>
                    @endif
                @endif
            </div>
        </section>
    @endif
</div>
