<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('individuals.title') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <a class="btn btn-secondary" href="{{ route('admin.individual.import.index') }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                    </svg>
                    <span>{{ __('main.import_individuals') }}</span>
                </a>

            </div>

        </div>

        <!-- FILTER RESULTS -->
        <x-filter-form :post="route('admin.individual.index')">
            <x-forms.filter-input-text label="{{ __('individuals.given_name') }}" name="filter_name" />
            <x-forms.filter-input-text label="{{ __('individuals.family_name') }}" name="filter_surname" />
            <x-forms.filter-input-select label="{{ __('individuals.nationality') }}" name="filter_country" :options="$countries" />
            <x-forms.filter-input-text label="{{ __('individuals.member_number') }}" name="member_number" />
            <x-forms.filter-input-select label="{{ __('individuals.affiliation_status') }}" name="filter_national_affiliation_status" :options="$affiliationStatuses" />
            <x-forms.filter-input-select label="{{ __('individuals.gender') }}" name="gender" :options="$genders" />
            <x-forms.filter-input-select label="{{ __('entities.entity') }}" name="filter_entity" :options="$entities" />
        </x-filter-form>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            <x-dynamic-table
                :headers="[
                    __('individuals.name'),
                    __('individuals.surname'),
                    __('individuals.birthdate'),
                    __('individuals.gender'),
                    __('individuals.nationality'),
                    __('individuals.member_number'),
                    __('individuals.id_number'),
                    __('individuals.affiliation_status'),
                    __('individuals.cmas_portal'),
                    __('main.actions')
                ]">
                @foreach($individuals as $individual)
                    @php
                        $individualPhoto = $individual->getFirstMediaUrl('profile', 'thumb');

                        if ($individual->has_active_validation_plan) {
                            $affiliationStatusVariant = 'green';
                            $affiliationStatusName = __('states.active');
                        } else {
                            $affiliationStatusVariant = 'gray';
                            $affiliationStatusName = __('states.inactive');
                        }

                        $genderLabel = match($individual->gender) {
                            'M', 'male' => __('individuals.male'),
                            'F', 'female' => __('individuals.female'),
                            default => $individual->gender ?? '-'
                        };
                    @endphp
                    <tr>
                        <!-- Foto + Nome -->
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <div class="flex items-center gap-2">
                                @if($individualPhoto)
                                    <img src="{{ $individualPhoto }}" alt="{{ $individual->name }}" class="h-8 w-8 rounded-full object-cover border border-gray-200 dark:border-gray-600 flex-shrink-0">
                                @else
                                    <div class="h-8 w-8 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center border border-gray-200 dark:border-gray-600 flex-shrink-0">
                                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @endif
                                <span class="font-medium text-slate-800 dark:text-slate-100">{{ $individual->name }}</span>
                            </div>
                        </td>

                        <!-- Apelido -->
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $individual->surname }}</td>

                        <!-- Data de Nascimento -->
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $individual->birthdate?->format('d/m/Y') ?? '-' }}</td>

                        <!-- Sexo -->
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $genderLabel }}</td>

                        <!-- Nacionalidade -->
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @if($individual->country)
                                <div class="flex items-center">
                                    <img src="{{ asset('img/flags/' . strtolower($individual->country->iso) . '.svg') }}" alt="flag"
                                         class="w-4 h-4 mr-1" />
                                    {{ $individual->country->name }}
                                </div>
                            @else
                                -
                            @endif
                        </td>

                        <!-- Nº Filiado -->
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $individual->member_number ?? '-' }}</td>

                        <!-- Nº ID (Member Code) -->
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $individual->member_code ?? '-' }}</td>

                        <!-- Estado Filiação -->
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <x-ui.badge :variant="$affiliationStatusVariant" size="sm">{{ $affiliationStatusName }}</x-ui.badge>
                        </td>

                        <!-- International portal -->
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @if($individual->has_international_portal)
                                <x-ui.badge variant="green" size="sm">{{ __('individuals.yes') }}</x-ui.badge>
                            @else
                                <x-ui.badge variant="gray" size="sm">{{ __('individuals.no') }}</x-ui.badge>
                            @endif
                        </td>

                        <!-- Acoes -->
                        <td class="px-2 first:pl-5 last:pr-5 w-px">
                            <div class="gap-x-2 flex justify-end">
                                <x-dynamic-table-buttons type="files"
                                                         :route="route(Request::segment(1).'.individual.files', $individual->id)" />
                                <x-dynamic-table-buttons type="show"
                                                         :route="route(Request::segment(1).'.individual.show', $individual->id)" />
                                <x-dynamic-table-buttons type="edit"
                                                         :route="route(Request::segment(1).'.individual.edit', $individual->id)" />
                                <x-dynamic-table-buttons type="delete"
                                                         :route="route(Request::segment(1).'.individual.destroy', $individual->id)"
                                                         method="DELETE" />
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-dynamic-table>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{$individuals->links()}}
        </div>

    </div>
</x-layout>
