@section('title', __('main.entity_insurance_plans'))
<x-layout>
    <div x-data="{
        showDetailsModal: false,
        detailType: '',
        detailName: '',
        detailDescription: '',
        detailFiles: []
    }" class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('main.entity_insurance_plans') }}</h1>
            </div>
        </div>


        <!-- Current insurances section -->
        @if($currentInsurances->isNotEmpty())
            <div class="mb-8">
                
                <x-ui.card-grid columns="2" gap="lg">
                    @foreach($currentInsurances as $item)
                        @if($item instanceof \Domain\Memberships\Models\MemberSubscription)
                            {{-- Insurance subscription from membership package --}}
                            <x-ui.card variant="interactive" class="h-full">
                                <!-- Card Header with Package Name and Status -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-slate-900 leading-tight mb-2">
                                            {{ $item->membershipPackage->name }}
                                        </h3>
                                        
                                        <!-- Status Badge -->
                                        <div class="flex items-center gap-2">
                                            @if($item->status_class === 'Domain\Memberships\States\ActiveMemberSubscriptionState')
                                                <x-ui.badge variant="green" size="sm">
                                                    {{ __('main.active') }}
                                                </x-ui.badge>
                                            @elseif($item->status_class === 'Domain\Memberships\States\PendingPaymentMemberSubscriptionState')
                                                <x-ui.badge variant="yellow" size="sm">
                                                    {{ __('main.pending_payment') }}
                                                </x-ui.badge>
                                            @endif
                                            
                                            <!-- Insurance badge -->
                                            <x-ui.badge variant="blue" size="sm">
                                                {{ __('insurances.insurance') }}
                                            </x-ui.badge>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Subscription Details -->
                                <div class="space-y-3 mb-6">
                                    <!-- Insurance Plans in Package -->
                                    @if($item->membershipPackage->insurancePlans->isNotEmpty())
                                        <div class="pt-2 border-t border-slate-100">
                                            <div class="mb-2">
                                                <span class="text-sm font-medium text-slate-700">{{ __('insurances.included_insurance_plans') }}</span>
                                            </div>
                                            @foreach($item->membershipPackage->insurancePlans as $plan)
                                                <div class="bg-blue-50 px-2 py-2 rounded mb-1">
                                                    <div class="flex items-center justify-between mb-1">
                                                        <div class="flex items-center space-x-2">
                                                            <svg class="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                            </svg>
                                                            <span class="text-xs font-medium text-slate-700">{{ $plan->name }}</span>
                                                        </div>
                                                        @if($plan->entity_fee > 0)
                                                            <span class="text-xs text-slate-500">{{ money($plan->entity_fee) }}</span>
                                                        @endif
                                                    </div>
                                                    @if($plan->description)
                                                        <p class="text-xs text-slate-600 ml-5">{{ $plan->description }}</p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    <!-- Validity Period -->
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-slate-500">{{ __('common.valid_from') }}:</span>
                                        <span class="text-xs text-slate-600 font-medium">
                                            {{ $item->start_date->format('d/m/Y') }} - {{ $item->end_date->format('d/m/Y') }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Card Actions -->
                                <div class="mt-auto pt-4 border-t border-slate-100">
                                    <div class="flex gap-2">
                                        <x-ui.button 
                                            variant="outline" 
                                            size="sm" 
                                            class="flex-1"
                                            @click="
                                                detailType = 'subscription';
                                                detailName = '{{ $item->membershipPackage->name }}';
                                                detailDescription = '{{ $item->membershipPackage->description ?? __('common.no_description_available') }}';
                                                showDetailsModal = true;
                                            "
                                        >
                                            {{ __('common.details') }}
                                        </x-ui.button>
                                        
                                    </div>
                                </div>
                            </x-ui.card>
                        @else
                            {{-- Direct insurance record --}}
                            <x-ui.insurance-card 
                                :insurance="$item"
                                type="current"
                            />
                        @endif
                    @endforeach
                </x-ui.card-grid>
            </div>
        @endif

        <!-- Available Insurance Packages -->
        @if($availableInsurancePackages->isNotEmpty())
            <div>
                <h2 class="font-semibold text-slate-800 mb-6 text-xl">{{ __('main.available_insurance_plans') }}</h2>

                <x-ui.card-grid columns="3" gap="lg">
                    @foreach($availableInsurancePackages as $package)
                        <x-ui.insurance-card 
                            :package="$package"
                            type="available"
                            action-type="subscribe"
                        />
                    @endforeach
                </x-ui.card-grid>
            </div>
        @else
            <x-ui.empty-state-card 
                :title="__('main.no_insurance_packages_available')"
                :description="__('main.no_insurance_packages_description')"
            />
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
                            <h4 class="font-medium text-gray-900 mb-2">{{ __('main.related_documents') }}</h4>
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

    </div>

</x-layout>