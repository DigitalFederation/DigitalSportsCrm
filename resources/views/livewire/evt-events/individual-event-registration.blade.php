<div x-data="{
    showSuccess: false,
    message: '',
    documentId: null,
    totalCost: 0,
    participantCount: 0
}"
    x-on:registration-success.window="
        showSuccess = true;
        message = $event.detail.totalCost > 0
            ? '{{ __('events.registration_successful_proceed_payment') }}'
            : '{{ __('events.registration_completed_successfully') }}';
        documentId = $event.detail.documentId;
        totalCost = $event.detail.totalCost;
        participantCount = $event.detail.participantCount;
    "
    class="relative"
>
    <!-- Success Message -->
    @if($showSuccessMessage)
        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg mb-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <x-heroicon-s-check-circle class="w-5 h-5 text-green-400 mr-3" />
                    <p class="text-green-700">{{ $successMessage }}</p>
                </div>
                @if($documentId)
                    <a href="{{ route('federation.document.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        <x-heroicon-s-arrow-right class="w-5 h-5 mr-2" />
                        {{ __('events.proceed_to_payment') }}
                    </a>
                @endif
            </div>
        </div>
    @endif

    {{-- Back Button Header --}}
    <div class="mb-6">
        <div class="flex md:flex-row flex-col items-center justify-between">
            <div class="flex items-center space-x-2">
                <a href="{{
                    $this->model instanceof \Domain\Federations\Models\Federation
                        ? route('federation.evt-events.events.show', $event)
                        : route('entity.evt-events.events.show', $event)
                }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <x-heroicon-m-arrow-left class="w-5 h-5 mr-2 -ml-1" />
                    {{ __('events.back_to_event', ['event' => $event->name]) }}
                </a>
            </div>
            <div class="flex items-center text-sm text-gray-500 mt-2 md:mt-0">
                <x-heroicon-m-calendar class="w-5 h-5 mr-1" />
                {{ $event->start_date->format('M d, Y') }} - {{ $event->end_date->format('M d, Y') }}
            </div>
        </div>
    </div>

    <div class="space-y-8">
        <!-- Hero Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="max-w-full">
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $event->name }}
                </h1>
                <p class="mt-2 text-gray-600">
                    {{ __('events.register_individuals_instructions') }}
                </p>

                <!-- Registration Stats -->
                <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-3 w-full">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <dt class="text-sm font-medium text-gray-500"> {{ __('events.total_selected') }}</dt>
                        <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                            <div class="flex items-baseline text-2xl font-semibold text-primary-600">
                                {{ $selectedCount }}
                                <span class="ml-2 text-sm font-medium text-gray-500"> {{ __('events.participants') }}</span>
                            </div>
                        </dd>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <dt class="text-sm font-medium text-gray-500">
                            {{ __('events.total_cost') }}
                        </dt>
                        <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                            <div class="flex items-baseline text-2xl font-semibold text-primary-600">
                                €{{ number_format($totalCost, 2) }}
                            </div>
                        </dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            <!-- Left Column: Participant Table -->
            <div class="col-span-12 md:col-span-8 lg:col-span-9">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <!-- Tab Navigation -->
                    <nav class="flex space-x-4 border-b border-gray-200 px-6">
                        <button
                            wire:click="$set('activeTab', 'participants')"
                            class="px-4 py-3 text-sm font-medium relative
                                {{ $activeTab === 'participants'
                                    ? 'text-primary-600 border-b-2 border-primary-600'
                                    : 'text-gray-500 hover:text-gray-700' }}"
                        >
                            {{ __('events.enrollments') }}
                            @if($selectedCount > 0)
                                <span class="ml-2 bg-primary-100 text-primary-700 px-2 py-1 rounded-full text-xs">
                                    {{ $selectedCount }}
                                </span>
                            @endif
                        </button>

                        @if(count($selectedParticipants) > 0 && !empty($roleAttributes))
                            <button
                                wire:click="$set('activeTab', 'attributes')"
                                class="px-4 py-3 text-sm font-medium relative
                                    {{ $activeTab === 'attributes'
                                        ? 'text-primary-600 border-b-2 border-primary-600'
                                        : 'text-gray-500 hover:text-gray-700' }}"
                            >
                                {{ __('events.attributes') }}
                            </button>
                        @endif
                    </nav>

                    <!-- Tab Content -->
                    <div class="p-6">
                        @if($activeTab === 'participants')
                            <div x-data="{ selectedRecords: @entangle('selectedRecords') }">
                                {{ $this->table }}
                            </div>
                        @else
                            <div class="space-y-6">
                                <!-- Header Section -->
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            {{ __('events.participant_attributes') }}
                                        </h3>
                                        <p class="text-sm text-gray-500 mt-1">
                                            {{ __('events.required_info_for_participants') }}
                                            <span class="text-primary-600 font-medium">
                                                {{ __('events.required_fields_marked') }}
                                            </span>
                                        </p>
                                    </div>
                                    <button
                                        wire:click="$set('activeTab', 'participants')"
                                        class="inline-flex items-center text-sm text-primary-600 hover:text-primary-800"
                                    >
                                        <x-heroicon-m-arrow-left class="w-4 h-4 mr-1" />
                                        {{ __('events.back_to_participants') }}
                                    </button>
                                </div>

                                <!-- Participants List -->
                                @foreach($selectedParticipants as $participant)
                                    <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100">
                                        <!-- Participant Header -->
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="flex items-center space-x-3">
                                                <x-heroicon-m-user-circle class="w-5 h-5 text-gray-400" />
                                                <h4 class="text-base font-medium text-gray-900">
                                                    {{ $participant['name'] }}
                                                </h4>
                                            </div>

                                            <!-- Add Remove Button -->
                                            <button
                                                wire:click="removeParticipant('{{ $participant['id'] }}')"
                                                type="button"
                                                class="inline-flex items-center px-2 py-1 text-xs text-red-600 hover:text-red-800 transition-colors duration-150 border border-transparent hover:border-red-200 rounded"
                                                title="{{ __('events.remove_participant') }}"
                                            >
                                                <x-heroicon-m-trash class="w-4 h-4 mr-1" />
                                                {{ __('events.remove') }}
                                            </button>
                                        </div>

                                        <!-- Attributes Grid -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            @foreach($roleAttributes as $attribute)
                                                <div class="bg-gray-50 p-4 rounded-md">
                                                    <div>
                                                        <x-attribute-form-input
                                                            :attribute="$attribute"
                                                            :wire="'roleAttributeValues.' . $participant['id'] . '.' . $attribute['attribute_data']['id']"
                                                            :value="$roleAttributeValues[$participant['id']][$attribute['attribute_data']['id']] ?? $attribute['attribute_data']['default_value']"
                                                            :options="$attribute['attribute_data']['options'] ?? []"
                                                            :attributeName="''"
                                                        />
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach

                                <!-- Save & Continue -->
                                <div class="sticky bottom-0 bg-white border-t border-gray-100 p-4 -mx-6 -mb-6">
                                    <div class="flex justify-end space-x-3">
                                        <button
                                            wire:click="$set('activeTab', 'participants')"
                                            class="btn btn-info"
                                        >
                                            {{ __('events.back_to_participants') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: Summary -->
            <div class="col-span-12 md:col-span-4 lg:col-span-3">
                <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                    <h3 class="text-lg font-semibold mb-4"> {{ __('events.registration_summary') }}</h3>

                    @if(count($selectedParticipants) > 0)
                        <div class="space-y-4">
                            <!-- Pricing Selection -->
                            <div class="space-y-2">
                                <label for="pricing" class="block text-sm font-medium text-gray-700">
                                    {{ __('events.pricing_option') }}
                                </label>
                                <select
                                    wire:model="selectedPricing"
                                    id="pricing"
                                    class="w-full rounded-md border border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                    @if(count($pricingOptions) === 1) disabled @endif
                                >
                                    @foreach($pricingOptions as $id => $label)
                                        <option value="{{ $id }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @if(count($pricingOptions) === 1)
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ __('events.only_one_pricing_available') }}
                                    </p>
                                @endif
                            </div>

                            <!-- Selected Participants List -->
                            <div class="pt-4 border-t border-gray-200">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">
                                    {{ __('events.selected_members') }} ({{ count($selectedParticipants) }})
                                </h4>
                                <div class="space-y-2 max-h-60 overflow-y-auto">
                                    @foreach($selectedParticipants as $participant)
                                        <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-md">
                                            <span class="text-sm font-medium">{{ $participant['name'] }}</span>
                                            <span class="text-sm text-gray-500">
                                                @if($participant['price'] > 0)
                                                    €{{ number_format($participant['price'], 2) }}
                                                @else
                                                    {{ __('events.free') }}
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Global Attributes -->
                            @if(!empty($globalAttributes))
                                <div class="pt-4 border-t border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">
                                        {{ __('events.global_attributes') }}
                                    </h4>
                                    <div class="space-y-2">
                                        @foreach($globalAttributes as $attribute)
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                                    {{ $attribute['attribute_data']['name'] }}
                                                </label>
                                                <x-attribute-form-input
                                                    :attribute="$attribute"
                                                    :wire="'globalAttributeValues.' . $attribute['attribute_data']['id']"
                                                    :value="$globalAttributeValues[$attribute['attribute_data']['id']] ?? $attribute['attribute_data']['default_value']"
                                                    :options="$attribute['attribute_data']['options'] ?? []"
                                                />
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Submit Button -->
                            <div class="pt-4 border-t border-gray-200">
                                <button
                                    wire:click="register"
                                    @if($selectedCount === 0) disabled @endif
                                    class="w-full btn btn-primary @if($selectedCount === 0) opacity-50 cursor-not-allowed @endif"
                                >
                                    {{ __('events.complete_registration') }}
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            {{ __('events.no_participants_selected') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
