@section('title', __('entity.members_list'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('entity.member_list') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-secondary" href="{{ route('entity.individual.create') }}">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M8 7V0c0-.6-.4-1-1-1S6 .4 6 0v7H1c-.6 0-1 .4-1 1s.4 1 1 1h5v7c0 .6.4 1 1 1s1-.4 1-1V9h7c.6 0 1-.4 1-1s-.4-1-1-1H8z" />
                    </svg>
                    <span class="ml-2">{{ __('entity.create_individual') }}</span>
                </a>

                <a class="btn btn-secondary" href="{{ route(Request::segment(1) . '.individual-approve.index') }}">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M5 3a2 2 0 00-2 2v1a2 2 0 002 2h6a2 2 0 002-2V5a2 2 0 00-2-2H5zm0 2h6v1H5V5zm-3 6a2 2 0 012-2h8a2 2 0 012 2v1a2 2 0 01-2 2H4a2 2 0 01-2-2v-1zm2 0v1h8v-1H4z" />
                    </svg>
                    <span class="ml-2">{{ __('entity.individuals_to_approve') }}</span>
                </a>

                <x-dynamic-modal :viewName="'entity.individual-request'" :headerTitle="__('entity.member_invitation_form')" :buttonLabel="__('entity.invite_member')"
                    buttonClass="btn btn-primary" :isLivewire="true" animation="transition ease-in duration-200" />

            </div>
        </div>

        <!-- FILTER RESULTS -->
        <x-filter-form :post="route('entity.individual.index')">
            <x-forms.filter-input-text :label="__('entity.given_name')" name="filter_name" />
            <x-forms.filter-input-text :label="__('entity.family_name')" name="filter_surname" />
            <x-forms.filter-input-text :label="__('entity.member_number')" name="filter_member_number" />
            <x-forms.filter-input-select :label="__('entity.nationality')" name="filter_country" :options="$countries" />
            <x-forms.filter-input-select :label="__('entity.federation')" name="filter_federation" :options="$federations" />
            <x-forms.filter-input-select
                :label="__('entity.national_affiliation')"
                name="filter_national_affiliation_status"
                :options="[
                    'active' => __('entity.affiliation_active'),
                    'inactive' => __('entity.affiliation_inactive'),
                ]"
            />
        </x-filter-form>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            <x-dynamic-table :headers="[__('entity.given_name'), __('entity.family_name'), __('entity.birthdate'), __('entity.member_number'), __('entity.gender'), __('entity.nationality'), __('entity.national_affiliation'), '']">
                @foreach ($individuals as $individual)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $individual->name }}</td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $individual->surname }}</td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            {{ Carbon\Carbon::parse($individual->birthdate)->format('d/m/Y') }}
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            {{ $individual->member_number ?? '-' }}
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @if($individual->gender === 'male')
                                {{ __('main.male') }}
                            @elseif($individual->gender === 'female')
                                {{ __('main.female') }}
                            @else
                                -
                            @endif
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <div class="flex items-center">
                                <img src="{{ asset('img/flags/' . strtolower($individual->country->iso) . '.svg') }}" alt="flag"
                                    class="w-4 h-4 mr-1" />
                                {{ $individual->country->name }}
                            </div>
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @php
                                $activeAffiliation = $individual->affiliations
                                    ->filter(function($affiliation) {
                                        return $affiliation->status_class === 'Domain\Affiliations\States\ActiveAffiliationState'
                                            || $affiliation->status_class === 'Domain\Memberships\States\ActiveAffiliationState';
                                    })
                                    ->first();
                            @endphp

                            @if($activeAffiliation)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ __('entity.affiliation_active') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ __('entity.affiliation_inactive') }}
                                </span>
                            @endif
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px justify-end items-center">
                            <div class="space-x-1 flex items-center justify-end">

                                <x-dynamic-table-buttons type="show" :route="route('entity.individual.show', $individual->id)" :target="'_blank'"
                                    title="Show" />
                                <x-dynamic-table-buttons type="delete" :route="route('entity.individual.delete', $individual->id)" method="DELETE" />

                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-dynamic-table>

        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $individuals->links() }}
        </div>

    </div>
</x-layout>
