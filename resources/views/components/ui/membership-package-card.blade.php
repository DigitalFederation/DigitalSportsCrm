@props([
    'package',
    'showActions' => true,
    'showPrice' => true,
    'showDescription' => true,
    'showFeatures' => true,
    'actionType' => 'subscribe', // subscribe, manage, view
    'selected' => false,
    'selectable' => false,
    'compact' => false,
    'userType' => 'entity', // entity, individual
])

@php
    $cardVariant = $selected ? 'interactive' : 'default';
    $cardSize = $compact ? 'compact' : 'default';
    
    // Determine if package has pricing
    $hasPrice = $package->affiliationPlans->isNotEmpty() || $package->insurancePlans->isNotEmpty();
    
    // Get the primary price (first affiliation plan or insurance plan)
    $primaryPrice = null;
    if ($hasPrice) {
        $primaryPrice = $package->affiliationPlans->first()?->price ?? $package->insurancePlans->first()?->price;
    }
    
    // Get distribution methods for display
    $distributionMethods = is_array($package->distribution_methods) ? $package->distribution_methods : [];
    $isDirectAvailable = in_array('direct', $distributionMethods);
    $isEntityManaged = in_array('entity_managed', $distributionMethods);
    
    // Determine availability for current user type
    $isAvailable = true;
    if ($userType === 'individual' && !$isDirectAvailable) {
        $isAvailable = false;
    }
    
    // Build card classes
    $cardClasses = 'flex flex-col h-full border border-slate-200 hover:border-slate-300 hover:shadow-md transition-all duration-200';
    if ($selected) {
        $cardClasses .= ' ring-2 ring-slate-400 border-slate-400';
    }
    if ($selectable) {
        $cardClasses .= ' cursor-pointer';
    }
    if (!$isAvailable) {
        $cardClasses .= ' opacity-75';
    }
@endphp

<x-ui.card 
    variant="outlined" 
    :size="$cardSize"
    class="{{ $cardClasses }}"
    :clickable="$selectable"
>
    <!-- Card Header -->
    <div class="flex-shrink-0">
        <!-- Package Name and Status -->
        <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
                <h3 class="text-xl font-semibold text-slate-900 leading-tight mb-2">
                    {{ $package->name }}
                </h3>
                
                <!-- Badges Row -->
                <div class="flex items-center gap-2 flex-wrap">
                    <x-ui.badge 
                        variant="gray"
                        size="sm"
                    >
                        {{ ucfirst($package->target_type->value) }}
                    </x-ui.badge>
                    
                    @if(!$isAvailable)
                        <x-ui.badge variant="yellow" size="sm">
                            {{ __('Entity Only') }}
                        </x-ui.badge>
                    @endif
                    
                    @if(!$package->is_active)
                        <x-ui.badge variant="red" size="sm">
                            {{ __('Inactive') }}
                        </x-ui.badge>
                    @endif
                </div>
            </div>
            
            <!-- Selection Indicator -->
            @if($selectable)
                <div class="flex-shrink-0 ml-3">
                    @if($selected)
                        <div class="w-6 h-6 bg-slate-600 rounded-full flex items-center justify-center">
                            <x-svg.check class="w-4 h-4 text-white" />
                        </div>
                    @else
                        <div class="w-6 h-6 border-2 border-slate-300 rounded-full"></div>
                    @endif
                </div>
            @endif
        </div>
        
        <!-- Price Display -->
        @if($showPrice && $hasPrice && $primaryPrice)
            <div class="mb-4 pb-4 border-b border-slate-100">
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-slate-900">
                        €{{ number_format($primaryPrice, 2) }}
                    </span>
                    <span class="text-sm text-slate-500">{{ __('starting from') }}</span>
                </div>
            </div>
        @endif
    </div>
    
    <!-- Card Body -->
    <div class="flex-1 space-y-4">
        <!-- Description -->
        @if($showDescription && $package->description)
            <div class="text-sm text-slate-600 leading-relaxed">
                {{ Str::limit($package->description, $compact ? 80 : 150) }}
            </div>
        @endif
        
        <!-- Features/Plans -->
        @if($showFeatures && !$compact)
            <div class="space-y-4">
                <!-- Plan Summary -->
                <div class="bg-slate-50 rounded-lg p-3">
                    <div class="text-sm text-slate-700">
                        <strong>{{ __('This package includes:') }}</strong>
                    </div>
                    
                    <div class="mt-2 space-y-1">
                        @if($package->affiliationPlans->isNotEmpty())
                            <div class="text-xs text-slate-600">
                                • {{ $package->affiliationPlans->count() }} {{ __('affiliation plan(s)') }}
                            </div>
                        @endif
                        
                        @if($package->insurancePlans->isNotEmpty())
                            <div class="text-xs text-slate-600">
                                • {{ $package->insurancePlans->count() }} {{ __('insurance plan(s)') }}
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Detailed Plans (Collapsible) -->
                @if($package->affiliationPlans->isNotEmpty() || $package->insurancePlans->isNotEmpty())
                    <details class="group">
                        <summary class="text-sm font-medium text-slate-700 cursor-pointer hover:text-slate-900 transition-colors">
                            {{ __('View included plans') }}
                        </summary>
                        
                        <div class="mt-3 space-y-3 pl-4 border-l-2 border-slate-200">
                            <!-- Affiliation Plans -->
                            @if($package->affiliationPlans->isNotEmpty())
                                <div>
                                    <h5 class="text-xs font-medium text-slate-900 mb-1 uppercase tracking-wide">
                                        {{ __('Affiliations') }}
                                    </h5>
                                    <div class="space-y-1">
                                        @foreach($package->affiliationPlans->take(3) as $plan)
                                            <div class="flex items-center justify-between text-xs text-slate-600">
                                                <span>{{ $plan->name }}</span>
                                                <span class="font-medium">€{{ number_format($plan->price, 2) }}</span>
                                            </div>
                                        @endforeach
                                        
                                        @if($package->affiliationPlans->count() > 3)
                                            <div class="text-xs text-slate-500">
                                                {{ __('+ :count more', ['count' => $package->affiliationPlans->count() - 3]) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Insurance Plans -->
                            @if($package->insurancePlans->isNotEmpty())
                                <div>
                                    <h5 class="text-xs font-medium text-slate-900 mb-1 uppercase tracking-wide">
                                        {{ __('Insurance') }}
                                    </h5>
                                    <div class="space-y-1">
                                        @foreach($package->insurancePlans->take(3) as $plan)
                                            <div class="flex items-center justify-between text-xs text-slate-600">
                                                <span>{{ $plan->name }}</span>
                                                <span class="font-medium">€{{ number_format($plan->price, 2) }}</span>
                                            </div>
                                        @endforeach
                                        
                                        @if($package->insurancePlans->count() > 3)
                                            <div class="text-xs text-slate-500">
                                                {{ __('+ :count more', ['count' => $package->insurancePlans->count() - 3]) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </details>
                @endif
            </div>
        @endif
    </div>
    
    <!-- Custom Footer Slot -->
    @if(isset($footer))
        {{ $footer }}
        
    <!-- Default Card Footer -->
    @elseif($showActions && $isAvailable && $package->is_active)
        <div class="flex-shrink-0 pt-4 mt-auto border-t border-slate-100">
            <div class="flex gap-2">
                @if($actionType === 'subscribe')
                    <x-ui.button 
                        variant="secondary" 
                        size="sm" 
                        class="flex-1"
                        onclick="openSubscriptionModal('{{ $package->id }}')"
                    >
                        {{ __('Subscribe') }}
                    </x-ui.button>
                    
                    <x-ui.button 
                        variant="ghost" 
                        size="sm"
                        onclick="viewPackageDetails('{{ $package->id }}')"
                    >
                        {{ __('Details') }}
                    </x-ui.button>
                    
                @elseif($actionType === 'manage')
                    <x-ui.button 
                        variant="outline" 
                        size="sm" 
                        class="flex-1"
                        href="{{ route('admin.membership-packages.edit', $package) }}"
                    >
                        {{ __('Manage') }}
                    </x-ui.button>
                    
                @elseif($actionType === 'view')
                    <x-ui.button 
                        variant="outline" 
                        size="sm" 
                        class="flex-1"
                        onclick="viewPackageDetails('{{ $package->id }}')"
                    >
                        {{ __('View Details') }}
                    </x-ui.button>
                @endif
            </div>
        </div>
    @elseif(!$isAvailable)
        <div class="flex-shrink-0 pt-4 mt-auto border-t border-slate-100">
            <div class="text-sm text-slate-500 text-center">
                {{ __('Available through your entity only') }}
            </div>
        </div>
    @endif
</x-ui.card>