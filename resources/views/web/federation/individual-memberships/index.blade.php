@section('title', __('federation.individual_memberships.title'))
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="page-first-title">{{ __('federation.individual_memberships.title') }}</h1>
                <p class="text-slate-600 mt-2">{{ __('federation.individual_memberships.subtitle') }}</p>
            </div>
            <div>
                <a href="{{ route('federation.individual-memberships.create') }}" 
                   class="btn btn-primary">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z"/>
                    </svg>
                    <span class="ml-2">{{ __('federation.common.add_new') }}</span>
                </a>
            </div>
        </div>

        <!-- Information Box -->
        <x-information-box
            title="{{ __('federation.individual_memberships.info_title') }}"
            body="{{ __('federation.individual_memberships.info_body') }}">
        </x-information-box>

        <!-- Subscriptions Table -->
        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            <x-dynamic-table
                :headers="[
                    __('federation.common.member'),
                    __('federation.common.type'),
                    __('federation.common.plan_name'),
                    __('federation.common.organization'),
                    __('federation.common.start_date'),
                    __('federation.common.end_date'),
                    __('federation.common.requested_by'),
                    __('federation.common.status'),
                    __('federation.common.view_profile')
                 ]">
                @forelse($subscriptions as $subscription)
                    <tr>
                        {{-- 1. Membro --}}
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <div class="font-medium text-slate-800">{{ $subscription->member->name ?? $subscription->member->full_name ?? 'N/A' }}</div>
                            <div class="text-xs text-slate-500">{{ $subscription->member->member_code ?? $subscription->member->code_internal ?? '' }}</div>
                        </td>
                        {{-- 2. Tipo --}}
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ __('federation.common.individual') }}
                            </span>
                        </td>
                        {{-- 3. Nome do Plano --}}
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <div class="font-medium text-slate-800">{{ $subscription->membershipPackage->name }}</div>
                        </td>
                        {{-- 4. Organização --}}
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @php
                                $entity = $subscription->member->entities->first();
                            @endphp
                            @if($entity)
                                <div class="font-medium text-slate-800">{{ $entity->name }}</div>
                                <div class="text-xs text-slate-500">{{ $entity->code_internal }}</div>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        {{-- 5. Data de Início --}}
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <div class="text-sm text-slate-800">{{ $subscription->start_date->format('d/m/Y') }}</div>
                        </td>
                        {{-- 6. Data de Fim --}}
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <div class="text-sm text-slate-800">{{ $subscription->end_date->format('d/m/Y') }}</div>
                        </td>
                        {{-- 7. Solicitado por --}}
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @if($subscription->requester_type)
                                <div class="text-sm">
                                    @php
                                        $requesterType = is_array($subscription->requester_type) ? 'unknown' : $subscription->requester_type;
                                        $requesterTypeLabel = match($requesterType) {
                                            'entity' => __('federation.common.entity'),
                                            'individual' => __('federation.common.individual'),
                                            'federation' => __('federation.common.federation'),
                                            'user' => __('federation.common.user'),
                                            default => __('federation.common.unknown')
                                        };
                                        // Ensure it's always a string
                                        if (is_array($requesterTypeLabel)) {
                                            $requesterTypeLabel = 'Unknown';
                                        }
                                    @endphp
                                    @if($subscription->requester && is_object($subscription->requester) && isset($subscription->requester->name))
                                        <div class="text-sm font-medium text-slate-800">
                                            {{ $subscription->requester->name }}
                                        </div>
                                        <div class="text-xs text-slate-500">{{ $requesterTypeLabel }}</div>
                                    @else
                                        <span class="font-medium text-sm">{{ $requesterTypeLabel }}</span>
                                    @endif
                                    @if($subscription->request_type === 'federation_facilitated')
                                        <span class="text-xs text-slate-500">({{ __('federation.common.facilitated') }})</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        {{-- 8. Estado --}}
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @php
                                $statusClass = class_basename($subscription->status_class);
                                $statusColor = match($statusClass) {
                                    'ActiveMemberSubscriptionState' => 'bg-emerald-100 text-emerald-600',
                                    'PendingPaymentMemberSubscriptionState' => 'bg-amber-100 text-amber-600',
                                    'ExpiredMemberSubscriptionState' => 'bg-slate-100 text-slate-600',
                                    'PendingMemberSubscriptionState' => 'bg-blue-100 text-blue-600',
                                    default => 'bg-slate-100 text-slate-600'
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ $subscription->state->name() }}
                            </span>
                        </td>
                        {{-- 9. Ver Perfil (action) --}}
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <div class="flex justify-center">
                                <a href="{{ route('federation.individual-memberships.show', $subscription->id) }}" 
                                   class="text-indigo-600 hover:text-indigo-900"
                                   title="{{ __('federation.common.view_profile') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-2 first:pl-5 last:pr-5 py-12 text-center">
                            <div class="text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-lg font-medium">{{ __('federation.individual_memberships.no_records_found') }}</p>
                                <p class="text-sm mt-2">{{ __('federation.individual_memberships.no_records_help') }}</p>
                                <a href="{{ route('federation.individual-memberships.create') }}" 
                                   class="btn btn-primary mt-4">
                                    {{ __('federation.individual_memberships.create_first') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-dynamic-table>
        </div>

        <!-- Pagination -->
        @if($subscriptions->hasPages())
            <div class="mt-6">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>
</x-layout>