<!-- License Management Modal -->
<div x-data="{ 
    activeTab: 'details',
    confirmAction: false,
    actionType: '',
    actionReason: '',
    newExpirationDate: ''
}" 
@if($showModal) 
    class="fixed inset-0 z-50 overflow-y-auto" 
    aria-labelledby="modal-title" 
    role="dialog" 
    aria-modal="true"
@else
    class="hidden"
@endif>
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>
        
        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            
            <!-- Modal header -->
            <div class="bg-white px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        {{ __('Manage License') }}: {{ $license->license_name ?? '' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Tab Navigation -->
                <div class="mt-4">
                    <nav class="flex space-x-8">
                        <button @click="activeTab = 'details'" 
                                :class="activeTab === 'details' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                            {{ __('Details') }}
                        </button>
                        <button @click="activeTab = 'actions'" 
                                :class="activeTab === 'actions' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                            {{ __('Actions') }}
                        </button>
                        <button @click="activeTab = 'history'" 
                                :class="activeTab === 'history' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                            {{ __('History') }}
                        </button>
                        <button @click="activeTab = 'notes'" 
                                :class="activeTab === 'notes' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                            {{ __('Notes') }}
                        </button>
                    </nav>
                </div>
            </div>
            
            <!-- Modal content -->
            <div class="bg-white px-6 py-4 max-h-96 overflow-y-auto">
                
                <!-- Details Tab -->
                <div x-show="activeTab === 'details'" class="space-y-6">
                    @if($license)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- License Information -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 mb-3">{{ __('License Information') }}</h4>
                                <dl class="space-y-2">
                                    <div>
                                        <dt class="text-sm text-gray-500">{{ __('License Name') }}</dt>
                                        <dd class="text-sm font-medium text-gray-900">{{ $license->license_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm text-gray-500">{{ __('License Code') }}</dt>
                                        <dd class="text-sm font-medium text-gray-900">{{ $license->license_code }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm text-gray-500">{{ __('Status') }}</dt>
                                        <dd class="text-sm font-medium">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                {{ $license->stateColor() === 'green' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $license->stateColor() === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $license->stateColor() === 'red' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $license->stateColor() === 'gray' ? 'bg-gray-100 text-gray-800' : '' }}">
                                                {{ ucfirst(__($license->stateName())) }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm text-gray-500">{{ __('International Code') }}</dt>
                                        <dd class="text-sm font-medium text-gray-900">{{ $license->license_number ?: __('Not assigned') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm text-gray-500">{{ __('Expiration Date') }}</dt>
                                        <dd class="text-sm font-medium text-gray-900">
                                            @if($license->date_expire)
                                                {{ \Carbon\Carbon::parse($license->date_expire)->format('d/m/Y') }}
                                                @if(\Carbon\Carbon::parse($license->date_expire)->isPast())
                                                    <span class="text-red-600 text-xs ml-1">({{ __('Expired') }})</span>
                                                @elseif(\Carbon\Carbon::parse($license->date_expire)->diffInDays() <= 30)
                                                    <span class="text-amber-600 text-xs ml-1">({{ __('Expires in :days days', ['days' => \Carbon\Carbon::parse($license->date_expire)->diffInDays()]) }})</span>
                                                @endif
                                            @else
                                                {{ __('Permanent') }}
                                            @endif
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                            
                            <!-- Holder Information -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 mb-3">{{ __('License Holder') }}</h4>
                                <dl class="space-y-2">
                                    <div>
                                        <dt class="text-sm text-gray-500">{{ __('Name') }}</dt>
                                        <dd class="text-sm font-medium text-gray-900">{{ $license->holder_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm text-gray-500">{{ __('Email') }}</dt>
                                        <dd class="text-sm font-medium text-gray-900">{{ $license->holder_email }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm text-gray-500">{{ __('Type') }}</dt>
                                        <dd class="text-sm font-medium text-gray-900">
                                            {{ $license->owner_type === 'individual' ? __('Individual') : __('Entity') }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm text-gray-500">{{ __('Federation') }}</dt>
                                        <dd class="text-sm font-medium text-gray-900">{{ $license->federation_name }}</dd>
                                    </div>
                                    @if($license->activated_at)
                                        <div>
                                            <dt class="text-sm text-gray-500">{{ __('Activation Date') }}</dt>
                                            <dd class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($license->activated_at)->format('d/m/Y H:i') }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>
                        
                        <!-- Purchase Information -->
                        @if($license->purchased_at || $license->total_value)
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 mb-3">{{ __('Purchase Information') }}</h4>
                                <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    @if($license->purchased_at)
                                        <div>
                                            <dt class="text-sm text-gray-500">{{ __('Purchase Date') }}</dt>
                                            <dd class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($license->purchased_at)->format('d/m/Y H:i') }}</dd>
                                        </div>
                                    @endif
                                    @if($license->total_value)
                                        <div>
                                            <dt class="text-sm text-gray-500">{{ __('Total Value') }}</dt>
                                            <dd class="text-sm font-medium text-gray-900">{{ money($license->total_value) }}</dd>
                                        </div>
                                    @endif
                                    @if($license->request_type)
                                        <div>
                                            <dt class="text-sm text-gray-500">{{ __('Request Type') }}</dt>
                                            <dd class="text-sm font-medium text-gray-900">{{ ucfirst(__($license->request_type)) }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        @endif
                    @endif
                </div>
                
                <!-- Actions Tab -->
                <div x-show="activeTab === 'actions'" class="space-y-4">
                    
                    <!-- State Actions -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">{{ __('License Status Actions') }}</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            
                            @if($license && $license->status_class === \Domain\Licenses\States\ActiveLicenseAttributedState::class)
                                <button @click="confirmAction = true; actionType = 'suspend'" 
                                        class="flex items-center justify-center px-4 py-2 border border-yellow-300 rounded-md shadow-sm bg-yellow-50 text-sm font-medium text-yellow-700 hover:bg-yellow-100">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ __('Suspend License') }}
                                </button>
                            @endif
                            
                            @if($license && $license->status_class === \Domain\Licenses\States\SuspendedLicenseAttributedState::class)
                                <button @click="confirmAction = true; actionType = 'activate'" 
                                        class="flex items-center justify-center px-4 py-2 border border-green-300 rounded-md shadow-sm bg-green-50 text-sm font-medium text-green-700 hover:bg-green-100">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ __('Reactivate License') }}
                                </button>
                            @endif
                            
                            <button @click="confirmAction = true; actionType = 'delete'" 
                                    class="flex items-center justify-center px-4 py-2 border border-red-300 rounded-md shadow-sm bg-red-50 text-sm font-medium text-red-700 hover:bg-red-100">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                {{ __('Delete License') }}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Expiration Management -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">{{ __('Expiration Management') }}</h4>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('New Expiration Date') }}</label>
                                <input type="date" x-model="newExpirationDate" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            <button @click="if(newExpirationDate) { $wire.updateExpiration(newExpirationDate) }" 
                                    class="flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ __('Update Expiration') }}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Communication -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">{{ __('Communication') }}</h4>
                        <div class="space-y-3">
                            <button wire:click="sendExpirationReminder" 
                                    class="flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                {{ __('Send Expiration Reminder') }}
                            </button>
                            
                            <button wire:click="sendRenewalNotice" 
                                    class="flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                {{ __('Send Renewal Notice') }}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Confirmation Dialog -->
                    <div x-show="confirmAction" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                        {{ __('Confirm Action') }}
                                    </h3>
                                    <p class="text-sm text-gray-500 mb-4">
                                        <span x-show="actionType === 'suspend'">{{ __('Are you sure you want to suspend this license?') }}</span>
                                        <span x-show="actionType === 'activate'">{{ __('Are you sure you want to reactivate this license?') }}</span>
                                        <span x-show="actionType === 'delete'">{{ __('Are you sure you want to delete this license? This action cannot be undone.') }}</span>
                                    </p>
                                    <div x-show="actionType === 'suspend' || actionType === 'delete'">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Reason (optional)') }}</label>
                                        <textarea x-model="actionReason" rows="3" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="{{ __('Enter reason for this action...') }}"></textarea>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                    <button @click="
                                        if(actionType === 'suspend') $wire.suspendLicense(actionReason);
                                        if(actionType === 'activate') $wire.activateLicense();
                                        if(actionType === 'delete') $wire.deleteLicense(actionReason);
                                        confirmAction = false; actionReason = '';
                                    " class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                        {{ __('Confirm') }}
                                    </button>
                                    <button @click="confirmAction = false; actionReason = ''" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                        {{ __('Cancel') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- History Tab -->
                <div x-show="activeTab === 'history'" class="space-y-4">
                    @if($licenseHistory && count($licenseHistory) > 0)
                        <div class="flow-root">
                            <ul role="list" class="-mb-8">
                                @foreach($licenseHistory as $index => $activity)
                                    <li>
                                        <div class="relative pb-8">
                                            @if(!$loop->last)
                                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                            @endif
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full 
                                                        {{ str_contains(strtolower($activity->event ?? ''), 'suspend') ? 'bg-yellow-500' : '' }}
                                                        {{ str_contains(strtolower($activity->event ?? ''), 'activ') ? 'bg-green-500' : '' }}
                                                        {{ str_contains(strtolower($activity->event ?? ''), 'delet') ? 'bg-red-500' : '' }}
                                                        {{ str_contains(strtolower($activity->event ?? ''), 'creat') ? 'bg-blue-500' : '' }}
                                                        {{ !str_contains(strtolower($activity->event ?? ''), 'suspend') && !str_contains(strtolower($activity->event ?? ''), 'activ') && !str_contains(strtolower($activity->event ?? ''), 'delet') && !str_contains(strtolower($activity->event ?? ''), 'creat') ? 'bg-gray-500' : '' }}
                                                        flex items-center justify-center">
                                                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500">
                                                            {{ $activity->description ?? __('Activity recorded') }}
                                                        </p>
                                                        @if($activity->properties && is_array($activity->properties))
                                                            <div class="mt-2 text-xs text-gray-400">
                                                                @foreach($activity->properties as $key => $value)
                                                                    <div>{{ ucfirst($key) }}: {{ $value }}</div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                        {{ \Carbon\Carbon::parse($activity->created_at)->format('d/m/Y H:i') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <p>{{ __('No history available for this license.') }}</p>
                        </div>
                    @endif
                </div>
                
                <!-- Notes Tab -->
                <div x-show="activeTab === 'notes'" class="space-y-4">
                    <div>
                        <label for="adminNotes" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Admin Notes') }}</label>
                        <textarea wire:model.defer="adminNotes" id="adminNotes" rows="6" 
                                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                                  placeholder="{{ __('Add administrative notes about this license...') }}"></textarea>
                    </div>
                    
                    <div class="flex justify-end">
                        <button wire:click="saveNotes" class="btn btn-primary">
                            {{ __('Save Notes') }}
                        </button>
                    </div>
                    
                    @if($license && $license->notes)
                        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                            <h5 class="text-sm font-medium text-gray-900 mb-2">{{ __('Current Notes') }}</h5>
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $license->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Modal footer -->
            <div class="bg-gray-50 px-6 py-3 flex justify-end space-x-3">
                <button wire:click="closeModal" class="btn btn-secondary">
                    {{ __('Close') }}
                </button>
            </div>
        </div>
    </div>
</div>