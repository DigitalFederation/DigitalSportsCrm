@section('title', __('events.technical_delegate') . ' - ' . $event->name)

<x-layout>
    <div class="previous-layout-classes">
        <livewire:evt-events.delegate-enrollments :event="$event" />
    </div>
</x-layout>
