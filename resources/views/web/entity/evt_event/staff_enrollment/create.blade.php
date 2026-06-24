@section('title', __('events.staff_enrollment'))
<x-layout>

    <livewire:evt-events.staff-create-enrollment :event="$event"
                                                 enrollmentTypeSlug="staff"
                                                 :entity="$entity" />

</x-layout>
