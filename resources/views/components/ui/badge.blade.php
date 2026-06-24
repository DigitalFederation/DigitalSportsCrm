@props([
    'variant' => 'default', // default, blue, green, yellow, red, gray, indigo, purple
    'size' => 'default', // sm, default, lg
    'rounded' => true,
    'removable' => false,
])

@php
    // Base classes
    $baseClasses = 'inline-flex items-center font-medium';
    
    // Size classes  
    $sizeClasses = match($size) {
        'sm' => 'px-2 py-0.5 text-xs',
        'lg' => 'px-3 py-1 text-sm',
        default => 'px-2.5 py-0.5 text-xs',
    };
    
    // Rounded classes
    $roundedClasses = $rounded ? 'rounded-full' : 'rounded-md';
    
    // Variant classes
    $variantClasses = match($variant) {
        'blue' => 'bg-blue-100 text-blue-800 border border-blue-200',
        'green' => 'bg-green-100 text-green-800 border border-green-200',
        'yellow' => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
        'red' => 'bg-red-100 text-red-800 border border-red-200',
        'gray' => 'bg-gray-100 text-gray-800 border border-gray-200',
        'indigo' => 'bg-indigo-100 text-indigo-800 border border-indigo-200',
        'purple' => 'bg-purple-100 text-purple-800 border border-purple-200',
        default => 'bg-slate-100 text-slate-800 border border-slate-200',
    };
    
    // Combine classes
    $classes = trim(implode(' ', [
        $baseClasses,
        $sizeClasses,
        $roundedClasses,
        $variantClasses,
    ]));
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
    
    @if($removable)
        <button 
            type="button" 
            class="ml-1 -mr-0.5 h-4 w-4 rounded-full inline-flex items-center justify-center hover:bg-black hover:bg-opacity-10 focus:outline-none focus:bg-black focus:bg-opacity-10"
        >
            <x-svg.x-circle class="h-3 w-3" />
        </button>
    @endif
</span>