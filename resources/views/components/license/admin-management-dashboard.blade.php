<div class="space-y-6">
    <!-- Dashboard Header with Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Total Licenses -->
        <div class="bg-white rounded-lg border border-slate-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-sm font-medium text-slate-600">{{ __('Total Licenses') }}</div>
                    <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['total_licenses'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <!-- Active Licenses -->
        <div class="bg-white rounded-lg border border-slate-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l3 3 8-8"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-sm font-medium text-slate-600">{{ __('Active Licenses') }}</div>
                    <div class="text-2xl font-bold text-green-600">{{ number_format($stats['active_licenses'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <!-- Pending Payment -->
        <div class="bg-white rounded-lg border border-slate-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-sm font-medium text-slate-600">{{ __('Pending Payment') }}</div>
                    <div class="text-2xl font-bold text-amber-600">{{ number_format($stats['pending_licenses'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <!-- Expiring Soon -->
        <div class="bg-white rounded-lg border border-slate-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.734-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-sm font-medium text-slate-600">{{ __('Expiring Soon') }}</div>
                    <div class="text-2xl font-bold text-red-600">{{ number_format($stats['expiring_soon'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg border border-slate-200 p-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ __('Quick Actions') }}</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <button wire:click="openBulkSuspendModal" class="btn btn-warning">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ __('Bulk Suspend') }}
            </button>
            
            <button wire:click="openBulkActivateModal" class="btn btn-success">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ __('Bulk Activate') }}
            </button>
            
            <button wire:click="openSendNotificationModal" class="btn btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                {{ __('Send Notifications') }}
            </button>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg border border-slate-200 p-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ __('Find Licenses') }}</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Search') }}</label>
                <input type="text" wire:model.debounce.300ms="search" 
                       placeholder="{{ __('License name, holder name, Nº Filiado...') }}"
                       class="form-input w-full">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Status') }}</label>
                <select wire:model="statusFilter" class="form-select w-full">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="pending">{{ __('Pending') }}</option>
                    <option value="active">{{ __('Active') }}</option>
                    <option value="suspended">{{ __('Suspended') }}</option>
                    <option value="expired">{{ __('Expired') }}</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('License Type') }}</label>
                <select wire:model="licenseFilter" class="form-select w-full">
                    <option value="">{{ __('All Licenses') }}</option>
                    @foreach($availableLicenses as $license)
                        <option value="{{ $license->id }}">{{ $license->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Date Range') }}</label>
                <select wire:model="dateFilter" class="form-select w-full">
                    <option value="">{{ __('All Time') }}</option>
                    <option value="today">{{ __('Today') }}</option>
                    <option value="week">{{ __('This Week') }}</option>
                    <option value="month">{{ __('This Month') }}</option>
                    <option value="year">{{ __('This Year') }}</option>
                </select>
            </div>
        </div>
    </div>

    <!-- License Results Table -->
    <div class="bg-white rounded-lg border border-slate-200">
        <div class="p-6 border-b border-slate-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-slate-800">
                    {{ __('License Results') }} 
                    @if($totalResults > 0)
                        <span class="text-sm font-normal text-slate-600">({{ number_format($totalResults) }} {{ __('found') }})</span>
                    @endif
                </h3>
                
                <div class="flex gap-2">
                    <button wire:click="exportResults" class="btn btn-secondary btn-sm">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        {{ __('Export') }}
                    </button>
                    
                    <div class="relative">
                        <input type="checkbox" wire:model="selectAll" class="form-checkbox mr-2">
                        <span class="text-sm text-slate-600">{{ __('Select All') }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                            <input type="checkbox" wire:model="selectAll" class="form-checkbox">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                            {{ __('Holder') }}
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                            {{ __('License') }}
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                            {{ __('Status') }}
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                            {{ __('Expiration') }}
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                            {{ __('main.Member Code') }}
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                            {{ __('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse($licenses as $license)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <input type="checkbox" wire:model="selectedLicenses" value="{{ $license->id }}" class="form-checkbox">
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-slate-900">{{ $license->holder_name }}</div>
                                        <div class="text-sm text-slate-500">{{ $license->holder_email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-900">{{ $license->license_name }}</div>
                                <div class="text-sm text-slate-500">{{ $license->license_code }}</div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $license->stateColor() === 'green' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $license->stateColor() === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $license->stateColor() === 'red' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $license->stateColor() === 'gray' ? 'bg-gray-100 text-gray-800' : '' }}">
                                    {{ ucfirst(__($license->stateName())) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-900">
                                @if($license->date_expire)
                                    {{ \Carbon\Carbon::parse($license->date_expire)->format('d/m/Y') }}
                                    @if(\Carbon\Carbon::parse($license->date_expire)->isPast())
                                        <span class="text-red-600 text-xs">({{ __('Expired') }})</span>
                                    @elseif(\Carbon\Carbon::parse($license->date_expire)->diffInDays() <= 30)
                                        <span class="text-amber-600 text-xs">({{ __(':days days', ['days' => \Carbon\Carbon::parse($license->date_expire)->diffInDays()]) }})</span>
                                    @endif
                                @else
                                    <span class="text-slate-500">{{ __('Permanent') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-900">
                                {{ $license->license_number ?: __('Not assigned') }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <button wire:click="viewLicense({{ $license->id }})" 
                                            class="text-indigo-600 hover:text-indigo-900 text-sm">
                                        {{ __('View') }}
                                    </button>
                                    
                                    @if($license->status_class === \Domain\Licenses\States\ActiveLicenseAttributedState::class)
                                        <button wire:click="suspendLicense({{ $license->id }})" 
                                                class="text-yellow-600 hover:text-yellow-900 text-sm">
                                            {{ __('Suspend') }}
                                        </button>
                                    @elseif($license->status_class === \Domain\Licenses\States\SuspendedLicenseAttributedState::class)
                                        <button wire:click="activateLicense({{ $license->id }})" 
                                                class="text-green-600 hover:text-green-900 text-sm">
                                            {{ __('Activate') }}
                                        </button>
                                    @endif
                                    
                                    <button wire:click="manageLicense({{ $license->id }})" 
                                            class="text-blue-600 hover:text-blue-900 text-sm">
                                        {{ __('Manage') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                <svg class="w-12 h-12 mx-auto text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-lg font-medium mb-2">{{ __('No licenses found') }}</p>
                                <p>{{ __('Try adjusting your search criteria or filters.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($licenses instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $licenses->links() }}
            </div>
        @endif
    </div>

    <!-- Bulk Actions Bar (shown when licenses are selected) -->
    @if(count($selectedLicenses) > 0)
        <div class="fixed bottom-6 left-1/2 transform -translate-x-1/2 bg-white rounded-lg shadow-lg border border-slate-200 p-4 z-50">
            <div class="flex items-center space-x-4">
                <span class="text-sm font-medium text-slate-700">
                    {{ trans_choice(':count license selected|:count licenses selected', count($selectedLicenses), ['count' => count($selectedLicenses)]) }}
                </span>
                
                <div class="flex space-x-2">
                    <button wire:click="bulkSuspend" class="btn btn-warning btn-sm">
                        {{ __('Suspend') }}
                    </button>
                    <button wire:click="bulkActivate" class="btn btn-success btn-sm">
                        {{ __('Activate') }}
                    </button>
                    <button wire:click="bulkNotify" class="btn btn-primary btn-sm">
                        {{ __('Notify') }}
                    </button>
                    <button wire:click="bulkExport" class="btn btn-secondary btn-sm">
                        {{ __('Export') }}
                    </button>
                </div>
                
                <button wire:click="clearSelection" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif
</div>