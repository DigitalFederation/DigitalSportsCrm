@props([
    'title' => null,
    'description' => null,
    'icon' => null,
    'actionText' => null,
    'actionHref' => null,
    'actionClick' => null,
])

<x-ui.card variant="outlined" class="text-center py-12">
    <!-- Icon -->
    @if($icon)
        <div class="mx-auto w-16 h-16 text-slate-400 mb-4">
            {{ $icon }}
        </div>
    @else
        <div class="mx-auto w-16 h-16 text-slate-400 mb-4">
            <x-svg.squares-plus class="w-full h-full" />
        </div>
    @endif
    
    <!-- Title -->
    @if($title)
        <h3 class="text-lg font-medium text-slate-900 mb-2">
            {{ $title }}
        </h3>
    @endif
    
    <!-- Description -->
    @if($description)
        <p class="text-slate-600 mb-6 max-w-sm mx-auto">
            {{ $description }}
        </p>
    @endif
    
    <!-- Action Button -->
    @if($actionText && ($actionHref || $actionClick))
        <x-ui.button 
            variant="primary"
            :href="$actionHref"
            :onclick="$actionClick"
        >
            {{ $actionText }}
        </x-ui.button>
    @endif
    
    <!-- Custom Slot Content -->
    @if($slot->isNotEmpty())
        <div class="mt-6">
            {{ $slot }}
        </div>
    @endif
</x-ui.card>