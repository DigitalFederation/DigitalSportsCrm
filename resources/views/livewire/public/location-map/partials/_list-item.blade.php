{{-- public/location-map/partials/_list-item.blade.php --}}
<div
wire:key="location-{{ $location['type'] }}-{{ $location['id'] }}"
class="p-4 hover:bg-gray-50 cursor-pointer transition-colors {{ $selectedItem && $selectedItem['id'] == $location['id'] && $selectedItem['type'] == $location['type'] ? 'bg-blue-50' : '' }}"
wire:click="showDetails('{{ $location['id'] }}', '{{ $location['type'] }}')"
>
<div class="flex items-start gap-3">
    {{-- Location Icon --}}
    <div @class([
        'w-10 h-10 rounded-full flex items-center justify-center text-white font-medium',
        'bg-blue-600' => $location['type'] === 'federation',
        'bg-emerald-600' => $location['type'] === 'entity'
    ])>
        {{ $location['type'] === 'federation' ? 'F' : 'E' }}
    </div>

    {{-- Location Info --}}
    <div class="flex-grow min-w-0">
        <h3 class="text-sm font-medium text-gray-900 truncate">{{ $location['name'] }}</h3>
        <div class="mt-1 flex items-center text-xs text-gray-500 gap-2">
            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="truncate">{{ $location['location'] }}, {{ $location['country'] }}</span>
        </div>

        {{-- Licenses (for entities) --}}
        @if($location['type'] === 'entity' && !empty($location['licenses']))
            <div class="mt-2 flex flex-wrap gap-1">
                @foreach($location['licenses'] as $license)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">
                        {{ $license }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Arrow Icon --}}
    <div class="flex-shrink-0 self-center">
        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
        </svg>
    </div>
</div>
</div>
