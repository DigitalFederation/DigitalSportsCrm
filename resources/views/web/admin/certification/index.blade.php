<x-layout>

    @php
        $isSportCommittee = ($committeeType ?? 'diving') === 'sport';
        $currentRoute = $isSportCommittee ? 'admin.certification.sport' : 'admin.certification.diving';
    @endphp

    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ $title }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <a class="btn btn-primary"
                   href="{{ route('admin.certification.create') }}">
                    <span>{{ __('Create Certification') }}</span>
                </a>

            </div>

        </div>

        <!-- FILTER RESULTS -->
        <x-filter-form :post="route($currentRoute)">
            <x-forms.filter-input-text label="Certification" name="filter_name" />
            @if($isSportCommittee)
                <x-forms.filter-input-select label="Sport" name="filter_sport" :options="$sports" />
                <x-forms.filter-input-select label="Role" name="filter_professional_role"
                                             :options="$professional_roles" />
            @else
                <x-forms.filter-input-select label="Committee" name="filter_committee" :options="$committees" />
                <x-forms.filter-input-select label="Role" name="filter_professional_role"
                                             :options="$professional_roles" />
                <x-forms.filter-input-select label="License Relation" name="filter_license" :options="$licenses" />
                <x-forms.filter-input-select label="{{ __('certifications.index.filters.available') }}" name="filter_available" :options="['1' => __('Yes'), '0' => __('No')]" />
            @endif
        </x-filter-form>

        <!-- More actions -->
        @if(Request::segment(3) != 'filter')
            <div class="mb-2 sm:mb-0">
                <h2 class="grow font-semibold text-slate-800 truncate">{{ __('All entries')}}</h2>
            </div>
        @endif

        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            <!-- Table -->
            <div class="bg-white shadow-lg rounded-sm border border-slate-200 mb-8 w-full">
                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="table-auto w-full">
                        <!-- Table header -->
                        <thead
                            class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3">
                                <div class="font-semibold text-left">{{ __('Certification') }}</div>
                            </th>
                            @if (!$isSportCommittee)
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('Committee') }}</div>
                                </th>
                            @else
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('Sport Committion') }}</div>
                                </th>
                            @endif
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('Role') }}</div>
                            </th>

                            @if (!$isSportCommittee)
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('License Relation') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-center">{{ __('certifications.index.table.digital') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-center">{{ __('certifications.index.table.card') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-center">{{ __('certifications.index.table.ref') }}</div>
                                </th>
                            @else
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-center">{{ __('Price') }}</div>
                                </th>
                            @endif
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-center">{{ __('Available') }}</div>
                            </th>

                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-right">{{ __('Actions') }}</div>
                            </th>
                        </tr>
                        </thead>
                        <!-- Table body -->
                        <tbody class="text-sm divide-y divide-slate-200">
                        <!-- Row -->
                        @foreach($certifications as $certification)

                            <tr>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    {{ $certification->name }}
                                </td>

                                @if (!$isSportCommittee)
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                        @if($certification->committee)
                                            @if($certification->committee->code === 'DIVING')
                                                {{ __('certifications.index.table.cmas_diving') }}
                                            @elseif($certification->committee->code === 'SCIENTIFIC')
                                                {{ __('certifications.index.table.cmas_scientific') }}
                                            @else
                                                {{ $certification->committee->name }}
                                            @endif
                                        @endif
                                    </td>
                                @else
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $certification->license->sport->name ?? null }}</td>
                                @endif

                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $certification->professionalRole->name ?? null }}</td>

                                @if (!$isSportCommittee)
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $certification->license->name ?? null }}</td>

                                    {{-- Digital Price --}}
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-center">
                                        @if($certification->digital_price && $certification->digital_price > 0)
                                            <span class="font-medium text-sm">{{ money($certification->digital_price) }}</span>
                                        @else
                                            <span class="text-slate-500 italic text-sm">{{ __('Free') }}</span>
                                        @endif
                                    </td>

                                    {{-- Card Price --}}
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-center">
                                        @if($certification->digital_plus_card_price && $certification->digital_plus_card_price > 0)
                                            <span class="font-medium text-sm">{{ money($certification->digital_plus_card_price) }}</span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>

                                    {{-- Moloni Reference --}}
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-center">
                                        @if($certification->moloni_reference)
                                            <span class="text-sm font-mono">{{ $certification->moloni_reference }}</span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                @else
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-center">
                                        @if($certification->isFree())
                                            <span class="text-slate-500 italic">{{ __('Free') }}</span>
                                        @else
                                            <div class="text-sm">
                                                @if($certification->unit_value)
                                                    <span class="font-medium">{{ money($certification->unit_value) }}</span>
                                                @endif
                                                @if($certification->unit_value_individual || $certification->unit_value_entity)
                                                    <div class="text-xs text-slate-500">
                                                        @if($certification->unit_value_individual)
                                                            <span>{{ __('Ind') }}: {{ money($certification->unit_value_individual) }}</span>
                                                        @endif
                                                        @if($certification->unit_value_entity)
                                                            <span class="ml-1">{{ __('Ent') }}: {{ money($certification->unit_value_entity) }}</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                @endif
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-center">
                                    @if($certification->is_available)
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                            {{ __('Yes') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800">
                                            {{ __('No') }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end">
                                    <div class="space-x-1 flex justify-end items-end">

                                        <x-dynamic-table-buttons type="edit"
                                                                 :route="route('admin.certification.edit', $certification->id)" />
                                        <x-dynamic-table-buttons type="delete"
                                                                 :route="route('admin.certification.destroy', $certification->id)"
                                                                 method="DELETE" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{$certifications->links()}}
        </div>

    </div>
</x-layout>
