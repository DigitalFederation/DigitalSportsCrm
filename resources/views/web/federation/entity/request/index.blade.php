@section('title', __('entity.entities_to_approve'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('entity.entities_to_approve') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn self-center bg-slate-500 text-white"
                   href="{{ route(Request::segment(1).'.entity.index') }}"> {{ __('main.back') }} </a>
            </div>
        </div>

        <!-- FILTER RESULTS -->
        <x-filter-form :post="route('federation.entity-request.index')">
            <x-forms.filter-input-text label="{{ __('entity.table_name') }}" name="filter_name" />
            <x-forms.filter-input-select label="{{ __('entity.table_nationality') }}" name="filter_country" :options="$countries" />
            <x-forms.filter-input-text label="{{ __('entity.table_email') }}" name="filter_email" />
        </x-filter-form>

        @if(!empty($entities) && $entities->count() > 0)
            <!-- More actions -->
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
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('entity.table_name') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('entity.table_nationality') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('entity.table_email') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('entity.table_requested') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-right">{{ __('entity.table_actions') }}</div>
                                </th>
                            </tr>
                            </thead>
                            <!-- Table body -->
                            <tbody class="text-sm divide-y divide-slate-200">
                            <!-- Row -->
                            @foreach($entities as $entity)
                                @php
                                    $entityFederation = $entity->entityFederations->first();
                                @endphp
                                <tr>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $entity->name }} {{ $entity->surname }}</td>

                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                        <div class="flex items-center">
                                            <img alt="flag"
                                                 src="{{ asset('img/flags/' . strtolower($entity->country->iso) . '.svg') }}"
                                                 class="w-4 h-4 mr-1" />
                                            {{ $entity->country->name }}
                                        </div>
                                    </td>

                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px hover:text-indigo-600">
                                        <a href="mailto:{{ $entity->email }}"
                                           target="_blank">{{ $entity->email }}</a>
                                    </td>

                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                        @if($entityFederation?->created_at)
                                            {{ Carbon\Carbon::parse($entityFederation->created_at) }}
                                        @endif
                                    </td>

                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px justify-end items-center">
                                        <div class="space-x-1 flex items-center justify-end">

                                            <x-dynamic-table-buttons
                                                type="show"
                                                :route="route(Request::segment(1).'.entity.show', $entity->id)" />

                                            @php
                                                $userFederation = auth()->user()->federations()->first();
                                                $isPrimaryFederationUser = $userFederation && $userFederation->parent_id === null;
                                            @endphp

                                            <x-filament::modal width="lg">
                                                <x-slot name="trigger">
                                                    <x-svg.check class="h-5 w-5 text-green-500" />
                                                </x-slot>

                                                <x-slot name="heading">
                                                    {{ __('entity.accept_request') }}
                                                </x-slot>

                                                <x-slot name="description">
                                                    <form method="POST"
                                                          class="pb-4"
                                                          action="{{ route(Request::segment(1).'.entity-request.accept', $entity->id) }}">
                                                        @csrf

                                                        @if($isPrimaryFederationUser)
                                                            {{-- Main federation user: number will be auto-generated --}}
                                                            <div class="w-full flex-wrap whitespace-normal text-gray-600">
                                                                {{ __('entity.approval_national_federation_message') }}
                                                            </div>
                                                            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                                                <p class="text-sm text-blue-700">
                                                                    {{ __('entity.member_number_auto_generated') }}
                                                                </p>
                                                            </div>
                                                        @else
                                                            {{-- Other associations: Simple approval, no number --}}
                                                            <div class="w-full flex-wrap whitespace-normal text-gray-600">
                                                                {{ __('entity.approval_association_message') }}
                                                            </div>
                                                        @endif

                                                        <div class="w-full mt-6">
                                                            <button type="submit"
                                                                    class="btn btn-primary"> {{ __('entity.approve_entity') }}</button>
                                                        </div>
                                                    </form>
                                                </x-slot>
                                            </x-filament::modal>


                                            <x-dynamic-table-buttons
                                                type="reject"
                                                method="POST"
                                                :route="route(Request::segment(1).'.entity-request.reject', $entity->id)" />

                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <x-utility.no-data />
        @endif

        <!-- Pagination -->
        <div class="mt-8">
            {{$entities->links()}}
        </div>

    </div>
</x-layout>
