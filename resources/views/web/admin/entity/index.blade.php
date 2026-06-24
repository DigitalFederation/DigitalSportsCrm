<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('entity.entities') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <a class="btn btn-secondary" href="{{ route('admin.entity.import.index') }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                    </svg>
                    <span>{{ __('main.import_entities') }}</span>
                </a>

                <a class="btn btn-primary" href="{{ route(Request::segment(1).'.entity.create') }}">
                    <span>{{ __('Create Entity') }}</span>
                </a>

            </div>

        </div>

        <div class="sm:flex flex-row gap-4">

            <x-utility.card-total title="{{ __('entity.entities') }}" :count="$entities->total()"></x-utility.card-total>

            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('admin.entity.index')">
                <x-forms.filter-input-text label="{{ __('entity.designation') }}" name="filter_name"/>
                <x-forms.filter-input-text label="{{ __('entity.member_number') }}" name="member_number"/>
                <x-forms.filter-input-text label="{{ __('entity.id_number') }}" name="filter_member_code"/>
                <x-forms.filter-input-select label="{{ __('entity.zones') }}" name="filter_by_zone" :options="$zones" />
                <x-forms.filter-input-select label="{{ __('entity.district') }}" name="filter_district" :options="$districts" />
                <x-forms.filter-input-select label="{{ __('entity.affiliation_status') }}" name="affiliation_status" :options="$affiliationStatuses" />
                <x-forms.filter-input-select label="{{ __('individuals.cmas_portal') }}" name="has_international_portal" :options="$cmasPortalOptions" />
            </x-filter-form>

        </div>


        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            <x-dynamic-table
                :headers="[
                    __('entity.designation'),
                    __('entity.member_number'),
                    __('entity.id_number'),
                    __('entity.district'),
                    __('entity.zones'),
                    __('entity.affiliation_status'),
                    __('individuals.cmas_portal'),
                    __('entity.table_actions')
                ]">
                @foreach($entities as $entity)
                    @php
                        $mainFederationAffiliation = $entity->entityFederations->first(fn ($ef) => $ef->federation?->is_default_federation);
                        $entityLogo = $entity->getFirstMediaUrl('profile', 'thumb');

                        if ($entity->has_active_validation_plan) {
                            $statusVariant = 'green';
                            $statusName = __('states.active');
                        } elseif ($mainFederationAffiliation) {
                            $statusVariant = match($mainFederationAffiliation->status_class) {
                                \Domain\Entities\States\PendingEntityFederationState::class => 'yellow',
                                \Domain\Entities\States\RejectedEntityFederationState::class => 'red',
                                default => 'gray'
                            };
                            $statusName = match($mainFederationAffiliation->status_class) {
                                \Domain\Entities\States\ActiveEntityFederationState::class => __('states.inactive'),
                                default => $mainFederationAffiliation->stateName(),
                            };
                        } else {
                            $statusVariant = 'gray';
                            $statusName = null;
                        }
                    @endphp
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <div class="flex items-center gap-2">
                                @if($entityLogo)
                                    <img src="{{ $entityLogo }}" alt="{{ $entity->name }}" class="h-8 w-8 rounded-lg object-cover border border-gray-200 dark:border-gray-600 flex-shrink-0">
                                @else
                                    <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center border border-gray-200 dark:border-gray-600 flex-shrink-0">
                                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 16.5v-13h-.25a.75.75 0 010-1.5h12.5a.75.75 0 010 1.5H16v13h.25a.75.75 0 010 1.5h-3.5a.75.75 0 01-.75-.75v-2.5a.75.75 0 00-.75-.75h-2.5a.75.75 0 00-.75.75v2.5a.75.75 0 01-.75.75h-3.5a.75.75 0 010-1.5H4zm3-11a.5.5 0 01.5-.5h1a.5.5 0 01.5.5v1a.5.5 0 01-.5.5h-1a.5.5 0 01-.5-.5v-1zM7.5 9a.5.5 0 00-.5.5v1a.5.5 0 00.5.5h1a.5.5 0 00.5-.5v-1a.5.5 0 00-.5-.5h-1zM11 5.5a.5.5 0 01.5-.5h1a.5.5 0 01.5.5v1a.5.5 0 01-.5.5h-1a.5.5 0 01-.5-.5v-1zm.5 3.5a.5.5 0 00-.5.5v1a.5.5 0 00.5.5h1a.5.5 0 00.5-.5v-1a.5.5 0 00-.5-.5h-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @endif
                                <span class="font-medium text-slate-800 dark:text-slate-100">{{ $entity->name }}</span>
                            </div>
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $entity->member_number ?? '-' }}</td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $entity->member_code ?? '-' }}</td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $entity->district?->name ?? '-' }}</td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $entity->zones->pluck('name')->join(', ') ?: '-' }}</td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @if($statusName)
                                <x-ui.badge :variant="$statusVariant" size="sm">{{ $statusName }}</x-ui.badge>
                            @else
                                -
                            @endif
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @if($entity->has_international_portal)
                                <x-ui.badge variant="green" size="sm">{{ __('individuals.yes') }}</x-ui.badge>
                            @else
                                <x-ui.badge variant="gray" size="sm">{{ __('individuals.no') }}</x-ui.badge>
                            @endif
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end">
                            <div class="space-x-1 flex justify-end items-end">
                                <x-dynamic-table-buttons type="show" :route="route(Request::segment(1).'.entity.show', $entity->id)" :target="'_blank'" title="{{ __('entities.view') }}"/>
                                <x-dynamic-table-buttons type="edit" :route="route(Request::segment(1).'.entity.edit', $entity->id)" :target="'_blank'" title="{{ __('entities.edit') }}"/>
                                <x-dynamic-table-buttons type="delete"
                                                         :route="route(Request::segment(1).'.entity.destroy', $entity->id)"
                                                         title="{{ __('entities.delete') }}"
                                                         method="DELETE"/>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-dynamic-table>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{$entities->links()}}
        </div>

    </div>
</x-layout>
