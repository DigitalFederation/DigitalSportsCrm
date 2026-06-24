<div>
    <!-- Affiliation Status Check -->
    @if(!$this->entityHasActiveAffiliation)
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-red-800">{{ __('licenses.Active Affiliation Required') }}</h3>
                    <p class="text-sm text-red-700 mt-1">
                        {{ __('licenses.Your entity must have an active affiliation (membership package) to purchase licenses. Please ensure your entity membership is active and paid before proceeding.') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- License Selection Table -->
    <div class="mb-8 card">
        <h2 class="text-xl font-semibold text-slate-800 mb-6">{{ __('licenses.Select License') }}</h2>
        
        @if(count($licenses) > 0)
            <!-- Search Input -->
            <div class="mb-4">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" 
                           wire:model.live.debounce.300ms="licenseSearch" 
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                           placeholder="{{ __('licenses.Search licenses...') }}">
                </div>
            </div>

            <!-- License Table -->
            <div class="bg-white shadow overflow-hidden rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="w-12 px-6 py-3 text-left">
                                <!-- Select -->
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('licenses.License Name') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('licenses.Code') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('licenses.Price') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($this->filteredLicenses as $license)
                            <tr class="hover:bg-gray-50 cursor-pointer {{ $selectedLicenseId == $license->id ? 'bg-blue-50' : '' }}"
                                wire:click="$set('selectedLicenseId', {{ $license->id }})">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="radio" 
                                           wire:model="selectedLicenseId" 
                                           name="license_id"
                                           value="{{ $license->id }}" 
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $license->name }}</div>
                                    @if($license->description)
                                        <div class="text-sm text-gray-500">{{ Str::limit($license->description, 50) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($license->license_code)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ $license->license_code }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    @if($license->calculated_price !== null)
                                        @if($license->calculated_price > 0)
                                            <span class="font-semibold">€{{ number_format($license->calculated_price, 2) }}</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ __('licenses.Free') }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    {{ __('licenses.No licenses found matching your search.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <div class="flex">
                    <svg class="w-6 h-6 text-yellow-400 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h3 class="text-base font-medium text-yellow-800">{{ __('licenses.No licenses available') }}</h3>
                        <p class="text-sm text-yellow-700 mt-1">
                            @if($licenseType === 'members')
                                {{ __('licenses.Your entity must have an active entity license for a sport before you can purchase member licenses for that sport.') }}
                            @else
                                {{ __('licenses.There are no licenses available for entity purchase at the moment.') }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- License Requirements Display -->
    @if($selectedLicense && $licenseRequirements['has_requirements'])
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-sm font-medium text-blue-900 mb-2">{{ __('licenses.license_requirements') }}</h3>
            
            @if(!empty($licenseRequirements['certifications']))
                <div class="mb-2">
                    <span class="text-sm font-medium text-blue-800">{{ __('licenses.required_certifications') }}:</span>
                    <span class="text-sm text-blue-700">
                        {{ collect($licenseRequirements['certifications'])->pluck('display')->join(', ') }}
                    </span>
                </div>
            @endif
            
            @if(!empty($licenseRequirements['documents']))
                <div>
                    <span class="text-sm font-medium text-blue-800">{{ __('licenses.required_documents') }}:</span>
                    <span class="text-sm text-blue-700">
                        {{ collect($licenseRequirements['documents'])->pluck('name')->join(', ') }}
                    </span>
                </div>
            @endif
        </div>
    @endif

    <!-- Member Selection Table (only for member licenses) -->
    @if($licenseType === 'members')
        @if($selectedLicense && count($entityMembers) == 0)
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded">
                <p class="text-sm text-red-800">{{ __('licenses.no_members_for_entity') }}</p>
            </div>
        @endif
    @endif
    
    @if($selectedLicense && $licenseType === 'members' && count($entityMembers) > 0)
        <x-ui.card class="mb-8 compact">
            <div class="p-2">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-medium flex items-center text-gray-900">
                        @svg('heroicon-o-users', 'w-5 h-5 mr-2')
                        {{ __('licenses.Select Members') }}
                    </h2>
                    
                    @if($this->ineligibleMemberCount > 0)
                        <button wire:click="toggleShowIneligibleMembers" 
                                type="button"
                                class="text-sm text-blue-600 hover:text-blue-800 underline">
                            @if($showIneligibleMembers)
                                {{ __('licenses.hide_ineligible_members') }} ({{ $this->ineligibleMemberCount }})
                            @else
                                {{ __('licenses.show_ineligible_members') }} ({{ $this->ineligibleMemberCount }})
                            @endif
                        </button>
                    @endif
                </div>
                
                @if($this->eligibleMemberCount == 0)
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded">
                        <p class="text-sm text-red-800">{{ __('licenses.no_eligible_members') }}</p>
                    </div>
                @elseif($this->ineligibleMemberCount > 0)
                    <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                        <p class="text-sm text-yellow-800">
                            {{ __('licenses.some_members_ineligible', ['eligible' => $this->eligibleMemberCount, 'total' => count($entityMembers)]) }}
                        </p>
                    </div>
                @endif

                {{-- Search and Filters --}}
                <div class="mb-4 sm:flex sm:justify-between sm:items-center space-y-4 sm:space-y-0">
                    <div class="flex-1 max-w-md">
                        <div class="relative">
                            <input
                                wire:model.live.debounce.300ms="memberSearch"
                                type="text"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="{{ __('licenses.Search members...') }}"
                            >
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                @svg('heroicon-o-magnifying-glass', 'w-5 h-5 text-gray-400')
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Members Table --}}
                <div class="border rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-8">
                                    <input
                                        type="checkbox"
                                        wire:click="toggleAllMembers"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                        {{ count($selectedIndividualIds) === count($entityMembers) ? 'checked' : '' }}
                                    >
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('individual.photo') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sort('name')">
                                    <div class="flex items-center space-x-1">
                                        <span>{{ __('common.name') }}</span>
                                        <x-tables.sort-indicator :field="$sortField" :direction="$sortDirection" :name="'name'" />
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sort('surname')">
                                    <div class="flex items-center space-x-1">
                                        <span>{{ __('common.surname') }}</span>
                                        <x-tables.sort-indicator :field="$sortField" :direction="$sortDirection" :name="'surname'" />
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sort('birthdate')">
                                    <div class="flex items-center space-x-1">
                                        <span>{{ __('common.birthdate') }}</span>
                                        <x-tables.sort-indicator :field="$sortField" :direction="$sortDirection" :name="'birthdate'" />
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sort('member_number')">
                                    <div class="flex items-center space-x-1">
                                        <span>{{ __('common.filiation_number') }}</span>
                                        <x-tables.sort-indicator :field="$sortField" :direction="$sortDirection" :name="'member_number'" />
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sort('gender')">
                                    <div class="flex items-center space-x-1">
                                        <span>{{ __('main.gender') }}</span>
                                        <x-tables.sort-indicator :field="$sortField" :direction="$sortDirection" :name="'gender'" />
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($this->filteredMembers as $individual)
                                @php
                                    $eligibility = $memberEligibility[$individual->id] ?? ['is_eligible' => true, 'eligibility_message' => ''];
                                    $isEligible = $eligibility['is_eligible'];
                                @endphp
                                <tr class="hover:bg-gray-50 {{ !$isEligible ? 'bg-yellow-50' : (in_array($individual->id, $selectedIndividualIds) ? 'bg-blue-50' : '') }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($isEligible)
                                            <input
                                                type="checkbox"
                                                wire:model.live="selectedIndividualIds"
                                                value="{{ $individual->id }}"
                                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                            >
                                        @else
                                            <div class="relative group">
                                                <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                                <div class="absolute left-0 bottom-full mb-2 hidden group-hover:block z-10 w-64 p-2 bg-gray-800 text-white text-xs rounded shadow-lg">
                                                    {{ $eligibility['eligibility_message'] }}
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <x-secure-profile-image :individual="$individual" size="thumb" class="h-10 w-10 rounded-full object-cover" />
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium {{ $isEligible ? 'text-gray-900' : 'text-gray-500' }}">
                                            {{ $individual->name }}
                                        </div>
                                        @if(!$isEligible && !empty($eligibility['eligibility_message']))
                                            <div class="text-xs text-yellow-600 mt-1">
                                                {{ Str::limit($eligibility['eligibility_message'], 50) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $individual->surname }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            @if($individual->birthdate)
                                                @if(is_string($individual->birthdate))
                                                    {{ \Carbon\Carbon::parse($individual->birthdate)->format('d/m/Y') }}
                                                @else
                                                    {{ $individual->birthdate->format('d/m/Y') }}
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $individual->member_number ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            @if($individual->gender === 'male')
                                                {{ __('individual.male') }}
                                            @elseif($individual->gender === 'female')
                                                {{ __('individual.female') }}
                                            @else
                                                {{ $individual->gender ?? '-' }}
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        {{ __('licenses.No members found matching your search.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(count($selectedIndividualIds) > 0)
                    <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <p class="text-sm text-blue-800 font-medium">
                                {{ __('licenses.:count members selected', ['count' => count($selectedIndividualIds)]) }}
                            </p>
                        </div>
                    </div>
                @endif

                @error('selectedIndividualIds') 
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </x-ui.card>
    @endif

    <!-- Purchase Summary -->
    @if($selectedLicense)
        <div class="mb-8">
            <div class="bg-white rounded-lg shadow-lg border border-slate-200 overflow-hidden">
                <!-- Summary Header -->
                <div class="bg-green-600 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        {{ __('licenses.Purchase Summary') }}
                    </h3>
                </div>
                
                <!-- Summary Content -->
                <div class="p-6">
                    <dl class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <dt class="text-sm font-medium text-slate-600">{{ __('licenses.License') }}</dt>
                            <dd class="text-sm font-semibold text-slate-900">{{ $selectedLicense->name }}</dd>
                        </div>
                        @if($licenseType === 'entity')
                            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                                <dt class="text-sm font-medium text-slate-600">{{ __('licenses.License Type') }}</dt>
                                <dd class="text-sm font-semibold text-slate-900">{{ __('licenses.Entity License') }}</dd>
                            </div>
                        @elseif($licenseType === 'members' && count($selectedIndividualIds) > 0)
                            <div class="flex justify-between items-center py-2 border-b border-slate-100">
                                <dt class="text-sm font-medium text-slate-600">{{ __('licenses.Number of Members') }}</dt>
                                <dd class="text-sm font-semibold text-slate-900">{{ count($selectedIndividualIds) }}</dd>
                            </div>
                            @if(count($selectedIndividualIds) > 1)
                                <div class="flex justify-between items-center py-2 border-b border-slate-100">
                                    <dt class="text-sm font-medium text-slate-600">{{ __('licenses.Price per License') }}</dt>
                                    <dd class="text-sm font-semibold text-slate-900">
                                        @if($calculatedPrice > 0)
                                            €{{ number_format($calculatedPrice, 2) }}
                                        @else
                                            {{ __('licenses.Free') }}
                                        @endif
                                    </dd>
                                </div>
                            @endif
                        @endif
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <dt class="text-sm font-medium text-slate-600">{{ __('licenses.Entity') }}</dt>
                            <dd class="text-sm font-semibold text-slate-900">{{ $entity->name }}</dd>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <dt class="text-sm font-medium text-slate-600">{{ __('licenses.Federation') }}</dt>
                            <dd class="text-sm font-semibold text-slate-900">{{ $federation?->legal_name ?? __('licenses.No federation') }}</dd>
                        </div>
                        <div class="flex justify-between items-center py-3 bg-slate-50 -mx-6 px-6 mt-4">
                            <dt class="text-lg font-bold text-slate-900">{{ __('licenses.Total') }}</dt>
                            <dd class="text-2xl font-bold {{ $totalPrice > 0 ? 'text-green-600' : 'text-blue-600' }}">
                                @if($totalPrice > 0)
                                    €{{ number_format($totalPrice, 2) }}
                                @else
                                    {{ __('licenses.Free') }}
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    @endif

    <!-- Hidden Form Fields -->
    <input type="hidden" name="license_type" value="{{ $licenseType }}">
    <input type="hidden" name="license_id" value="{{ $selectedLicenseId }}">
    <input type="hidden" name="committee" value="{{ $committee }}">
    @foreach($selectedIndividualIds as $individualId)
        <input type="hidden" name="individual_ids[]" value="{{ $individualId }}">
    @endforeach

    <!-- Debug Information -->
    @if(!$canSubmit)
        <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded">
            <p class="text-sm font-medium text-yellow-800 mb-2">{{ __('licenses.cannot_proceed_with_purchase') }}</p>
            <ul class="text-xs text-yellow-700 space-y-1">
                @if(!$this->entityHasActiveAffiliation)
                    <li>• {{ __('licenses.entity_no_active_affiliation') }}</li>
                @endif
                @if(!$selectedLicenseId)
                    <li>• {{ __('licenses.no_license_selected') }}</li>
                @endif
                @if($calculatedPrice === null)
                    <li>• {{ __('licenses.price_not_calculated') }}</li>
                @endif
                @if($licenseType === 'members' && empty($selectedIndividualIds))
                    <li>• {{ __('licenses.no_members_selected') }}</li>
                @endif
                @if($this->validationPlanMessage)
                    <li>• {{ __('licenses.validation_plan') }}: {{ $this->validationPlanMessage }}</li>
                @endif
            </ul>
        </div>
    @endif

    <!-- Submit Button -->
    <div class="flex justify-end">
        <button type="submit" 
                class="inline-flex items-center px-6 py-3 transition-all duration-200 {{ $canSubmit ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-md hover:shadow-lg' : 'bg-gray-300 text-gray-500 cursor-not-allowed' }} font-semibold rounded-lg"
                wire:loading.attr="disabled"
                @disabled(!$canSubmit)>
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            @if($totalPrice > 0)
                {{ __('licenses.Purchase for €:amount', ['amount' => number_format($totalPrice, 2)]) }}
            @else
                {{ __('licenses.Request Free License') }}
            @endif
        </button>
    </div>
</div>