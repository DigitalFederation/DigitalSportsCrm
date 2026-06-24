<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title">{{ __('menu.entity.federation_organizations') }}</h1>
        </div>

        <x-information-box
            :title="__('main.information')"
            :body="__('entity.federation_membership_info')"
        />

        <!-- More actions -->
        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            <!-- Table -->
            <x-dynamic-table
                :headers="[__('entity.designation'), __('individuals.membership_status'), '']">
                @foreach($federations as $federation)
                    <tr>
                        <td class="px-2 py-3 whitespace-wrap text-left">{{ $federation->legal_name }}</td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @php
                                $entityFederation = $federation->entityFederations->first();
                                $isApproved = $entityFederation->isActive();
                            @endphp
                            @if($isApproved)
                                <x-tables.badge
                                    :status="__('entity.member_approved')"
                                    color="active-state" />
                            @else
                                <x-tables.badge
                                    :status="__('entity.member_pending_approval')"
                                    color="pending-state" />
                            @endif
                        </td>

                        <td class="px-3 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end content-end">
                            <div class="space-x-1 flex items-end justify-end content-end">
                                <x-dynamic-table-buttons
                                    type="show"
                                    :route="route('entity.federation.show', $federation->id)"
                                ></x-dynamic-table-buttons>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-dynamic-table>

        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{$federations->links()}}
        </div>
    </div>
</x-layout>