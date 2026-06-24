@props([
    'package',
    'selected' => false,
    'disabled' => false,
    'showPrice' => true,
    'showDescription' => false,
])

@php
    $isSelected = $selected && !$disabled;
    $isDisabled = $disabled;
    
    $cardClasses = 'transition-all duration-200 ease-in-out cursor-pointer';
    if ($isSelected) {
        $cardClasses .= ' ring-2 ring-slate-400 border-slate-400 bg-slate-50';
    }
    if ($isDisabled) {
        $cardClasses .= ' opacity-50 cursor-not-allowed';
    } else {
        $cardClasses .= ' hover:shadow-lg hover:border-slate-300';
    }
@endphp

<div 
    class="bg-white border border-slate-200 shadow-sm rounded-lg p-4 {{ $cardClasses }}"
    {{ $attributes }}
>
    <div class="flex items-center justify-between">
        <!-- Package Info -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3">
                <!-- Selection Radio -->
                <div class="flex-shrink-0">
                    @if($isSelected)
                        <div class="w-5 h-5 bg-slate-600 rounded-full flex items-center justify-center">
                            <x-svg.check class="w-3 h-3 text-white" />
                        </div>
                    @else
                        <div class="w-5 h-5 border-2 border-slate-300 rounded-full {{ $isDisabled ? '' : 'group-hover:border-slate-400' }}"></div>
                    @endif
                </div>
                
                <!-- Package Details -->
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-medium text-slate-900 truncate">
                        {{ $package->name }}
                    </h4>
                    
                    @if($showDescription && $package->description)
                        <p class="text-xs text-slate-600 mt-1 line-clamp-2">
                            {{ Str::limit($package->description, 100) }}
                        </p>
                    @endif
                    
                    <!-- Package Type -->
                    <div class="flex items-center gap-2 mt-1">
                        <x-ui.badge 
                            variant="gray"
                            size="sm"
                        >
                            {{ ucfirst($package->target_type->value) }}
                        </x-ui.badge>
                        
                        @if(!$package->is_active)
                            <x-ui.badge variant="red" size="sm">
                                {{ __('Inactive') }}
                            </x-ui.badge>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Price Display -->
        @if($showPrice)
            <div class="flex-shrink-0 ml-4">
                @php
                    $primaryPrice = null;
                    if ($package->affiliationPlans->isNotEmpty()) {
                        $primaryPrice = $package->affiliationPlans->first()->price;
                    } elseif ($package->insurancePlans->isNotEmpty()) {
                        $primaryPrice = $package->insurancePlans->first()->price;
                    } elseif (isset($package->price)) {
                        $primaryPrice = $package->price;
                    }
                @endphp
                
                @if($primaryPrice)
                    <div class="text-right">
                        <div class="text-sm font-semibold text-slate-900">
                            €{{ number_format($primaryPrice, 2) }}
                        </div>
                        <div class="text-xs text-slate-500">
                            {{ __('from') }}
                        </div>
                    </div>
                @else
                    <div class="text-xs text-slate-500">
                        {{ __('Custom pricing') }}
                    </div>
                @endif
            </div>
        @endif
    </div>
    
    <!-- Additional Info -->
    @if($package->affiliationPlans->isNotEmpty() || $package->insurancePlans->isNotEmpty())
        <div class="mt-3 pt-3 border-t border-slate-100">
            <div class="text-xs text-slate-600">
                @if($package->affiliationPlans->isNotEmpty())
                    {{ trans_choice('{1} :count affiliation plan|[2,*] :count affiliation plans', $package->affiliationPlans->count(), ['count' => $package->affiliationPlans->count()]) }}
                @endif
                
                @if($package->affiliationPlans->isNotEmpty() && $package->insurancePlans->isNotEmpty())
                    {{ __(' • ') }}
                @endif
                
                @if($package->insurancePlans->isNotEmpty())
                    {{ trans_choice('{1} :count insurance plan|[2,*] :count insurance plans', $package->insurancePlans->count(), ['count' => $package->insurancePlans->count()]) }}
                @endif
            </div>
        </div>
    @endif
</div>