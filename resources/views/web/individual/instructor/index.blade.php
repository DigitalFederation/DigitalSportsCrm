@section('title', __('instructor.instructors_and_leaders_entities'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('instructor.instructors_and_leaders_entities') }}</h1>
            </div>
        </div>

        <x-information-box
            :title="__('instructor.information')"
            :body="__('instructor.information_body')">
        </x-information-box>

        <!-- Pending Generic Invites -->
        @if(!empty($pendingGenericInvites) && $pendingGenericInvites->count() > 0)
            <div class="mb-6 p-4 border border-yellow-300 rounded-lg bg-slate-50">
                <h2 class="text-lg font-semibold text-gray-800 mb-3">{{ __('instructor.pending_invitations') }}</h2>
                <div class="space-y-3">
                    @foreach($pendingGenericInvites as $genericInvite)
                        <div
                            class="flex flex-col sm:flex-row justify-between items-center p-3 bg-white rounded border border-gray-200 shadow-sm">
                            <div class="mb-2 sm:mb-0">
                                <span class="font-medium">{{ $genericInvite->entity->name }}</span>
                                <span class="text-sm text-gray-500">({{ ucfirst(strtolower($genericInvite->committee_code)) }})</span>
                            </div>
                            <div class="flex space-x-2">
                                <a href="{{ $genericInvite->accept_url }}"
                                   class="btn-sm btn btn-primary text-white whitespace-nowrap">
                                    {{ __('Accept') }}
                                </a>
                                <a href="{{ $genericInvite->reject_url }}"
                                   class="btn-sm btn btn-danger text-white whitespace-nowrap">
                                    {{ __('Reject') }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- More actions -->
        @if(!empty($invites) && $invites->count() > 0)
            <div class="sm:flex sm:justify-center sm:items-center mb-5">
                <x-dynamic-table
                    :headers="[__('instructor.table.entity'), __('instructor.table.member_number'), __('instructor.table.email'), __('instructor.table.type'), __('instructor.table.acceptance_date'), __('instructor.table.status'), '']">
                    @foreach($invites as $instructor)
                        @php
                            $isActive = $instructor->status_class === \Domain\Entities\States\ActiveEntityProfessionalRoleState::class;
                            $isPending = $instructor->status_class === \Domain\Entities\States\PendingEntityProfessionalRoleState::class;
                        @endphp
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">{{ $instructor->entity->name }}</td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">{{ $instructor->entity->member_number }}</td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">{{ $instructor->entity->email }}</td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">{{ $instructor->role_name }}</td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                @if($isActive)
                                    {{ $instructor->updated_at->format('d/m/Y') }}
                                @else
                                    <span class="text-slate-500">-</span>
                                @endif
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <x-tables.badge :status="$instructor->stateName()" :color="$instructor->stateColor()" />
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="space-x-1 flex items-center justify-end">

                                    @if($isPending)
                                        <form action="{{ route('individual.instructor.response', $instructor->id) }}"
                                              onsubmit="return confirm('{{ __('instructor.confirm_action') }}')"
                                              method="POST">
                                            @csrf
                                            @method('PUT')

                                            <input type="hidden" name="status_class"
                                                   value="{{ \Domain\Entities\States\ActiveEntityProfessionalRoleState::class }}">

                                            <button type="submit"
                                                    class="btn-sm btn-outline border-green-500 text-green-500"
                                                    title="{{ __('Accept') }}">
                                                {{ __('Accept') }}
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('individual.entity.show', $instructor->entity) }}"
                                       target="_blank"
                                       class="btn-sm btn-outline border-indigo-500 text-indigo-500 hover:bg-indigo-50"
                                       title="{{ __('instructor.view_entity') }}">
                                        {{ __('instructor.view_entity') }}
                                    </a>

                                    <button @click="$dispatch('open-deactivation-modal', { id: {{ $instructor->id }} })"
                                            type="button"
                                            class="btn-sm btn-outline border-rose-500 text-rose-500 hover:bg-rose-50"
                                            title="{{ __('instructor.disassociate') }}">
                                        {{ __('instructor.disassociate') }}
                                    </button>

                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>
            </div>
        @else
            <x-utility.no-data></x-utility.no-data>
        @endif

        <!-- Pagination -->
        <div class="mt-8">
            {{$invites->links()}}
        </div>
    </div>

    <!-- Include deactivation modals for each instructor -->
    @foreach ($invites as $instructor)
        <x-professional-deactivation-modal
            :professional-id="$instructor->id"
            :professional-name="$instructor->entity->name . ' - ' . $instructor->role_name"
            :action="route('individual.instructor.delete', $instructor->id)" />
    @endforeach
</x-layout>
