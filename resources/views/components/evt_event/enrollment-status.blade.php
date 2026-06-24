@props(['status'])

@php
    $statusClasses = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'registered' => 'bg-blue-100 text-blue-800',
        'paid' => 'bg-green-100 text-green-800',
        'completed' => 'bg-purple-100 text-purple-800',
        'canceled' => 'bg-red-100 text-red-800',
    ];

    $statusLabels = [
        'pending' => __('Pending'),
        'registered' => __('Registered'),
        'paid' => __('Paid'),
        'completed' => __('Completed'),
        'canceled' => __('Canceled'),
    ];

    $statusClass = strtolower($status);
    $colorClasses = $statusClasses[$statusClass] ?? 'bg-gray-100 text-gray-800';
    $label = $statusLabels[$statusClass] ?? $status;
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClasses }}">
    {{ $label }}
</span>
