<x-layout>
    <div class="previous-layout-classes">
        @php
            $actions = [
                [
                    'type' => 'link',
                    'class' => 'btn btn-info',
                    'url' => route('admin.evt-events.attributes.index'),
                    'text' => __('common.back'),
                ],
            ];
        @endphp

        <x-layout.page-header
            title="{{ __('events.create_an_attribute') }}"
            subtitle="{{ __('events.attributes_list') }}"
            :actions="$actions"
        >
        </x-layout.page-header>


        <form action="{{ route('admin.evt-events.attributes.store') }}" method="POST">
            @csrf

            <x-information-box
                title="{{ __('events.instructions_title') }}"
                body="{{ __('events.attribute_instructions') }}">
            </x-information-box>


            <div class="card flex flex-col md:-mr-px">
                @include('web.admin.evt_events.attributes.partials.form')
            </div>

        </form>

    </div>

</x-layout>
