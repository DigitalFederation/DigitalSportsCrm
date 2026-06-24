@section('title', __('events.create_enrollment'))
<x-layout>
    <div class="previous-layout-classes">
        <x-layout.page-header
            :title="__('events.create_enrollment')"
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
                    'url' => route('admin.evt-events.events.staff-enrollment.index', $event->id),
                    'text' => __('events.staff_members'),
                ],
            ]"
        />

        <div class="bg-white shadow-md rounded-lg p-6">
            <livewire:evt-events.staff-create-enrollment :event="$event" :discipline="$discipline ?? null" />
        </div>
    </div>
</x-layout>
