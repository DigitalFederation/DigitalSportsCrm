<div class="previous-layout-classes">

    <!-- Page header -->
    <div class="mb-8 flex justify-between">
        <!-- Title -->
        <h1 class="page-first-title">{{ __('Diving Files') }}</h1>
    </div>

    <div class="flex information-box mb-4 ">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-4" width="24" height="24" viewBox="0 0 24 24"
            stroke-width="1.5" stroke="#9e9e9e" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <circle cx="12" cy="12" r="9" />
            <line x1="12" y1="8" x2="12.01" y2="8" />
            <polyline points="11 12 12 12 12 16 13 16" />
        </svg>
        <p class="text-sm"> Use the form bellow to upload files. <br>
            <strong>Attention</strong> to the file size. Don't upload files bigger than 50MB.
        </p>

    </div>

    <h2 class="font-bold"> {{ __('File Upload Form') }}</h2>
    <div class="sm:space-x-4">

        <div>
            <div class="card md:-mr-px mb-8 w-full">
                <form wire:submit="save">
                    <div>
                        <input type="hidden" wire:model.live="model" value="$model">

                        <div class="md:flex flex-wrap justify-start items-center">

                            <div class="w-full md:w-96 flex flex-col">
                                <input
                                    class="relative m-0 block w-full min-w-0 flex-auto rounded border border-solid border-neutral-300 bg-clip-padding px-3 text-base font-normal text-neutral-700 transition duration-300 ease-in-out file:-mx-3 file:py-2 file:overflow-hidden file:rounded-none file:border-0 file:border-solid file:border-inherit file:bg-neutral-100 file:px-3 file:text-neutral-700 file:transition file:duration-150 file:ease-in-out file:[margin-inline-end:0.75rem] file:[border-inline-end-width:1px] hover:file:bg-neutral-200 focus:border-primary focus:text-neutral-700 focus:shadow-[0_0_0_1px] focus:shadow-primary focus:outline-none dark:border-neutral-600 dark:text-neutral-200 dark:file:bg-neutral-700 dark:file:text-neutral-100"
                                    type="file" wire:model.live="attachments" multiple />
                                @if (array_key_exists('attachments', $validationErrors))
                                    <span class="error">{{ $validationErrors['attachments'][0] }}</span>
                                @endif
                            </div>

                            <div class="w-full md:w-auto md:ml-4 flex flex-col md:mt-0 mt-2">
                                <select class="p-2 rounded border border-solid border-neutral-300 form-select"
                                    wire:model.live="type" name="type" required>
                                    <option value="" selected disabled> -- Select a document type --</option>
                                    @foreach ($types as $key => $type)
                                        @if (is_array($type))
                                            <optgroup label="{{ ucfirst($key) }}">
                                                @foreach ($type as $value)
                                                    <option value="{{ $value }}"> {{ $value }} </option>
                                                @endforeach
                                            </optgroup>
                                        @else
                                            <option value="{{ $type }}"> {{ $type }} </option>
                                        @endif
                                    @endforeach
                                </select>
                                @if (array_key_exists('type', $validationErrors))
                                    <span class="error">{{ $validationErrors['type'][0] }}</span>
                                @endif
                            </div>

                            @if (!empty($federations))
                                <div class="w-full md:w-auto md:ml-4 flex flex-col md:mt-0 mt-2">
                                    <select class="p-2 rounded border border-solid border-neutral-300 form-select"
                                        wire:model.live="federation_id" name="federation_id" required>
                                        <option value="null" selected disabled> -- Select a federation --</option>
                                        <option value=""></option>
                                        @foreach ($federations as $federation)
                                            <option value="{{ $federation->id }}">{{ $federation->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if ($licenses->count() >= 1)
                                <div class="w-full md:w-auto md:ml-4 flex flex-col md:mt-0 mt-2">
                                    <select class="p-2 rounded border border-solid border-neutral-300 form-input"
                                        wire:model.live="license_ids" name="license_ids" multiple required>
                                        <option selected disabled> -- Selects the license corresponding
                                            to that file --</option>
                                        @foreach ($licenses as $license)
                                            <option value="{{ $license->id }}">{{ $license->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if ($certifications->count() >= 1)
                                <div class="w-full md:w-auto md:ml-4 flex flex-col md:mt-0 mt-2">
                                    <select class="p-2 rounded border border-solid border-neutral-300 form-input"
                                        wire:model.live="certification_ids" name="certification_ids" multiple required>
                                        <option value="" selected disabled> -- Selects the certification
                                            corresponding to that file --</option>
                                        @foreach ($certifications as $certification)
                                            <option value="{{ $certification->id }}">{{ $certification->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if ($professionalRoles->count() >= 1)
                                <div class="w-full md:w-auto md:ml-4 flex flex-col md:mt-0 mt-2">
                                    <select class="p-2 rounded border border-solid border-neutral-300 form-select"
                                        wire:model.live="professionalRole_id" name="professionalRole_id" required>
                                        <option value="null" selected disabled> -- Selects the professional
                                            corresponding to that file --</option>
                                        @foreach ($professionalRoles as $professionalRole)
                                            <option value="{{ $professionalRole->id }}">{{ $professionalRole->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if ($countries->count() >= 1)
                                <div class="w-full md:w-auto md:ml-4 flex flex-col md:mt-0 mt-2">
                                    <select class="p-2 rounded border border-solid border-neutral-300 form-select"
                                        wire:model.live="country_id" name="country_id" required>
                                        <option value="" selected disabled> -- Select the country that you want to
                                            send the document to --</option>
                                        @foreach ($countries as $country)
                                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <button type="button" wire:click="save"
                                class="btn btn-action w-full md:w-auto ml-0 md:ml-2 mt-2 md:mt-0">Upload
                                File</button>
                        </div>

                        <div wire:loading wire:target="save">
                            Processing file upload...
                        </div>

                        <div>{{ $message }}</div>
                    </div>

                </form>
            </div>

            @if (!empty($model) && $model->count() > 0)
                <h2 class="font-bold">{{ __('Files Uploaded') }}</h2>
                <div class="sm:flex sm:space-x-4">

                    <div class="card md:-mr-px mb-8 w-full">
                        <table class="table-auto w-full">

                            <thead
                                class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                                <tr>
                                    <th class="py-2 text-center"> {{ __('Category') }} </th>
                                    <th class="py-2 text-center"> {{ __('Licenses') }} </th>
                                    <th class="py-2 text-center"> {{ __('Certifications') }} </th>
                                    <th class="py-2 text-center"> {{ __('Country') }} </th>
                                    <th class="py-2 text-center"> {{ __('Professional') }} </th>
                                    <th class="py-2 text-center"> {{ __('Date') }} </th>
                                    <th class="py-2 text-right"></th>
                                </tr>
                            </thead>

                            <tbody class="text-sm divide-y divide-slate-200">
                                @foreach ($model as $document)
                                    <tr>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 break-words text-center">
                                            {{ $document->category }}
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 break-words text-center">
                                            @foreach ($document->licenses as $documentLicense)
                                                <p>{{ $documentLicense->name }}</p>
                                            @endforeach
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 break-words text-center">
                                            @foreach ($document->licenses as $documentLicense)
                                                <p>{{ $documentLicense->name }}</p>
                                            @endforeach
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 break-words text-center">
                                            <span class="flex items-center justify-center">
                                                <img src="{{ asset('img/flags/' . strtolower($document->country->iso) . '.svg') }}"
                                                    alt="flag" class="w-4 h-4 mr-1" />
                                                {{ $document->country->name }}
                                            </span>
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 break-words text-center">
                                            <span class="flex items-center justify-center">
                                                {{ $document->professional_role?->name }}
                                            </span>
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-center">
                                            <div>{{ \Carbon\Carbon::parse($document->created_at)->format('d-m-Y') }}
                                            </div>
                                            <small>({{ \Carbon\Carbon::parse($document->created_at)->diffForHumans() }})</small>
                                        </td>
                                        <td
                                            class="px-2 first:pl-5 last:pr-5 py-3 break-words text-right flex justify-end">
                                            <form
                                                action="{{ route(strtolower(auth()->user()->group->pluck('name')->first()) . '.media.download') }}"
                                                method="POST" enctype="multipart/form-data" class="w-auto">
                                                @csrf
                                                <input type="hidden" name="id"
                                                    value="{{ $document->media->value('id') }}">
                                                <button type="submit"
                                                    class="btn-sm border-slate-200 hover:border-slate-300 shadow-sm">
                                                    <span class="flex items-center">
                                                        {{ __('Download') }}
                                                    </span>
                                                </button>
                                            </form>

                                            <form
                                                action="{{ route(strtolower(auth()->user()->group->pluck('name')->first()) . '.diving-attachment.delete',$document->id) }}"
                                                method="POST" class="ml-4 w-auto"
                                                onsubmit="return confirm('Are you sure you want to delete this file?')">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                    class="btn-sm bg-red-500 border-red-200 hover:border-red-300 text-white shadow-sm">
                                                    <span class="flex items-center">
                                                        {{ __('Delete') }}
                                                    </span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            @else
                <!-- No documents uploaded -->
                <div class="sm:flex sm:space-x-4 text-center ">
                    <p class="mt-2 md:mt-4 text-center text-gray-700 text-xl font-bold mx-auto">
                        {{ __('No files uploaded yet') }} </p>
                </div>

            @endif

        </div>
    </div>
</div>
