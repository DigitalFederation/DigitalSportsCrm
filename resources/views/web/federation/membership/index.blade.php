@section('title', __('memberships.title'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('memberships.title') }}</h1>
            </div>

            @if(empty(auth()->user()->federations()->first()->parent_id))
                <div class="btn btn-primary">
                    <a href="{{ route('federation.local-membership-plan.index') }}">{{ __('memberships.organizations_membership_association') }}</a>
                </div>
            @endif

        </div>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            <!-- Table -->

            <x-dynamic-table
                :headers="[__('memberships.name'), __('memberships.plans'), __('memberships.status'), __('memberships.expiration_date')]">

                @foreach($memberships as $membership)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $membership->name }}</td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @if($membership->plans->count() > 0)
                                <div class="flex flex-col gap-y-1">
                                    @foreach($membership->plans as $plan)
                                        <x-tables.badge :status="$plan->name" color="default" />
                                    @endforeach
                                </div>
                            @endif
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <x-tables.badge :status="ucfirst($membership->stateName())"
                                            :color="$membership->stateColor()" />
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-right">
                            @if($membership->current_term_ends_at)
                                <x-tables.badge :status="date('d-m-Y', strtotime($membership->current_term_ends_at))"
                                                color="default" />
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-dynamic-table>

        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{$memberships->links()}}
        </div>

    </div>
</x-layout>
