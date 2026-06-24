@section('title', __('member_number_settings.title'))
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('member_number_settings.title') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2"></div>

        </div>
        
        <x-information-box
            title="{{ __('member_number_settings.instructions_title') }}"
            body="{{ __('member_number_settings.instructions_body') }}">
        </x-information-box>

        <div class="sm:flex sm:justify-center sm:items-center">

            <div class="card w-full">
                <h3 class="text-base text-slate-800 font-semibold mb-4">{{ __('member_number_settings.current_counters') }}</h3>
                
                <form action="{{ route('admin.member-number-settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Individual Counter -->
                        <div class="flex flex-col">
                            <label for="individual_counter" class="mb-2 font-medium text-gray-700">
                                {{ __('member_number_settings.individual_counter') }}
                            </label>
                            <input type="number" 
                                   name="individual_counter" 
                                   id="individual_counter" 
                                   class="form-input" 
                                   value="{{ $individualCounter }}"
                                   min="1"
                                   required>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ __('member_number_settings.next_individual_number', ['number' => $individualCounter]) }}
                            </p>
                        </div>

                        <!-- Entity Counter -->
                        <div class="flex flex-col">
                            <label for="entity_counter" class="mb-2 font-medium text-gray-700">
                                {{ __('member_number_settings.entity_counter') }}
                            </label>
                            <input type="number" 
                                   name="entity_counter" 
                                   id="entity_counter" 
                                   class="form-input" 
                                   value="{{ $entityCounter }}"
                                   min="1"
                                   required>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ __('member_number_settings.next_entity_number', ['number' => $entityCounter]) }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="btn btn-primary">
                            {{ __('member_number_settings.update_counters') }}
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <!-- Information Section -->
        <div class="mt-6">
            <div class="card">
                <h4 class="text-base font-semibold mb-3">{{ __('member_number_settings.important_information') }}</h4>
                <ul class="list-disc list-inside space-y-2 text-sm text-gray-600">
                    <li>{{ __('member_number_settings.info_1') }}</li>
                    <li>{{ __('member_number_settings.info_2') }}</li>
                    <li>{{ __('member_number_settings.info_3') }}</li>
                    <li>{{ __('member_number_settings.info_4') }}</li>
                    <li>{{ __('member_number_settings.info_5') }}</li>
                </ul>
            </div>
        </div>

    </div>
</x-layout>