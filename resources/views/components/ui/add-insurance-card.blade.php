@props([
    'href' => null,
    'title' => null,
    'description' => null,
    'context' => null, // individual, entity - will be auto-detected if null
])

@php
    // Auto-detect context if not provided
    if (!$context) {
        $context = Request::is('entity/*') ? 'entity' : 'individual';
    }
    
    $title = $title ?? __('main.add_insurance');
    $description = $description ?? __('main.explore_and_add_more_insurance_plans');
    $href = $href ?? route($context . '.subscriptions.index');
@endphp

<x-ui.card 
    variant="outlined" 
    class="h-full hover:border-blue-300 hover:shadow-lg transition-all duration-300 group"
    :href="$href"
>
    <div class="flex flex-col items-center justify-center text-center py-8">
        <!-- Icon -->
        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4 group-hover:bg-blue-200 transition-colors duration-300">
            <x-svg.plus class="w-8 h-8 text-blue-600" />
        </div>
        
        <!-- Title -->
        <h3 class="text-lg font-semibold text-slate-900 mb-2 group-hover:text-blue-600 transition-colors duration-300">
            {{ $title }}
        </h3>
        
        <!-- Description -->
        <p class="text-sm text-slate-600 group-hover:text-slate-800 transition-colors duration-300 max-w-sm">
            {{ $description }}
        </p>
    </div>
</x-ui.card>