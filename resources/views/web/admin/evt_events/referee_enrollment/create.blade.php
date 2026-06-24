@section('title', __('events.enroll_referee'))
<x-layout>
    <div class="previous-layout-classes">
        <x-layout.page-header
            :title="__('events.enroll_referee')"
            :subtitle="$event->name"
            :actions="[
                [
                    'type' => 'link',
                    'class' => 'btn btn-info',
                    'url' => route('admin.evt-events.events.show', $event->id),
                    'text' => __('events.back_to_event'),
                ],
                [
                    'type' => 'link',
                    'class' => 'btn btn-info',
                    'url' => route('admin.evt-events.events.referee-enrollment.index', $event->id),
                    'text' => __('events.technical_officials'),
                ],
            ]"
        />

        <div class="bg-white shadow-md rounded-lg p-6">
            <livewire:evt-events.referee-create-enrollment :event="$event" :discipline="$discipline ?? null" />
        </div>
    </div>
</x-layout>
