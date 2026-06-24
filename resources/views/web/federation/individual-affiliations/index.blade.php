@section('title', __('federation.individual_affiliations'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('federation.individual_affiliations') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <a href="{{ route('federation.individual-affiliations.create') }}" 
                   class="btn btn-primary">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z"/>
                    </svg>
                    <span class="ml-2">{{ __('federation.common.add_new') }}</span>
                </a>

                <livewire:federation-export-button exportType="individual-affiliations" />
            </div>

        </div>

        <!-- Filter and Card Total Section -->
        <div class="sm:flex flex-row gap-4">

            <x-utility.card-total title="{{ __('federation.individual_affiliations') }}" :count="$affiliations->total()"></x-utility.card-total>

            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('federation.individual-affiliations.index')">
                <div class="w-full md:w-1/3 lg:w-1/4">
                    <label class="block text-sm font-medium mb-1" for="filter_individual_name">{{ __('federation.individual_name') }}</label>
                    <input type="text"
                           class="form-input w-full"
                           name="filter_individual_name"
                           id="filter_individual_name"
                           value="{{ request('filter_individual_name', '') }}">
                </div>
                <div class="w-full md:w-1/3 lg:w-1/4">
                    <label class="block text-sm font-medium mb-1" for="filter_entity_id">{{ __('federation.entity') }}</label>
                    <select class="form-select w-full"
                            name="filter_entity_id"
                            id="filter_entity_id">
                        <option value="">{{ __('common.all') }}</option>
                        @foreach($entitiesForFilter as $entity)
                            <option value="{{ $entity->id }}" {{ request('filter_entity_id') == $entity->id ? 'selected' : '' }}>{{ $entity->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full md:w-1/3 lg:w-1/4">
                    <label class="block text-sm font-medium mb-1" for="filter_status_class">{{ __('federation.affiliation_status') }}</label>
                    <select class="form-select w-full"
                            name="filter_status_class"
                            id="filter_status_class">
                        <option value="">{{ __('common.all') }}</option>
                        <option value="Domain\Memberships\States\ActiveAffiliationState" {{ request('filter_status_class') === 'Domain\Memberships\States\ActiveAffiliationState' ? 'selected' : '' }}>{{ __('common.active') }}</option>
                        <option value="Domain\Memberships\States\InactiveAffiliationState" {{ request('filter_status_class') === 'Domain\Memberships\States\InactiveAffiliationState' ? 'selected' : '' }}>{{ __('common.inactive') }}</option>
                        <option value="Domain\Memberships\States\PendingPaymentAffiliationState" {{ request('filter_status_class') === 'Domain\Memberships\States\PendingPaymentAffiliationState' ? 'selected' : '' }}>{{ __('federation.pending_payment') }}</option>
                        <option value="Domain\Memberships\States\SuspendedAffiliationState" {{ request('filter_status_class') === 'Domain\Memberships\States\SuspendedAffiliationState' ? 'selected' : '' }}>{{ __('federation.suspended') }}</option>
                        <option value="Domain\Memberships\States\ExpiredAffiliationState" {{ request('filter_status_class') === 'Domain\Memberships\States\ExpiredAffiliationState' ? 'selected' : '' }}>{{ __('federation.expired') }}</option>
                    </select>
                </div>
                <x-forms.filter-input-date-range label="{{ __('federation.activation_date') }}" nameStart="filter_start_date" nameEnd="filter_end_date" />
            </x-filter-form>

        </div>

        <!-- Individual Affiliations Table -->
        <div class="card-no-padding mb-8">
            <div class="overflow-x-auto">
                <table class="table-auto w-full divide-y divide-slate-200">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-slate-200">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('federation.individual') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('federation.affiliation_plan') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('federation.requested_by') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('federation.activation_date') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('federation.expiration_date') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-right">{{ __('federation.value') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('common.status') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-right">{{ __('common.actions') }}</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200">
                        @forelse($affiliations as $affiliation)
                            <tr class="table-row">
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            @if($affiliation->member)
                                                <x-secure-profile-image :individual="$affiliation->member" size="thumb" class="h-8 w-8 rounded-full" />
                                            @else
                                                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-blue-600">?</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-slate-800">
                                                {{ $affiliation->member->full_name ?? 'N/A' }}
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                {{ $affiliation->member->member_code ?? $affiliation->member->code_internal ?? '' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="text-slate-800">{{ $affiliation->affiliationPlan->name ?? '-' }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    @if($affiliation->requester_type === 'entity' && $affiliation->requester)
                                        <div class="text-sm text-slate-800">{{ $affiliation->requester->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $affiliation->requester->code ?? '' }}</div>
                                    @elseif($affiliation->requester_type === 'individual' && $affiliation->requester)
                                        <div class="text-sm text-slate-800">{{ $affiliation->requester->full_name }}</div>
                                        <div class="text-xs text-slate-500">{{ __('federation.individual') }}</div>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="text-slate-800">{{ $affiliation->start_date?->format('d/m/Y') ?? '-' }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="text-slate-800">{{ $affiliation->end_date?->format('d/m/Y') ?? '-' }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-right">
                                    <div class="text-slate-800 font-medium">
                                        {{ number_format($affiliation->individual_fee ?? 0, 2, ',', '.') }} €
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    @php
                                        $statusColor = match($affiliation->stateColor()) {
                                            'green' => 'bg-emerald-100 text-emerald-600',
                                            'yellow' => 'bg-amber-100 text-amber-600',
                                            'red' => 'bg-rose-100 text-rose-600',
                                            'gray' => 'bg-slate-100 text-slate-600',
                                            default => 'bg-slate-100 text-slate-600'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                        {{ $affiliation->stateName() }}
                                    </span>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="space-x-1 flex justify-end items-end">
                                        <x-dynamic-table-buttons type="show" :route="route('federation.individual-affiliations.show', $affiliation)" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-2 first:pl-5 last:pr-5 py-12 text-center">
                                    <div class="text-slate-500">
                                        <p class="text-lg font-medium">{{ __('federation.no_individual_affiliations_found') }}</p>
                                        <p class="text-sm mt-2">{{ __('federation.start_by_creating_individual_affiliation') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $affiliations->links() }}
        </div>

    </div>
</x-layout>