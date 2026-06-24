@section('title', __('members.member_invitations'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('members.member_invitations') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-info" href="{{ route(Request::segment(1).'.individual.index') }}">
                    <span>{{ __('members.list_of_members') }}</span>
                </a>
            </div>
        </div>

        <!-- FILTER RESULTS -->
        <x-filter-form :post="route('entity.individual-approve.index')">
            <x-forms.filter-input-text :label="__('main.name')" name="filter_name" />
            <x-forms.filter-input-select :label="__('main.Nationality')" name="filter_country" :options="$countries" />
        </x-filter-form>

        <!-- More actions -->
        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            @if(!empty($individuals) && $individuals->count() > 0)
                <x-dynamic-table
                    :headers="[__('main.name'), __('main.Nationality'), __('main.Member Code'), __('main.birthdate'), __('members.requested_date'), ['text'=>__('main.status'),'alignment'=>'right'], '']">
                    @foreach($individuals as $individual)
                        @php
                            $individualEntity = $individual->individualEntities->first();
                        @endphp
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $individual->name }} {{ $individual->surname }}</td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="flex items-center">
                                    <img src="{{ asset('img/flags/' . strtolower($individual->country->iso) . '.svg') }}" alt="flag"
                                         class="w-4 h-4 mr-1" />
                                    {{ $individual->country->name }}
                                </div>
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $individual->member_code }}</td>


                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ Carbon\Carbon::parse($individual->birthdate)->format('d/m/Y') }}</td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                @if($individualEntity?->created_at)
                                    {{ Carbon\Carbon::parse($individualEntity->created_at)->format('d/m/Y') }}
                                @endif
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center">
                                @php
                                    $statusName = $individualEntity->stateName();
                                    $translatedStatus = match($statusName) {
                                        'Active' => __('members.status_active'),
                                        'Pending' => __('members.status_pending'),
                                        'Pending Individual' => __('members.status_pending_individual'),
                                        'Pending Entity' => __('members.status_pending_entity'),
                                        'Canceled' => __('members.status_canceled'),
                                        'Denied' => __('members.status_denied'),
                                        default => $statusName
                                    };
                                @endphp
                                <x-tables.badge :status="$translatedStatus"
                                                :color="$individualEntity->stateColor()" />
                            </td>


                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px justify-end items-center">
                                <div class="space-x-1 flex items-center justify-end">


                                    @if($individualEntity->status_class == \Domain\Individuals\States\PendingFromEntityIndividualEntityState::class)
                                        <form class="flex"
                                              action="{{ route(Request::segment(1).'.individual-approve.store') }}"
                                              method="post"
                                              onsubmit="return confirm('{{ __('members.confirm_accept_request') }}')">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $individual->id }}">
                                            <button type="submit"
                                                    class="text-green-500 hover:text-green-600 rounded-full"
                                                    title="{{ __('members.accept') }}">
                                                <span class="sr-only">{{ __('members.accept') }}</span>
                                                <x-svg.check class="w-5 h-5" />
                                            </button>
                                        </form>
                                    @endif

                                    <x-dynamic-table-buttons type="delete"
                                                             :route="route('entity.individual.delete', $individual->id)"
                                                             method="DELETE" />


                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>
            @else
                <x-utility.no-data :in-card="true"></x-utility.no-data>
            @endif
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{$individuals->links()}}
        </div>

    </div>
</x-layout>
