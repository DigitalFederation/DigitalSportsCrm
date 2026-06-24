<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('Local Organization Memberships') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <a class="btn btn-primary" href="{{ route('federation.local-membership-plan.create') }}">
                    <span>{{ __('Add Memberships to Organizations') }}</span>
                </a>
            </div>
        </div>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            <!-- Table -->
            @if(!empty($localFederations) && $localFederations->count() > 0)
                <x-dynamic-table
                    :headers="[__('main.organization'), __('memberships.title'), __('main.Actions')]">
                    @foreach($localFederations as $localFederation)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $localFederation->name }}</td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 flex gap-2 flex-wrap">
                                @foreach($localFederation->localMembershipPlan as $association)
                                    <div class="bg-slate-200 px-2 py-1 w-auto rounded">{{ $association->membershipPlan->name }}</div>
                                @endforeach
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-right">
                                <div class="flex justify-end">
                                    <x-dynamic-table-buttons  type="edit" :route="route('federation.local-membership-plan.edit', $localFederation->id)" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>
            @else
                <x-utility.no-data></x-utility.no-data>
            @endif
        </div>


    </div>
</x-layout>
