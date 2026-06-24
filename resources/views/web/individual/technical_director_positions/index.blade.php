<x-layout>
@section('title', __('diving.technical_director_positions'))
    <div class="previous-layout-classes">
        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('diving.technical_director_positions') }}</h1>
                <p class="text-sm text-slate-600 mt-1">{{ __('diving.technical_director_positions_description') }}</p>
            </div>
        </div>

        @if($pendingInvitations->count() > 0)
            <div class="card-no-padding">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-snug text-slate-800 font-bold">{{ __('diving.pending_technical_director_invitations') }}</h3>
                    <p class="text-sm text-slate-600 mt-1">{{ __('diving.pending_technical_director_invitations_info') }}</p>
                </div>
                <div class="overflow-x-auto border-t border-slate-200">
                    <table class="table-auto w-full divide-y divide-slate-200">
                        <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-slate-200">
                            <tr>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('entities.name') }}</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('diving.license_type') }}</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('diving.certification_systems') }}</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('common.requested_at') }}</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-right">{{ __('common.actions') }}</div></th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-200">
                            @foreach($pendingInvitations as $invitation)
                                <tr>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">{{ $invitation->entity->name }}</td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        @if($invitation->licenseAttributed && $invitation->licenseAttributed->license)
                                            {{ $invitation->licenseAttributed->license->code ?? '-' }}
                                            <span class="text-xs text-gray-500 block">{{ $invitation->licenseAttributed->license->name ?? '' }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        @if($invitation->certification_systems)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($invitation->certification_systems as $system)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ $system }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">{{ $invitation->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="space-x-1 flex justify-end items-end">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ __('diving.pending_your_acceptance') }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($technicalDirectorPositions->count() > 0)
            <div class="card-no-padding {{ $pendingInvitations->count() > 0 ? 'mt-6' : '' }}">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-snug text-slate-800 font-bold">{{ __('diving.active_technical_director_positions') }}</h3>
                    <p class="text-sm text-slate-600 mt-1">{{ __('diving.active_technical_director_positions_info') }}</p>
                </div>
                <div class="overflow-x-auto border-t border-slate-200">
                    <table class="table-auto w-full divide-y divide-slate-200">
                        <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-slate-200">
                            <tr>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('entities.designation') }}</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('licenses.license') }}</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('diving.diving_training_systems') }}</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('common.status') }}</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('diving.accepted_date') }}</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-right">{{ __('common.actions') }}</div></th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-200">
                            @foreach($technicalDirectorPositions as $position)
                                <tr>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">{{ $position->entity->name }}</td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        @if($position->licenseAttributed && $position->licenseAttributed->license)
                                            {{ $position->licenseAttributed->license->code ?? '-' }}
                                            <span class="text-xs text-gray-500 block">{{ $position->licenseAttributed->license->name ?? '' }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        @if($position->certification_systems)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($position->certification_systems as $system)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ $system }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        @if(!$position->licenseAttributed)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ __('common.unknown') }}
                                            </span>
                                        @elseif($position->licenseAttributed->status_class === 'Domain\Licenses\States\ActiveLicenseAttributedState')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ __('common.active') }}
                                            </span>
                                        @elseif($position->licenseAttributed->status_class === 'Domain\Licenses\States\PendingTechnicalDirectorApprovalLicenseAttributedState')
                                            @if($position->hasApproved())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ __('diving.approved_by_you') }}
                                                </span>
                                            @elseif($position->hasRejected())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    {{ __('diving.rejected_by_you') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                    {{ __('diving.pending_your_approval') }}
                                                </span>
                                            @endif
                                        @elseif($position->licenseAttributed->status_class === 'Domain\Licenses\States\PendingValidationLicenseAttributedState')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ __('diving.status_pending_validation') }}
                                            </span>
                                        @elseif($position->licenseAttributed->status_class === 'Domain\Licenses\States\PendingLicenseAttributedState')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                {{ __('diving.pending_payment') }}
                                            </span>
                                        @elseif($position->licenseAttributed->status_class === 'Domain\Licenses\States\CanceledLicenseAttributedState')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                {{ __('diving.rejected_by_dt') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ class_basename($position->licenseAttributed->status_class) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        {{ $position->responded_at ? $position->responded_at->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="space-x-1 flex justify-end items-end">
                                            @if($position->licenseAttributed && $position->licenseAttributed->status_class === 'Domain\Licenses\States\PendingTechnicalDirectorApprovalLicenseAttributedState' && $position->isPendingApproval())
                                                <button
                                                    type="button"
                                                    x-data="{}"
                                                    x-on:click="$dispatch('show-approve-modal', {
                                                        technicalDirectorId: '{{ $position->id }}',
                                                        entityName: '{{ $position->entity->name }}',
                                                        licenseName: '{{ $position->licenseAttributed && $position->licenseAttributed->license ? $position->licenseAttributed->license->name : '-' }}'
                                                    })"
                                                    class="btn btn-xs btn-success">
                                                    {{ __('common.approve') }}
                                                </button>
                                                <button
                                                    type="button"
                                                    x-data="{}"
                                                    x-on:click="$dispatch('show-reject-modal', {
                                                        technicalDirectorId: '{{ $position->id }}',
                                                        entityName: '{{ $position->entity->name }}',
                                                        licenseName: '{{ $position->licenseAttributed && $position->licenseAttributed->license ? $position->licenseAttributed->license->name : '-' }}'
                                                    })"
                                                    class="btn btn-xs btn-danger">
                                                    {{ __('common.reject') }}
                                                </button>
                                            @else
                                                <a href="{{ route('individual.entity.show', $position->entity) }}" class="btn btn-sm btn-secondary">
                                                    {{ __('diving.view_entity') }}
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($technicalDirectorPositions->count() == 0 && $pendingInvitations->count() == 0)
            <div class="card">
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('diving.no_technical_director_positions') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('diving.no_technical_director_positions_info') }}</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Approve Modal -->
    <div x-data="{ 
        open: false, 
        technicalDirectorId: null,
        entityName: '',
        licenseName: '',
        approvalNotes: '',
        isSubmitting: false
    }"
         x-on:show-approve-modal.window="
            technicalDirectorId = $event.detail.technicalDirectorId;
            entityName = $event.detail.entityName;
            licenseName = $event.detail.licenseName;
            approvalNotes = '';
            open = true;
         "
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         x-transition:enter="ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0">
        
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-on:click="open = false"></div>
            
            <div class="card inline-block align-bottom text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="grow font-semibold text-slate-800 truncate mb-3">{{ __('diving.approve_license') }}</h3>
                        <div class="mt-2">
                            <p class="text-sm text-slate-600">
                                {{ __('diving.approve_license_confirmation', ['entity' => '']) }}<strong x-text="entityName"></strong>{{ __('diving.for_license') }} <strong x-text="licenseName"></strong>?
                            </p>
                            <div class="mt-4">
                                <label class="block text-sm font-medium mb-1" for="approval-notes">
                                    {{ __('diving.approval_notes_optional') }}
                                </label>
                                <textarea 
                                    id="approval-notes"
                                    x-model="approvalNotes" 
                                    rows="3"
                                    class="form-textarea w-full"
                                    :placeholder="'{{ __('diving.approval_notes_placeholder') }}'"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-wrap justify-end space-x-2 mt-6">
                    <button 
                        type="button" 
                        x-on:click="open = false" 
                        class="btn btn-secondary">
                        {{ __('common.cancel') }}
                    </button>
                    <button 
                        type="button" 
                        x-on:click="
                            if (!isSubmitting) {
                                isSubmitting = true;
                                fetch('{{ url('individual/technical-director-positions') }}/' + technicalDirectorId + '/approve', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                                    },
                                    body: JSON.stringify({
                                        approval_notes: approvalNotes
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        location.reload();
                                    } else {
                                        alert(data.message);
                                        isSubmitting = false;
                                    }
                                })
                                .catch(error => {
                                    alert('{{ __('common.error_occurred') }}');
                                    isSubmitting = false;
                                });
                            }
                        "
                        :disabled="isSubmitting"
                        class="btn btn-success">
                        <span x-show="!isSubmitting">{{ __('common.approve') }}</span>
                        <span x-show="isSubmitting">{{ __('common.processing') }}...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-data="{ 
        open: false, 
        technicalDirectorId: null,
        entityName: '',
        licenseName: '',
        rejectionReason: '',
        isSubmitting: false
    }"
         x-on:show-reject-modal.window="
            technicalDirectorId = $event.detail.technicalDirectorId;
            entityName = $event.detail.entityName;
            licenseName = $event.detail.licenseName;
            rejectionReason = '';
            open = true;
         "
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         x-transition:enter="ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0">
        
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-on:click="open = false"></div>
            
            <div class="card inline-block align-bottom text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="grow font-semibold text-slate-800 truncate mb-3">{{ __('diving.reject_license') }}</h3>
                        <div class="mt-2">
                            <p class="text-sm text-slate-600">
                                {{ __('diving.reject_license_confirmation', ['entity' => '']) }}<strong x-text="entityName"></strong>{{ __('diving.for_license') }} <strong x-text="licenseName"></strong>?
                            </p>
                            <div class="mt-4">
                                <label class="block text-sm font-medium mb-1" for="rejection-reason">
                                    {{ __('diving.rejection_reason_required') }} <span class="text-rose-500">*</span>
                                </label>
                                <textarea 
                                    id="rejection-reason"
                                    x-model="rejectionReason" 
                                    required
                                    rows="3"
                                    class="form-textarea w-full"
                                    :placeholder="'{{ __('diving.rejection_reason_placeholder') }}'"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-wrap justify-end space-x-2 mt-6">
                    <button 
                        type="button" 
                        x-on:click="open = false" 
                        class="btn btn-secondary">
                        {{ __('common.cancel') }}
                    </button>
                    <button 
                        type="button" 
                        x-on:click="
                            if (!isSubmitting && rejectionReason.trim()) {
                                isSubmitting = true;
                                fetch('{{ url('individual/technical-director-positions') }}/' + technicalDirectorId + '/reject', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                                    },
                                    body: JSON.stringify({
                                        rejection_reason: rejectionReason
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        location.reload();
                                    } else {
                                        alert(data.message);
                                        isSubmitting = false;
                                    }
                                })
                                .catch(error => {
                                    alert('{{ __('common.error_occurred') }}');
                                    isSubmitting = false;
                                });
                            } else if (!rejectionReason.trim()) {
                                alert('{{ __('diving.rejection_reason_required') }}');
                            }
                        "
                        :disabled="isSubmitting || !rejectionReason.trim()"
                        class="btn btn-danger">
                        <span x-show="!isSubmitting">{{ __('common.reject') }}</span>
                        <span x-show="isSubmitting">{{ __('common.processing') }}...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layout>