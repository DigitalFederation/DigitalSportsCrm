<div class="previous-layout-classes">

    <!-- Page header -->
    <div class="mb-8 flex justify-between">
        <h1 class="page-first-title">@if(!empty($committee))
                {{ $committee->code }}
            @else
                {{ config('branding.international.short_name', 'IF') }}
            @endif {{ __('Files') }} </h1>
    </div>

    <x-information-box
        title="Attention"
        :body="__('The file size limit is 50MB. If you need to upload a larger file, please contact the IT Department.')" />


    <h2 class="font-bold"> {{ __('File Upload Form') }}</h2>

    <div class="card md:-mr-px mb-8 w-full">

        <!-- Status Messages -->
        @if(!empty($message_type) && !empty($message_title))
            <div
                class="flex gap-4 @if($message_type =='success') bg-admin_green @elseif($message_type == 'info') bg-cyan-300  @else bg-canceled @endif p-4 rounded-md mb-8 items-center">
                <div class="w-max">
                    <div class="flex rounded-full text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="w-8 h-8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>

                    </div>
                </div>
                <div class="text-sm">
                    <h6 class=" text-white font-bold"> {{ $message_title }} </h6>
                    <p class="text-white leading-tight">
                        {{ $message_body }}
                    </p>
                </div>
            </div>
        @endif
        <!-- end Status Messages -->

        <form wire:submit="saveAttachment">

            <input type="hidden" wire:model.live="model" value="$model">

            <div class="flex flex-col md:flex-row justify-start items-start gap-x-4 mb-4">

                <div class="w-full md:w-1/2 flex flex-col">

                    <label class="block text-sm font-medium mb-1" for="attachments">{{ __('File Name') }}</label>
                    <input
                        class="form-input w-full"
                        type="text"
                        wire:model.live="attachment_name" />

                </div>


                <div class="w-full md:w-1/2 flex flex-col">

                    <label class="block text-sm font-medium mb-1" for="attachments">{{ __('File to upload') }}
                    </label>
                    <input
                        class="relative m-0 block w-full min-w-0 flex-auto rounded border border-solid border-neutral-300 bg-clip-padding px-3 text-base font-normal text-neutral-700 transition duration-300 ease-in-out file:-mx-3 file:py-2 file:overflow-hidden file:rounded-none file:border-0 file:border-solid file:border-inherit file:bg-neutral-100 file:px-3 file:text-neutral-700 file:transition file:duration-150 file:ease-in-out file:[margin-inline-end:0.75rem] file:[border-inline-end-width:1px] hover:file:bg-neutral-200 focus:border-primary focus:text-neutral-700 focus:shadow-[0_0_0_1px] focus:shadow-primary focus:outline-none dark:border-neutral-600 dark:text-neutral-200 dark:file:bg-neutral-700 dark:file:text-neutral-100"
                        type="file"
                        name="attachments"
                        wire:model="attachments"
                    />

                </div>


            </div>

            <div class="flex flex-col md:flex-row justify-start items-start gap-x-4 mb-4">
                <div class="w-full md:w-1/3 flex flex-col">

                    <label class="block text-sm font-medium mb-1" for="recipient">{{ __('Recipient') }} <span
                            class="text-red-500">*</span></label>
                    <select class="p-2 pr-6 rounded border border-solid border-neutral-300 form-select"
                            wire:model.live="recipient" name="type" required>
                        <option value="" selected> -- Select a recipient --</option>
                        @foreach ($recipients as $key => $type)
                            <option value="{{ $key }}"> {{ $type }} </option>
                        @endforeach
                    </select>

                </div>

                <div class="w-full md:w-1/3 flex flex-col">

                    <label class="block text-sm font-medium mb-1" for="categories">{{ __('Document Category') }} <span
                            class="text-red-500">*</span></label>
                    <select class="pr-6 rounded border border-solid border-neutral-300 form-select w-full"
                            wire:model.live="selected_category" name="type" required>
                        <option value="" selected disabled> -- Select a category --</option>
                        @foreach ($categories as $key => $type)
                            @if (is_array($type))
                                <optgroup label="{{ ucfirst($key) }}">
                                    @foreach ($type as $value)
                                        <option value="{{ $key }}"> {{ $value }} </option>
                                    @endforeach
                                </optgroup>
                            @else
                                <option value="{{ $key }}"> {{ $type }} </option>
                            @endif
                        @endforeach
                    </select>

                </div>

                <div class="w-full md:w-1/3 flex flex-col">

                    <label class="block text-sm font-medium mb-1" for="categories">{{ __('File Language') }} </label>
                    <select class="pr-6 rounded border border-solid border-neutral-300 form-select"
                            wire:model.live="selected_language" required>
                        <option value="" selected disabled> -- Select a language --</option>
                        @foreach ($languages as $language)
                            <option value="{{ $language['id'] }}">{{ $language['name'] }}</option>
                        @endforeach
                    </select>
                </div>

            </div>

            <!-- Federation Section -->

            @if(!empty($recipient) && $recipient == 'federation')
                <div class="flex information-box mb-4 items-center gap-x-2">
                    <x-svg.exclamation class="h-6 w-6 text-slate-600" />
                    <p class="text-sm"> {{ __('Use the available options to filter Federations to whom you need to allow access to the file. Filter by Country, License or choose a specific record from the list.') }} </p>
                </div>
                <section
                    class="flex flex-col md:flex-row justify-start items-start gap-x-4 my-4 border-t border-slate-600 pt-4">

                    <div class="w-full md:w-1/3 flex flex-col">
                        <label class="block text-sm font-medium mb-1" for="licenses">{{ __('Licenses') }} </label>
                        <livewire:input.select-multiple
                            wire.model.live="federation_licenses"
                            :items="$licenses"
                            inputId="licenses"
                            inputName="federation_licenses[]"
                            identifier="federation_licenses" />
                    </div>


                    <div class="w-full md:w-1/3 flex flex-col">

                        <label class="block text-sm font-medium mb-1" for="licenses">{{ __('Country') }} </label>
                        <livewire:input.select-multiple
                            wire.model.live="federation_countries"
                            :options="['maxItemCount' => 1]"
                            :items="$countries"
                            inputId="federation_countries"
                            inputName="federation_countries[]"
                            identifier="federation_countries" />

                    </div>

                    <div class="w-full md:w-1/3 flex flex-col">

                        <label class="block text-sm font-medium mb-1"
                               for="federations">{{ __('Federations list') }}</label>
                        <livewire:input.select-multiple
                            wire.model.live="federation_federations"
                            :options="['maxItemCount' => 1]"
                            :items="$federations"
                            inputId="federation_federations"
                            inputName="federation_federations[]"
                            identifier="federation_federations" />

                    </div>


                </section>
            @endif

            <!-- Entities Section -->
            @if(!empty($recipient) && $recipient == 'entity')
                <div class="flex information-box mb-4 items-center gap-x-2">
                    <x-svg.exclamation class="h-6 w-6 text-slate-600" />
                    <p class="text-sm"> {{ __('Use the available options to filter Entities to whom you need to allow access to the file. Filter only from one: Licenses or Federations. Not both.') }} </p>
                </div>
                <section
                    class="flex flex-col md:flex-row justify-start items-start gap-x-4 my-4 border-t border-slate-600 pt-4">

                    <div class="w-full md:w-1/3 flex flex-col">
                        <label class="block text-sm font-medium mb-1"
                               for="entities_licenses">{{ __('Licenses') }} </label>
                        <livewire:input.select-multiple
                            wire.model.live="entities_licenses"
                            :items="$entity_licenses"
                            inputId="entities_licenses"
                            inputName="entities_licenses[]"
                            identifier="entities_licenses" />
                    </div>
                </section>
            @endif

            <!-- Individuals Section -->
            @if($this->recipient == 'individual')
                <div class="flex information-box mb-4 items-center gap-x-2">
                    <x-svg.exclamation class="h-6 w-6 text-slate-600" />
                    <p class="text-sm"> {{ __('Use the available options to filter Individuals to whom you need to allow access to the file. Filter only from one: Licenses, Certifications or Roles.') }} </p>
                </div>
                <section
                    class="flex flex-col md:flex-row justify-between items-center gap-x-4 my-4 border-t border-slate-600 pt-4">

                    <div class="w-full md:w-1/3 flex flex-col">
                        <label class="block text-sm font-medium mb-1"
                               for="individuals_licenses">{{ __('Licenses') }} </label>
                        <livewire:input.select-multiple
                            wire.model.live="individuals_licenses"
                            :items="$individual_licenses"
                            inputId="individuals_licenses"
                            inputName="individuals_licenses[]"
                            identifier="individuals_licenses" />
                    </div>
                    <div class="w-full md:w-1/12 flex flex-col text-center">
                        <p> {{ __('-- OR --') }} </p>
                    </div>
                    <div class="w-full md:w-1/3 flex flex-col">
                        <label class="block text-sm font-medium mb-1"
                               for="individuals_certifications">{{ __('Certifications') }} </label>
                        <livewire:input.select-multiple
                            wire.model.live="individuals_certifications"
                            :items="$certifications"
                            inputId="individuals_certifications"
                            inputName="individuals_certifications[]"
                            identifier="individuals_certifications" />
                    </div>
                    <div class="w-full md:w-1/12 flex flex-col text-center">
                        <p> {{ __('-- OR --') }} </p>
                    </div>

                    <div class="w-full md:w-1/3 flex flex-col">
                        <label class="block text-sm font-medium mb-1"
                               for="individuals_professional_roles">{{ __('Role') }} </label>
                        <livewire:input.select-multiple
                            wire.model.live="individuals_professional_roles"
                            :items="$professional_roles"
                            inputId="individuals_professional_roles"
                            inputName="individuals_professional_roles[]"
                            identifier="individuals_professional_roles" />
                    </div>

                </section>
            @endif

            <!-- Preview Section -->
            @if(!empty($federations_preview) || !empty($entities_preview_count) || !empty($individuals_preview_count))
                <section class="border-t border-slate-300 pt-6 mt-6">
                    @if(!empty($federations_preview) && count($federations_preview) > 0)
                        <h2 class="font-bold text-lg mb-4 text-slate-400"> {{ __('Recipients Preview') }}</h2>
                        <table class="w-full table-auto border border-slate-400">
                            <thead>
                            <tr>
                                <th class="text-left border border-slate-600 p-2">Federation Name</th>
                                <th class="text-left border border-slate-600 p-2">Code</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($this->federations_preview as $federation)
                                <tr>
                                    <td class="border border-slate-700 p-2 text-sm">{{ $federation->name }}</td>
                                    <td class="border border-slate-700 p-2 text-sm">{{ $federation->member_code }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @elseif(!empty($entities_preview_count))
                        <h2 class="font-bold text-lg mb-4 text-slate-400"> {{ __('Recipients Preview') }}</h2>
                        <div>
                            <strong>Total Entities: </strong> <span
                                class="text-2xl font-bold">{{ $this->entities_preview_count }}</span>
                        </div>
                    @elseif(!empty($individuals_preview_count))
                        <h2 class="font-bold text-lg mb-4 text-slate-400"> {{ __('Recipients Preview') }}</h2>
                        <div>
                            <strong>Total Individuals: </strong> <span
                                class="text-2xl font-bold"> {{ $this->individuals_preview_count }} </span>
                        </div>
                    @endif
                </section>
            @endif

            <section class="border-t border-slate-300 pt-6 mt-6">

                <div class="flex self-end gap-x-4">

                    <a class="btn self-center bg-slate-500 text-white" href="{{ URL::previous() }}">
                        {{ __('Back') }}
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        @if(empty($recipient)) disabled @endif>
                        Upload File
                    </button>

                </div>


                <div wire:loading wire:target="save">
                    Processing file upload...
                </div>

            </section>


        </form>
    </div>

</div>
