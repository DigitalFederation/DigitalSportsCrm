@props(['professionalId', 'professionalName', 'action'])

<div x-data="{ 
        open: false, 
        selectedAction: 'deactivate',
        reason: '',
        submitForm() {
            if (this.selectedAction === 'deactivate' && !this.reason.trim()) {
                alert('{{ __('diving.deactivation_reason_required') }}');
                return;
            }
            
            // Set form values
            document.getElementById('action-' + {{ $professionalId }}).value = this.selectedAction;
            document.getElementById('reason-' + {{ $professionalId }}).value = this.selectedAction === 'delete' ? '' : this.reason;
            
            // Submit the form
            document.getElementById('remove-form-' + {{ $professionalId }}).submit();
        }
    }"
    @open-deactivation-modal.window="if ($event.detail.id === {{ $professionalId }}) open = true">
    
    <!-- Modal -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                {{ __('diving.manage_professional_relationship') }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    {{ __('diving.manage_relationship_with', ['name' => $professionalName]) }}
                                </p>
                            </div>
                            
                            <!-- Action Selection -->
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('diving.select_action') }}
                                </label>
                                <div class="space-y-2">
                                    <label class="flex items-start">
                                        <input type="radio" 
                                               x-model="selectedAction" 
                                               value="deactivate" 
                                               class="mt-0.5 mr-2">
                                        <div>
                                            <span class="font-medium">{{ __('diving.deactivate_relationship') }}</span>
                                            <p class="text-xs text-gray-500">{{ __('diving.deactivate_description') }}</p>
                                        </div>
                                    </label>
                                    <label class="flex items-start">
                                        <input type="radio" 
                                               x-model="selectedAction" 
                                               value="delete" 
                                               class="mt-0.5 mr-2">
                                        <div>
                                            <span class="font-medium">{{ __('diving.permanently_delete') }}</span>
                                            <p class="text-xs text-gray-500">{{ __('diving.delete_description') }}</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Reason Input (shown only for deactivation) -->
                            <div class="mt-4" x-show="selectedAction === 'deactivate'">
                                <label for="reason-{{ $professionalId }}-modal" class="block text-sm font-medium text-gray-700">
                                    {{ __('diving.deactivation_reason') }} <span class="text-red-500">*</span>
                                </label>
                                <textarea x-model="reason"
                                          id="reason-{{ $professionalId }}-modal"
                                          rows="3"
                                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                          placeholder="{{ __('diving.reason_placeholder') }}"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="submitForm()"
                            type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        {{ __('diving.confirm') }}
                    </button>
                    <button @click="open = false; selectedAction = 'deactivate'; reason = ''"
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        {{ __('diving.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hidden Form -->
    <form id="remove-form-{{ $professionalId }}" 
          action="{{ $action }}" 
          method="POST" 
          style="display: none;">
        @csrf
        @method('DELETE')
        <input type="hidden" name="action" id="action-{{ $professionalId }}" value="deactivate">
        <input type="hidden" name="reason" id="reason-{{ $professionalId }}" value="">
    </form>
</div>