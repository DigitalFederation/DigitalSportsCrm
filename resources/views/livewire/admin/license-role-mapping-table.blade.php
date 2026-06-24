<div>
    <!-- Success/Error Messages -->
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button type="button" class="inline-flex bg-green-50 rounded-md p-1.5 text-green-500 hover:bg-green-100" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button type="button" class="inline-flex bg-red-50 rounded-md p-1.5 text-red-500 hover:bg-red-100" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Filters -->
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <div class="flex-1">
            <input type="text" 
                   class="form-input w-full" 
                   placeholder="{{ __('admin.role_mappings.search_placeholder') }}"
                   wire:model.live.debounce.300ms="search">
        </div>
        <div class="flex-1">
            <select class="form-input w-full" wire:model.live="selectedCommittee">
                <option value="">{{ __('admin.role_mappings.all_committees') }}</option>
                @foreach($committees as $committee)
                    <option value="{{ $committee->id }}">{{ $committee->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1">
            <select class="form-input w-full" wire:model.live="selectedRequesterModel">
                <option value="">{{ __('admin.role_mappings.all_license_types') }}</option>
                <option value="Domain\Individuals\Models\Individual">{{ __('admin.role_mappings.individual_licenses') }}</option>
                <option value="Domain\Entities\Models\Entity">{{ __('admin.role_mappings.entity_licenses') }}</option>
            </select>
        </div>
        <div class="flex-shrink-0">
            @if(count($selectedBulkLicenses) > 0)
                <button class="btn btn-primary" wire:click="openBulkModal">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    {{ __('admin.role_mappings.bulk_edit_roles', ['count' => count($selectedBulkLicenses)]) }}
                </button>
            @endif
        </div>
    </div>

    <!-- Table -->
    <x-dynamic-table
        :headers="['', __('admin.role_mappings.license_name'), __('admin.role_mappings.code'), __('admin.role_mappings.license_type'), __('admin.role_mappings.committee'), __('admin.role_mappings.professional_role'), __('admin.role_mappings.assigned_roles'), __('admin.role_mappings.actions')]">
        @forelse($licenses as $license)
            <tr>
                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                    <input type="checkbox" 
                           class="form-checkbox"
                           value="{{ $license->id }}" 
                           wire:model.live="selectedBulkLicenses">
                </td>
                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">{{ $license->name }}</td>
                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">{{ $license->code }}</td>
                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                    {{ $license->getFormattedRequesterTypes() }}
                </td>
                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">{{ $license->committee->name ?? '-' }}</td>
                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                    @if($license->professionalRole)
                        <span class="px-2 py-1 bg-gray-200 text-gray-800 text-xs font-medium rounded-full">{{ $license->professionalRole->name }}</span>
                    @else
                        -
                    @endif
                </td>
                <td class="px-2 first:pl-5 last:pr-5 py-3">
                    @if(isset($licenseRoles[$license->id]))
                        <div class="flex flex-wrap gap-1">
                            @foreach($licenseRoles[$license->id] as $role)
                                <span class="px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-medium rounded-full">{{ $role->name }}</span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-slate-500 text-sm">{{ __('admin.role_mappings.no_roles_assigned') }}</span>
                    @endif
                </td>
                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                    <button class="btn btn-sm btn-outline-primary" 
                            wire:click="editRoles({{ $license->id }})">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        {{ __('admin.role_mappings.edit') }}
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-2 first:pl-5 last:pr-5 py-8 text-center text-slate-500">
                    {{ __('admin.role_mappings.no_licenses_found') }}
                </td>
            </tr>
        @endforelse
    </x-dynamic-table>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $licenses->links() }}
    </div>

    <!-- Edit Modal -->
    @if($showEditModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('admin.role_mappings.edit_license_roles') }}</h3>
                        <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-3">{{ __('admin.role_mappings.select_roles') }}</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-64 overflow-y-auto">
                            @foreach($allRoles as $role)
                                <div class="flex items-center">
                                    <input class="form-checkbox h-4 w-4 text-indigo-600" 
                                           type="checkbox" 
                                           value="{{ $role->id }}" 
                                           id="role_{{ $role->id }}"
                                           wire:model="selectedRoles">
                                    <label class="ml-2 text-sm text-gray-700" for="role_{{ $role->id }}">
                                        {{ $role->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end pt-4 mt-4 border-t border-gray-200 space-x-3">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            {{ __('admin.role_mappings.cancel') }}
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="saveRoles">
                            {{ __('admin.role_mappings.save_changes') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Bulk Edit Modal -->
    @if($showBulkModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeBulkModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('admin.role_mappings.bulk_edit_title', ['count' => count($selectedBulkLicenses)]) }}</h3>
                        <button wire:click="closeBulkModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="mt-4">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">{{ __('admin.role_mappings.bulk_edit_warning') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <label class="block text-sm font-medium text-gray-700 mb-3">{{ __('admin.role_mappings.select_roles') }}</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-64 overflow-y-auto">
                            @foreach($allRoles as $role)
                                <div class="flex items-center">
                                    <input class="form-checkbox h-4 w-4 text-indigo-600" 
                                           type="checkbox" 
                                           value="{{ $role->id }}" 
                                           id="bulk_role_{{ $role->id }}"
                                           wire:model="bulkRoles">
                                    <label class="ml-2 text-sm text-gray-700" for="bulk_role_{{ $role->id }}">
                                        {{ $role->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end pt-4 mt-4 border-t border-gray-200 space-x-3">
                        <button type="button" class="btn btn-secondary" wire:click="closeBulkModal">
                            {{ __('admin.role_mappings.cancel') }}
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="saveBulkRoles">
                            {{ __('admin.role_mappings.apply_to_selected') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>