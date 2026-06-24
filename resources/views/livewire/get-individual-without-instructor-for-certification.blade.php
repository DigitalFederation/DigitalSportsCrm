<div class="w-full sm:flex gap-x-4 card mb-4">
    <div class="mb-8 sm:w-1/3">
        @if(!empty($federations))
            <div class="mb-4">
                <div>
                    <label for="federation" class="block text-sm font-medium mb-1">{{ __('Federation') }} <span
                            class="text-rose-500">*</span></label>
                    <select name="federation_id" id="federation" class="form-select w-full"
                            wire:model.live="selectedFederation" required>
                        <option hidden selected value="">{{ __('Choose a federation') }}</option>
                        @foreach($federations as $federation)
                            <option value="{{ $federation->id }}"
                                    @if(old('federation_id')===$federation->id) selected @endif>{{ $federation->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @else
            <input type="hidden" name="federation_id" value="{{ $federationId }}">
        @endif

        <div class="mb-4">
            <div>
                <label class="block text-sm font-medium mb-1" id="individual"
                       for="individual"> {{ __('main.Member Code') }}
                    <span class="text-rose-500">*</span></label>
                <div class="flex">
                    <input type="text" wire:model.live="codeIndividual" id="individual"
                           class="form-input w-full rounded-r-none">
                    <button type="button" class="btn btn-primary rounded-l-none"
                            wire:click="searchIndividual"> {{ __('Add') }} </button>
                </div>
            </div>
            <div class="text-xs mt-1">{{ __('Provide a valid Nº Filiado to find the user') }}</div>

            @if(!empty($selected_license_error_individual))
                <p class="text-sm text-rose-500">{{$selected_license_error_individual}}</p>
            @endif
        </div>

    </div>

    <div class="mb-8 sm:w-full">

        @if(empty($individual))
            <div class="card flex space-x-4 mb-4">
                <div class="flex information-box items-center w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-4" width="24" height="24"
                         viewBox="0 0 24 24" stroke-width="1.5" stroke="#9e9e9e" fill="none" stroke-linecap="round"
                         stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <circle cx="12" cy="12" r="9" />
                        <line x1="12" y1="8" x2="12.01" y2="8" />
                        <polyline points="11 12 12 12 12 16 13 16" />
                    </svg>
                    <p class="text-sm">Please add a code for a user on the left column to start associating data to this
                        request.</p>
                </div>
            </div>
        @endif

        @if(!empty($individual))
            <div class="card flex space-x-4 mb-4">
                <div class="md:w-1/2">
                    <label class="block text-sm font-medium mb-1" for="certification_id"> {{ __('Certification') }}
                        <span class="text-rose-500">*</span></label>
                    <select name="certification_id" id="certification_id" class="form-input w-full" required>
                        <option hidden selected value="">Choose a certification</option>
                        @foreach($certifications as $certification)
                            <option value="{{ $certification->id }}">{{ $certification->name }}</option>
                        @endforeach
                    </select>
                    <div
                        class="text-xs mt-1">{{ __('Choose the certification to be attributed for this request') }}</div>
                </div>
            </div>
        @endif

        @if(!empty($individual))
            <div class="card mb-4">

                <p class="text-slate-600 font-bold mb-4 border-b border-slate-600">{{ __('Selected Individual')}}</p>
                @foreach($individual as $index => $ind)

                    <div class="md:flex items-center mt-4 justify-between ">


                        <div class="flex justify-between items-center gap-x-4">

                            <div class="flex gap-x-1">
                                <button type="button" wire:click="removeItem({{ $index }})"
                                        class="btn-xs bg-red-500 hover:bg-red-600 text-white mr-2">
                                    <x-svg.trash class="w-4 h-4" />
                                </button>

                                <a target="_blank" href="../individual/{{$ind['id']}}"
                                   class="btn-xs btn-info" title="{{ __('Profile') }}">
                                    <x-svg.person-lines class="w-4 h-4" />
                                </a>
                            </div>

                            <div class="flex flex-col md:flex-row gap-x-2">
                                <div class="font-bold text-sm">{{ __('Name') }}:</div>
                                <div class="text-sm"> {{ $ind['name']}} {{ $ind['surname'] }} </div>
                            </div>

                        </div>


                        <div class="md:flex gap-x-2 items-center">
                            <label
                                class="md:w-full font-bold text-sm">{{ __('National Certification nº') }}</label>
                            <input type="text" placeholder="XXX/F00/ZZ/9999/888888"
                                   name="individual[national_code][]"
                                   class="form-input w-full">
                        </div>
                    </div>

                    <input name="individual[id][]" value="{{$ind['id']}}" type="hidden">
                    <input name="individual[name][]" value="{{ $ind['name'] . ' ' . $ind['surname'] }}" type="hidden">

                @endforeach
            </div>
        @endif
    </div>
</div>
