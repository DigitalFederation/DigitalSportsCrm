@props([
    'variant' => 'default', // default, elevated, outlined, interactive
    'size' => 'default', // compact, default, spacious
    'padding' => null, // override default padding
    'rounded' => 'default', // none, sm, default, lg, xl
    'shadow' => null, // override default shadow
    'hoverable' => false,
    'clickable' => false,
    'href' => null,
    'disabled' => false,
])

@php
    // Base classes for all cards
    $baseClasses = 'bg-white transition-all duration-200 ease-in-out';
    
    // Variant-specific classes
    $variantClasses = match($variant) {
        'elevated' => 'shadow-lg border-0',
        'outlined' => 'shadow-sm border border-slate-200',
        'interactive' => 'shadow-md border border-slate-200 hover:shadow-lg hover:border-slate-300',
        default => 'shadow-md border border-slate-200',
    };
    
    // Size-specific padding
    $sizeClasses = match($size) {
        'compact' => 'p-4',
        'spacious' => 'p-8',
        default => 'p-6',
    };
    
    // Override padding if specified
    if ($padding) {
        $sizeClasses = $padding;
    }
    
    // Rounded corners
    $roundedClasses = match($rounded) {
        'none' => '',
        'sm' => 'rounded-sm',
        'lg' => 'rounded-lg',
        'xl' => 'rounded-xl',
        default => 'rounded-lg',
    };
    
    // Shadow override
    if ($shadow) {
        $variantClasses = preg_replace('/shadow-\w+/', $shadow, $variantClasses);
    }
    
    // Hover effects
    $hoverClasses = '';
    if ($hoverable && !$clickable) {
        $hoverClasses = 'hover:shadow-lg hover:scale-[1.02]';
    }
    
    // Clickable states
    $clickableClasses = '';
    if ($clickable || $href) {
        $clickableClasses = 'cursor-pointer hover:shadow-lg hover:scale-[1.02] active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2';
    }
    
    // Disabled states
    $disabledClasses = '';
    if ($disabled) {
        $disabledClasses = 'opacity-60 cursor-not-allowed pointer-events-none';
    }
    
    // Combine all classes
    $classes = trim(implode(' ', [
        $baseClasses,
        $variantClasses,
        $sizeClasses,
        $roundedClasses,
        $hoverClasses,
        $clickableClasses,
        $disabledClasses,
    ]));
    
    // Handle additional classes from attributes
    $additionalClasses = $attributes->get('class', '');
    if (is_array($additionalClasses)) {
        $additionalClasses = implode(' ', array_filter($additionalClasses));
    }
    if ($additionalClasses) {
        $classes .= ' ' . $additionalClasses;
    }
    
    // Remove class from attributes to avoid duplication
    $attributes = $attributes->except('class');
@endphp

@if($href && !$disabled)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <div {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </div>
@endif