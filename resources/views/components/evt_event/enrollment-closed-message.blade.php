@props(['event'])

@php
    $enrollmentMessage = null;
    $isActive = $event->stateName() === 'Active';
    $now = now();
    $start = $event->start_registration;
    $end = $event->end_registration;

    if (!$isActive) {
        $enrollmentMessage = __('Registration is not open because the event is not active (Status: :stateName).', ['stateName' => $event->stateName()]);
    } elseif ($start && $now->lt($start)) {
        $enrollmentMessage = __('Registration will open on :date.', ['date' => $start->format('Y-m-d')]);
    } elseif ($end && $now->gt($end)) {
        $enrollmentMessage = __('Registration closed on :date.', ['date' => $end->format('Y-m-d')]);
    } elseif (!$start && !$end) {
        $enrollmentMessage = __('Registration is not available for this event. Please contact the organizer.');
    }
@endphp

@if($enrollmentMessage)
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
        <p class="font-bold">{{ __('Notice') }}</p>
        <p>{{ $enrollmentMessage }}</p>
    </div>
@endif
