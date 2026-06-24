<div class="container mx-auto">

    <form wire:submit.prevent="save">
        <section class="flex gap-x-4">

            <section class="w-full md:w-2/3">
                <div class="card mb-4">
                    <!-- Competition Name -->
                    <h2 class="font-bold mb-2 text-slate-400">General Information</h2>
                    <div class="flex gap-4">
                        <div class="mb-4 md:w-3/4">
                            <label for="full_name" class="block text-sm font-medium mb-1">Full Competition Name <span
                                    class="text-rose-500">*</span></label>
                            <input type="text" wire:model="full_name" id="full_name" class="form-input w-full">
                            @error('full_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:w-1/4">
                            <label class="block text-sm font-medium mb-1" for="number">Competition Number <span
                                    class="text-rose-500">*</span></label>
                            <input type="number" name="number" id="number" min="0" wire:model="number"
                                   class="form-input w-full"
                                   required>
                            @error('number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Moloni Reference --}}
                    <div class="mb-4 md:w-1/2">
                        <label for="moloni_reference" class="block text-sm font-medium mb-1">{{ __('moloni.product_reference') }}</label>
                        <input type="text" wire:model="moloni_reference" id="moloni_reference" class="form-input w-full" maxlength="50">
                        <p class="text-xs text-slate-500 mt-1">{{ __('moloni.product_reference_help') }}</p>
                        @error('moloni_reference') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <h2 class="font-bold mb-2 text-slate-400">Dates</h2>
                    <div class="flex flex-col mb-4 md:flex-row w-full gap-4">
                        <div class="md:w-1/2">
                            <label class="block text-sm font-medium mb-1" for="competition_start_date">Competition Start
                                Date</label>
                            <input type="date" name="competition_start_date" id="competition_start_date"
                                   wire:model="competition_start_date" class="form-input w-full">
                            @error('competition_start_date') <span
                                class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:w-1/2">
                            <label class="block text-sm font-medium mb-1" for="competition_end_date">Competition End
                                Date</label>
                            <input type="date" name="competition_end_date" id="competition_end_date"
                                   wire:model="competition_end_date" class="form-input w-full">
                            @error('competition_end_date') <span
                                class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row w-full gap-4">
                        <div class="md:w-1/2">
                            <label class="block text-sm font-medium mb-1" for="registration_start_date">Registration
                                Start Date</label>
                            <input type="date" name="registration_start_date" id="registration_start_date"
                                   wire:model="registration_start_date" class="form-input w-full">
                            @error('registration_start_date') <span
                                class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:w-1/2">
                            <label class="block text-sm font-medium mb-1" for="registration_end_date">Registration
                                End Date</label>
                            <input type="date" name="registration_end_date" id="registration_end_date"
                                   wire:model="registration_end_date" class="form-input w-full">
                            @error('registration_end_date') <span
                                class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Competition Type -->
                    <h2 class="font-bold mb-2 mt-8 text-slate-400">Competition Information</h2>
                    <div class="flex flex-col md:flex-row w-full gap-4">

                        <div class="sm:w-1/3">
                            <label class="block text-sm font-medium mb-1" for="sport_id">{{ __('Competition Sport') }}
                                <span
                                    class="text-rose-500">*</span></label>
                            <select name="sport_id"
                                    id="sport_id"
                                    wire:model="sport_id"
                                    class="form-input w-full {{ $errors->has('sport_id') ? 'border-rose-300' : '' }}"
                                    required>
                                <option value="" selected> {{ __('-- Select an option --') }} </option>
                                @foreach($sport_options as $key => $sport)
                                    <option
                                        value="{{ $key }}"
                                        @if(old('sport_id') == $key) selected @endif
                                    >{{ $sport }}</option>
                                @endforeach
                            </select>

                            @if($errors->has('sport_id'))
                                <div class="text-xs mt-1 text-rose-500 h-2">
                                    {{ $errors->first('sport_id') }}
                                </div>
                            @endif
                        </div>

                        <div class="md:w-1/3">
                            <label class="block text-sm font-medium mb-1" for="competition_types">Competition Type <span
                                    class="text-rose-500">*</span></label>
                            <livewire:input.select-multiple
                                :inputSelected="$competition_types"
                                identifier="competition_types"
                                :items="$competition_type_options"
                                inputId="competition_types"
                                inputName="competition_types[]" />
                            @error('competition_types') <span
                                class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:w-1/3">
                            <label class="block text-sm font-medium mb-1" for="rounds_total">Rounds Total</label>
                            <input type="number" name="rounds_total" id="rounds_total" wire:model="rounds_total"
                                   class="form-input w-full">
                            @error('rounds_total') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                    </div>

                    <div class="flex flex-col md:flex-row w-full gap-4 mt-4">
                        <div class="md:w-1/4">
                            <label class="block text-sm font-medium mb-1" for="cat_age">Category Age</label>
                            <input type="text" id="cat_age" wire:model="cat_age" class="form-input w-full">
                            @error('cat_age') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:w-1/4">
                            <label class="block text-sm font-medium mb-1" for="cat_competition">Competition
                                Category</label>
                            <select
                                id="cat_competition"
                                wire:model="cat_competition"
                                class="form-input w-full">
                                @foreach($cat_competition_options as $cat_competition)
                                    <option value="{{ $cat_competition->name }}"
                                            @if(old('cat_competition') == $cat_competition->name) selected @endif>{{ $cat_competition }}</option>
                                @endforeach
                            </select>
                            @error('cat_competition') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:w-1/4">
                            <label class="block text-sm font-medium mb-1" for="environment">Environment</label>
                            <select
                                id="environment"
                                wire:model="environment"
                                class="form-input w-full">
                                @foreach($environment_options as $environment)
                                    <option value="{{ $environment->name }}"
                                            @if(old('environment') == $environment->name) selected @endif>{{ $environment }}</option>
                                @endforeach
                            </select>
                            @error('environment') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                    </div>


                    <!-- Medals information -->
                    <h2 class="font-bold mb-2 mt-8 text-slate-400">Medals Information</h2>
                    <div class="flex flex-col md:flex-row w-full gap-4 mt-4">
                        <div class="w-1/3">
                            <label for="medals_gold" class="block text-sm font-medium mb-1">Nº Gold medals</label>
                            <input type="number" min="0" wire:model="medals_gold" id="medals_gold"
                                   class="form-input w-full">
                            @error('medals_gold') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="w-1/3">
                            <label for="medals_silver" class="block text-sm font-medium mb-1">Nº Silver medals</label>
                            <input type="number" min="0" wire:model="medals_silver" id="medals_silver"
                                   class="form-input w-full">
                            @error('medals_silver') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="w-1/3">
                            <label for="medals_bronze" class="block text-sm font-medium mb-1">Nº Bronze medals</label>
                            <input type="number" min="0" wire:model="medals_bronze" id="medals_bronze"
                                   class="form-input w-full">
                            @error('medals_bronze') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <h3 class="font-bold text-lg text-slate-500 border-b border-slate-500 mb-2 pb-2">{{ __('Attachments') }}</h3>
                    <p class="text-xs text-gray-400 mb-2">Attach any relevant files for the event. You can add multiple
                        files.</p>
                    <div class="mb-4">
                        @foreach($attachments as $index => $attachment)
                            <div class="flex flex-col md:flex-row items-end justify-between gap-4 my-4">

                                <div class="w-2/3">
                                    @error('attachments.'.$index.'.file') <span
                                        class="text-red-500">{{ $message }}</span> @enderror
                                    <label class="block text-sm font-medium mb-1"> {{ __('File Name') }}
                                        <input class="form-input w-full" type="text"
                                               wire:model="attachments.{{ $index }}.name" placeholder="File Name">
                                    </label>
                                </div>

                                <div class="w-1/3">
                                    <label class="block text-sm font-medium mb-1"> {{ __('File') }} </label>

                                    <div
                                        x-data="{ uploading: false, progress: 0 }"
                                        x-on:livewire-upload-start="uploading = true"
                                        x-on:livewire-upload-finish="uploading = false"
                                        x-on:livewire-upload-error="uploading = false"
                                        x-on:livewire-upload-progress="progress = $event.detail.progress"
                                    >

                                        <div x-show="uploading">
                                            <progress max="100" x-bind:value="progress"></progress>
                                        </div>

                                        <input
                                            x-hide="uploading"
                                            wire:model="attachments.{{ $index }}.file"
                                            class="relative m-0 block w-full min-w-0 flex-auto rounded border border-solid border-neutral-300 bg-clip-padding px-3 text-base font-normal text-neutral-700 transition duration-300 ease-in-out file:-mx-3 file:py-2 file:overflow-hidden file:rounded-none file:border-0 file:border-solid file:border-inherit file:bg-neutral-100 file:px-3 file:text-slate-700 file:transition file:duration-150 file:ease-in-out file:[margin-inline-end:0.75rem] file:[border-inline-end-width:1px] hover:file:bg-neutral-200 focus:border-primary focus:text-neutral-700 focus:shadow-[0_0_0_1px] focus:shadow-primary focus:outline-none dark:border-neutral-600 dark:text-neutral-200 dark:file:bg-neutral-700 dark:file:text-neutral-100"
                                            type="file"
                                        />

                                    </div>

                                </div>


                                <div>
                                    <button class="btn btn-danger" type="button"
                                            wire:click="removeAttachment({{ $index }})">Remove
                                    </button>
                                </div>

                            </div>
                        @endforeach
                        <button class="btn btn-info" type="button" wire:click="addAttachment">Click to append File
                        </button>
                    </div>
                </div>

                @if(!empty($disciplines))
                    <div class="card mt-4">
                        <livewire:evt-events.competition-event-pricing-component
                            :competition_id="$competition_id"
                            :event_id="$event_id" />
                    </div>
                @endif
            </section>

            <section class="w-full md:w-1/3 flex flex-col gap-y-4">
                <!-- Status -->
                <div class="card">
                    <div class="flex gap-x-2 items-center border-b border-gray-300 pb-2 mb-4">
                        <x-svg.toggles class="w-6 h-6 text-slate-600" />
                        <span class="font-bold">{{ __('Status') }}</span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="status_class">Status</label>
                        <select id="status_class" wire:model="status_class" class="form-input w-full">
                            @foreach($status_options as $status)
                                <option value="{{ $status->name }}"
                                        @if(old('status') == $status->name) selected @endif>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Venue -->
                <div class="card">
                    <div class="flex gap-x-2 items-center border-b border-gray-300 pb-2 mb-4">
                        <x-svg.geo-alt class="w-6 h-6 text-slate-600" />
                        <span class="font-bold">{{ __('Venue Information') }}</span>
                    </div>
                    <div class="flex flex-col gap-y-2">
                        <div class="w-full">
                            <label class="block text-sm font-medium mb-1" for="venue">Venue Name</label>
                            <input type="text" name="venue" id="venue" wire:model="venue" class="form-input w-full">
                        </div>

                        <div class="w-full">
                            <label class="block text-sm font-medium mb-1" for="venue_address">Venue Address</label>
                            <input type="text" name="venue_address" id="venue_address" wire:model="venue_address"
                                   class="form-input w-full">
                        </div>

                        <div class="w-full">
                            <label class="block text-sm font-medium mb-1" for="venue_city">Venue City</label>
                            <input type="text" name="venue_city" id="venue_city" wire:model="venue_city"
                                   class="form-input w-full">
                        </div>

                        <div class="w-full">
                            <label class="block text-sm font-medium mb-1" for="venue_country">Venue Country</label>
                            <select name="venue_country"
                                    id="venue_country"
                                    wire:model="venue_country"
                                    class="form-input w-full {{ $errors->has('venue_country') ? 'border-rose-300' : '' }}">
                                <option value="" selected> {{ __('-- Select an option --') }} </option>
                                @foreach($countries as $key => $country)
                                    <option
                                        value="{{ $key }}"
                                        @if(old('venue_country') == $key) selected @endif
                                    >{{ $country }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Anti-Doping -->
                <div class="card">
                    <div class="flex gap-x-2 items-center border-b border-gray-300 pb-2 mb-4">
                        <x-svg.capsule-pill class="w-6 h-6 text-slate-600" />
                        <span class="font-bold">{{ __('Anti-Doping Information') }}</span>
                    </div>
                    <div class="flex flex-col gap-2">
                        <div class="w-full">
                            <label for="n_control_planned" class="block text-sm font-medium mb-1">Number of controls
                                planned</label>
                            <input type="number" min="0" wire:model="n_control_planned" id="n_control_planned"
                                   class="form-input w-full">
                            @error('n_control_planned') <span
                                class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="w-full">
                            <label class="block text-sm font-medium mb-1" for="n_control">Number of controls</label>
                            <input type="number" name="n_control" id="n_control" wire:model="n_control"
                                   class="form-input w-full">
                        </div>
                    </div>
                </div>
            </section>


        </section>
        <!-- Save Button -->
        <div class="mt-4">
            <x-forms.card-form-submit
                buttonText="Save"
                backRoute="admin.evt-events.events.index">
            </x-forms.card-form-submit>
        </div>

    </form>

</div>
