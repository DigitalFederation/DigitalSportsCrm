@section('title', __('individuals.title'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">
                    @if(!empty(request()->filter['instructors']) && request()->filter['instructors'] == 'true')
                        {{ ucwords(request()->filter['committee'] ?? '') . ' ' . __('individuals.instructors_leaders') }}
                    @elseif(!empty(request()->filter['coachs']) && request()->filter['coachs'] == 'true')
                        {{ __('individuals.coachs') }}
                    @elseif(!empty(request()->filter['referees']) && request()->filter['referees'] == 'true')
                        {{ __('individuals.referees_judges') }}
                    @else
                        {{ ucwords(request()->filter['committee'] ?? '') . ' ' . __('individuals.title') }}
                    @endif
                </h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-primary" href="{{ route('federation.individual.create') }}">
                    <span>{{ __('individuals.create_individual') }}</span>
                </a>
                @if((empty(request()->filter['instructors']) || request()->filter['instructors'] != 'true') && (empty(request()->filter['coachs']) || request()->filter['coachs'] != 'true') && (empty(request()->filter['referees']) || request()->filter['referees'] != 'true'))
                    <a class="btn btn-primary" href="{{ route(Request::segment(1).'.individual-request.index') }}">
                        <span>{{ __('individuals.individuals_to_approve') }}</span>
                    </a>
                @endif

                <livewire:federation-export-button export-type="individuals" />

            </div>
        </div>

        <div class="sm:flex flex-row gap-4">
            @if(!empty(request()->filter['instructors']) && request()->filter['instructors'] == 'true')
                <x-utility.card-total :title="__('individuals.instructors_leaders')"
                                      :count="$individuals->total()"></x-utility.card-total>
            @elseif(!empty(request()->filter['coachs']) && request()->filter['coachs'] == 'true')
                <x-utility.card-total :title="__('individuals.coachs')" :count="$individuals->total()"></x-utility.card-total>
            @elseif(!empty(request()->filter['referees']) && request()->filter['referees'] == 'true')
                <x-utility.card-total :title="__('individuals.referees_judges')" :count="$individuals->total()"></x-utility.card-total>
            @else
                <x-utility.card-total
                    :title="!empty(request()->filter['instructors']) && request()->filter['instructors'] == 'true' ? __('individuals.instructors_leaders') : __('individuals.title')"
                    :count="$individuals->total()"></x-utility.card-total>
            @endif

            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('federation.individual.index')">
                @if(!empty(request()->filter['committee']))
                    <input type="hidden" name="filter[committee]" value="{{request()->filter['committee']}}">
                @endif
                @if(!empty(request()->filter['instructors']))
                    <input type="hidden" name="filter[instructors]" value="{{request()->filter['instructors']}}">
                @endif
                @if(!empty(request()->filter['coachs']))
                    <input type="hidden" name="filter[coachs]" value="{{request()->filter['coachs']}}">
                @endif
                @if(!empty(request()->filter['referees']))
                    <input type="hidden" name="filter[referees]" value="{{request()->filter['referees']}}">
                @endif
                <x-forms.filter-input-text label="{{ __('individuals.given_name') }}" name="filter_name" />
                <x-forms.filter-input-text label="{{ __('individuals.family_name') }}" name="filter_surname" />
                <x-forms.filter-input-select label="{{ __('individuals.nationality') }}" name="filter_country" :options="$countries" />
                <x-forms.filter-input-text label="{{ __('individuals.member_number') }}" name="member_number" />
                <x-forms.filter-input-select label="{{ __('individuals.affiliation_status') }}" name="filter_national_affiliation_status" :options="$affiliationStatuses" />
                <x-forms.filter-input-select label="{{ __('individuals.gender') }}" name="gender" :options="$genders" />
                <x-forms.filter-input-text label="{{ __('main.Member Code') }}" name="filter_member_code" />
                <x-forms.filter-input-select label="{{ __('main.Entity') }}" name="filter_entity" :options="$entities" />
            </x-filter-form>
        </div>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            <x-dynamic-table
                :headers="[
                    __('individuals.name'),
                    __('individuals.surname'),
                    __('individuals.birthdate'),
                    __('individuals.gender'),
                    __('individuals.nationality'),
                    __('individuals.member_number'),
                    __('main.Member Code'),
                    __('individuals.affiliation_status'),
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
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $individual->surname }}</td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $individual->birthdate?->format('d/m/Y') ?? '-' }}</td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $genderLabel }}</td>
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
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $individual->member_number ?? '-' }}</td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $individual->member_code ?? '-' }}</td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <x-ui.badge :variant="$affiliationStatusVariant" size="sm">{{ $affiliationStatusName }}</x-ui.badge>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 w-px">
                            <div class="gap-x-2 flex justify-end">
                                <x-dynamic-table-buttons type="files"
                                                         :route="route(Request::segment(1).'.individual.files', $individual->id)" />
                                <x-dynamic-table-buttons type="show"
                                                         :route="route(Request::segment(1).'.individual.show', $individual->id)" />
                                <x-dynamic-table-buttons type="edit"
                                                         :route="route(Request::segment(1).'.individual.edit', $individual->id)" />
                                <x-dynamic-table-buttons type="delete"
                                                         :route="route(Request::segment(1).'.individual.delete', $individual->id)"
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
