@props([
    'variant' => 'primary', // primary, secondary, outline, ghost, danger
    'size' => 'default', // sm, default, lg
    'type' => 'button',
    'disabled' => false,
    'loading' => false,
    'href' => null,
    'target' => null,
])

@php
    // Base classes - includes 'btn' class to exclude from global link styles
    $baseClasses = 'btn inline-flex items-center justify-center gap-2 font-medium tracking-wide shadow-sm transition-colors duration-150 focus:outline-none focus:ring focus:ring-primary-light/30 disabled:opacity-50 disabled:cursor-not-allowed';

    // Size classes
    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-sm rounded-md',
        'lg' => 'px-6 py-3 text-base rounded-lg',
        default => 'px-5 py-2.5 text-sm rounded-lg',
    };

    // Variant classes - using app's primary color palette
    $variantClasses = match($variant) {
        'primary' => 'bg-primary border border-transparent text-white hover:bg-primary-light focus:border-primary active:bg-primary-light',
        'secondary' => 'bg-white border border-primary-light text-primary hover:bg-secondary-light focus:border-primary active:bg-secondary',
        'outline' => 'border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 focus:border-slate-400 active:bg-slate-100',
        'ghost' => 'text-slate-600 hover:bg-slate-100 focus:ring-slate-500 active:bg-slate-200 shadow-none',
        'danger' => 'bg-red-600 border border-transparent text-white hover:bg-red-700 focus:ring-red-500 active:bg-red-800',
    };

    // Loading state
    $loadingClasses = $loading ? 'opacity-75 cursor-not-allowed' : '';

    // Full width on mobile for action bars
    $responsiveClasses = 'w-full sm:w-auto';

    // Combine classes
    $classes = trim(implode(' ', [
        $baseClasses,
        $sizeClasses,
        $variantClasses,
        $loadingClasses,
        $responsiveClasses,
    ]));
@endphp

@if($href && !$disabled)
    <a
        href="{{ $href }}"
        @if($target) target="{{ $target }}" @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if($loading)
            <x-heroicon-m-arrow-path class="w-4 h-4 animate-spin" />
        @endif
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        @if($disabled || $loading) disabled @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if($loading)
            <x-heroicon-m-arrow-path class="w-4 h-4 animate-spin" />
        @endif
        {{ $slot }}
    </button>
@endif