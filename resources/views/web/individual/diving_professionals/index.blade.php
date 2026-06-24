@section('title', __('diving.diving_professionals'))
<x-layout>
    <div class="previous-layout-classes" x-data="{
        rejectModalOpen: false,
        deactivateModalOpen: false,
        selectedId: null,
        openRejectModal(id) {
            this.selectedId = id;
            this.rejectModalOpen = true;
        },
        openDeactivateModal(id) {
            this.selectedId = id;
            this.deactivateModalOpen = true;
        }
    }">

        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('diving.my_diving_professional_relationships') }}</h1>
                <p class="text-sm text-slate-600 mt-1">{{ __('diving.manage_professional_invitations_description') }}</p>
            </div>
        </div>

        <!-- Professional Relationships Table (merged pending and active) -->
        <div class="mb-8">
            @if($professionalRoles->count() > 0)
                <div class="card-no-padding">
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full divide-y divide-slate-200">
                            <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-slate-200">
                                <tr>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-left">{{ __('entities.designation') }}</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-left">{{ __('main.district') }}</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-left">{{ __('common.member_since') }}</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-left">{{ __('common.status') }}</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-right">{{ __('common.actions') }}</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-slate-200">
                                @foreach($professionalRoles as $role)
                                    @php
                                        $isPending = $role->status_class === \Domain\Entities\States\PendingEntityProfessionalRoleState::class;
                                        $isActive = $role->status_class === \Domain\Entities\States\ActiveEntityProfessionalRoleState::class;
                                        $isRejected = $role->status_class === \Domain\Entities\States\RejectedEntityProfessionalRoleState::class;
                                    @endphp
                                    <tr class="table-row">
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <div class="flex-shrink-0 h-8 w-8">
                                                    <img src="{{ $role->entity->getFirstMediaUrl('profile', 'thumb') ?: asset('img/user_placeholder.png') }}"
                                                         alt="{{ $role->entity->name }}"
                                                         class="h-8 w-8 rounded-full object-cover">
                                                </div>
                                                <span class="font-medium text-slate-800">{{ $role->entity->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                            @if($role->entity->district)
                                                {{ $role->entity->district->name }}
                                            @else
                                                <span class="text-slate-500">-</span>
                                            @endif
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                            @if($isActive)
                                                {{ $role->updated_at->format('d/m/Y') }}
                                            @else
                                                <span class="text-slate-500">-</span>
                                            @endif
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                            @if($isPending)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                    {{ __('common.pending') }}
                                                </span>
                                            @elseif($isActive)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                    {{ __('common.active') }}
                                                </span>
                                            @elseif($isRejected)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    {{ __('common.rejected') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                            <div class="space-x-1 flex justify-end items-center">
                                                <!-- View Entity Details -->
                                                <a href="{{ route('individual.entity.show', $role->entity) }}"
                                                   class="btn btn-sm btn-secondary"
                                                   title="{{ __('main.view_details') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </a>

                                                @if($isPending)
                                                    <!-- Accept/Reject for pending -->
                                                    <form action="{{ route('individual.diving_professionals.accept', $role) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            {{ __('common.accept') }}
                                                        </button>
                                                    </form>
                                                    <button type="button"
                                                            class="btn btn-sm btn-danger"
                                                            @click="openRejectModal({{ $role->id }})">
                                                        {{ __('common.reject') }}
                                                    </button>
                                                @elseif($isActive)
                                                    <!-- Disassociate for active -->
                                                    <button type="button"
                                                            class="btn btn-sm btn-danger"
                                                            @click="openDeactivateModal({{ $role->id }})">
                                                        {{ __('diving.disassociate_entity') }}
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="text-center py-8">
                        <p class="text-slate-500">{{ __('diving.no_active_professional_relationships') }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Pagination -->
        @if($professionalRoles->hasPages())
            <div class="mt-8">
                {{ $professionalRoles->links() }}
            </div>
        @endif

        <!-- Reject Modal -->
        <div x-show="rejectModalOpen"
             x-cloak
             class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
             @click.self="rejectModalOpen = false"
             @keydown.escape.window="rejectModalOpen = false">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <form :action="`/individual/diving-professionals/${selectedId}/reject`" method="POST">
                    @csrf
                    <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('diving.reject_invitation') }}</h3>
                    <div class="mb-4">
                        <label for="reject_reason" class="block text-sm font-medium mb-1">
                            {{ __('common.reason') }}
                        </label>
                        <textarea id="reject_reason"
                                  name="reason"
                                  rows="3"
                                  class="form-textarea w-full"
                                  placeholder="{{ __('diving.rejection_reason_placeholder') }}"></textarea>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button"
                                @click="rejectModalOpen = false"
                                class="btn btn-secondary">
                            {{ __('common.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-danger">
                            {{ __('common.reject') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Deactivate Modal -->
        <div x-show="deactivateModalOpen"
             x-cloak
             class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
             @click.self="deactivateModalOpen = false"
             @keydown.escape.window="deactivateModalOpen = false">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <form :action="`/individual/diving-professionals/${selectedId}`" method="POST">
                    @csrf
                    @method('DELETE')
                    <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('diving.end_professional_relationship') }}</h3>
                    <p class="text-sm text-slate-600 mb-4">{{ __('diving.end_relationship_confirmation') }}</p>
                    <div class="mb-4">
                        <label for="deactivate_reason" class="block text-sm font-medium mb-1">
                            {{ __('common.reason') }} <span class="text-rose-500">*</span>
                        </label>
                        <textarea id="deactivate_reason"
                                  name="reason"
                                  rows="3"
                                  class="form-textarea w-full"
                                  placeholder="{{ __('diving.deactivation_reason_placeholder') }}"
                                  required></textarea>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button"
                                @click="deactivateModalOpen = false"
                                class="btn btn-secondary">
                            {{ __('common.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-danger">
                            {{ __('common.confirm') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
