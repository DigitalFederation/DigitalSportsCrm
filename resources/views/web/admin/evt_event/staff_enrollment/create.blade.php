@section('title', __('Staff enrollment'))
<x-layout>

    <livewire:evt-events.staff-create-enrollment :event="$event"
                                                 enrollmentTypeSlug="staff"
    />

</x-layout>
