<x-layout>
    <div x-data="{
    showConfirmModal: false,
    showDetailsModal: false,
    selectedPackage: null,
    selectedPackageName: '',
    selectedPackagePrice: 0,
    selectedPackageItems: [],
    detailType: '',
    detailName: '',
    detailDescription: '',
    detailFiles: []
    }" class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">{{ __('subscriptions.membership_packages') }}</h1>
            </div>
        </div>

        <!-- Improved copy in Portuguese -->
        <div class="bg-white shadow-lg rounded-lg border border-slate-200 mb-8 p-6">
            <h2 class="text-xl font-semibold text-slate-800 mb-2">{{ __('subscriptions.welcome') }}</h2>
            <p class="text-slate-600 font-medium">
                {{ __('subscriptions.welcome_message') }}
            </p>
        </div>

        <!-- Current subscriptions section -->
        @if($currentSubscription->isNotEmpty())
            <div class="mb-8">
                <h2 class="font-semibold text-slate-800 mb-6 text-xl">{{ __('subscriptions.current_subscriptions') }}</h2>
                
                <x-ui.card-grid columns="2" gap="lg">
                    @foreach($currentSubscription as $subscription)
                        @php
                            $isActive = $subscription->status_class === 'Domain\Memberships\States\ActiveMemberSubscriptionState';
                            $isPending = $subscription->status_class === 'Domain\Memberships\States\PendingPaymentMemberSubscriptionState';
                            $headerBg = $isActive ? 'bg-green-600' : ($isPending ? 'bg-amber-500' : 'bg-slate-600');
                            $daysRemaining = floor(now()->diffInDays($subscription->end_date, false));
                            $isExpiring = $daysRemaining <= 30 && $daysRemaining > 0;
                            $isExpired = $daysRemaining < 0;
                        @endphp
                        <div class="bg-white rounded-lg shadow-lg border border-slate-200 overflow-hidden hover:shadow-xl transition-all duration-300 h-full flex flex-col">
                            <!-- Colored Header -->
                            <div class="{{ $headerBg }} px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-white">
                                        {{ $subscription->membershipPackage->name }}
                                    </h3>
                                    <div class="flex items-center text-white">
                                        @if($isActive)
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        @elseif($isPending)
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-1">
                                    <span class="text-sm text-white/90">
                                        @if($isActive)
                                            {{ __('main.active') }}
                                        @elseif($isPending)
                                            {{ __('main.pending_payment') }}
                                        @else
                                            {{ $subscription->status->label() }}
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <!-- Card Content -->
                            <div class="flex-1 p-6 flex flex-col">
                                <!-- Subscription Details -->
                                <div class="space-y-3 flex-1">
                                    <!-- Start Date -->
                                    <div class="flex items-center justify-between py-2 border-b border-slate-100">
                                        <span class="text-sm text-slate-600">{{ __('subscriptions.start_date') }}</span>
                                        <span class="text-sm font-medium text-slate-900">
                                            {{ $subscription->start_date->format('d/m/Y') }}
                                        </span>
                                    </div>

                                    <!-- Expiration Date -->
                                    <div class="flex items-center justify-between py-2 border-b border-slate-100">
                                        <span class="text-sm text-slate-600">{{ __('subscriptions.expiration_date') }}</span>
                                        <span class="text-sm font-medium text-slate-900">
                                            {{ $subscription->end_date->format('d/m/Y') }}
                                        </span>
                                    </div>

                                    <!-- Time Remaining -->
                                    @if(!$isExpired)
                                        <div class="flex items-center justify-between py-2">
                                            <span class="text-sm text-slate-600">{{ __('subscriptions.time_remaining') }}</span>
                                            <span class="text-sm font-medium {{ $isExpiring ? 'text-amber-600' : 'text-slate-900' }}">
                                                @if($daysRemaining > 0)
                                                    {{ $daysRemaining }} {{ __('subscriptions.days') }}
                                                @else
                                                    {{ __('subscriptions.expires_today') }}
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Card Actions -->
                                <div class="mt-6 pt-4 border-t border-slate-100">
                                    <div class="flex gap-2">
                                        <x-ui.button
                                            variant="outline"
                                            size="default"
                                            class="flex-1"
                                            href="{{ route('individual.subscriptions.show', $subscription->id) }}"
                                        >
                                            {{ __('subscriptions.view_details') }}
                                        </x-ui.button>

                                        @if($isPending)
                                            @if(isset($subscription->pendingDocument) && $subscription->pendingDocument)
                                                <x-ui.button
                                                    variant="primary"
                                                    size="default"
                                                    class="flex-1"
                                                    href="{{ route('individual.document.show', $subscription->pendingDocument->id) }}"
                                                >
                                                    {{ __('subscriptions.pay') }}
                                                </x-ui.button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </x-ui.card-grid>
            </div>
        @endif

        @if($subscriptionHistory->isNotEmpty())
        <!-- Subscription history section -->
            <div class="mb-8">
                <h2 class="font-semibold text-slate-800 mb-6 text-xl">{{ __('subscriptions.subscription_history') }}</h2>
                
                <x-ui.card-grid columns="3" gap="default">
                    @foreach($subscriptionHistory as $subscription)
                        <x-ui.card variant="outlined" size="compact" class="h-full">
                            <!-- Card Header -->
                            <div class="mb-3">
                                <h3 class="text-base font-semibold text-slate-900 leading-tight mb-2">
                                    {{ $subscription->membershipPackage->name }}
                                </h3>
                                
                                <!-- Status Badge -->
                                <div class="flex items-center gap-2">
                                    <x-ui.badge
                                        variant="{{ $subscription->end_date->isPast() ? 'red' : 'green' }}"
                                        size="sm"
                                    >
                                        {{ $subscription->end_date->isPast() ? __('subscriptions.expired') : __('subscriptions.active') }}
                                    </x-ui.badge>
                                </div>
                            </div>

                            <!-- Subscription Period -->
                            <div class="space-y-2 mb-4 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-600">{{ __('subscriptions.period') }}</span>
                                    <span class="text-slate-900 font-medium text-xs">
                                        {{ $subscription->start_date->format('d/m/Y') }} - {{ $subscription->end_date->format('d/m/Y') }}
                                    </span>
                                </div>

                                <!-- Duration -->
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-600">{{ __('subscriptions.duration') }}</span>
                                    <span class="text-slate-900 font-medium">
                                        {{ $subscription->start_date->diffInDays($subscription->end_date) }} {{ __('subscriptions.days') }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Federation Flag (if available) -->
                            @if(isset($subscription->federation))
                                <div class="pt-3 border-t border-slate-100">
                                    <div class="flex items-center gap-2">
                                        <img src="{{ asset('img/flags/pt.svg') }}" alt="{{ $subscription->federation?->country?->name }}" class="w-4 h-4">
                                        <span class="text-xs text-slate-600">{{ $subscription->federation->name }}</span>
                                    </div>
                                </div>
                            @endif
                        </x-ui.card>
                    @endforeach
                </x-ui.card-grid>
            </div>
        @endif

        <!-- Details Modal -->
        <div x-show="showDetailsModal"
             class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.away="showDetailsModal = false"
             x-cloak>
            <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full m-4"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95">
                <div class="p-6">
                    <button @click="showDetailsModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4" x-text="detailName"></h3>
                    <p class="text-sm text-gray-600 mb-4" x-text="detailDescription"></p>
                    <template x-if="detailType === 'insurance' && detailFiles.length > 0">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">{{ __('subscriptions.related_documents') }}</h4>
                            <ul class="space-y-2">
                                <template x-for="file in detailFiles" :key="file.id">
                                    <li class="flex items-center space-x-2">
                                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                        </svg>
                                        <a :href="file.url" target="_blank" class="text-blue-600 hover:underline" x-text="file.name"></a>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>
                </div>
            </div>
        </div>


        <!-- Membership selection section -->
        @if($availablePackages->isNotEmpty())
        <div class="">
            <h2 class="font-semibold text-slate-800 mb-6 text-xl">{{ __('subscriptions.choose_package') }}</h2>

            <x-ui.card-grid columns="3" gap="lg">
                @foreach($availablePackages as $package)
                    <div class="bg-white rounded-lg shadow-lg border border-slate-200 overflow-hidden hover:shadow-xl transition-all duration-300 h-full flex flex-col">
                        <!-- Blue Header -->
                        <div class="bg-blue-600 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-white">
                                    {{ $package->name }}
                                </h3>
                                <div class="flex items-center text-white">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="flex-1 p-6 flex flex-col">
                            <!-- Price Section -->
                            @php
                                $price = $package->calculatePriceFor(get_class($individual));
                            @endphp
                            @if($price > 0)
                                <div class="mb-4">
                                    <div class="flex items-baseline">
                                        <span class="text-2xl font-bold text-slate-900">€{{ number_format($price, 2) }}</span>
                                        <span class="text-sm text-slate-500 ml-1">/ ano</span>
                                    </div>
                                </div>
                            @else
                                <div class="mb-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Gratuito
                                    </span>
                                </div>
                            @endif

                            <!-- Description -->
                            @if($package->description)
                                <div class="mb-4 inline-flex">
                                    <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    <p class="text-sm text-slate-600 leading-relaxed">{{ $package->description }}</p>
                                </div>
                            @endif

                            <!-- Package Contents -->
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold text-slate-900 mb-3 flex items-center">

                                    {{ __('subscriptions.whats_included') }}
                                </h4>

                                <div class="space-y-3">
                                    <!-- Affiliation Plans -->
                                    @if($package->affiliationPlans->isNotEmpty())
                                        <div>
                                            <div class="flex items-center text-xs font-medium text-slate-700 mb-2">
                                                {{ __('subscriptions.affiliations') }}
                                            </div>
                                            <div class="space-y-1">
                                                @foreach($package->affiliationPlans as $plan)
                                                    <div class="flex items-center text-sm text-slate-600">
                                                        <svg class="w-3 h-3 mr-2 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                        <span class="flex-1">{{ $plan->name }}</span>
                                                        @if($plan->individual_fee > 0)
                                                            <span class="text-xs text-slate-500">€{{ number_format($plan->individual_fee, 2) }}</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Insurance Plans -->
                                    @if($package->insurancePlans->isNotEmpty())
                                        <div>
                                            <div class="flex items-center text-xs font-medium text-slate-700 mb-2">
                                                {{ __('subscriptions.insurances') }}
                                            </div>
                                            <div class="space-y-1">
                                                @foreach($package->insurancePlans as $plan)
                                                    <div class="flex items-center text-sm text-slate-600">
                                                        <svg class="w-3 h-3 mr-2 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                        <span class="flex-1">{{ $plan->name }}</span>
                                                        @if($plan->individual_fee > 0)
                                                            <span class="text-xs text-slate-500">€{{ number_format($plan->individual_fee, 2) }}</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Action Button -->
                            <div class="mt-6 pt-4 border-t border-slate-100">
                                @php
                                    $packageItems = [];
                                    foreach($package->affiliationPlans as $plan) {
                                        $packageItems[] = $plan->name;
                                    }
                                    foreach($package->insurancePlans as $plan) {
                                        $packageItems[] = $plan->name;
                                    }
                                @endphp
                                
                                <x-ui.button 
                                    variant="primary" 
                                    size="default" 
                                    class="w-full"
                                    @click="
                                        selectedPackage = {{ $package->id }};
                                        selectedPackageName = {{ json_encode($package->name) }};
                                        selectedPackagePrice = {{ $package->calculatePriceFor(get_class($individual)) }};
                                        selectedPackageItems = {{ json_encode($packageItems) }};
                                        showConfirmModal = true;
                                    "
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    {{ __('subscriptions.subscribe_now') }}
                                </x-ui.button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </x-ui.card-grid>
        </div>
        @endif



 
        <!-- Confirmation Modal -->
        <!-- Confirmation Modal -->
        <div x-show="showConfirmModal"
             class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             x-cloak>
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full m-4"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('subscriptions.confirm_subscription') }}</h3>
                    <div class="mb-6">
                        <h4 class="text-md font-medium text-gray-900 mb-2" x-text="selectedPackageName"></h4>
                        <p class="text-sm text-gray-600 mb-2">
                            {{ __('subscriptions.price') }} <span class="font-medium" x-text="`{{ config('squidflex.currency_symbol', '€') }}${selectedPackagePrice}`"></span>
                        </p>
                        <div class="text-sm text-gray-600">
                            <p class="font-medium mb-1">{{ __('subscriptions.package_includes') }}</p>
                            <ul class="list-disc list-inside space-y-1">
                                <template x-for="item in selectedPackageItems" :key="item">
                                    <li x-text="item"></li>
                                </template>
                            </ul>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mb-6">
                        {{ __('subscriptions.confirm_subscription_question') }}
                    </p>
                    <form action="{{ route('individual.subscriptions.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="membership_package_id" x-bind:value="selectedPackage">
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                            {{ __('subscriptions.confirm') }}
                        </button>
                        <button @click="showConfirmModal = false" type="button" class="w-full px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-colors duration-200">
                            {{ __('subscriptions.cancel') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>