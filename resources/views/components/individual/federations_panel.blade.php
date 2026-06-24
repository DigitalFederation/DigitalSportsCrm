@props(['federations', 'individual'])

<section class="mt-2 bg-white rounded-md shadow hover:shadow-xl">
    <div class="mx-5 mt-4 mb-2 flex justify-between">
        <h2 class="font-semibold text-slate-600 text-lg">{{ __('Organizações') }}</h2>
    </div>

    @if(!empty($federations))
        <x-dynamic-table
            :headers="['Code','Organização', 'Status']">
            @foreach($federations as $federationIndividual)
                <tr role="row">
                    <td class="pl-5 py-2 whitespace-nowrap text-left">
                        {{ $federationIndividual->federation?->member_code }}
                    </td>
                    <td class="px-2 py-2 text-left">
                        {{ $federationIndividual->federation?->name }}
                    </td>
                    <td class="px-2 py-2 whitespace-nowrap text-right">
                        <x-tables.badge :status="ucfirst($federationIndividual->stateName())"
                                        :color="$federationIndividual->stateColor()" />
                    </td>
                </tr>
            @endforeach
        </x-dynamic-table>
    @endif

</section>

