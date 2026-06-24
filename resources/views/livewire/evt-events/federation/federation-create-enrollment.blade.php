<div x-data="{ currentStep: @entangle('currentStep') }">
    <!-- Event Information Card -->
    <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6">
            <div class="flex flex-col md:flex-row justify-between items-start gap-y-4">
                <div class="max-w-[800px]">
                    <h1 class="text-2xl font-bold text-gray-800">{{ $event->name }}</h1>
                    <div class="mt-2 flex flex-col md:flex-row md:items-center space-x-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <x-heroicon-o-calendar class="w-4 h-4 mr-1" />
                            <span>{{ \Carbon\Carbon::parse($event->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($event->end_date)->format('d M Y') }}</span>
                        </div>
                        <div class="flex items-center">
                            <x-heroicon-o-users class="w-4 h-4 mr-1" />
                            <span>{{ count($selectedIndividuals) }} athletes selected</span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col md:flex-row gap-y-2 space-x-2 w-full md:w-auto">
                </div>
            </div>
        </div>
    </div>

    <!-- Pre-registration Summary Banner -->
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <x-heroicon-s-clipboard-document-check class="h-5 w-5 text-blue-400" />
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Pre-registration Status</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>You have {{ $preRegisteredCount }} athletes pre-registered for this event
                        on {{ $preRegistrationDate->format('M d, Y') }}</p>
                    <a href="{{ route('federation.evt-events.events.enrollments.pre-register', $event) }}"
                       class="inline-flex items-center mt-2 text-blue-600 hover:text-blue-800">
                        <x-heroicon-m-plus-circle class="h-4 w-4 mr-1" />
                        Add more athletes to pre-registration
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div x-show="currentStep === 1">
        <!-- Enhanced Instructions Card -->


        @if ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::ATHLETE->value)
            <!-- Filters Card -->
            <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="font-semibold text-gray-900">{{ __('Filter Disciplines') }}</h2>
                    <div class="text-sm text-gray-500">
                        {{ __('Selected: :count of :total disciplines', ['count' => $filteredDisciplines->count(), 'total' => $disciplines->count()]) }}
                    </div>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                        @if($availableFilters['has_individual'] || $availableFilters['has_relay'])
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-gray-700">{{ __('Competition Type') }}</label>
                                <div class="relative">
                                    <select
                                        wire:model.live="disciplineFilters.enrollment_type"
                                        class="form-select w-full pl-3 pr-10 py-2"
                                    >
                                        <option value="">{{ __('All Types') }}</option>
                                        @if($availableFilters['has_individual'])
                                            <option value="individual">Individual</option>
                                        @endif
                                        @if($availableFilters['has_relay'])
                                            <option value="relay">Relay</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        @endif

                        @if($availableFilters['has_male'] || $availableFilters['has_female'] || $availableFilters['has_mixed'])
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-gray-700">{{ __('Gender') }}</label>
                                <select wire:model.live="disciplineFilters.gender" class="form-select">
                                    <option value="">{{ __('Select Gender') }}</option>
                                    @if($availableFilters['has_male'])
                                        <option value="male">{{ __('Male') }}</option>
                                    @endif
                                    @if($availableFilters['has_female'])
                                        <option value="female">{{ __('Female') }}   </option>
                                    @endif
                                    @if($availableFilters['has_mixed'])
                                        <option value="mixed">{{ __('Mixed') }}</option>
                                    @endif
                                </select>
                            </div>
                        @endif

                        @if(count($availableFilters['styles']))
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-gray-700">{{ __('Style') }}</label>
                                <select wire:model.live="disciplineFilters.style" class="form-select">
                                    <option value="">{{ __('Select Style') }}</option>
                                    @foreach($availableFilters['styles'] as $style)
                                        <option value="{{ $style }}">{{ $style }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if(count($availableFilters['distances']))
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-gray-700">{{ __('Distance') }}</label>
                                <select wire:model.live="disciplineFilters.distance" class="form-select">
                                    <option value="">{{ __('Select Distance') }}</option>
                                    @foreach($availableFilters['distances'] as $distance)
                                        <option value="{{ $distance }}">{{ $distance }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if($filteredDisciplines->count() > 0)
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-gray-700">{{ __('Discipline') }}</label>
                                <select wire:model.live="selectedDiscipline" class="form-select">
                                    <option value="">{{ __('Select Discipline') }}</option>
                                    @foreach($filteredDisciplines as $discipline)
                                        <option value="{{ $discipline->id }}">{{ $discipline->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif


                    </div>

                    <!-- Active Filters Summary -->
                    @if(collect($disciplineFilters)->filter()->isNotEmpty())
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach($disciplineFilters as $key => $value)
                                @if($value)
                                    <span
                                        class="inline-flex items-center gap-x-1.5 rounded-md px-2 py-1 text-xs font-medium text-primary-700 bg-primary-50 ring-1 ring-inset ring-primary-600/20">
                                        <span class="text-primary-900">{{ ucfirst($value) }}</span>
                                        <button
                                            wire:click="$set('disciplineFilters.{{ $key }}', '')"
                                            class="ml-1 text-primary-400 hover:text-primary-600"
                                            title="Remove filter"
                                        >
                                            <x-heroicon-m-x-mark class="h-3 w-3" />
                                        </button>
                                    </span>
                                @endif
                            @endforeach

                            <button
                                wire:click="resetFilters"
                                class="text-sm text-gray-500 hover:text-gray-700"
                            >
                                Clear all filters
                            </button>
                        </div>
                    @endif

                    <div class="flex flex-col gap-4 md:flex-row">
                        <!-- Pricing -->
                        @if($multiplePerPersonPricing)
                            <select wire:model="selectedPricingIds.perPerson" class="form-select w-full">
                                <option value="">{{ __('Select Per Person Pricing') }}</option>
                                @foreach($pricingData->where('price_type', \App\Enums\EvtEventFeeTypeEnum::PER_PERSON->value) as $pricing)
                                    <option value="{{ $pricing->id }}">{{ $pricing->description }}
                                        - {{ $pricing->price }}€
                                    </option>
                                @endforeach
                            </select>
                        @endif

                        @if($multipleDisciplinePricing)
                            <select wire:model="selectedPricingIds.discipline" class="form-select w-full">
                                <option value="">{{ __('Select Discipline Pricing') }}</option>
                                @foreach($pricingData->where('price_type', \App\Enums\EvtEventFeeTypeEnum::PER_DISCIPLINE->value) as $pricing)
                                    <option value="{{ $pricing->id }}">{{ $pricing->description }}
                                        - {{ $pricing->price }}€
                                    </option>
                                @endforeach
                            </select>
                        @endif

                        @if($multipleEventFeePricing)
                            <select wire:model="selectedPricingIds.eventFee" class="form-select w-full">
                                <option value="">{{ __('Select Event Fee Pricing') }}</option>
                                @foreach($pricingData->where('price_type', \App\Enums\EvtEventFeeTypeEnum::EVENT_FEE->value) as $pricing)
                                    <option value="{{ $pricing->id }}">{{ $pricing->description }}
                                        - {{ $pricing->price }}€
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>
            </div>

        @else
            @if($multiplePerPersonPricing)
                <select wire:model="selectedPricingIds.perPerson" class="form-select w-full">
                    <option value="">{{ __('Select Per Person Pricing') }}</option>
                    @foreach($pricingData->where('price_type', \App\Enums\EvtEventFeeTypeEnum::PER_PERSON->value) as $pricing)
                        <option value="{{ $pricing->id }}">{{ $pricing->description }} - {{ $pricing->price }}€</option>
                    @endforeach
                </select>
            @endif
            @if($multipleEventFeePricing)
                <select wire:model="selectedPricingIds.eventFee" class="form-select w-full">
                    <option value="">{{ __('Select Event Fee Pricing') }}</option>
                    @foreach($pricingData->where('price_type', \App\Enums\EvtEventFeeTypeEnum::EVENT_FEE->value) as $pricing)
                        <option value="{{ $pricing->id }}">{{ $pricing->description }} - {{ $pricing->price }}€</option>
                    @endforeach
                </select>
            @endif
        @endif

        @if ($errorMessages)
            <div class="flex gap-4 bg-red-700 p-4 rounded-md mb-4 items-center mt-2">
                <div class="w-max">
                    <div class="flex rounded-full text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-sm">
                    @foreach ($errorMessages as $message)
                        <p class="text-white leading-tight">{{ $message }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <x-heroicon-o-light-bulb class="w-6 h-6 text-amber-500" />
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">How Discipline Assignment Works</h3>
                    <div class="mt-2 space-y-3 text-sm text-gray-600">
                        @if($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::ATHLETE->value)
                            <div class="flex items-start space-x-2">
                                <span class="font-medium">1.</span>
                                <p>This page allows you to assign disciplines to your pre-registered athletes. Only
                                    athletes who completed pre-registration are shown here.</p>
                            </div>
                            <div class="flex items-start space-x-2">
                                <span class="font-medium">2.</span>
                                <p>Use the discipline filters above to find specific disciplines. <span
                                        class="text-amber-600 font-medium">Note: These filters affect disciplines only, not the athletes list.</span>
                                </p>
                            </div>
                            <div class="flex items-start space-x-2">
                                <span class="font-medium">3.</span>
                                <p>Select athletes from the table and assign them to the filtered discipline. You can
                                    repeat this process for different disciplines.</p>
                            </div>
                        @else
                            <!-- Instructions for other enrollment types -->
                            <p>Select {{ strtolower($enrollmentType) }}s to register for this event.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <section class="w-full md:flex md:flex-col bg-white rounded-lg shadow-sm border border-gray-200">
            {{ $this->table }}
        </section>

    </div>

    <div x-show="currentStep === 2">

        <div class="space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900"> Selected Athletes Information </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Complete the required information for {{ count($selectedIndividuals) }} selected athletes
                        </p>
                    </div>
                    <button
                        wire:click="$set('currentStep', 1)"
                        class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-x-1"
                    >
                        <x-heroicon-m-arrow-left class="h-4 w-4" />
                        Back to Selection
                    </button>
                </div>

                @if($selectedDisciplineModel)
                    <div class="border-t border-gray-200 px-4 py-3 bg-gray-50">
                        <div class="flex flex-wrap items-center gap-4">
                            <!-- Discipline Name -->
                            <div class="flex items-center gap-x-2">
                            <span
                                class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-sm font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                {{ $selectedDisciplineModel->name }}
                            </span>
                            </div>

                            <!-- Active Filters -->
                            @php
                                $activeFilters = collect($disciplineFilters)->filter()->map(function($value, $key) {
                                    return [
                                        'key' => ucfirst($key),
                                        'value' => ucfirst($value)
                                    ];
                                });
                            @endphp

                            @if($activeFilters->isNotEmpty())
                                <div class="flex flex-wrap items-center gap-2">
                                    @foreach($activeFilters as $filter)
                                        <span
                                            class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">
                                        <span class="font-medium">{{ $filter['key'] }}:</span>
                                        <span class="ml-1">{{ $filter['value'] }}</span>
                                    </span>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Additional Info -->
                            @if($selectedDisciplineModel->athlete_limit)
                                <span
                                    class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800">
                                <x-heroicon-m-user-group class="h-4 w-4 mr-1" />
                                Max Athletes: {{ $selectedDisciplineModel->athlete_limit }}
                            </span>
                            @endif
                        </div>

                    </div>
                @endif
            </div>

            <!-- Global Attributes Section -->
            @if (!empty($globalAttributes))

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Global Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <x-global-attribute-form-input :globalAttributes="$globalAttributes"
                                                       :values="$globalAttributeValues" />
                    </div>
                </div>
            @endif

            <!-- Individual Athletes Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900">Individual Properties</h3>
                </div>

                <div class="divide-y divide-gray-200">
                    @foreach ($selectedIndividuals as $selected)
                        <div class="p-4 hover:bg-gray-50">
                            <div class="flex flex-wrap items-center gap-4">
                                <!-- Athlete Info -->
                                <div class="flex items-center gap-x-3 min-w-[250px]">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-500">
                                            {{ substr($selected['name'], 0, 2) }}
                                        </span>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900">{{ $selected['name'] }}</h4>
                                        <p class="text-xs text-gray-500">CMAS: {{ $selected['member_code'] }}</p>
                                    </div>
                                </div>

                                <!-- Inline Attributes -->
                                <div class="flex-1 flex flex-wrap items-center gap-4">
                                    @if ($enrollmentType === \App\Enums\EvtEventEnrollmentRoleEnum::ATHLETE->value && !empty($disciplineAttributes))
                                        @foreach ($disciplineAttributes as $attributeKey => $attributeData)
                                            <div class="justify-end content-end">
                                                <label
                                                    for="attribute_{{ $attributeData['attribute_data']['id'] }}_{{ $selected['id'] }}"
                                                    class="block text-xs font-medium text-gray-600 mb-1">
                                                    {{ $attributeData['attribute_data']['name'] }}
                                                </label>
                                                <x-attribute-form-input
                                                    :selected="$selected"
                                                    :attribute="$attributeData"
                                                    :wire="'disciplineAttributeValues.' . $selected['id'] . '.' . $attributeData['attribute_data']['id']"
                                                    :value="$attributeValues[$selected['id']][$attributeData['attribute_data']['id']] ?? $attributeData['attribute_data']['default_value']"
                                                    :options="$attributeData['attribute_data']['options'] ?? []" />
                                            </div>
                                        @endforeach
                                    @endif

                                    @if (!empty($roleAttributes))
                                        @foreach ($roleAttributes as $attributeKey => $attributeData)
                                            <div class="justify-end">
                                                <label
                                                    for="attribute_{{ $attributeData['attribute_data']['id'] }}_{{ $selected['id'] }}"
                                                    class="block text-xs font-medium text-gray-600 mb-1">
                                                    {{ $attributeData['attribute_data']['name'] }}
                                                </label>
                                                <x-attribute-form-input
                                                    :selected="$selected"
                                                    :attribute="$attributeData"
                                                    :name="'disciplineAttributeValues.' . $selected['id'] . '.' . $attributeData['attribute_data']['id']"
                                                    :value="$attributeValues[$selected['id']][$attributeData['attribute_data']['id']] ?? $attributeData['attribute_data']['default_value']"
                                                    :options="$attributeData['attribute_data']['options'] ?? []" />
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                <!-- Remove Button -->
                                <div class="flex-shrink-0">
                                    <button
                                        wire:click="removeIndividualFromSelection('{{ $selected['id'] }}')"
                                        class="text-gray-400 hover:text-red-500 p-1"
                                    >
                                        <x-heroicon-m-trash class="h-5 w-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-x-3">
                <x-filament::button
                    wire:click="$set('currentStep', 1)"
                    color="gray"
                    class=""
                >
                    Back to Selection
                </x-filament::button>

                <x-filament::button
                    wire:click="doShowConfirmation"
                    color="primary"
                    :disabled="count($selectedIndividuals) < 1"
                >
                    {{ __('Update Data') }}
                </x-filament::button>
            </div>
        </div>
    </div>

    <x-livewire-confirmation-modal
        :isOpen="$showConfirmation"
        title="Submitting to Waiting List"
        message="You are about to submit {{ count($selectedIndividuals) }} {{ strtolower($enrollmentType) }}s to the waiting list. Are you sure?"
        confirmMethod="submitEnrollment"
        confirmText="Yes, Confirm"
        cancelMethod="$set('showConfirmation', false)"
        cancelText="No, Cancel"
        buttonColor="bg-green-500"
    />
</div>
