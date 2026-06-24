<x-layout>
    <div class="previous-layout-classes">

        <div class="mb-8 flex justify-between">
            <h1 class="page-first-title">
                {{ __('Create a rule') }}
                @if (! empty($attribute))
                    {{ __('for') }} <span class="font-normal">{{ $attribute->name }}</span>
                @endif
            </h1>
        </div>

        <form action="{{ route('admin.evt-events.attribute-rules.store', ['attribute' => $attribute]) }}" method="POST">
            @csrf
            <div class="flex gap-x-4">
                <div class="card sm:w-2/3 flex flex-col md:flex-row">
                    @include('web.admin.evt_events.attribute-rules.partials.form')
                </div>

                @include('web.admin.evt_events.attribute-rules.partials.operators-help')
            </div>
        </form>

    </div>

</x-layout>
