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
                <a href="{{ route('individual.subscriptions.index') }}" class="btn bg-slate-500 hover:bg-slate-600 text-white">
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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="lg:col-span-2 bg-slate-50 rounded-lg p-4">
                        <dt class="text-sm font-medium text-slate-600">{{ __('main.package') }}</dt>
                        <dd class="mt-1 text-lg text-slate-900 font-semibold">{{ $subscription->membershipPackage->name }}</dd>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-4">
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
                    <div class="bg-slate-50 rounded-lg p-4">
                        <dt class="text-sm font-medium text-slate-600">{{ __('main.start_date') }}</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $subscription->start_date->format('d/m/Y') }}</dd>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-4">
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

        <!-- Package Contents -->
        <div class="bg-white shadow-lg rounded-lg border border-slate-200 mb-8">
            <div class="px-6 py-5">
                <h2 class="font-semibold text-slate-800 mb-4 text-xl">{{ __('main.package_contents') }}</h2>
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Affiliation Plans -->
                    @if($subscription->membershipPackage->affiliationPlans->count() > 0)
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h3 class="font-medium text-blue-900 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ __('main.affiliation_plans') }}
                        </h3>
                        <ul class="space-y-2">
                            @foreach($subscription->membershipPackage->affiliationPlans as $affiliation)
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-blue-600 mt-0.5 mr-2 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <div class="text-sm font-medium text-blue-900">{{ $affiliation->name }}</div>
                                        @if($affiliation->federation)
                                            <div class="text-xs text-blue-700">{{ $affiliation->federation->name }}</div>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Insurance Plans -->
                    @if($subscription->membershipPackage->insurancePlans->count() > 0)
                    <div class="bg-green-50 rounded-lg p-4">
                        <h3 class="font-medium text-green-900 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ __('main.insurance_plans') }}
                        </h3>
                        <ul class="space-y-2">
                            @foreach($subscription->membershipPackage->insurancePlans as $insurance)
                                <li class="flex items-start">
                                    <svg class="w-4 h-4 text-green-600 mt-0.5 mr-2 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <div class="text-sm font-medium text-green-900">{{ $insurance->name }}</div>
                                        @if($insurance->description)
                                            <div class="text-xs text-green-700">{{ Str::limit($insurance->description, 50) }}</div>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Action buttons -->
        <div class="flex flex-col sm:flex-row gap-4">
            @if($subscription->status_class === 'Domain\Memberships\States\ExpiredMemberSubscriptionState')
                <form action="{{ route('individual.subscriptions.renew', $subscription) }}" method="POST" class="inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn bg-green-500 hover:bg-green-600 text-white w-full sm:w-auto">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                        </svg>
                        {{ __('main.renew_subscription') }}
                    </button>
                </form>
            @endif

        </div>
    </div>
</x-layout>