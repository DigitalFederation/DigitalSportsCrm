@props([
    'context' => null, // 'individual' or 'entity' - auto-detected if null
])

@php
    // Auto-detect context if not provided
    if (!$context) {
        $context = Request::is('entity/*') ? 'entity' : 'individual';
    }
    
    $title = $context === 'entity' 
        ? __('main.no_active_insurance_policies') 
        : __('main.no_active_insurance_policies');
        
    $actionText = __('main.explore_insurance_packages');
    $actionRoute = route($context . '.subscriptions.index');
@endphp

<div class="bg-slate-50 rounded-xl border-2 border-dashed border-slate-200 p-12">
    <div class="text-center">
        <!-- Icon -->
        <div class="mx-auto w-16 h-16 text-slate-400 mb-6">
            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
        </div>
        
        <!-- Title -->
        <h3 class="text-lg font-medium text-slate-900 mb-3">
            {{ $title }}
        </h3>


    </div>
</div>