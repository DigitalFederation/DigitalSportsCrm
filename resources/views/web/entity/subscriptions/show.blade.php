@section('title', __('main.subscription_details'))

<x-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">{{ __('main.subscription_details') }}</h1>
                <p class="text-slate-600">{{ $subscription->membershipPackage->name }}</p>
            </div>
            <!-- Right: Back button -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('entity.subscriptions.index') }}" class="btn bg-slate-500 hover:bg-slate-600 text-white">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="m9.4 13.4-1.4 1.4-6-6 6-6 1.4 1.4-4.6 4.6z"/>
                    </svg>
                    <span class="ml-2">{{ __('main.back_to_subscriptions') }}</span>
                </a>
            </div>
        </div>

        <!-- Subscription Overview -->
        <div class="bg-white shadow-lg rounded-lg border border-slate-200 mb-8">
            <div class="px-6 py-5">
                <h2 class="font-semibold text-slate-800 mb-4 text-xl">{{ __('main.subscription_overview') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-slate-600">{{ __('main.package') }}</dt>
                        <dd class="mt-1 text-sm text-slate-900 font-semibold">{{ $subscription->membershipPackage->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-600">{{ __('main.status') }}</dt>
                        <dd class="mt-1">
                            @if($subscription->status_class === 'Domain\Memberships\States\ActiveMemberSubscriptionState')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ __('main.active') }}
                                </span>
                            @elseif($subscription->status_class === 'Domain\Memberships\States\PendingPaymentMemberSubscriptionState')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    {{ __('main.pending_payment') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                    {{ __('main.inactive') }}
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-600">{{ __('main.start_date') }}</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $subscription->start_date->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-600">{{ __('main.end_date') }}</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $subscription->end_date->format('d/m/Y') }}</dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- Package Description -->
        @if($subscription->membershipPackage->description)
        <div class="bg-white shadow-lg rounded-lg border border-slate-200 mb-8">
            <div class="px-6 py-5">
                <h2 class="font-semibold text-slate-800 mb-4 text-xl">{{ __('main.package_description') }}</h2>
                <p class="text-slate-600">{{ $subscription->membershipPackage->description }}</p>
            </div>
        </div>
        @endif

        <!-- Affiliations -->
        @if($subscription->affiliations->count() > 0)
        <div class="bg-white shadow-lg rounded-lg border border-slate-200 mb-8">
            <div class="px-6 py-5">
                <h2 class="font-semibold text-slate-800 mb-4 text-xl">{{ __('main.affiliations') }}</h2>
                <x-dynamic-table :headers="[__('main.affiliation_plan'), __('main.federation'), __('main.fee'), __('main.period'), __('main.status')]">
                    @foreach($subscription->affiliations as $affiliation)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium text-slate-900">
                                    {{ $affiliation->affiliation_plan->name ?? __('main.not_available') }}
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($affiliation->federation)
                                        <div class="flex items-center">
                                            @if($affiliation->federation->country)
                                                <img src="{{ asset('img/flags/' . strtolower($affiliation->federation->country->code) . '.svg') }}" 
                                                     alt="{{ $affiliation->federation->country->name }}" 
                                                     class="w-4 h-4 mr-2">
                                            @endif
                                            {{ $affiliation->federation->name }}
                                        </div>
                                    @else
                                        <span class="text-slate-500">{{ __('main.not_available') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                @if($affiliation->entity_fee)
                                    {{ money($affiliation->entity_fee) }}
                                @else
                                    <span class="text-slate-500">{{ __('main.free') }}</span>
                                @endif
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                {{ $affiliation->start_date->format('d/m/Y') }} - {{ $affiliation->end_date->format('d/m/Y') }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                @if($affiliation->end_date->isFuture())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ __('main.active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                        {{ __('main.expired') }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>
            </div>
        </div>
        @endif

        <!-- Insurances -->
        @if($subscription->insurances->count() > 0)
        <div class="bg-white shadow-lg rounded-lg border border-slate-200 mb-8">
            <div class="px-6 py-5">
                <h2 class="font-semibold text-slate-800 mb-4 text-xl">{{ __('main.insurances') }}</h2>
                <x-dynamic-table :headers="[__('main.insurance_plan'), __('main.fee'), __('main.period'), __('main.policy_number'), __('main.status')]">
                    @foreach($subscription->insurances as $insurance)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                {{ $insurance->insurancePlan->name ?? __('main.not_available') }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                @if($insurance->entity_fee)
                                    {{ money($insurance->entity_fee) }}
                                @else
                                    <span class="text-slate-500">{{ __('main.free') }}</span>
                                @endif
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                {{ $insurance->start_date->format('d/m/Y') }} - {{ $insurance->end_date->format('d/m/Y') }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                @if($insurance->policy_number)
                                    <span class="font-mono text-sm">{{ $insurance->policy_number }}</span>
                                @else
                                    <span class="text-slate-500">{{ __('main.not_available') }}</span>
                                @endif
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                @if($insurance->end_date->isFuture())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ __('main.active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                        {{ __('main.expired') }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>
            </div>
        </div>
        @endif


    </div>
</x-layout>