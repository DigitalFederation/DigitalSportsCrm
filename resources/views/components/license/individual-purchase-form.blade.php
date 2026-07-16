<div class="space-y-6">
    <!-- License Selection -->
    <div class="bg-white rounded-lg border border-slate-200 p-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ __('Available Licenses') }}</h3>
        
        @if(!empty($availableLicenses))
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($availableLicenses as $license)
                    <label class="border border-slate-200 rounded-lg p-4 cursor-pointer hover:border-primary-300 transition-colors
                                  {{ $selectedLicenseId == $license->id ? 'border-primary-500 bg-primary-50' : '' }}">
                        <input type="radio" name="license_id" value="{{ $license->id }}" 
                               class="sr-only" wire:model="selectedLicenseId">
                        
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="font-medium text-slate-900">{{ $license->name }}</div>
                                <div class="text-sm text-slate-600 mt-1">{{ $license->license_code }}</div>
                                
                                @if($license->professional_role)
                                    <div class="text-xs text-primary-600 mt-1">{{ $license->professional_role->name }}</div>
                                @endif
                                
                                @if($license->sport)
                                    <div class="text-xs text-slate-500 mt-1">{{ $license->sport->name }}</div>
                                @endif
                                
                                @if($license->committee)
                                    <div class="text-xs text-slate-500 mt-1">{{ $license->committee->name }}</div>
                                @endif
                            </div>
                            
                            <div class="text-right">
                                <div class="font-semibold text-slate-900">
                                    {{ money($license->unit_value_individual ?? $license->unit_value ?? 0) }}
                                </div>
                                @if($license->tax_percentage)
                                    <div class="text-xs text-slate-500">+{{ $license->tax_percentage }}% tax</div>
                                @endif
                            </div>
                        </div>
                        
                        @if($license->interval && $license->interval_unit)
                            <div class="text-xs text-slate-500 mt-2">
                                {{ __('Valid for :interval :unit', [
                                    'interval' => $license->interval,
                                    'unit' => $license->interval_unit
                                ]) }}
                            </div>
                        @endif
                        
                        <!-- License description or requirements -->
                        @if($license->description)
                            <div class="text-xs text-slate-600 mt-2 border-t border-slate-100 pt-2">
                                {{ Str::limit($license->description, 100) }}
                            </div>
                        @endif
                    </label>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-slate-500">
                <svg class="w-12 h-12 mx-auto text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-lg font-medium mb-2">{{ __('No licenses available') }}</p>
                <p>{{ __('There are currently no licenses available for individual purchase.') }}</p>
                <p class="text-sm mt-2">{{ __('Please contact your federation for more information.') }}</p>
            </div>
        @endif
    </div>

    <!-- Federation Information (if multiple federations available) -->
    @if(!empty($availableFederations) && count($availableFederations) > 1)
        <div class="bg-white rounded-lg border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ __('Federation') }}</h3>
            
            <div class="space-y-2">
                @foreach($availableFederations as $federation)
                    <label class="flex items-center p-3 border border-slate-200 rounded hover:bg-slate-50">
                        <input type="radio" name="federation_id" value="{{ $federation->id }}" 
                               class="form-radio mr-3" wire:model="selectedFederationId">
                        
                        <div class="flex-1">
                            <div class="font-medium">{{ $federation->name }}</div>
                            <div class="text-sm text-slate-600">{{ $federation->country ?? 'International' }}</div>
                        </div>
                    </label>
                @endforeach
            </div>
            
            <div class="text-xs text-slate-500 mt-3">
                {{ __('Note: Please select the federation that corresponds to your nationality or residence.') }}
            </div>
        </div>
    @endif

    <!-- Individual Information Summary -->
    @if($individual)
        <div class="bg-white rounded-lg border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ __('License Holder Information') }}</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Full Name') }}</label>
                    <div class="mt-1 text-slate-900">{{ $individual->full_name }}</div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700">{{ __('Email') }}</label>
                    <div class="mt-1 text-slate-900">{{ $individual->email }}</div>
                </div>
                
                @if($individual->birthdate)
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('Date of Birth') }}</label>
                        <div class="mt-1 text-slate-900">{{ \Carbon\Carbon::parse($individual->birthdate)->format('d/m/Y') }}</div>
                    </div>
                @endif
                
                @if($individual->member_code)
                    <div>
                        <label class="block text-sm font-medium text-slate-700">{{ __('main.Member Code') }}</label>
                        <div class="mt-1 text-slate-900">{{ $individual->member_code }}</div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Purchase Summary -->
    @if($selectedLicenseId)
        <div class="bg-white rounded-lg border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ __('Purchase Summary') }}</h3>
            
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span>{{ __('License') }}:</span>
                    <span class="font-medium">{{ $selectedLicense->name ?? '' }}</span>
                </div>
                
                <div class="flex justify-between">
                    <span>{{ __('License Holder') }}:</span>
                    <span class="font-medium">{{ $individual->full_name ?? '' }}</span>
                </div>
                
                <div class="flex justify-between">
                    <span>{{ __('Unit Price') }}:</span>
                    <span class="font-medium">{{ money($unitPrice ?? 0) }}</span>
                </div>
                
                @if($taxAmount > 0)
                    <div class="flex justify-between text-sm text-slate-600">
                        <span>{{ __('Tax') }} ({{ $selectedLicense->tax_percentage ?? 0 }}%):</span>
                        <span>{{ money($taxAmount) }}</span>
                    </div>
                @endif
                
                <div class="border-t border-slate-200 pt-3">
                    <div class="flex justify-between text-lg font-semibold">
                        <span>{{ __('Total') }}:</span>
                        <span class="text-primary-600">{{ money($totalCost ?? 0) }}</span>
                    </div>
                </div>
                
                @if($selectedLicense && $selectedLicense->interval)
                    <div class="text-sm text-slate-600 pt-2 border-t border-slate-100">
                        {{ __('License expires: :date', [
                            'date' => \Carbon\Carbon::now()->add($selectedLicense->interval, $selectedLicense->interval_unit)->format('d/m/Y')
                        ]) }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Prerequisites Check -->
    @if($selectedLicenseId && $selectedLicense)
        <div class="bg-white rounded-lg border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ __('Requirements Check') }}</h3>
            
            <div class="space-y-3">
                <!-- Age requirement -->
                @if($individual->birthdate)
                    @php
                        $age = \Carbon\Carbon::parse($individual->birthdate)->age;
                        $minAge = $selectedLicense->minimum_age ?? 18;
                        $ageValid = $age >= $minAge;
                    @endphp
                    
                    <div class="flex items-center">
                        @if($ageValid)
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-green-700">{{ __('Age requirement met') }} ({{ $age }} years)</span>
                        @else
                            <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-red-700">{{ __('Minimum age :age required', ['age' => $minAge]) }} ({{ __('You are :age', ['age' => $age]) }})</span>
                        @endif
                    </div>
                @endif
                
                <!-- Profile completion -->
                @php
                    $profileComplete = !empty($individual->full_name) && !empty($individual->email) && !empty($individual->birthdate);
                @endphp
                
                <div class="flex items-center">
                    @if($profileComplete)
                        <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-green-700">{{ __('Profile information complete') }}</span>
                    @else
                        <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-red-700">{{ __('Please complete your profile information') }}</span>
                    @endif
                </div>

                <!-- Existing license check -->
                @if($hasExistingLicense ?? false)
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-amber-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-amber-700">{{ __('You already have an active license of this type') }}</span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Notes -->
    <div class="bg-white rounded-lg border border-slate-200 p-6">
        <label class="block text-sm font-medium mb-2" for="notes">{{ __('Additional Notes') }}</label>
        <textarea name="notes" id="notes" rows="3" 
                  class="form-textarea w-full" 
                  placeholder="{{ __('Add any additional information or special requests...') }}"></textarea>
        <div class="text-xs text-slate-500 mt-1">
            {{ __('Optional: Provide any additional context for your license request') }}
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row gap-4 justify-end">
        <a href="{{ route('individual.license-attributed.index') }}" 
           class="btn btn-secondary">{{ __('Cancel') }}</a>
           
        @if($selectedLicenseId && $profileComplete && !($ageValid ?? true === false))
            <button type="submit" class="btn btn-primary">
                {{ __('Proceed to Payment') }} ({{ money($totalCost ?? 0) }})
            </button>
        @else
            <button type="button" class="btn btn-primary opacity-50 cursor-not-allowed" disabled>
                @if(!$selectedLicenseId)
                    {{ __('Select a License') }}
                @elseif(!$profileComplete)
                    {{ __('Complete Profile First') }}
                @elseif(($ageValid ?? true) === false)
                    {{ __('Age Requirement Not Met') }}
                @else
                    {{ __('Select License to Continue') }}
                @endif
            </button>
        @endif
    </div>
</div>