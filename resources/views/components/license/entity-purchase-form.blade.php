<div class="space-y-6">
    <!-- Purchase Type Selection -->
    <div class="bg-white rounded-lg border border-slate-200 p-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ __('Purchase Type') }}</h3>
        
        <div class="space-y-4">
            <label class="flex items-center">
                <input type="radio" name="purchase_type" value="direct" 
                       class="form-radio text-primary-600 mr-3" checked
                       wire:model="purchaseType">
                <div>
                    <div class="font-medium">{{ __('Entity License') }}</div>
                    <div class="text-sm text-slate-600">{{ __('Purchase a license for this entity') }}</div>
                </div>
            </label>
            
            @if($allowGroupPurchase)
                <label class="flex items-center">
                    <input type="radio" name="purchase_type" value="group" 
                           class="form-radio text-primary-600 mr-3"
                           wire:model="purchaseType">
                    <div>
                        <div class="font-medium">{{ __('Group Purchase for Members') }}</div>
                        <div class="text-sm text-slate-600">{{ __('Purchase licenses for multiple members at once') }}</div>
                    </div>
                </label>
            @endif
        </div>
    </div>

    <!-- License Selection -->
    <div class="bg-white rounded-lg border border-slate-200 p-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ __('Select License') }}</h3>
        
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
                            </div>
                            
                            <div class="text-right">
                                <div class="font-semibold text-slate-900">
                                    €{{ number_format($license->unit_value ?? 0, 2) }}
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
                    </label>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-slate-500">
                <p>{{ __('No licenses available for purchase at this time.') }}</p>
            </div>
        @endif
    </div>

    <!-- Group Member Selection (only shown for group purchases) -->
    @if($purchaseType === 'group' && $selectedLicenseId)
        <div class="bg-white rounded-lg border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ __('Select Members') }}</h3>
            
            @if(!empty($entityMembers))
                <!-- Search members -->
                <div class="mb-4">
                    <input type="text" placeholder="{{ __('Search members...') }}" 
                           class="form-input w-full" wire:model.debounce.300ms="memberSearch">
                </div>
                
                <!-- Select all toggle -->
                <div class="mb-4 border-b border-slate-200 pb-4">
                    <label class="flex items-center">
                        <input type="checkbox" class="form-checkbox mr-2" wire:model="selectAllMembers">
                        <span class="font-medium">{{ __('Select All Members') }}</span>
                    </label>
                </div>
                
                <!-- Member list -->
                <div class="max-h-64 overflow-y-auto space-y-2">
                    @foreach($filteredMembers as $member)
                        <label class="flex items-center p-3 border border-slate-200 rounded hover:bg-slate-50">
                            <input type="checkbox" value="{{ $member->id }}" 
                                   class="form-checkbox mr-3" wire:model="selectedMembers">
                            
                            <div class="flex-1">
                                <div class="font-medium">{{ $member->full_name }}</div>
                                <div class="text-sm text-slate-600">{{ $member->email }}</div>
                                @if($member->member_code)
                                    <div class="text-xs text-slate-500">CMAS: {{ $member->member_code }}</div>
                                @endif
                            </div>
                            
                            <!-- Check if member already has this license -->
                            @if($member->hasActiveLicense($selectedLicenseId))
                                <div class="text-xs text-amber-600 bg-amber-50 px-2 py-1 rounded">
                                    {{ __('Already Licensed') }}
                                </div>
                            @endif
                        </label>
                    @endforeach
                </div>
                
                @if(count($selectedMembers) > 0)
                    <div class="mt-4 p-3 bg-blue-50 rounded">
                        <div class="text-sm font-medium text-blue-800">
                            {{ __('Selected: :count members', ['count' => count($selectedMembers)]) }}
                        </div>
                    </div>
                @endif
            @else
                <div class="text-center py-8 text-slate-500">
                    <p>{{ __('No members found for group purchase.') }}</p>
                </div>
            @endif
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
                    <span>{{ __('Quantity') }}:</span>
                    <span class="font-medium">{{ $purchaseType === 'group' ? count($selectedMembers) : 1 }}</span>
                </div>
                
                <div class="flex justify-between">
                    <span>{{ __('Unit Price') }}:</span>
                    <span class="font-medium">€{{ number_format($unitPrice ?? 0, 2) }}</span>
                </div>
                
                @if($taxAmount > 0)
                    <div class="flex justify-between text-sm text-slate-600">
                        <span>{{ __('Tax') }} ({{ $selectedLicense->tax_percentage ?? 0 }}%):</span>
                        <span>€{{ number_format($taxAmount, 2) }}</span>
                    </div>
                @endif
                
                <div class="border-t border-slate-200 pt-3">
                    <div class="flex justify-between text-lg font-semibold">
                        <span>{{ __('Total') }}:</span>
                        <span class="text-primary-600">€{{ number_format($totalCost ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Notes -->
    <div class="bg-white rounded-lg border border-slate-200 p-6">
        <label class="block text-sm font-medium mb-2" for="notes">{{ __('Notes') }}</label>
        <textarea name="notes" id="notes" rows="3" 
                  class="form-textarea w-full" 
                  placeholder="{{ __('Add any additional notes for this purchase...') }}"></textarea>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row gap-4 justify-end">
        <a href="{{ route(Request::segment(1).'.license-attributed.index') }}" 
           class="btn btn-secondary">{{ __('Cancel') }}</a>
           
        @if($selectedLicenseId && ($purchaseType !== 'group' || count($selectedMembers) > 0))
            <button type="submit" class="btn btn-primary">
                {{ __('Proceed to Payment') }} (€{{ number_format($totalCost ?? 0, 2) }})
            </button>
        @else
            <button type="button" class="btn btn-primary opacity-50 cursor-not-allowed" disabled>
                {{ __('Select License to Continue') }}
            </button>
        @endif
    </div>
</div>