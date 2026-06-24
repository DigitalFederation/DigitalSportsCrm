@props([
    'package',
    'showActions' => true,
    'showPrice' => true,
    'showDescription' => true,
    'showCoverage' => true,
    'actionType' => 'subscribe', // subscribe, manage, view
    'selected' => false,
    'selectable' => false,
    'compact' => false,
    'currentInsurance' => null,
])

@php
    $cardVariant = $selected ? 'interactive' : 'default';
    $cardSize = $compact ? 'compact' : 'default';
    
    // Check if user already has this insurance
    $hasCurrentInsurance = $currentInsurance && $currentInsurance->insurance_plan_id === $package->id;
    
    // Determine package status
    $isActive = $package->is_active ?? true;
    $isExpired = $currentInsurance && $currentInsurance->end_date && $currentInsurance->end_date->isPast();
@endphp

<x-ui.card 
    :variant="$cardVariant" 
    :size="$cardSize"
    :class="[
        'flex flex-col h-full',
        $selected ? 'ring-2 ring-blue-500 border-blue-300' : '',
        $selectable ? 'cursor-pointer' : '',
        $hasCurrentInsurance && !$isExpired ? 'ring-2 ring-green-500 border-green-300' : ''
    ]"
    :clickable="$selectable"
>
    <!-- Card Header -->
    <div class="flex-shrink-0">
        <!-- Package Name and Status -->
        <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-slate-900 leading-tight">
                    {{ $package->name }}
                </h3>
                
                <!-- Status Badges -->
                <div class="flex items-center gap-2 mt-2 flex-wrap">
                    @if($hasCurrentInsurance && !$isExpired)
                        <x-ui.badge variant="green" size="sm">
                            {{ __('Current') }}
                        </x-ui.badge>
                    @elseif($hasCurrentInsurance && $isExpired)
                        <x-ui.badge variant="yellow" size="sm">
                            {{ __('Expired') }}
                        </x-ui.badge>
                    @endif
                    
                    @if(!$isActive)
                        <x-ui.badge variant="red" size="sm">
                            {{ __('Inactive') }}
                        </x-ui.badge>
                    @endif
                    
                    @if($package->is_recommended ?? false)
                        <x-ui.badge variant="blue" size="sm">
                            {{ __('Recommended') }}
                        </x-ui.badge>
                    @endif
                </div>
            </div>
            
            <!-- Selection Indicator -->
            @if($selectable)
                <div class="flex-shrink-0 ml-3">
                    @if($selected)
                        <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center">
                            <x-svg.check class="w-4 h-4 text-white" />
                        </div>
                    @elseif($hasCurrentInsurance && !$isExpired)
                        <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                            <x-svg.check class="w-4 h-4 text-white" />
                        </div>
                    @else
                        <div class="w-6 h-6 border-2 border-slate-300 rounded-full"></div>
                    @endif
                </div>
            @endif
        </div>
        
        <!-- Price Display -->
        @if($showPrice && isset($package->price))
            <div class="mb-4">
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-slate-900">
                        €{{ number_format($package->price, 2) }}
                    </span>
                    <span class="text-sm text-slate-500">{{ __('per year') }}</span>
                </div>
                
                @if($package->monthly_price ?? false)
                    <div class="text-sm text-slate-500">
                        {{ __('or €:price per month', ['price' => number_format($package->monthly_price, 2)]) }}
                    </div>
                @endif
            </div>
        @endif
    </div>
    
    <!-- Card Body -->
    <div class="flex-1 space-y-4">
        <!-- Description -->
        @if($showDescription && ($package->description ?? false))
            <div class="text-sm text-slate-600 leading-relaxed">
                {{ Str::limit($package->description, $compact ? 80 : 150) }}
            </div>
        @endif
        
        <!-- Coverage Details -->
        @if($showCoverage && !$compact)
            <div class="space-y-3">
                <!-- Coverage Amount -->
                @if($package->coverage_amount ?? false)
                    <div class="flex items-center gap-2">
                        <x-svg.check class="w-4 h-4 text-green-500 flex-shrink-0" />
                        <span class="text-sm text-slate-600">
                            {{ __('Coverage up to €:amount', ['amount' => number_format($package->coverage_amount)]) }}
                        </span>
                    </div>
                @endif
                
                <!-- Geographic Coverage -->
                @if($package->geographic_coverage ?? false)
                    <div class="flex items-center gap-2">
                        <x-svg.check class="w-4 h-4 text-green-500 flex-shrink-0" />
                        <span class="text-sm text-slate-600">
                            {{ __('Coverage: :coverage', ['coverage' => $package->geographic_coverage]) }}
                        </span>
                    </div>
                @endif
                
                <!-- Activities Covered -->
                @if(isset($package->activities_covered) && is_array($package->activities_covered))
                    <div>
                        <div class="text-sm font-medium text-slate-900 mb-1">
                            {{ __('Activities Covered') }}
                        </div>
                        <div class="space-y-1">
                            @foreach(array_slice($package->activities_covered, 0, 3) as $activity)
                                <div class="flex items-center gap-2">
                                    <x-svg.check class="w-4 h-4 text-green-500 flex-shrink-0" />
                                    <span class="text-sm text-slate-600">{{ $activity }}</span>
                                </div>
                            @endforeach
                            
                            @if(count($package->activities_covered) > 3)
                                <div class="text-xs text-slate-500 pl-6">
                                    {{ __('+ :count more activities', ['count' => count($package->activities_covered) - 3]) }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                
                <!-- Current Insurance Details -->
                @if($hasCurrentInsurance && $currentInsurance)
                    <div class="bg-slate-50 rounded-md p-3 border-l-4 border-green-400">
                        <div class="text-sm font-medium text-slate-900 mb-1">
                            {{ __('Current Coverage') }}
                        </div>
                        <div class="text-xs text-slate-600 space-y-1">
                            <div>{{ __('Valid until: :date', ['date' => $currentInsurance->end_date?->format('M j, Y')]) }}</div>
                            @if($currentInsurance->policy_number)
                                <div>{{ __('Policy: :number', ['number' => $currentInsurance->policy_number]) }}</div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
    
    <!-- Card Footer -->
    @if($showActions && $isActive)
        <div class="flex-shrink-0 pt-4 mt-auto border-t border-slate-100">
            <div class="flex gap-2">
                @if($actionType === 'subscribe')
                    @if($hasCurrentInsurance && !$isExpired)
                        <x-ui.button 
                            variant="outline" 
                            size="sm" 
                            class="flex-1"
                            onclick="renewInsurance('{{ $package->id }}')"
                        >
                            {{ __('Renew') }}
                        </x-ui.button>
                        
                        <x-ui.button 
                            variant="outline" 
                            size="sm"
                            onclick="viewInsuranceDetails('{{ $package->id }}')"
                        >
                            {{ __('Details') }}
                        </x-ui.button>
                    @else
                        <x-ui.button 
                            variant="primary" 
                            size="sm" 
                            class="flex-1"
                            onclick="subscribeToInsurance('{{ $package->id }}')"
                        >
                            {{ $hasCurrentInsurance && $isExpired ? __('Reactivate') : __('Subscribe') }}
                        </x-ui.button>
                        
                        <x-ui.button 
                            variant="outline" 
                            size="sm"
                            onclick="viewInsuranceDetails('{{ $package->id }}')"
                        >
                            {{ __('Details') }}
                        </x-ui.button>
                    @endif
                    
                @elseif($actionType === 'manage')
                    <x-ui.button 
                        variant="outline" 
                        size="sm" 
                        class="flex-1"
                        href="{{ route('admin.insurance-plans.edit', $package) }}"
                    >
                        {{ __('Manage') }}
                    </x-ui.button>
                    
                @elseif($actionType === 'view')
                    <x-ui.button 
                        variant="outline" 
                        size="sm" 
                        class="flex-1"
                        onclick="viewInsuranceDetails('{{ $package->id }}')"
                    >
                        {{ __('View Details') }}
                    </x-ui.button>
                @endif
            </div>
        </div>
    @elseif(!$isActive)
        <div class="flex-shrink-0 pt-4 mt-auto border-t border-slate-100">
            <div class="text-sm text-slate-500 text-center">
                {{ __('Currently unavailable') }}
            </div>
        </div>
    @endif
</x-ui.card>