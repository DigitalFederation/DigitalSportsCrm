@section('title', __('common.users'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('common.users') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <a class="btn btn-primary" href="{{ route('admin.user.create') }}">
                    <span>{{ __('common.create_user') }}</span>
                </a>

            </div>

        </div>

        <!-- FILTER RESULTS -->
        <x-filter-form :post="route('admin.users.index')">
            <x-forms.filter-input-text label="common.date" name="filter_date" />
            <x-forms.filter-input-text label="common.email" name="filter_email" />
            <x-forms.filter-input-select label="common.relationship" name="filter_relationship" :options="$filter_relationships" />
            <x-forms.filter-input-select label="common.status" name="filter_status" :options="$filter_status" />
            <x-forms.filter-input-select label="common.federation" name="filter_federation" :options="$federations" />
            <x-forms.filter-input-select label="common.entity" name="filter_entity" :options="$entities" />
            <x-forms.filter-input-select label="common.cmas_admin" name="filter_cmas_admin" :options="$filter_cmas_admin" />
        </x-filter-form>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            <x-dynamic-table
                :headers="[__('common.date'), __('common.email'), __('common.status'), __('common.last_login'), __('common.roles'), __('common.relationship'), __('common.actions')]">
                @foreach ($users as $user)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            {{ $user->created_at->format('d/m/Y') }}
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            {{ $user->email }}
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <x-ui.badge :variant="$user->active ? 'green' : 'red'" size="sm">
                                {{ $user->active ? __('common.active') : __('common.inactive') }}
                            </x-ui.badge>
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('d/m/Y') : __('common.never') }}
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 w-auto">
                            <div class="flex flex-wrap gap-1">
                                @foreach ($user->roles as $role)
                                    <x-ui.badge variant="gray" size="sm">{{ $role->name }}</x-ui.badge>
                                @endforeach
                            </div>
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3">
                            <div class="flex flex-col gap-1">
                                @foreach($user->federations as $federation)
                                    <div class="flex items-center gap-1.5">
                                        <x-ui.badge variant="indigo" size="sm">{{ __('common.federation') }}</x-ui.badge>
                                        <a href="{{ route('admin.federation.show', $federation->id) }}" class="text-sm text-indigo-600 hover:text-indigo-800 truncate max-w-[200px]">
                                            {{ $federation->name }}
                                        </a>
                                    </div>
                                @endforeach
                                @foreach($user->entities as $entity)
                                    <div class="flex items-center gap-1.5">
                                        <x-ui.badge variant="blue" size="sm">{{ __('common.entity') }}</x-ui.badge>
                                        <a href="{{ route('admin.entity.show', $entity->id) }}" class="text-sm text-indigo-600 hover:text-indigo-800 truncate max-w-[200px]">
                                            {{ $entity->name }}
                                        </a>
                                    </div>
                                @endforeach
                                @foreach($user->individuals as $individual)
                                    <div class="flex items-center gap-1.5">
                                        <x-ui.badge variant="purple" size="sm">{{ __('common.individual') }}</x-ui.badge>
                                        <a href="{{ route('admin.individual.show', $individual->id) }}" class="text-sm text-indigo-600 hover:text-indigo-800 truncate max-w-[200px]">
                                            {{ $individual->full_name }}
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end content-end justify-end">
                            <div class="space-x-1 flex items-end content-end justify-end">
                                <x-dynamic-table-buttons type="edit" :route="route('admin.user.edit', $user->id)" />
                                <x-dynamic-table-buttons type="delete" method="DELETE" :route="route('admin.user.delete', $user->id)" />
                                @php
                                    $targetUserGroup = $user->group()->first();
                                @endphp
                                @if (auth()->user()->can('impersonate users') && $targetUserGroup)
                                    <a href="{{ route('admin.impersonate.start', $user->id) }}"
                                       class="btn btn-xs text-white px-2 py-1 bg-amber-500 hover:bg-amber-600 rounded"
                                       title="{{ __('common.are_you_sure') }}"
                                       onclick="return confirm('{{ __('common.are_you_sure') }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-dynamic-table>

        </div>

        <!-- Pagination -->
        <div class="mt-8">
            @if ($users instanceof \Illuminate\Pagination\LengthAwarePaginator)
                {{ $users->links() }}
            @endif
        </div>

    </div>
</x-layout>
