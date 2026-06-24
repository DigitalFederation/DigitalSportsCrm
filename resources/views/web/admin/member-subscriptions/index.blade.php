<x-layout>
    <div class="previous-layout-classes">

        <div class="mb-8 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('main.membership_subscriptions') }}</h1>
            <a href="{{ route('admin.member-subscriptions.create') }}" class="btn btn-primary">
                {{ __('main.create_membership_subscription') }}
            </a>
        </div>

        <x-information-box title="{{ __('main.membership_subscriptions_info_title') }}"
            body="{{ __('main.membership_subscriptions_info_body') }}" />

        <div class="sm:flex flex-row gap-4">
            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('admin.member-subscriptions.index')">
                <x-forms.filter-input-select label="{{ __('main.member_type') }}" name="member_type"
                    :options="[
                        'individual' => __('main.individual_type'),
                        'entity' => __('main.entity_type'),
                    ]" />
                <x-forms.filter-input-select label="{{ __('main.status') }}" name="status_class"
                    :options="[
                        'Domain\Memberships\States\ActiveMemberSubscriptionState' => __('subscriptions.active'),
                        'Domain\Memberships\States\PendingPaymentMemberSubscriptionState' => __('subscriptions.pending_payment'),
                        'Domain\Memberships\States\ExpiredMemberSubscriptionState' => __('subscriptions.expired'),
                        'Domain\Memberships\States\PendingMemberSubscriptionState' => __('subscriptions.pending'),
                    ]" />
                <x-forms.filter-input-text label="{{ __('main.member_name') }}" name="member.name" />
                <x-forms.filter-input-select label="{{ __('main.package_name') }}" name="membership_package_id"
                    :options="$membershipPackages->toArray()" />
                <x-forms.filter-input-select label="{{ __('main.requested_by_entity') }}" name="requester_id"
                    :options="$requesterEntities->toArray()" />
            </x-filter-form>
        </div>

        <x-dynamic-table :pagination="$subscriptions" paginationTitle="{{ __('main.membership_subscriptions') }}"
            :headers="[
                __('main.member'),
                __('main.type'),
                __('main.plan_name'),
                __('main.start_date'),
                __('main.end_date'),
                __('main.requested_by'),
                __('main.status'),
                __('main.actions'),
            ]">
            @foreach ($subscriptions as $subscription)
                <tr>
                    {{-- 1. Membro --}}
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div>
                            <div class="text-sm font-medium text-gray-900">
                                @if($subscription->member)
                                    @if($subscription->member_type === 'individual' || $subscription->member_type === \Domain\Individuals\Models\Individual::class)
                                        {{ $subscription->member->native_name ?? $subscription->member->full_name }}
                                    @else
                                        {{ $subscription->member->name }}
                                    @endif
                                @else
                                    {{ __('subscriptions.member_not_found') }}
                                @endif
                            </div>
                            @if($subscription->member && method_exists($subscription->member, 'code_internal'))
                                <div class="text-xs text-gray-500">
                                    {{ $subscription->member->code_internal }}
                                </div>
                            @endif
                        </div>
                    </td>
                    {{-- 2. Tipo --}}
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        @php
                            $type = $subscription->member_type;
                        @endphp
                        @if ($type === 'individual' || $type === \Domain\Individuals\Models\Individual::class)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ __('main.individual_type') }}
                            </span>
                        @elseif ($type === 'entity' || $type === \Domain\Entities\Models\Entity::class)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ __('main.entity_type') }}
                            </span>
                        @else
                            {{ $type }}
                        @endif
                    </td>
                    {{-- 3. Nome do Plano --}}
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $subscription->membershipPackage->name }}
                        </div>
                    </td>
                    {{-- 4. Data de Início --}}
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $subscription->start_date->format('d/m/Y') }}</div>
                    </td>
                    {{-- 5. Data de Fim --}}
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $subscription->end_date->format('d/m/Y') }}</div>
                    </td>
                    {{-- 6. Solicitado por --}}
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        @if($subscription->requester_type)
                            <div class="text-sm">
                                @php
                                    $requesterType = is_array($subscription->requester_type) ? 'unknown' : $subscription->requester_type;
                                    $requesterTypeLabel = match($requesterType) {
                                        \Domain\Entities\Models\Entity::class, 'entity' => __('main.entity'),
                                        \Domain\Individuals\Models\Individual::class, 'individual' => __('main.individual'),
                                        \Domain\Federations\Models\Federation::class, 'federation' => __('main.federation'),
                                        \App\Models\User::class, 'user' => __('main.user'),
                                        default => __('main.unknown')
                                    };
                                @endphp
                                @if($subscription->requester && is_object($subscription->requester) && isset($subscription->requester->name))
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $subscription->requester->name }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $requesterTypeLabel }}</div>
                                @else
                                    <span class="font-medium text-sm">{{ $requesterTypeLabel }}</span>
                                @endif
                            </div>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    {{-- 7. Estado --}}
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        @php
                            $statusClass = class_basename($subscription->status_class);
                            $statusColor = match($statusClass) {
                                'ActiveMemberSubscriptionState' => 'bg-emerald-100 text-emerald-600',
                                'PendingPaymentMemberSubscriptionState' => 'bg-amber-100 text-amber-600',
                                'ExpiredMemberSubscriptionState' => 'bg-slate-100 text-slate-600',
                                'PendingMemberSubscriptionState' => 'bg-blue-100 text-blue-600',
                                default => 'bg-slate-100 text-slate-600'
                            };
                            $statusLabel = match($statusClass) {
                                'ActiveMemberSubscriptionState' => __('subscriptions.active'),
                                'PendingPaymentMemberSubscriptionState' => __('subscriptions.pending_payment'),
                                'ExpiredMemberSubscriptionState' => __('subscriptions.expired'),
                                'PendingMemberSubscriptionState' => __('subscriptions.pending'),
                                default => $subscription->state->name()
                            };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                    {{-- 8. Actions --}}
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="space-x-1 flex justify-end items-end">
                            <x-dynamic-table-buttons type="show" :route="route('admin.member-subscriptions.show', $subscription)" />
                            <x-dynamic-table-buttons type="delete" 
                                                     :route="route('admin.member-subscriptions.destroy', $subscription)" 
                                                     method="DELETE"
                                                     x-on:click.prevent="$dispatch('open-modal', { id: 'delete-subscription-{{ $subscription->id }}' })" />
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-dynamic-table>

        <div class="mt-4">
            {{ $subscriptions->links() }}
        </div>

        <!-- Delete Confirmation Modals -->
        @foreach ($subscriptions as $subscription)
            <x-modal name="delete-subscription-{{ $subscription->id }}" maxWidth="md">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ __('memberships.member_subscriptions.confirm_delete_title') }}
                    </h2>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-yellow-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-yellow-800">
                                {{ __('memberships.member_subscriptions.confirm_delete_warning') }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 p-3 rounded-lg mb-4">
                        <p class="text-sm">
                            <span class="font-semibold">{{ __('main.member') }}:</span> 
                            {{ $subscription->member?->name ?? __('main.unknown') }}
                        </p>
                        <p class="text-sm">
                            <span class="font-semibold">{{ __('main.package') }}:</span> 
                            {{ $subscription->membershipPackage?->name ?? __('main.unknown') }}
                        </p>
                        @if($subscription->start_date)
                            <p class="text-sm">
                                <span class="font-semibold">{{ __('main.period') }}:</span> 
                                {{ $subscription->start_date->format('d/m/Y') }} - {{ $subscription->end_date?->format('d/m/Y') ?? __('main.no_date') }}
                            </p>
                        @endif
                        @if($subscription->affiliations_count > 0 || $subscription->insurances_count > 0)
                            <p class="text-sm mt-2 text-red-600 font-semibold">
                                {{ __('memberships.member_subscriptions.will_delete_related', [
                                    'affiliations' => $subscription->affiliations_count,
                                    'insurances' => $subscription->insurances_count
                                ]) }}
                            </p>
                        @endif
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button"
                                @click="$dispatch('close-modal', { id: 'delete-subscription-{{ $subscription->id }}' })"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('main.cancel') }}
                        </button>
                        
                        <form action="{{ route('admin.member-subscriptions.destroy', $subscription) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                {{ __('memberships.member_subscriptions.delete_confirm') }}
                            </button>
                        </form>
                    </div>
                </div>
            </x-modal>
        @endforeach

    </div>
</x-layout>