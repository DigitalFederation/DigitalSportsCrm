<x-layout>
    <div class="previous-layout-classes">
        <div class="mb-8 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('main.membership_subscription_details') }}</h1>
            <div class="flex gap-2">
                <a href="{{ route('admin.member-subscriptions.index') }}" class="btn btn-info">
                    {{ __('main.back') }}
                </a>
            </div>
        </div>

        <x-information-box title="{{ __('main.membership_subscription_info_title') }}"
            body="{{ __('main.membership_subscription_info_body') }}" />

        <div class="card mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
                <div>
                    <div class="text-sm text-gray-500">{{ __('main.member_type') }}</div>
                    <div class="font-semibold text-gray-900">
                        @php
                            $type = $subscription->member_type;
                        @endphp
                        @if ($type === 'individual' || $type === \Domain\Individuals\Models\Individual::class)
                            {{ __('main.individual_type') }}
                        @elseif ($type === 'entity' || $type === \Domain\Entities\Models\Entity::class)
                            {{ __('main.entity_type') }}
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">{{ __('main.member') }}</div>
                    <div class="font-semibold text-gray-900">
                        {{ $subscription->member?->name ?? __('subscriptions.member_not_found') }}
                        @if($subscription->member_type === 'Domain\\Individuals\\Models\\Individual' || $subscription->member_type === 'individual')
                            @php
                                $distributionMethods = $subscription->membershipPackage->distribution_methods ?? [];
                                $isEntityManaged = is_array($distributionMethods) && in_array('entity_managed', $distributionMethods);
                            @endphp
                            @if($isEntityManaged)
                                <span class="text-sm text-gray-500 font-normal">
                                    ({{ __('entity managed') }})
                                </span>
                            @endif
                        @endif
                    </div>
                    @if($subscription->member && method_exists($subscription->member, 'code_internal'))
                        <div class="text-sm text-gray-500">
                            {{ __('Code') }}: {{ $subscription->member->code_internal }}
                        </div>
                    @endif
                </div>
                <div>
                    <div class="text-sm text-gray-500">{{ __('main.package') }}</div>
                    <div class="font-semibold text-gray-900">{{ $subscription->membershipPackage->name }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">{{ __('main.status') }}</div>
                    <div class="flex items-center gap-2">
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
                        <button type="button"
                                x-data
                                @click="$dispatch('open-modal', { id: 'change-status-modal' })"
                                class="btn btn-sm btn-secondary">
                            {{ __('memberships.member_subscriptions.change_status') }}
                        </button>
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">{{ __('main.start_date') }}</div>
                    <div class="font-semibold text-gray-900">{{ $subscription->start_date->format('d/m/Y') }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">{{ __('main.end_date') }}</div>
                    <div class="font-semibold text-gray-900">{{ $subscription->end_date->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <!-- Affiliations -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-2">{{ __('main.affiliations') }}</h3>
                @if ($subscription->affiliations->count())
                    <ul class="text-sm">
                        @foreach ($subscription->affiliations as $affiliation)
                            <li>
                                <div class="font-medium text-gray-800">{{ $affiliation->federation->name ?? '-' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ __('main.period') }}: {{ $affiliation->start_date->format('d/m/Y') }} -
                                    {{ $affiliation->end_date->format('d/m/Y') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ __('main.fee') }}:
                                    {{ money($affiliation->individual_fee ?? $affiliation->entity_fee) }}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-gray-500 text-sm">{{ __('main.no_affiliations_found') }}</div>
                @endif
            </div>
            <!-- Insurances -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-2">{{ __('main.insurances') }}</h3>
                @if ($subscription->insurances->count())
                    <ul class="text-sm">
                        @foreach ($subscription->insurances as $insurance)
                            <li>
                                <div class="font-medium text-gray-800">
                                  {{ $insurance->insurancePlan->name ?? '-' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ __('main.period') }}: {{ $insurance->start_date->format('d/m/Y') }} -
                                    {{ $insurance->end_date->format('d/m/Y') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ __('main.fee') }}:
                                    {{ money($insurance->individual_fee ?? $insurance->entity_fee) }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ __('main.policy_number') }}:
                                    {{ $insurance->policy_number ?? __('main.not_assigned') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ __('main.external') }}:
                                    {{ $insurance->is_external ? __('main.yes') : __('main.no') }}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-gray-500 text-sm">{{ __('main.no_insurances_found') }}</div>
                @endif
            </div>

        </div>

        <!-- Change Status Modal -->
        <x-modal name="change-status-modal" maxWidth="md">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ __('memberships.member_subscriptions.change_status_title') }}
                </h2>

                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-amber-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-amber-800">
                            {{ __('memberships.member_subscriptions.change_status_warning') }}
                        </p>
                    </div>
                </div>

                <form action="{{ route('admin.member-subscriptions.update-status', $subscription) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="mb-4">
                        <label for="status_class" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('memberships.member_subscriptions.new_status') }}
                        </label>
                        <select name="status_class" id="status_class" class="form-select w-full" required>
                            <option value="Domain\Memberships\States\ActiveMemberSubscriptionState"
                                {{ $subscription->status_class === 'Domain\Memberships\States\ActiveMemberSubscriptionState' ? 'selected' : '' }}>
                                {{ __('main.active') }}
                            </option>
                            <option value="Domain\Memberships\States\PendingMemberSubscriptionState"
                                {{ $subscription->status_class === 'Domain\Memberships\States\PendingMemberSubscriptionState' ? 'selected' : '' }}>
                                {{ __('main.pending') }}
                            </option>
                            <option value="Domain\Memberships\States\PendingPaymentMemberSubscriptionState"
                                {{ $subscription->status_class === 'Domain\Memberships\States\PendingPaymentMemberSubscriptionState' ? 'selected' : '' }}>
                                {{ __('memberships.member_subscriptions.pending_payment') }}
                            </option>
                            <option value="Domain\Memberships\States\ExpiredMemberSubscriptionState"
                                {{ $subscription->status_class === 'Domain\Memberships\States\ExpiredMemberSubscriptionState' ? 'selected' : '' }}>
                                {{ __('main.expired') }}
                            </option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button"
                                @click="$dispatch('close-modal', { id: 'change-status-modal' })"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('main.cancel') }}
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('memberships.member_subscriptions.update_status') }}
                        </button>
                    </div>
                </form>
            </div>
        </x-modal>
    </div>
</x-layout>
