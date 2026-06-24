<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('individuals.federation_and_organizations') }}</h1>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('individual.federation-request.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="ml-2">{{ __('federation.request_association') }}</span>
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            <x-dynamic-table
                :headers="[__('individuals.federation_id'), __('entity.designation'), __('individuals.membership_status'), '']">
                @foreach($federations as $federation)
                    <tr>
                        <td class="px-4 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            {{ $federation->member_code }}
                        </td>

                        <td class="px-4 py-3 text-left">
                            {{ $federation->legal_name }}
                        </td>

                        <td class="px-4 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            @php
                                $individualFederation = $federation->individualFederations->first();
                                $isApproved = $individualFederation->isActive();
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

                        <td class="px-4 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1">
                                <x-dynamic-table-buttons
                                    type="show"
                                    :route="route('individual.federation.show', $federation->id)"
                                ></x-dynamic-table-buttons>
                                @unless($federation->is_default_federation)
                                    <x-dynamic-table-buttons
                                        type="disassociate"
                                        method="DELETE"
                                        :route="route('individual.federation.delete', $federation->id)"
                                        :confirmText="__('individuals.confirm_disassociate_federation')"
                                    ></x-dynamic-table-buttons>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-dynamic-table>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $federations->links() }}
        </div>
    </div>
</x-layout>
