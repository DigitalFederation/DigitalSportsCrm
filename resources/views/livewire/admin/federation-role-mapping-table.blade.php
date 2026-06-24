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

    <!-- Global Roles Section -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm mb-6">
        <div class="bg-indigo-600 text-white px-5 py-3 rounded-t-lg">
            <h2 class="text-lg font-semibold flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ __('admin.role_mappings.global_federation_roles') }}
            </h2>
        </div>
        <div class="p-5">
            <p class="text-slate-600 mb-4">{{ __('admin.role_mappings.global_roles_description') }}</p>
            <div class="mb-4">
                @if($currentGlobalRoles->count() > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach($currentGlobalRoles as $role)
                            <span class="inline-flex items-center px-3 py-1 bg-indigo-100 text-indigo-800 text-sm font-medium rounded-full">
                                {{ $role->name }}
                                @if($role->requires_active_membership)
                                    <svg class="w-4 h-4 ml-1 text-indigo-600" fill="currentColor" viewBox="0 0 20 20" title="{{ __('admin.role_mappings.requires_membership') }}">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                @endif
                            </span>
                        @endforeach
                    </div>
                @else
                    <span class="text-slate-500">{{ __('admin.role_mappings.no_global_roles') }}</span>
                @endif
            </div>
            <button class="btn btn-primary" wire:click="editGlobalRoles">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                {{ __('admin.role_mappings.edit_global_roles') }}
            </button>
        </div>
    </div>

    <!-- Federation-Specific Roles -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
        <div class="px-5 py-3 border-b border-slate-200">
            <h2 class="text-lg font-semibold text-slate-800">{{ __('admin.role_mappings.federation_specific_roles') }}</h2>
        </div>
        <div class="p-5">
            <!-- Search -->
            <div class="mb-6">
                <input type="text" 
                       class="form-input w-full md:w-1/2" 
                       placeholder="{{ __('admin.role_mappings.search_federations') }}"
                       wire:model.live.debounce.300ms="search">
            </div>

            <!-- Table -->
            <x-dynamic-table
                :headers="[__('admin.role_mappings.federation_name'), __('admin.role_mappings.code'), __('admin.role_mappings.assigned_roles'), __('admin.role_mappings.actions')]">
                @forelse($federations as $federation)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">{{ $federation->name }}</td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">{{ $federation->code }}</td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3">
                            @if(isset($federationRoles[$federation->id]))
                                <div class="flex flex-wrap gap-1">
                                    @foreach($federationRoles[$federation->id] as $role)
                                        <span class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                            {{ $role->name }}
                                            @if($role->requires_active_membership)
                                                <svg class="w-3 h-3 ml-1 text-blue-600" fill="currentColor" viewBox="0 0 20 20" title="{{ __('admin.role_mappings.requires_membership') }}">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-slate-500 text-sm">{{ __('admin.role_mappings.no_roles_assigned') }}</span>
                            @endif
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <button class="btn btn-sm btn-outline-primary" 
                                    wire:click="editRoles({{ $federation->id }})">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                {{ __('admin.role_mappings.edit') }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-2 first:pl-5 last:pr-5 py-8 text-center text-slate-500">
                            {{ __('admin.role_mappings.no_federations_found') }}
                        </td>
                    </tr>
                @endforelse
            </x-dynamic-table>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $federations->links() }}
            </div>
        </div>
    </div>

    <!-- Edit Federation Roles Modal -->
    @if($showEditModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('admin.role_mappings.edit_federation_roles') }}</h3>
                        <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('admin.role_mappings.select_roles') }}</label>
                        <p class="text-sm text-gray-500 mb-4">{{ __('admin.role_mappings.federation_roles_help') }}</p>
                        <div class="max-h-64 overflow-y-auto">
                            @foreach($allRoles as $role)
                                <div class="mb-3">
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
                                    @if(in_array($role->id, $selectedRoles))
                                        <div class="ml-6 mt-2">
                                            <div class="flex items-center">
                                                <input class="form-checkbox h-4 w-4 text-indigo-600" 
                                                       type="checkbox" 
                                                       id="membership_{{ $role->id }}"
                                                       wire:model="requiresActiveMembership.{{ $role->id }}">
                                                <label class="ml-2 text-sm text-gray-500" for="membership_{{ $role->id }}">
                                                    {{ __('admin.role_mappings.requires_active_membership') }}
                                                </label>
                                            </div>
                                        </div>
                                    @endif
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

    <!-- Edit Global Roles Modal -->
    @if($showGlobalModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="closeGlobalModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white" wire:click.stop>
                <div class="mt-3">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('admin.role_mappings.edit_global_federation_roles') }}</h3>
                        <button wire:click="closeGlobalModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="mt-4">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">{{ __('admin.role_mappings.global_roles_info') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <label class="block text-sm font-medium text-gray-700 mb-3">{{ __('admin.role_mappings.select_global_roles') }}</label>
                        <div class="max-h-64 overflow-y-auto">
                            @foreach($allRoles as $role)
                                <div class="mb-3">
                                    <div class="flex items-center">
                                        <input class="form-checkbox h-4 w-4 text-indigo-600" 
                                               type="checkbox" 
                                               value="{{ $role->id }}" 
                                               id="global_role_{{ $role->id }}"
                                               wire:model="globalRoles">
                                        <label class="ml-2 text-sm text-gray-700" for="global_role_{{ $role->id }}">
                                            {{ $role->name }}
                                        </label>
                                    </div>
                                    @if(in_array($role->id, $globalRoles))
                                        <div class="ml-6 mt-2">
                                            <div class="flex items-center">
                                                <input class="form-checkbox h-4 w-4 text-indigo-600" 
                                                       type="checkbox" 
                                                       id="global_membership_{{ $role->id }}"
                                                       wire:model="globalRequiresActiveMembership.{{ $role->id }}">
                                                <label class="ml-2 text-sm text-gray-500" for="global_membership_{{ $role->id }}">
                                                    {{ __('admin.role_mappings.requires_active_membership') }}
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end pt-4 mt-4 border-t border-gray-200 space-x-3">
                        <button type="button" class="btn btn-secondary" wire:click="closeGlobalModal">
                            {{ __('admin.role_mappings.cancel') }}
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="saveGlobalRoles">
                            {{ __('admin.role_mappings.save_changes') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>