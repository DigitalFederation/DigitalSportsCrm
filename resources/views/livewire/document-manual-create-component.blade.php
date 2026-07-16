<!-- File: resources/views/livewire/document-manual-create-component.blade.php -->
<div x-data="{ openModal: false, customerType: @entangle('customerType').live }">
    {{-- Display validation errors --}}
    @if ($errors->any())
        <div class="flex gap-4 bg-red-600 p-4 rounded-md mb-4 items-center">
            <div class="w-max">
                <div class="flex rounded-full text-white">
                    <x-svg.exclamation class="w-6 h-6" />
                </div>
            </div>
            <div>
                <h6 class="font-bold text-white text-lg">{{ __('documents.attention') }}</h6>
                <ul class="text-red-100 leading-tight list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Document form --}}
    <form wire:submit.prevent="saveDocument">
        @if (!empty($errorMessage))
            <div class="flex gap-4 bg-red-600 p-4 rounded-md mb-4 items-center">
                <div class="w-max">
                    <div class="flex rounded-full text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                </div>
                <div class="text-sm">
                    <h6 class="font-medium text-white">{{ __('common.error') }}</h6>
                    <p class="text-red-100 leading-tight">{{ $errorMessage }}</p>
                </div>
            </div>
        @endif

        <div class="card relative overflow-hidden p-6 bg-white shadow rounded">
            {{-- Draft Label --}}
            <div class="absolute right-0 top-0 h-16 w-16">
                <div
                    class="absolute transform rotate-45 bg-yellow-500 text-center text-white font-semibold py-1 right-[-40px] top-[30px] w-[170px]">
                    {{ __('common.draft') }}
                </div>
            </div>

            {{-- Document Header Fields --}}
            <div class="md:flex mb-8 justify-between items-start border-b border-dotted pb-4 border-slate-400">
                <div class="w-full md:w-2/4 flex flex-col space-y-4">
                    <div class="flex items-center">
                        <label class="w-32 text-gray-800 font-bold text-sm uppercase tracking-wide">{{ __('documents.document_no') }}</label>
                        <input type="text" class="form-input w-full md:w-1/2" disabled readonly
                               wire:model="documentDataArray.number_extended">
                    </div>
                    <div class="flex items-center">
                        <label class="w-32 text-gray-800 font-bold text-sm uppercase tracking-wide">{{ __('documents.due_date') }}</label>
                        <input type="date" class="form-input w-full md:w-1/2" autocomplete="off"
                               wire:model="documentDataArray.due_date">
                    </div>
                    <div class="flex items-center">
                        <label class="w-32 text-gray-800 font-bold text-sm uppercase tracking-wide">{{ __('documents.vat_number') }}</label>
                        <input type="text" class="form-input w-full md:w-1/2" autocomplete="off"
                               wire:model="documentDataArray.tax_number">
                    </div>
                </div>

                {{-- Customer & Owner Selection --}}
                <div class="w-full md:w-2/4 md:pr-28">
                    {{-- Tab Navigation for Customer Type --}}
                    <div class="mb-4 border-b border-gray-200">
                        <nav class="flex -mb-px space-x-8">
                            <button type="button"
                                    wire:click="$set('customerType', 'federation')"
                                    :class="customerType === 'federation' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                                {{ __('documents.federation') }}
                            </button>
                            <button type="button"
                                    wire:click="$set('customerType', 'entity')"
                                    :class="customerType === 'entity' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                                {{ __('documents.entity') }}
                            </button>
                            <button type="button"
                                    wire:click="$set('customerType', 'individual')"
                                    :class="customerType === 'individual' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                                {{ __('documents.individual') }}
                            </button>
                            <button type="button"
                                    wire:click="$set('customerType', 'manual')"
                                    :class="customerType === 'manual' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                                {{ __('documents.manual_entry') }}
                            </button>
                        </nav>
                    </div>

                    {{-- Federation Section --}}
                    @if($customerType === 'federation')
                        <div class="mb-4">
                            <label class="block text-gray-800 font-bold">{{ __('documents.select_federation') }}</label>
                            <select wire:model="selectedFederationId" class="form-select mt-1 block w-full">
                                <option value="0">{{ __('documents.select_federation_option') }}</option>
                                @foreach($customerFederations as $federation)
                                    <option value="{{ $federation->id }}">{{ $federation->member_code }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Entity Section --}}
                    @if($customerType === 'entity')
                        <div class="mb-4">
                            <label class="block text-gray-800 font-bold">{{ __('documents.select_entity') }}</label>
                            <select wire:model="selectedEntityId" class="form-select mt-1 block w-full">
                                <option value="0">{{ __('documents.select_entity_option') }}</option>
                                @foreach($customerEntities as $entity)
                                    <option value="{{ $entity->id }}">{{ $entity->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Individual Search Section --}}
                    @if($customerType === 'individual')
                        <div class="mb-4">
                            <label class="block text-gray-800 font-bold">{{ __('documents.search_individual') }}</label>
                            <div class="relative">
                                <input type="text"
                                       wire:model.live.debounce.300ms="individualSearchTerm"
                                       placeholder="{{ __('documents.search_individual_placeholder') }}"
                                       class="form-input mt-1 block w-full">

                                {{-- Loading indicator --}}
                                <div wire:loading wire:target="individualSearchTerm"
                                     class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>

                                {{-- Search Results --}}
                                @if(!empty($individualSearchResults))
                                    <ul class="absolute z-10 w-full bg-white border border-gray-300 mt-1 rounded-md shadow-lg max-h-60 overflow-auto">
                                        @foreach($individualSearchResults as $individual)
                                            <li wire:click="selectIndividual('{{ $individual['id'] }}', '{{ addslashes($individual['name']) }}')"
                                                class="cursor-pointer p-3 hover:bg-gray-100 border-b last:border-0">
                                                <div class="font-semibold">
                                                    {{ $individual['member_code'] }} – {{ $individual['name'] }}
                                                </div>
                                                <div class="text-xs text-gray-500">{{ $individual['email'] }}</div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>

                        {{-- Preview Card for Selected Individual --}}
                        @if($selectedIndividualData)
                            <div class="mb-4 bg-white border rounded-lg shadow-sm overflow-hidden">
                                <!-- Header with international ID and Status -->
                                <div class="bg-gray-50 px-4 py-2 border-b flex justify-between items-center">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs font-medium text-gray-500">CMAS ID:</span>
                                        <span
                                            class="text-sm font-mono font-bold text-gray-900">{{ $selectedIndividualData['member_code'] }}</span>
                                    </div>
                                    @if($selectedIndividualData['is_active'])
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    {{ __('documents.active_member') }}
                </span>
                                    @endif
                                </div>

                                <!-- Main Content -->
                                <div class="px-4 py-3">
                                    <!-- Personal Info Section -->
                                    <div class="flex items-start justify-between">
                                        <div class="flex-grow">
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                {{ $selectedIndividualData['name'] }}
                                            </h3>
                                            <div class="mt-1 text-sm text-gray-500">
                                                {{ $selectedIndividualData['email'] }}
                                            </div>
                                        </div>
                                        <button type="button"
                                                wire:click="clearSelectedIndividual"
                                                class="ml-4 text-sm text-gray-400 hover:text-red-500">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Details Grid -->
                                    <div class="mt-4 grid grid-cols-2 gap-4">
                                        <!-- Left Column -->
                                        <div class="space-y-3">
                                            <div>
                                                <span class="text-xs font-medium text-gray-500">{{ __('documents.birth_date') }}</span>
                                                <div class="text-sm text-gray-900">
                                                    {{ $selectedIndividualData['birthdate'] ? date('d M Y', strtotime($selectedIndividualData['birthdate'])) : __('common.na') }}
                                                </div>
                                            </div>
                                            <div>
                                                <span class="text-xs font-medium text-gray-500">{{ __('documents.address') }}</span>
                                                <div
                                                    class="text-sm text-gray-900">{{ $selectedIndividualData['address'] ?: __('common.na') }}</div>
                                            </div>
                                            <div>
                                                <span class="text-xs font-medium text-gray-500">{{ __('common.location') }}</span>
                                                <div class="text-sm text-gray-900">
                                                    {{ $selectedIndividualData['postal_code'] }} {{ $selectedIndividualData['city'] }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Right Column -->
                                        <div class="space-y-3">
                                            <div>
                                                <span class="ml-0 text-xs font-medium text-gray-500">{{ __('documents.country') }}</span>
                                                <div
                                                    class="text-sm text-gray-900">{{ $selectedIndividualData['country'] ?: __('common.na') }}</div>
                                            </div>
                                            <div>
                                                <span class="text-xs font-medium text-gray-500">{{ __('documents.federation') }}</span>
                                                <div
                                                    class="text-sm text-gray-900">{{ $selectedIndividualData['federation_name'] ?: __('common.na') }}</div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- Manual Entry Section --}}
                    @if($customerType === 'manual')
                        <div class="mb-4 border p-4 rounded">
                            <h3 class="font-bold text-gray-800 mb-2">{{ __('documents.manual_customer_entry') }}</h3>
                            <div class="mb-2">
                                <label class="block text-gray-800 font-bold">{{ __('documents.customer_name') }}</label>
                                <input type="text" wire:model="documentDataArray.customer_name"
                                       class="form-input mt-1 block w-full" />
                            </div>
                            <div class="mb-2">
                                <label class="block text-gray-800 font-bold">{{ __('documents.address') }}</label>
                                <input type="text" wire:model="documentDataArray.customer_address"
                                       class="form-input mt-1 block w-full" />
                            </div>
                            <div class="mb-2">
                                <label class="block text-gray-800 font-bold">{{ __('documents.postal_code') }}</label>
                                <input type="text" wire:model="documentDataArray.customer_postal_code"
                                       class="form-input mt-1 block w-full" />
                            </div>
                            <div class="mb-2">
                                <label class="block text-gray-800 font-bold">{{ __('documents.city') }}</label>
                                <input type="text" wire:model="documentDataArray.customer_city"
                                       class="form-input mt-1 block w-full" />
                            </div>
                            <div class="mb-2">
                                <label class="block text-gray-800 font-bold">{{ __('documents.country') }}</label>
                                <input type="text" wire:model="documentDataArray.customer_country"
                                       class="form-input mt-1 block w-full" />
                            </div>
                        </div>
                    @endif

                    {{-- If editing and the document is still a draft, allow state changes --}}
                    @if($isEditing && $documentDataArray['status_class'] === \Domain\Documents\States\DraftDocumentState::class)
                        <div class="form-group md:flex items-center">
                            <label class="md:w-32 text-gray-800 font-bold text-sm uppercase tracking-wide"
                                   for="documentState">{{ __('documents.document_state') }}</label>
                            <div class="flex-1">
                                <select class="form-select w-full" id="documentState"
                                        wire:model="documentDataArray.status_class">
                                    @foreach($documentStates as $value => $label)
                                        <option value="{{ $value }}">{{ ucfirst($label) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- Invoice Items Section --}}
            <div class="w-full mx-auto">
                <div class="flex -mx-1 border-b py-2 items-start border-slate-200">
                    <div class="flex-1 px-1">
                        <p class="text-gray-800 uppercase tracking-wide text-sm font-bold">{{ __('documents.description') }}</p>
                    </div>
                    <div class="px-1 w-20 text-right">
                        <p class="text-gray-800 uppercase tracking-wide text-sm font-bold">{{ __('documents.qty') }}</p>
                    </div>
                    <div class="px-1 w-32 text-right">
                        <p class="leading-none">
                            <span
                                class="block uppercase tracking-wide text-sm font-bold text-gray-800">{{ __('documents.unit_price') }}</span>
                        </p>
                    </div>
                    <div class="px-1 w-32 text-right">
                        <p class="leading-none">
                            <span class="block uppercase tracking-wide text-sm font-bold text-gray-800">{{ __('documents.amount') }}</span>
                        </p>
                    </div>
                    <div class="px-1 w-20 text-center"></div>
                </div>

                @foreach ($documentDetailDataArray as $index => $detail)
                    <div class="flex -mx-1 py-2 border-b">
                        <div class="flex-1 px-1">
                            <p class="text-gray-800">{{ $detail['description'] }}</p>
                        </div>
                        <div class="px-1 w-20 text-right">
                            <p class="text-gray-800">{{ $detail['quantity'] }}</p>
                        </div>
                        <div class="px-1 w-32 text-right">
                            <p class="text-gray-800">{{ $detail['unit_value'] }}</p>
                        </div>
                        <div class="px-1 w-32 text-right">
                            <p class="text-gray-800">{{ $detail['total_value'] }}</p>
                        </div>
                        <div class="px-1 w-20 text-right">
                            <a href="#" wire:click="deleteDetail({{ $index }})"
                               class="text-red-500 hover:text-red-600 text-sm font-semibold">
                                {{ __('documents.delete') }}
                            </a>
                        </div>
                    </div>
                @endforeach

                <button type="button" wire:click="prepareNewDetail"
                        class="mt-6 btn-sm btn-info" x-on:click="openModal = !openModal">
                    {{ __('documents.add_invoice_items') }}
                </button>

                {{-- Modal for adding a new invoice item --}}
                <div style="background-color: rgba(0, 0, 0, 0.8)"
                     class="fixed z-40 top-0 right-0 left-0 bottom-0 h-full w-full"
                     x-show.transition.opacity="openModal"
                     x-cloak>
                    <div class="p-4 max-w-xl mx-auto relative mt-24">
                        <div
                            class="shadow absolute right-0 top-0 w-10 h-10 rounded-full bg-white text-gray-500 hover:text-gray-800 inline-flex items-center justify-center cursor-pointer"
                            x-on:click="openModal = !openModal">
                            <svg class="fill-current w-6 h-6" xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24">
                                <path
                                    d="M16.192 6.344L11.949 10.586 7.707 6.344 6.293 7.758 10.535 12 6.293 16.242 7.707 17.656 11.949 13.414 16.192 17.656 17.606 16.242 13.364 12 17.606 7.758z" />
                            </svg>
                        </div>
                        <div class="shadow w-full rounded-lg bg-white overflow-hidden block p-8">
                            <h2 class="font-bold text-2xl mb-6 text-gray-800 border-b pb-2">{{ __('documents.document_line') }}</h2>
                            <div class="mb-4 border-b border-slate-200 pb-4">
                                <label class="block text-gray-800 font-bold text-sm uppercase tracking-wide">{{ __('documents.product_service') }}</label>
                                <input type="text"
                                       class="form-input focus:bg-white focus:border-blue-500 w-full bg-slate-50"
                                       wire:model="newDetail.description">
                            </div>
                            <div class="flex gap-x-4 justify-between mb-4">
                                <div class="w-32">
                                    <label
                                        class="block text-gray-800 font-bold text-sm uppercase tracking-wide">{{ __('documents.qty') }}</label>
                                    <input type="number" wire:model="newDetail.quantity"
                                           class="form-input focus:bg-white focus:border-blue-500 w-full bg-slate-50">
                                </div>
                                <div class="w-32">
                                    <label class="block text-gray-800 font-bold text-sm uppercase tracking-wide">{{ __('documents.unit_price') }}</label>
                                    <input type="number" wire:model="newDetail.unit_value"
                                           class="form-input focus:bg-white focus:border-blue-500 w-full bg-slate-50">
                                </div>
                                <div class="w-32">
                                    <label class="block text-gray-800 font-bold text-sm uppercase tracking-wide">{{ __('documents.vat_percentage') }}</label>
                                    <input type="number" wire:model="newDetail.tax_percentage"
                                           class="form-input focus:bg-white focus:border-blue-500 w-full bg-slate-50">
                                </div>
                            </div>
                            <div class="mt-8 text-right">
                                <button type="button" class="btn-sm btn-info mr-2" x-on:click="openModal = false">
                                    {{ __('common.cancel') }}
                                </button>
                                <button type="button" class="btn-action btn-sm" wire:click="addNewDetail"
                                        x-on:click="openModal = false">
                                    {{ __('documents.add_item') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- End Modal --}}

                <div class="flex flex-row gap-x-4 items-end justify-end text-right mt-4">
                    <span>{{ __('documents.subtotal') }}:</span>
                    <div class="font-bold">
                        {{ money(array_reduce($documentDetailDataArray, function ($carry, $item) {
                            return $carry + ($item['total_value'] ?? 0);
                        }, 0)) }}
                    </div>
                </div>
            </div>

            {{-- Document Notes --}}
            <div class="w-full mt-4">
                <textarea class="form-input w-full" wire:model="documentDataArray.notes" placeholder="{{ __('documents.notes') }}"></textarea>
            </div>
        </div>

        {{-- Form Actions --}}
        <section class="mt-4 flex flex-row gap-x-4 items-center">
            <a href="{{ route('admin.document.index') }}" class="btn btn-info">{{ __('common.cancel') }}</a>
            <button type="submit" class="btn-primary">{{ __('documents.save_document') }}</button>
        </section>
    </form>
</div>
