@props(['discipline', 'isSelected' => false])

<span @class([
    'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium mr-1 mb-1',
    'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-700/10' => !$isSelected,
    'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-700/10' => $isSelected,
])>
    {{ $discipline }}
    @if($isSelected)
        <span class="ml-1 text-xs">(selected)</span>
    @endif
</span>
