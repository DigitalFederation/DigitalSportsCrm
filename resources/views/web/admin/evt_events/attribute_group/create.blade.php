<x-layout>
    <div class="previous-layout-classes">

        <div class="mb-8 flex justify-between">
            <h1 class="page-first-title">
                {{ __('events.create_attribute_group') }}
            </h1>
        </div>

        <form action="{{ route('admin.evt-events.attribute-group.store') }}" method="POST">
            @csrf
            @include('web.admin.evt_events.attribute_group.form')
        </form>

    </div>

</x-layout>
