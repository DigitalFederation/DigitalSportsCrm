<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">
                    @php
                        $filter = Request::query('filter') ?? [];
                        // Use the filterHolderType from controller if it's set, otherwise fall back to request
                        $filterHolderType = $filterHolderType ?? $filter['filter_holder_type'] ?? null;
                        // Use committee from controller if set (separated routes), otherwise fall back to request
                        $committee = $committee ?? $filter['committee'] ?? null;
                        $filterProfessional = $filter['filter_professional'] ?? null;
                    @endphp

                    @if(!empty($pageTitle))
                        {{-- Use specific title from separated routes --}}
                        {{ $pageTitle }}
                    @elseif(empty($filterHolderType) || $filterHolderType !== 'individual')
                        {{ ucwords($committee ?? '') . ' ' . __('Entity') }} {{ __(' Licenses') }}
                    @else
                        @if(!empty($committee) && $committee == 'DIVING')
                            @if(!empty($filterProfessional) && $filterProfessional == 'instructorleader')
                                {{ __('Diving Instructor & Leaders') }}
                            @elseif(!empty($filterProfessional) && $filterProfessional == 'freediving')
                                {{ __('Freediving') }}
                            @endif
                        @endif

                        @if(!empty($committee) && $committee == 'SCIENTIFIC')
                            {{ __('Scientific Instructor & Leaders') }}
                        @endif

                        @if(!empty($committee) && $committee == 'SPORT')
                            @if(!empty($filterProfessional) && $filterProfessional == 'coach')
                                {{ __('Coach') }}
                            @else
                                {{ __('Athlete') }}
                            @endif
                        @endif

                        {{ __(' Licenses') }}
                    @endif
                </h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                @php
                    // Resolve the purchase route for the current committee + type
                    // from config/committees.php (see App\Support\Committees), with
                    // a generic fallback so any configured committee works.
                    $isMembers = !empty($filterHolderType) && $filterHolderType === 'individual';
                    $purchaseType = $isMembers ? 'members' : 'entity';

                    $purchaseRouteName = !empty($committee)
                        ? \App\Support\Committees::purchaseRouteName($committee, $purchaseType)
                        : null;

                    if (! $purchaseRouteName || ! \Illuminate\Support\Facades\Route::has($purchaseRouteName)) {
                        $purchaseRouteName = \App\Support\Committees::defaultEntityPurchaseRouteName();
                    }

                    $purchaseRoute = route($purchaseRouteName);
                @endphp
                <a href="{{ $purchaseRoute }}" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    {{ __('licenses.Purchase License') }}
                </a>
            </div>

        </div>


        <!-- FILTER RESULTS -->
        <div class="sm:flex flex-row gap-4">
            <x-utility.card-total title="Licenses" :count="$licenses->total()"></x-utility.card-total>
            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('entity.license-attributed.index')">

                @if(!empty(Request::query('filter.committee')))
                    <input type="hidden" name="filter[committee]" value="{{ Request::query('filter.committee') }}">
                @endif

                @if(!empty(Request::query('filter.filter_holder_type')))
                    <input type="hidden" name="filter[filter_holder_type]"
                           value="{{ Request::query('filter.filter_holder_type') }}">
                @endif

                @if(!empty(Request::query('filter.filter_professional')))
                    <input type="hidden" name="filter[filter_professional]"
                           value="{{ Request::query('filter.filter_professional') }}">
                @endif

                <x-forms.filter-input-text label="Name" name="filter_name" />

                <x-forms.filter-input-text label="{{ __('main.Member Code') }}" name="filter_member_code" />
                @if(!empty(Request::query('filter.committee')) && Request::query('filter.committee') == 'sport' && !empty(Request::query('filter.filter_holder_type')) && Request::query('filter.filter_holder_type') == 'individual')
                    <x-forms.filter-input-select label="Sport Commission" name="filter_sport" :options="$sports" />
                    <x-forms.filter-input-select label="Sport Categories" name="filter_category"
                                                 :options="$professional_roles" />
                @endif
                <x-forms.filter-input-date-range label="Issue date" nameStart="filter_expiration_start"
                                                 nameEnd="filter_expiration_end" />
                <x-forms.filter-input-select label="Status" name="filter_status" :options="$filter_status" />
            </x-filter-form>
        </div>


        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            <!-- Table -->
            <div class="bg-white shadow-lg rounded-sm border border-slate-200 mb-8 w-full">
                <div class="overflow-x-auto">
                    <table class="table-auto w-full">
                        <!-- Table header -->
                        <thead
                            class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('License') }}</div>
                            </th>
                            @if($filterHolderType === 'individual')
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('Name') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('individuals.member_number') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('ID Number') }}</div>
                                </th>
                            @endif
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('licenses.issue_date') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('licenses.expiration_date') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('Status') }}</div>
                            </th>
                        </tr>
                        </thead>
                        <!-- Table body -->
                        <tbody class="text-sm divide-y divide-slate-200">
                        <!-- Row -->
                        @foreach($licenses as $license)
                            <tr>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $license->license_name }}</td>
                                @if($filterHolderType === 'individual')
                                    @php
                                        $owner = $license->owner;
                                        $ownerName = $owner ? trim($owner->name . ' ' . $owner->surname) : $license->holder_name;
                                    @endphp
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                        @if($owner)
                                            <a href="{{ route('entity.individual.show', $owner->id) }}" class="text-indigo-600 hover:text-indigo-900 hover:underline">
                                                {{ $ownerName }}
                                            </a>
                                        @else
                                            {{ $ownerName }}
                                        @endif
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $owner->member_number ?? '-' }}</td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $owner->member_code ?? '-' }}</td>
                                @endif
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    @if($license->activated_at)
                                        {{ date('d/m/Y', strtotime($license->activated_at)) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    @if($license->current_term_ends_at)
                                        {{ $license->current_term_ends_at->format('d/m/Y') }}
                                    @elseif($license->expires_at)
                                        {{ date('d/m/Y', strtotime($license->expires_at)) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    <x-tables.badge :status="ucfirst($license->stateName())"
                                                    :color="$license->stateColor()" />
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
            {{$licenses->links()}}
        </div>

    </div>
</x-layout>
