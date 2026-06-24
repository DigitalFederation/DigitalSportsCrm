@props([
    'columns' => 'auto', // auto, 1, 2, 3, 4, 5, 6
    'gap' => 'default', // sm, default, lg, xl
    'responsive' => true,
])

@php
    // Base classes
    $baseClasses = 'grid';
    
    // Gap classes
    $gapClasses = match($gap) {
        'sm' => 'gap-4',
        'lg' => 'gap-8',
        'xl' => 'gap-12',
        default => 'gap-6',
    };
    
    // Column classes
    if ($columns === 'auto') {
        // Responsive auto-fit grid
        $columnClasses = $responsive 
            ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4'
            : 'grid-cols-1 md:grid-cols-2 xl:grid-cols-3';
    } else {
        // Fixed columns with responsive breakpoints
        $columnClasses = match($columns) {
            1 => 'grid-cols-1',
            2 => $responsive ? 'grid-cols-1 md:grid-cols-2' : 'grid-cols-2',
            3 => $responsive ? 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3' : 'grid-cols-3',
            4 => $responsive ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4' : 'grid-cols-4',
            5 => $responsive ? 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5' : 'grid-cols-5',
            6 => $responsive ? 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6' : 'grid-cols-6',
            default => 'grid-cols-1 md:grid-cols-2 xl:grid-cols-3',
        };
    }
    
    // Combine classes
    $classes = trim(implode(' ', [
        $baseClasses,
        $columnClasses,
        $gapClasses,
    ]));
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>