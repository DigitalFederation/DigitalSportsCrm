@section('title', __('diving.add_technical_director'))
<x-layout>
    <div class="previous-layout-classes">
        
        <!-- Page header -->
        <div class="mb-8">
            <h1 class="page-first-title">{{ __('diving.add_technical_director') }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                {{ __('diving.license') }}: {{ $licenseAttributed->license_name }}
            </p>
        </div>

        <!-- Form -->
        <form action="{{ route('entity.diving_license_directors.store', $licenseAttributed) }}" 
              method="POST"
              class="space-y-6">
            @csrf

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-4 py-5 sm:p-6">
                    
                    <!-- Nº Filiado -->
                    <div class="mb-6">
                        <label for="individual_code" class="block text-sm font-medium text-gray-700">
                            {{ __('diving.member_code') }} *
                        </label>
                        <div class="mt-1 relative">
                            <input type="text" 
                                   name="individual_code" 
                                   id="individual_code"
                                   value="{{ old('individual_code') }}"
                                   class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                   placeholder="{{ __('diving.enter_member_code') }}"
                                   required>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <button type="button"
                                        onclick="window.livewire.emit('open-entity-instructor-modal')"
                                        class="text-primary-600 hover:text-primary-700">
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @error('individual_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-sm text-gray-500">
                            {{ __('diving.member_code_help') }}
                        </p>
                    </div>

                    <!-- Certification Systems -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            {{ __('diving.certification_systems') }} *
                        </label>
                        <p class="text-sm text-gray-500 mb-3">
                            {{ __('diving.select_certification_systems_help') }}
                        </p>
                        <div class="space-y-2">
                            @foreach(config('diving.certification_systems') as $system)
                                <label class="inline-flex items-center mr-6">
                                    <input type="checkbox" 
                                           name="certification_systems[]" 
                                           value="{{ $system }}"
                                           {{ in_array($system, old('certification_systems', [])) ? 'checked' : '' }}
                                           class="form-checkbox h-4 w-4 text-primary-600 transition duration-150 ease-in-out">
                                    <span class="ml-2 text-sm text-gray-700">{{ $system }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('certification_systems')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('certification_systems.*')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Message (Optional) -->
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700">
                            {{ __('diving.invitation_message') }}
                        </label>
                        <div class="mt-1">
                            <textarea id="message"
                                      name="message"
                                      rows="3"
                                      class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                      placeholder="{{ __('diving.optional_message_placeholder') }}">{{ old('message') }}</textarea>
                        </div>
                        @error('message')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-sm text-gray-500">
                            {{ __('diving.message_will_be_included_in_invitation') }}
                        </p>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                    <a href="{{ route('entity.diving_license_directors.index', $licenseAttributed) }}"
                       class="btn btn-secondary mr-3">
                        {{ __('diving.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        {{ __('diving.send_invitation') }}
                    </button>
                </div>
            </div>
        </form>

        <!-- Information Box -->
        <div class="mt-6">
            <x-information-box 
                title="{{ __('diving.how_it_works') }}" 
                body="{{ __('diving.director_invitation_process_explanation') }}">
            </x-information-box>
        </div>
    </div>

    <!-- Include the instructor selector modal -->
    @livewire('entity-instructor-selector-modal')

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Listen for individual selection from modal
            window.addEventListener('individual-selected', function(e) {
                document.getElementById('individual_code').value = e.detail.code;
            });
        });
    </script>
    @endpush
</x-layout>