<section class="mt-2 bg-white rounded-md shadow hover:shadow-xl">
    <div class="mx-5 mt-4 mb-2 flex justify-between">
        <h2 class="font-semibold text-slate-600 text-lg">{{ __('main.associated_entities') }}</h2>
    </div>

    @if(!empty($entities) && $entities->count() > 0)
        <x-dynamic-table
            :headers="[__('main.entity'),__('main.status')]">
            @foreach($entities as $entityIndividual)
                <tr role="row">
                    <td class="px-2 py-2 text-left">
                        {{ $entityIndividual->entity?->name }}
                    </td>
                    <td class="px-2 py-2 whitespace-nowrap text-right">
                        <x-tables.badge :status="ucfirst($entityIndividual->stateName())"
                                        :color="$entityIndividual->stateColor()" />
                    </td>
                </tr>
            @endforeach
        </x-dynamic-table>
    @endif
</section>
