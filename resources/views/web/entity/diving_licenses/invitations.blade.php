@section('title', __('Technical Director Invitations'))
<x-layout>
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center">
        <!-- Left: Title -->
        <div class="mb-4 sm:mb-0">
            <h1 class="page-first-title">{{ __('diving.technical_director_invitations') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('diving.manage_invitations_sent') }}</p>
            <nav class="mt-2" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li>
                        <a href="{{ route('entity.diving_licenses.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">
                            {{ __('Diving Licenses') }}
                        </a>
                    </li>
                    <li class="text-gray-500">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </li>
                    <li class="text-sm text-gray-700">
                        {{ __('Invitations') }}
                    </li>
                </ol>
            </nav>
        </div>
        
        <!-- Right: Actions -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <a href="{{ route('entity.diving_licenses.create') }}"
               class="inline-flex items-center btn btn-primary">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                {{ __('diving.request_new_license') }}
            </a>
        </div>
    </div>

    <div class="mt-6">
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="border-t border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('diving.professional') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('diving.license') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('diving.certification_systems') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('diving.sent_on') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('diving.status') }}
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">{{ __('diving.actions') }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($invitations as $invitation)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $invitation->individual->full_name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $invitation->individual->email }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $invitation->licenseAttributed->license->name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        #{{ $invitation->licenseAttributed->license_number }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        @if(is_array($invitation->certification_systems))
                                            {{ implode(', ', $invitation->certification_systems) }}
                                        @else
                                            {{ $invitation->certification_system ?? '-' }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $invitation->created_at ? $invitation->created_at->format('d/m/Y') : '-' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $invitation->created_at ? $invitation->created_at->format('H:i') : '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($invitation->state->equals(\Domain\Diving\States\PendingDivingTechnicalDirectorInvitationState::class))
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            {{ __('diving.pending') }}
                                        </span>
                                    @elseif($invitation->state->equals(\Domain\Diving\States\AcceptedDivingTechnicalDirectorInvitationState::class))
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ __('diving.accepted') }}
                                        </span>
                                        @if($invitation->responded_at)
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ $invitation->responded_at->format('d/m/Y') }}
                                            </div>
                                        @endif
                                    @elseif($invitation->state->equals(\Domain\Diving\States\RejectedDivingTechnicalDirectorInvitationState::class))
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ __('diving.rejected') }}
                                        </span>
                                        @if($invitation->responded_at)
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ $invitation->responded_at->format('d/m/Y') }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ $invitation->state->label() }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if($invitation->canBeCanceled())
                                        <form action="{{ route('entity.diving_licenses.cancel_invitation', $invitation) }}" 
                                              method="POST" 
                                              class="inline"
                                              onsubmit="return confirm('{{ __('diving.confirm_cancel_invitation') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900">
                                                {{ __('diving.cancel') }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    {{ __('diving.no_invitations_found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($invitations->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $invitations->links() }}
                </div>
            @endif
        </div>

        <!-- Statistics -->
        @php
            $pending = $invitations->where('status_class', \Domain\Diving\States\PendingDivingTechnicalDirectorInvitationState::class)->count();
            $accepted = $invitations->where('status_class', \Domain\Diving\States\AcceptedDivingTechnicalDirectorInvitationState::class)->count();
            $rejected = $invitations->where('status_class', \Domain\Diving\States\RejectedDivingTechnicalDirectorInvitationState::class)->count();
        @endphp

        <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">
                        {{ __('diving.pending_invitations') }}
                    </dt>
                    <dd class="mt-1 text-3xl font-semibold text-yellow-600">
                        {{ $pending }}
                    </dd>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">
                        {{ __('diving.accepted') }}
                    </dt>
                    <dd class="mt-1 text-3xl font-semibold text-green-600">
                        {{ $accepted }}
                    </dd>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">
                        {{ __('diving.rejected') }}
                    </dt>
                    <dd class="mt-1 text-3xl font-semibold text-red-600">
                        {{ $rejected }}
                    </dd>
                </div>
            </div>
        </div>
    </div>
</x-layout>