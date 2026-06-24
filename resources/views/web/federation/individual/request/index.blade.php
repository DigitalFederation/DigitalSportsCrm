<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('Individuals to Approve') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="flex justify-start sm:justify-end gap-2">
                <a class="btn btn-info w-full md:w-auto"
                   href="{{ route(Request::segment(1).'.individual.index') }}"> {{ __('Back') }} </a>
            </div>
        </div>

        <!-- FILTER RESULTS -->
        <x-filter-form :post="route('federation.individual-request.index')">
            <x-forms.filter-input-text label="{{ __('main.name') }}" name="filter_name" />
            <x-forms.filter-input-text label="{{ __('main.surname') }}" name="filter_surname" />
            <x-forms.filter-input-select label="{{ __('main.status') }}" name="filter_status" :options="$statuses" />
            <x-forms.filter-input-select label="{{ __('main.Nationality') }}" name="filter_country" :options="$countries" />
        </x-filter-form>

        @if(!empty($individuals) && $individuals->count() > 0)
            <!-- More actions -->
            @if(Request::segment(3) != 'filter')
                <div class="mb-2 sm:mb-0">
                    <h2 class="grow font-semibold text-slate-800 truncate">{{ __('Latest entries') }}</h2>
                </div>
            @endif

            <div class="sm:flex sm:justify-center sm:items-center mb-5">

                <!-- Table -->
                <x-dynamic-table
                    :headers="[__('main.name'), __('main.Nationality'), __('main.Email'), __('individual_request.requested'), __('main.status'), '']">
                    @foreach($individuals as $individual)
                        @php
                            $individualFederation = $individual->individualFederations->first();
                        @endphp
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px"> {{ $individual->name }} {{ $individual->surname }}</td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="flex items-center">
                                    <img alt="flag"
                                         src="{{ asset('img/flags/' . strtolower($individual->country->iso) . '.svg') }}"
                                         class="w-4 h-4 mr-1" />
                                    {{ $individual->country->name }}
                                </div>
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px hover:text-indigo-600">
                                {{ $individual->email }}
                            </td>


                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                @if($individualFederation?->created_at)
                                    {{ Carbon\Carbon::parse($individualFederation->created_at) }}
                                @endif
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <x-tables.badge :status="$individualFederation->stateName()"
                                                :color="$individualFederation->stateColor()" />
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px justify-end items-center">
                                <div class="space-x-1 flex flex-row items-center justify-end">


                                    <x-dynamic-table-buttons
                                        :route="route('federation.individual.show', $individual->id)" type="show" />
                                    @if($individualFederation->status_class != \Domain\Individuals\States\ActiveIndividualFederationState::class &&
                                    $individualFederation->status_class != \Domain\Individuals\States\RejectedIndividualFederationState::class)
                                        <div x-data="{ open: false }" class="relative">
                                            <button @click="open = true" class="text-green-500 flex items-center">
                                                <x-svg.check class="w-5 h-5" />
                                            </button>

                                            <div x-show="open"
                                                 class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full"
                                                 x-cloak>
                                                <div class="container mx-auto h-full flex justify-center items-center">
                                                    <div class="card w-full mx-4 md:w-1/3">
                                                        <h2 class="text-lg font-bold mb-4"> {{ __('Accept Request') }}</h2>
                                                        <x-information-box :title="null"
                                                                           body="{{ __('individual_request.enter_national_fed_number') }}" />
                                                        <form
                                                            action="{{ route('federation.individual-request.accept', ['id' => $individualFederation->id]) }}"
                                                            method="POST">
                                                            @csrf
                                                            <div class="mb-4">
                                                                <label for="national_federation_number"
                                                                       class="block text-gray-700 text-sm font-bold mb-2"> {{ __('individual_request.member_number_label') }}</label>
                                                                <input type="text" name="national_federation_number"
                                                                       id="national_federation_number"
                                                                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                                            </div>

                                                            <div class="flex items-center justify-between">
                                                                <button class="btn btn-primary" type="submit">{{ __('individual_request.submit') }}
                                                                </button>
                                                                <button type="button" @click="open = false"
                                                                        class="btn btn-info">{{ __('main.Cancel') }}
                                                                </button>
                                                            </div>

                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <x-dynamic-table-buttons
                                            :route="route('federation.individual-request.reject', $individualFederation->id)"
                                            type="reject" method="POST" />
                                    @else
                                        <x-dynamic-table-buttons
                                            :route="route('federation.individual-request.delete', $individualFederation->id)"
                                            type="delete" method="DELETE" />
                                    @endif


                                </div>
                            </td>
                        </tr>

                    @endforeach
                </x-dynamic-table>

            </div>
        @else
            <x-utility.no-data />
        @endif

        <!-- Pagination -->
        <div class="mt-8">
            {{$individuals->links()}}
        </div>

    </div>
</x-layout>
