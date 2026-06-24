@section('title', __('diving.request_diving_license'))
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
        <!-- Left: Title -->
        <div class="mb-4 sm:mb-0">
            <h1 class="page-first-title">{{ __('diving.request_diving_license') }}</h1>
            <nav class="mt-2" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li>
                        <a href="{{ route('entity.diving_licenses.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">
                            {{ __('diving.diving_licenses') }}
                        </a>
                    </li>
                    <li class="text-gray-500">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </li>
                    <li class="text-sm text-gray-700">
                        {{ __('diving.request_license') }}
                    </li>
                </ol>
            </nav>
        </div>
        
        <!-- Right: Actions -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <!-- No actions for this page -->
        </div>
    </div>

        <div class="mt-6">
            <div class="card">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    {{ __('diving.license_information') }}
                </h3>

                <form action="{{ route('entity.diving_licenses.submit') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    @if(!$license)
                    <!-- License Selection -->
                    <div>
                        <label for="license_id" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('diving.select_license_type') }} <span class="text-red-500">*</span>
                        </label>
                        <select id="license_id" name="license_id" required
                                class="form-select w-full {{ $errors->has('license_id') ? 'border-rose-300' : '' }}"
                                onchange="window.location.href='{{ route('entity.diving_licenses.request') }}?license_id=' + this.value">
                            <option value="">{{ __('diving.choose_license_type') }}</option>
                            @foreach($availableLicenses as $availableLicense)
                                <option value="{{ $availableLicense->id }}" {{ request('license_id') == $availableLicense->id ? 'selected' : '' }}>
                                    {{ $availableLicense->name }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('license_id'))
                            <div class="text-xs mt-1 text-rose-500">
                                {{ $errors->first('license_id') }}
                            </div>
                        @endif
                    </div>
                    @else
                    <input type="hidden" name="license_id" value="{{ $license->id }}">
                    <div class="information-box mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-800">
                                    {{ __('diving.you_are_requesting') }}: <strong>{{ $license->name }}</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div>
                        <h4 class="text-md font-medium text-gray-900 mb-3">{{ __('diving.technical_directors') }}</h4>
                        <p class="text-sm text-gray-500 mb-4">
                            {{ __('diving.select_multiple_directors') }}
                        </p>

                        <div id="technical-directors-container" class="space-y-4">
                            <div class="technical-director-entry panel-box">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">
                                            {{ __('diving.select_professional') }} <span class="text-red-500">*</span>
                                        </label>
                                        <select name="technical_directors[0][individual_id]" required
                                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                            <option value="">{{ __('diving.choose_diving_professional') }}</option>
                                            @foreach($potentialDirectors as $director)
                                                <option value="{{ $director->id }}">
                                                    {{ $director->full_name }} - {{ $director->email }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            {{ __('diving.certification_systems') }} <span class="text-red-500">*</span>
                                        </label>
                                        <div class="space-y-2">
                                            @foreach(config('diving.certification_systems') as $system)
                                                <label class="inline-flex items-center mr-4">
                                                    <input type="checkbox" 
                                                           name="technical_directors[0][certification_systems][]" 
                                                           value="{{ $system }}"
                                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                    <span class="ml-2 text-sm text-gray-700">{{ $system }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" id="add-director" class="btn btn-secondary mt-4">
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0 mr-2" viewBox="0 0 16 16">
                                <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                            </svg>
                            {{ __('diving.add_another_director') }}
                        </button>
                    </div>

                    <div class="flex justify-end space-x-3 pt-6 border-t">
                        <a href="{{ route('entity.diving_licenses.index') }}"
                           class="btn btn-secondary">
                            {{ __('common.cancel') }}
                        </a>
                        <button type="submit"
                                class="btn btn-primary">
                            {{ __('diving.submit_request') }}
                        </button>
                    </div>
                </form>
            </div>

        <!-- Information Box -->
        <div class="mt-6">
            <x-information-box 
                title="{{ __('diving.important_information') }}" 
                body="{{ __('diving.directors_need_certifications') . PHP_EOL . 
                       __('diving.invitations_sent_all') . PHP_EOL . 
                       __('diving.license_activated_after_accept') . PHP_EOL . 
                       __('diving.assign_different_directors') }}">
            </x-information-box>
        </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let directorIndex = 1;
        
        document.getElementById('add-director').addEventListener('click', function() {
            const container = document.getElementById('technical-directors-container');
            const template = container.querySelector('.technical-director-entry').cloneNode(true);
            
            // Update input names with new index
            template.querySelectorAll('input, select').forEach(input => {
                if (input.name) {
                    input.name = input.name.replace('[0]', '[' + directorIndex + ']');
                    input.value = '';
                    if (input.type === 'checkbox') {
                        input.checked = false;
                    }
                }
            });
            
            // Add remove button
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'mt-2 text-sm text-red-600 hover:text-red-500';
            removeBtn.textContent = '{{ __('common.delete') }}';
            removeBtn.onclick = function() {
                template.remove();
            };
            template.appendChild(removeBtn);
            
            container.appendChild(template);
            directorIndex++;
        });
    </script>
    @endpush
</x-layout>