<div>

    @if ($errorMessage)
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">{{ __('events.validation_error') }}!</strong>
            <span class="block sm:inline">{{ $errorMessage }}</span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20" @click="$wire.set('errorMessage', '')">
                    <title>{{ __('Close') }}</title>
                    <path
                        d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                </svg>
            </span>
        </div>
    @endif

    @if ($errorMessages)
        <div class="flex gap-4 bg-red-700 p-4 rounded-md mb-4 items-center mt-2">
            <div class="w-max">
                <div class="flex rounded-full text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
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



    <!-- Register --->
    <x-information-box :title="__('events.instructions_title')"
        :body="__('events.registration_instructions')">
    </x-information-box>

    <div class="flex flex-col gap-6">
        <!-- Step 1: Discipline Selection -->
        <div class="rounded-lg border border-gray-200 p-6 bg-white shadow-sm">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <span
                    class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-800 text-sm">1</span>
                {{ __('events.choose_discipline') }}
            </h3>

            <div class="flex flex-col md:flex-row gap-4">
                <div class="w-full md:w-1/2 space-y-4">
                    <!-- Discipline Select -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 text-left">
                            {{ __('events.select_discipline') }}
                        </label>
                        <select wire:model.live="selectedDiscipline" class="form-select w-full">
                            <option value="">{{ __('events.choose_discipline') }}</option>
                            @foreach ($disciplines as $discipline)
                                <option value="{{ $discipline->id }}">
                                    {{ $discipline->name }}
                                </option>
                            @endforeach
                        </select>

                        @if ($disciplines->isEmpty())
                            <div class="mt-2 text-sm text-gray-600">
                                {{ __('events.no_disciplines_available') }}
                            </div>
                        @endif
                    </div>

                    <!-- Pricing Selects -->
                    @if ($multiplePerPersonPricing || $multipleDisciplinePricing || $multipleEventFeePricing)
                        <div class="space-y-3">
                            @if ($multiplePerPersonPricing)
                                <select wire:model="selectedPricingIds.perPerson" class="form-select w-full">
                                    <option value="">{{ __('events.select_per_person_pricing') }}</option>
                                    @foreach ($pricingData->where('price_type', \App\Enums\EvtEventFeeTypeEnum::PER_PERSON->value) as $pricing)
                                        <option value="{{ $pricing->id }}">{{ $pricing->description }}
                                            - {{ money($pricing->price) }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif

                            @if ($multipleDisciplinePricing)
                                <select wire:model="selectedPricingIds.discipline" class="form-select w-full">
                                    <option value="">{{ __('events.discipline_fee') }}</option>
                                    @foreach ($pricingData->where('price_type', \App\Enums\EvtEventFeeTypeEnum::PER_DISCIPLINE->value) as $pricing)
                                        <option value="{{ $pricing->id }}">{{ $pricing->description }}
                                            - {{ money($pricing->price) }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif

                            @if ($multipleEventFeePricing)
                                <select wire:model="selectedPricingIds.eventFee" class="form-select w-full">
                                    <option value="">{{ __('events.select_event_fee_pricing') }}</option>
                                    @foreach ($pricingData->where('price_type', \App\Enums\EvtEventFeeTypeEnum::EVENT_FEE->value) as $pricing)
                                        <option value="{{ $pricing->id }}">{{ $pricing->description }}
                                            - {{ money($pricing->price) }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Ineligible Disciplines (if any) -->
        @if ($ineligibleDisciplines && $ineligibleDisciplines->isNotEmpty())
            <div class="rounded-lg border border-amber-200 p-4 bg-amber-50 shadow-sm">
                <h4 class="text-sm font-semibold text-amber-800 mb-2 flex items-center gap-2">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                    {{ __('events.ineligible_disciplines') }}
                </h4>
                <p class="text-sm text-amber-700 mb-3">{{ __('events.ineligible_disciplines_description') }}</p>
                <ul class="list-disc list-inside text-sm text-amber-700 space-y-1">
                    @foreach ($ineligibleDisciplines as $ineligible)
                        <li>
                            <span class="font-medium">{{ $ineligible['discipline']->name }}</span>
                            @if (!empty($ineligible['reason']))
                                - <span class="italic">{{ $ineligible['reason'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Step 2: Discipline Attributes (only shown when discipline is selected) -->
        @if (!empty($disciplineAttributes))
            <div class="rounded-lg border border-gray-200 p-6 bg-white shadow-sm" wire:key="{{ $attributesKey }}">
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <span
                        class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-800 text-sm">2</span>
                    {{ __('events.discipline_details') }}
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach ($disciplineAttributes as $attributeId => $attributeData)
                        @php
                            $attrValue = null;
                            if (isset($disciplineAttributeValues[$individual->id]) && is_array($disciplineAttributeValues[$individual->id])) {
                                $attrValue = $disciplineAttributeValues[$individual->id][$attributeId] ?? null;
                            }
                            $attrValue = $attrValue ?? ($attributeData['default_value'] ?? null);
                        @endphp
                        <div class="flex-1">
                            <x-attribute-form-input :attribute="$attributeData" :wire="'disciplineAttributeValues.' . $individual->id . '.' . $attributeId" :value="$attrValue"
                                :options="$attributeData['options'] ?? []" />
                        </div>
                    @endforeach
                </div>

                <!-- Add Discipline Button moved here -->
                <div class="mt-6">
                    <x-ui.button wire:click="addDisciplineToEnrollmentItems" variant="primary">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        {{ __('events.add_discipline_to_registration') }}
                    </x-ui.button>
                </div>
            </div>
        @endif

        <!-- Step 3: Selected Disciplines List -->
        @if ($hasEnrollmentItems)
            <div class="rounded-lg border border-gray-200 p-6 bg-white shadow-sm">
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <span
                        class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-800 text-sm">3</span>
                    {{ __('events.selected_disciplines') }}
                </h3>

                @if (!empty($enrollmentItems))
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">{{ __('events.discipline') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('events.attributes') }}</th>
                                    <th scope="col" class="px-6 py-3 text-right">{{ __('events.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($enrollmentItems as $item)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            {{ $item['discipline_name'] ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if (!empty($item['attribute_values']) && is_array($item['attribute_values']))
                                                <ul class="list-disc list-inside text-sm space-y-1">
                                                    @foreach($item['attribute_values'] as $attrId => $value)
                                                        @php
                                                            // Try to find the attribute name based on ID
                                                            $attributeName = \Domain\EvtEvents\Models\Attribute::find($attrId)->name ?? "Attribute {$attrId}";
                                                        @endphp
                                                        <li><span class="font-medium">{{$attributeName}}:</span> {{$value}}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-gray-500">{{ __('events.no_attributes') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <button wire:click="removeDisciplineFromEnrollmentItems('{{ $item['cart_item_id'] }}')"
                                                class="text-red-600 hover:text-red-900"
                                                title="{{ __('events.remove') }}">
                                                <x-svg.trash class="w-5 h-5" />
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($totalCost > 0)
                        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                            <div class="flex justify-between items-center">
                                <div class="text-lg font-semibold">{{ __('events.pending_total_cost') }}</div>
                                <div class="text-xl font-bold">{{ money($totalCost) }}</div>
                            </div>
                            @if (!empty($costBreakdown) && is_array($costBreakdown))
                                <div class="mt-2 text-sm text-gray-600">
                                    @foreach ($costBreakdown as $item)
                                        <div class="flex justify-between">
                                            <span>{{ $item['description'] ?? '' }}</span>
                                            <span>{{ money($item['amount'] ?? 0) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                @else
                    <p class="text-sm text-gray-500 mt-4">{{ __('events.no_disciplines_added') }}</p>
                @endif
            </div>
        @endif

        <!-- Step 4: Finalize Registration -->
        @if ($this->shouldShowFinalizeButton())
            <div class="rounded-lg border border-gray-200 p-6 bg-white shadow-sm">
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <span
                        class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-800 text-sm">4</span>
                    {{ __('events.finalize_registration') }}
                </h3>

                <div class="mt-6">
                    <x-ui.button
                        wire:click="finalizeRegistrations"
                        wire:loading.attr="disabled"
                        wire:target="finalizeRegistrations"
                        variant="primary"
                        size="lg"
                        :disabled="!$hasEnrollmentItems"
                        :loading="$isFinalizing"
                        class="w-full"
                    >
                        @if ($isFinalizing)
                            {{ __('events.finalizing') }}
                        @else
                            {{ __('events.finalize_all_registrations') }}
                        @endif
                    </x-ui.button>

                    <p class="mt-2 text-sm text-gray-600">
                        {{ __('events.click_confirm_finalize') }}
                        @if (!$hasEnrollmentItems)
                            <span
                                class="text-red-500 block mt-1">{{ __('events.add_discipline_before_finalizing') }}</span>
                        @endif
                    </p>
                </div>
            </div>
        @endif

        <!-- Post-Registration Summary (show after finalization) -->
        @if ($confirmedAthleteEnrollments->isNotEmpty())
            <div class="rounded-lg border border-gray-200 p-6 bg-white shadow-sm mt-2">
                <h3 class="text-lg font-semibold mb-4">{{ __('events.confirmed_registrations') }}</h3>

                @php
                    // Collect all unique attributes from confirmed enrollments
                    $allConfirmedAttributes = collect();
                    foreach ($confirmedAthleteEnrollments as $enrollment) {
                        if ($enrollment->attributes) {
                            $allConfirmedAttributes = $allConfirmedAttributes->merge($enrollment->attributes);
                        }
                    }
                    $uniqueConfirmedAttributes = $allConfirmedAttributes->pluck('attribute.name')->filter()->unique()->toArray();
                @endphp

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-right"></th>
                                <th scope="col" class="px-6 py-3">{{ __('events.discipline') }}</th>
                                @foreach ($uniqueConfirmedAttributes as $attributeName)
                                    <th scope="col" class="px-6 py-3">{{ $attributeName }}</th>
                                @endforeach
                                <th scope="col" class="px-6 py-3">{{ __('events.status_label') }}</th>
                                <th scope="col" class="px-6 py-3 whitespace-nowrap">{{ __('events.date') }}</th>
                                <th scope="col" class="px-6 py-3 text-right">{{ __('events.payment') }}</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($confirmedAthleteEnrollments as $enrollment)
                                <tr class="bg-white border-b hover:bg-gray-50">

                                    <td class="px-6 py-4 text-right">
                                        @php
                                            $creatorUser = $enrollment->enrollment?->user;
                                            $isOwner = $creatorUser && $creatorUser->id === Auth::id();
                                            $creatorName = 'Unknown'; // Default
                                            if ($creatorUser) {
                                                $associatedEntity = $creatorUser->entities->first();
                                                $associatedFederation = $creatorUser->federations->first();
                                                if ($associatedEntity) {
                                                    $creatorName = $associatedEntity->name . ' (Entity)';
                                                } elseif ($associatedFederation) {
                                                    $creatorName = $associatedFederation->name . ' (Federation)';
                                                } else {
                                                    $creatorName = $creatorUser->name;
                                                }
                                            }
                                        @endphp

                                        @if ($isOwner)
                                            {{-- Show remove button only if current user is the owner --}}
                                            <button
                                                wire:click="requestRemoveConfirmedEnrollment({{ $enrollment->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="requestRemoveConfirmedEnrollment({{ $enrollment->id }})"
                                                type="button"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full text-red-600 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                title="{{ __('Remove Discipline Enrollment') }}"
                                            >
                                                <div wire:loading wire:target="requestRemoveConfirmedEnrollment({{ $enrollment->id }})">
                                                    <x-filament::loading-indicator class="w-4 h-4" />
                                                </div>
                                                <div wire:loading.remove wire:target="requestRemoveConfirmedEnrollment({{ $enrollment->id }})">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </div>
                                                <span class="sr-only">{{ __('Remove') }}</span>
                                            </button>
                                        @else
                                            {{-- Show creator name if current user is not the owner --}}
                                            <span class="text-xs text-gray-500 italic" title="Registered by {{ $creatorName }}">
                                                By: {{ $creatorName }}
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                        {{ $enrollment->discipline->name ?? '-' }}
                                    </td>

                                    @foreach ($uniqueConfirmedAttributes as $attributeName)
                                        <td class="px-6 py-4">
                                            @php
                                                $attributeValue = $enrollment->attributes
                                                    ->where('attribute.name', $attributeName)
                                                    ->first()?->value;
                                            @endphp
                                            {{ $attributeValue ?? '-' }}
                                        </td>
                                    @endforeach

                                    <td class="px-6 py-4">
                                        @php
                                            // Ensure we pass the scalar value to tryFrom
                                            $statusValue = $enrollment->status_class instanceof \UnitEnum ? $enrollment->status_class->value : $enrollment->status_class;
                                            $statusEnum = \App\Enums\EvtAthleteEnrollmentStatusEnum::tryFrom($statusValue);
                                            $statusText = $statusEnum ? \App\Enums\EvtAthleteEnrollmentStatusEnum::toString($statusEnum) : $statusValue;

                                            // Define colors based on status value
                                            $statusColorClass = match ($statusValue) {
                                                \App\Enums\EvtAthleteEnrollmentStatusEnum::PAID->value, \App\Enums\EvtAthleteEnrollmentStatusEnum::COMPLETED->value => 'bg-green-100 text-green-800',
                                                \App\Enums\EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value => 'bg-yellow-100 text-yellow-800',
                                                \App\Enums\EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value => 'bg-blue-100 text-blue-800',
                                                \App\Enums\EvtAthleteEnrollmentStatusEnum::REGISTERED->value => 'bg-gray-100 text-gray-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            };
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColorClass }} whitespace-nowrap">
                                            {{ $statusText }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $enrollment->created_at?->format('d M Y H:i') }}
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        @if ($enrollment->enrollment && $enrollment->enrollment->payment_status === \App\Enums\EvtEventPaymentStatusEnum::PENDING->value)
                                            @if($enrollment->enrollment->document_id)
                                                <x-ui.button
                                                    :href="route('individual.document.show', ['id' => $enrollment->enrollment->document_id])"
                                                    variant="secondary"
                                                    size="sm"
                                                >
                                                    {{ __('events.view_order_document') }}
                                                </x-ui.button>
                                            @endif
                                        @else
                                            <span class="text-green-600">{{ __('Paid') }}</span>
                                        @endif
                                    </td>


                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($totalCost > 0)
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                        <div class="flex justify-between items-center">
                            <div class="text-lg font-semibold">{{ __('events.total_cost') }}</div>
                            <div class="text-xl font-bold">{{ money($totalCost) }}</div>
                        </div>

                        @if (!empty($costBreakdown) && is_array($costBreakdown))
                            <div class="mt-2 text-sm text-gray-600">
                                @foreach ($costBreakdown as $item)
                                    <div class="flex justify-between">
                                        <span>{{ $item['description'] ?? '' }}</span>
                                        <span>{{ money($item['amount'] ?? 0) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if(isset($enrollment) && $enrollment->document_id)
                            <div class="mt-4">
                                <x-ui.button
                                    :href="route('individual.document.show', ['id' => $enrollment->document_id])"
                                    variant="primary"
                                >
                                    {{ __('events.proceed_to_payment') }}
                                </x-ui.button>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        <!-- After discipline selection -->
        @if ($selectedDiscipline)
            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                <div class="flex justify-between items-center">
                    <div class="text-lg font-semibold">{{ __('events.registration_cost') }}</div>
                    <div class="text-xl font-bold">{{ money($currentDisciplineCost) }}</div>
                </div>
            </div>
        @endif
    </div>

    <!-- Remove Confirmation Modal -->
    <div
        x-data="{ show: @entangle('showRemoveConfirmationModal').live }"
        x-show="show"
        x-cloak
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 transition-opacity bg-gray-500/75 dark:bg-gray-900/75"
                 @click="show = false; $wire.call('cancelRemoveConfirmedEnrollment')"
                 aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="show"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10 dark:bg-gray-800 sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
            >
                <div>
                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full dark:bg-red-900/50">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100" id="modal-title">
                            {{ __('events.remove_enrollment_confirmation') }}
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('events.confirm_remove_discipline') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <x-ui.button
                        wire:click="removeConfirmedEnrollment"
                        wire:loading.attr="disabled"
                        wire:target="removeConfirmedEnrollment"
                        variant="danger"
                        class="sm:col-start-2"
                    >
                        <span wire:loading.remove wire:target="removeConfirmedEnrollment">{{ __('events.confirm_removal') }}</span>
                        <span wire:loading wire:target="removeConfirmedEnrollment">{{ __('events.removing') }}</span>
                        <div wire:loading wire:target="removeConfirmedEnrollment" class="ml-2">
                           <x-filament::loading-indicator class="w-5 h-5" />
                        </div>
                    </x-ui.button>
                    <x-ui.button
                        @click="show = false"
                        wire:click="cancelRemoveConfirmedEnrollment"
                        wire:loading.attr="disabled"
                        wire:target="removeConfirmedEnrollment"
                        variant="outline"
                        class="mt-3 sm:mt-0 sm:col-start-1"
                    >
                        {{ __('events.cancel') }}
                    </x-ui.button>
                </div>
            </div>
        </div>
    </div>
</div>
