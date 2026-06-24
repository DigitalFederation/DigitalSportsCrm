<x-layout>
    <div class="previous-layout-classes">

        <div class="mb-8 flex justify-between">
            <h1 class="page-first-title">
                {{ __('Edit a rule') }}
                @if (! empty($attribute))
                    {{ __('for') }} <span class="font-normal">{{ $attribute->name }}</span>
                @endif
            </h1>
        </div>

        <form
            action="{{ route('admin.evt-events.attribute-rules.update', ['attribute' => $attribute->id, 'attribute_rule' => $attributeRule->id]) }}"
            method="POST">
            @csrf
            @method('PUT')
            <div class="flex gap-x-4">
                <div class="card sm:w-2/3 flex flex-col md:flex-row">

                    @include('web.admin.evt_events.attribute-rules.partials.form')
                </div>

                @include('web.admin.evt_events.attribute-rules.partials.operators-help')
            </div>
        </form>

    </div>

</x-layout>
