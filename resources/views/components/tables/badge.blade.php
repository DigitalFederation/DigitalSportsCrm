@php
    $colorClasses = match($color) {
        'green', 'active-state' => 'border-green-500 text-green-700 bg-green-50',
        'red', 'canceled-state' => 'border-red-500 text-red-700 bg-red-50',
        'yellow', 'pending-state', 'pending' => 'border-yellow-500 text-yellow-700 bg-yellow-50',
        'blue' => 'border-blue-500 text-blue-700 bg-blue-50',
        'gray' => 'border-gray-500 text-gray-700 bg-gray-50',
        default => 'border-gray-500 text-gray-700 bg-gray-50'
    };
@endphp

<span class="inline-block rounded border px-2 py-1 text-xs font-semibold w-fit {{ $colorClasses }}">
    {{ $status }}
</span>
