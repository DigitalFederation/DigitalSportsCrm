@section('title', __('Coach enrollment'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-4 flex justify-between">
            <h1 class="page-first-title"> {{ __('Coach enrollment') }} </h1>
        </div>

        <x-information-box
            :title="__('Instructions')"
            :body="__('Choose from the list the coaches you want to enroll for this event. If a coach is not listed, check their role.')"></x-information-box>

        <livewire:federation-create-coach-enrollment :event="$event" />
        
    </div>
</x-layout>
