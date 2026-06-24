<x-layout>
    <div class="previous-layout-classes">

        <div class="mb-8 flex justify-between">
            <h1 class="page-first-title">
                {{ __('Create a Discipline') }}
                @if (! empty($event))
                    {{ __('for') }} <span class="font-normal">{{ $event->name }}</span>
                @endif
            </h1>
        </div>

        <form action="{{ route('admin.evt-events.disciplines.store') }}" method="POST">
            @csrf
            <x-information-box
                :title="__('How to use')"
                :body="__('Fill in the discipline details, select the appropriate gender and enrollment type, and, if choosing Team or Relay, specify the minimum participants before saving.')" />

            <div class="card w-full flex flex-col md:flex-row md:-mr-px">
                @include('web.admin.evt_events.disciplines.partials.form')
            </div>
        </form>

    </div>

</x-layout>
