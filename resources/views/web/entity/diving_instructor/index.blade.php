@section('title', __('diving.instructor_leader_associated_entities'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('diving.diving_instructor_leaders') }}</h1>
                <p> {{ __('diving.associated_to') }} {{ auth()->user()->entities()?->first()?->name }} </p>
            </div>
        </div>

        <!-- FILTER RESULTS -->
        <div class="sm:flex flex-row gap-4 items-start">

            <div class="order-first md:order-last flex shadow-md my-5 information-box items-start rounded-lg bg-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 md:h-6 md:w-6 mr-4" width="24" height="24"
                     viewBox="0 0 24 24" stroke-width="1.5" stroke="#9e9e9e" fill="none" stroke-linecap="round"
                     stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                    <circle cx="12" cy="12" r="9"></circle>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    <polyline points="11 12 12 12 12 16 13 16"></polyline>
                </svg>
                <p class="text-xs md:text-sm">
                    {{ __('diving.instructor_invitation_info') }}
                    <br>
                    {{ __('diving.instructor_invitation_process') }}<br><br>

                    <strong>{{ __('diving.conditions') }}:</strong><br>
                    ▪ {{ __('diving.condition_active_license') }}<br>
                    ▪ {{ __('diving.condition_active_certification') }}<br>
                    ▪ {{ __('diving.condition_same_federation') }}<br>
                    ▪ {{ __('diving.condition_multiple_entities') }}<br>
                </p>
            </div>
        </div>

        <!-- Pending Generic Invitations Sent -->
        @isset($pendingInvitations)
            @if ($pendingInvitations->count() > 0)
                <div class="mb-6 mt-4 p-4 border border-blue-300 rounded-lg bg-slate-50">
                    <h2 class="text-lg text-gray-800 mb-3">{{ __('diving.pending_invitations_sent') }}</h2>
                    <p class="text-sm text-gray-600 mb-4">
                        {{ __('diving.pending_invitations_description') }}
                    </p>
                    <div class="space-y-3">
                        @foreach ($pendingInvitations as $pendingInvite)
                            <div
                                class="flex flex-col sm:flex-row justify-between items-center p-3 bg-white rounded border border-gray-200 shadow-sm">
                                <div class="mb-2 sm:mb-0 flex items-center gap-x-2">
                                    <div
                                        class="font-medium">
                                        {{ $pendingInvite->individual?->name }} {{ $pendingInvite->individual?->surname }}
                                    </div>
                                    @if($pendingInvite->individual?->member_code)
                                        <span
                                            class="text-sm text-gray-500">({{ $pendingInvite->individual->member_code }})</span>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-600">
                                    <span
                                        class="font-bold">{{ __('diving.invitation_sent') }}:</span> {{ $pendingInvite->created_at->format('d/m/Y') }}
                                    @if ($pendingInvite->expires_at)
                                        <span class="text-xs">
                                        ({{ __('diving.invitation_expire') }}
                                        : {{ $pendingInvite->expires_at->format('d/m/Y') }})
                                        </span>
                                    @endif
                                </div>

                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endisset

        <!-- New Filament Table Component -->
        <div class="mt-8">
            <livewire:manage-entity-instructors :professionalRoles="$professionalRoles" />
        </div>

        {{-- Removed old instructor table --}}
        {{-- @if ($instructors->count() > 0)
            @if (Request::segment(3) != 'filter')
                <div class="mb-2 sm:mb-0">
                    <h2 class="grow font-semibold text-slate-800 truncate">{{ __('List of Instructors') }}</h2>
                </div>
            @endif

            <div class="sm:flex sm:justify-center sm:items-center mb-5">
                <x-dynamic-table :headers="['International Code', 'Name', 'Location', 'Email', 'Sent', 'Instructor Type', 'Status', '']">
                    @foreach ($instructors as $instructor)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $instructor->individual?->member_code }}</td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $instructor->individual->name }} {{ $instructor->individual->surname }}</td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="flex items-center">
                                    <img src="{{ asset('img/flags/' . strtolower($instructor->individual->country->iso) . '.svg') }}"
                                        class="w-4 h-4 mr-1" />
                                    {{ $instructor->individual->country->name }}
                                </div>
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $instructor->individual->email }}</td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ \Carbon\Carbon::parse($instructor->created_at)->longRelativeDiffForHumans() }}</td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $instructor->role_name }}</td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <x-tables.badge :status="ucfirst($instructor->stateName())" :color="$instructor->stateColor()" />
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px justify-end items-center">
                                <div class="space-x-1 flex items-center justify-end">
                                    <x-dynamic-table-buttons type="show" :route="route(
                                        Request::segment(1) . '.individual.show',
                                        $instructor->individual->id,
                                    )" />

                                    <x-dynamic-table-buttons type="delete" method="DELETE" :route="route(
                                        Request::segment(1) . '.diving-instructor.delete',
                                        $instructor->id,
                                    )" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $instructors->links() }}
            </div>
        @else
            <x-utility.no-data />
        @endif --}}

    </div>
</x-layout>
