@section('title', __('Athletes registration list'))
<x-layout-full>
    <div class="previous-layout-classes relative">
        <div class="page-wrapper">
            <livewire:evt-events.enrolled-athletes :event="$event"></livewire:evt-events.enrolled-athletes>
        </div>
    </div>
</x-layout-full>
