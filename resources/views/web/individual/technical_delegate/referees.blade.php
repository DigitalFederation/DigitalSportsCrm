@section('title', __('events.chief_judge') . ' - ' . $event->name)

<x-layout>
    <div class="previous-layout-classes">
        <livewire:evt-events.judge-enrollments :event="$event" />
    </div>
</x-layout>
