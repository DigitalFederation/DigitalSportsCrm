@section('title', __('Invite Technical Director'))
<x-layout>
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center">
        <!-- Left: Title -->
        <div class="mb-4 sm:mb-0">
            <h1 class="page-first-title">{{ __('diving.invite_technical_director') }}</h1>
            <nav class="mt-2" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li>
                        <a href="{{ route('entity.diving_licenses.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">
                            {{ __('Diving Licenses') }}
                        </a>
                    </li>
                    <li class="text-gray-500">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </li>
                    <li>
                        <a href="{{ route('entity.diving_licenses.show', $licenseAttributed) }}" class="text-gray-500 hover:text-gray-700 text-sm">
                            {{ $licenseAttributed->license->name }}
                        </a>
                    </li>
                    <li class="text-gray-500">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </li>
                    <li class="text-sm text-gray-700">
                        {{ __('Invite Director') }}
                    </li>
                </ol>
            </nav>
        </div>
        
        <!-- Right: Actions -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <!-- No actions for this page -->
        </div>
    </div>

    <div class="mt-6 max-w-3xl mx-auto">
        <form action="{{ route('entity.diving_licenses.send_director_invitation', $licenseAttributed) }}" method="POST" class="space-y-8">
            @csrf

            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        {{ __('diving.technical_director_invitation') }}
                    </h3>

                    <div class="bg-blue-50 p-4 rounded-md mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-800">
                                    {{ __('diving.inviting_director_for') }}: <strong>{{ $licenseAttributed->license->name }}</strong>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <!-- Select Professional -->
                        <div>
                            <label for="individual_id" class="block text-sm font-medium text-gray-700">
                                {{ __('Select Diving Professional') }} <span class="text-red-500">*</span>
                            </label>
                            <p class="text-sm text-gray-500 mb-2">
                                {{ __('Choose a certified diving instructor to serve as technical director') }}
                            </p>
                            @livewire('entity-instructor-selector-modal', ['committee' => 'diving'])
                            @error('individual_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Certification System -->
                        <div>
                            <label for="certification_system" class="block text-sm font-medium text-gray-700">
                                {{ __('diving.certification_system') }} <span class="text-red-500">*</span>
                            </label>
                            <p class="text-sm text-gray-500 mb-2">
                                {{ __('diving.certification_system_of_the_director') }}
                            </p>
                            <select id="certification_system" name="certification_system" required
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">{{ __('diving.select_certification_system') }}</option>
                                <option value="SSI" {{ old('certification_system') == 'SSI' ? 'selected' : '' }}>SSI</option>
                                <option value="PADI" {{ old('certification_system') == 'PADI' ? 'selected' : '' }}>PADI</option>
                                <option value="SDI/TDI" {{ old('certification_system') == 'SDI/TDI' ? 'selected' : '' }}>SDI/TDI</option>
                                <option value="DDI" {{ old('certification_system') == 'DDI' ? 'selected' : '' }}>DDI</option>
                                <option value="GUE" {{ old('certification_system') == 'GUE' ? 'selected' : '' }}>GUE</option>
                                <option value="Other" {{ old('certification_system') == 'Other' ? 'selected' : '' }}>{{ __('diving.other') }}</option>
                            </select>
                            @error('certification_system')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Message -->
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700">
                                {{ __('diving.personal_message') }}
                            </label>
                            <p class="text-sm text-gray-500 mb-2">
                                {{ __('diving.optional_message') }}
                            </p>
                            <textarea id="message" name="message" rows="3"
                                      class="mt-1 block w-full shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 rounded-md"
                                      placeholder="{{ __('diving.invitation_message_placeholder') }}">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="px-4 py-3 bg-gray-50 sm:px-6 flex justify-end space-x-3">
                    <a href="{{ route('entity.diving_licenses.show', $licenseAttributed) }}"
                       class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ __('diving.cancel') }}
                    </a>
                    <button type="submit"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ __('diving.send_invitation') }}
                    </button>
                </div>
            </div>
        </form>

        <!-- Information Box -->
        <div class="mt-6">
            <x-information-box 
                title="{{ __('diving.what_happens_next') }}" 
                body="{{ __('diving.professional_receives_email') . PHP_EOL . 
                       __('diving.must_accept_invitation') . PHP_EOL . 
                       __('diving.responsible_for_operations') . PHP_EOL . 
                       __('diving.multiple_directors_systems') }}">
            </x-information-box>
        </div>
    </div>
</x-layout>