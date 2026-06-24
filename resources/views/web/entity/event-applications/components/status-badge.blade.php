@php
    $stateClass = $application->state;
    $stateName = $stateClass->name();

    $badgeColors = [
        'draft' => 'bg-gray-100 text-gray-800',
        'submitted' => 'bg-blue-100 text-blue-800',
        'in_validation' => 'bg-yellow-100 text-yellow-800',
        'returned_for_correction' => 'bg-orange-100 text-orange-800',
        'approved' => 'bg-green-100 text-green-800',
        'rejected' => 'bg-red-100 text-red-800',
        'published' => 'bg-purple-100 text-purple-800',
    ];

    $colorClass = $badgeColors[$stateName] ?? 'bg-gray-100 text-gray-800';
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
    {{ __('event_applications.states.' . $stateName) }}
</span>
