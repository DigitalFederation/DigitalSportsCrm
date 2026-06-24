<div>
    <!-- Affiliation Status Check -->
    @if(!$this->individualHasActiveAffiliation)
        <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400 mr-2 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-red-800">{{ __('licenses.Active Affiliation Required') }}</h3>
                    <p class="text-sm text-red-700 mt-1">
                        {{ __('licenses.You must have an active affiliation (membership package) to purchase licenses. Please ensure your individual membership is active and paid before proceeding.') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- DIVINGSERVICES Certification Requirement Check -->
    @if($this->committee === 'DIVINGSERVICES' && !$this->hasActiveDivingServicesCertification)
        <div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-yellow-400 mr-2 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-yellow-800">{{ __('licenses.active_diving_certification_required') }}</h3>
                    <p class="text-sm text-yellow-700 mt-1">
                        {{ __('licenses.active_diving_certification_required_description') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400 mr-2 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400 mr-2 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- License Selection Card -->
    <div class="card mb-6">
        <h2 class="text-xl font-semibold text-slate-800 mb-4">{{ __('licenses.Select License') }}</h2>

        @if(count($licenses) > 0)
            <!-- License Table -->
            <div class="bg-white shadow overflow-hidden rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="w-12 px-4 py-3 text-left">
                                <!-- Select -->
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('licenses.License') }}
                            </th>
                            @if(!in_array($committee, ['DIVING', 'SCIENTIFIC']))
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                                    {{ __('licenses.sport') }}
                                </th>
                            @endif
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('licenses.Price') }}
                            </th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('licenses.status') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($licenses as $license)
                            @php
                                $hasExisting = $this->hasExistingLicense($license->id);
                                $licenseStatus = $hasExisting ? $this->getLicenseStatus($license->id) : null;
                                $isSelectable = !$hasExisting && $this->canPurchase();
                            @endphp
                            <tr class="{{ $isSelectable ? 'hover:bg-gray-50 cursor-pointer' : 'bg-gray-50' }} {{ $selectedLicenseId == $license->id ? 'bg-blue-50' : '' }}"
                                wire:loading.class="pointer-events-none opacity-50"
                                wire:target="purchaseLicense, requestFreeLicense"
                                @if($isSelectable) wire:click="$set('selectedLicenseId', {{ $license->id }})" @endif>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @if($isSelectable)
                                        <input type="radio"
                                               wire:model.live="selectedLicenseId"
                                               name="license_id"
                                               value="{{ $license->id }}"
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $license->name }}</div>
                                    @if($license->description)
                                        <div class="text-xs text-gray-500">{{ Str::limit($license->description, 60) }}</div>
                                    @endif
                                    @if($license->sport && !in_array($committee, ['DIVING', 'SCIENTIFIC']))
                                        <div class="text-xs text-gray-500 md:hidden mt-1">{{ $license->sport->name }}</div>
                                    @endif
                                </td>
                                @if(!in_array($committee, ['DIVING', 'SCIENTIFIC']))
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
                                        {{ $license->sport ? $license->sport->name : '-' }}
                                    </td>
                                @endif
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-right">
                                    @if($license->calculated_price > 0)
                                        <span class="font-semibold text-gray-900">{{ number_format($license->calculated_price, 2) }} &euro;</span>
                                    @elseif($license->calculated_price == 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ __('licenses.Free') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    @if($licenseStatus === 'active')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ __('licenses.Active') }}
                                        </span>
                                    @elseif($licenseStatus === 'pending_payment')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            {{ __('licenses.Pending Payment') }}
                                        </span>
                                    @elseif($licenseStatus === 'pending_validation')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ __('licenses.Pending Validation') }}
                                        </span>
                                    @elseif($licenseStatus === 'pending_td_approval')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ __('licenses.Pending TD') }}
                                        </span>
                                    @elseif($licenseStatus)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ __('licenses.Processing') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @error('selectedLicenseId')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-yellow-400 mr-2 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium text-yellow-800">{{ __('licenses.No licenses available') }}</h3>
                        <p class="text-sm text-yellow-700 mt-1">{{ __('licenses.There are no licenses available for purchase at the moment.') }}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Purchase Summary (only shown when license selected) -->
    @if($selectedLicense)
        <div class="card mb-6">
            <div class="flex items-center mb-4">
                <svg class="w-5 h-5 mr-2 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h2 class="text-xl font-semibold text-slate-800">{{ __('licenses.Purchase Summary') }}</h2>
            </div>

            <dl class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-slate-100">
                    <dt class="text-sm font-medium text-slate-600">{{ __('licenses.License') }}</dt>
                    <dd class="text-sm font-semibold text-slate-900">{{ $selectedLicense->name }}</dd>
                </div>
                @if($selectedLicense->sport && !in_array($committee, ['DIVING', 'SCIENTIFIC']))
                    <div class="flex justify-between items-center py-2 border-b border-slate-100">
                        <dt class="text-sm font-medium text-slate-600">{{ __('licenses.sport') }}</dt>
                        <dd class="text-sm text-slate-900">{{ $selectedLicense->sport->name }}</dd>
                    </div>
                @endif
                <div class="flex justify-between items-center py-3 bg-slate-50 -mx-4 px-4 mt-4 rounded">
                    <dt class="text-lg font-bold text-slate-900">{{ __('licenses.Total') }}</dt>
                    <dd class="text-xl font-bold {{ $calculatedPrice > 0 ? 'text-slate-900' : 'text-green-600' }}">
                        @if($calculatedPrice > 0)
                            {{ number_format($calculatedPrice, 2) }} &euro;
                        @else
                            {{ __('licenses.Free') }}
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            @if($calculatedPrice > 0)
                <button wire:click="purchaseLicense"
                        wire:loading.attr="disabled"
                        wire:target="purchaseLicense"
                        class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <span wire:loading wire:target="purchaseLicense" class="mr-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    <svg wire:loading.remove wire:target="purchaseLicense" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    {{ __('licenses.Purchase for') }} {{ number_format($calculatedPrice, 2) }} &euro;
                </button>
            @else
                <button wire:click="requestFreeLicense"
                        wire:loading.attr="disabled"
                        wire:target="requestFreeLicense"
                        class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <span wire:loading wire:target="requestFreeLicense" class="mr-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    <svg wire:loading.remove wire:target="requestFreeLicense" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ __('licenses.Request Free License') }}
                </button>
            @endif
        </div>
    @endif
</div>
