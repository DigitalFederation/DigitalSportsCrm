<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('professional_roles.title') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-primary" href="{{ route('admin.professional-roles.create') }}">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0 mr-2" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z"/>
                    </svg>
                    <span>{{ __('professional_roles.create') }}</span>
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-slate-800 shadow-lg rounded-sm mb-5">
            <div class="p-4">
                <form action="{{ route('admin.professional-roles.index') }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Name Filter -->
                        <div>
                            <label class="block text-sm font-medium mb-1" for="filter_name">{{ __('professional_roles.name') }}</label>
                            <input id="filter_name" class="form-input w-full" type="text" name="filter[name]" value="{{ request('filter.name') }}" placeholder="{{ __('professional_roles.filter_by_name') }}">
                        </div>

                        <!-- Role Type Filter -->
                        <div>
                            <label class="block text-sm font-medium mb-1" for="filter_role">{{ __('professional_roles.role_type') }}</label>
                            <select id="filter_role" class="form-select w-full" name="filter[role]">
                                <option value="">{{ __('common.all') }}</option>
                                @foreach($roleTypes as $roleType)
                                    <option value="{{ $roleType }}" {{ request('filter.role') === $roleType ? 'selected' : '' }}>{{ $roleType }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Committee Filter -->
                        <div>
                            <label class="block text-sm font-medium mb-1" for="filter_committee">{{ __('professional_roles.committee') }}</label>
                            <select id="filter_committee" class="form-select w-full" name="filter[committee_id]">
                                <option value="">{{ __('common.all') }}</option>
                                @foreach($committees as $committee)
                                    <option value="{{ $committee->id }}" {{ request('filter.committee_id') == $committee->id ? 'selected' : '' }}>{{ $committee->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter Buttons -->
                        <div class="flex items-end gap-2">
                            <button type="submit" class="btn btn-primary">{{ __('common.filter') }}</button>
                            <a href="{{ route('admin.professional-roles.index') }}" class="btn bg-slate-500 text-white">{{ __('common.clear') }}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        @if($professionalRoles->isNotEmpty())
            <div class="bg-white dark:bg-slate-800 shadow-lg rounded-sm">
                <x-dynamic-table :headers="[
                    __('professional_roles.name'),
                    __('professional_roles.code'),
                    __('professional_roles.role_type'),
                    __('professional_roles.committee'),
                    __('common.actions')
                ]">
                    @foreach($professionalRoles as $role)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <span class="font-medium text-slate-800 dark:text-slate-100">{{ $role->name }}</span>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <code class="text-sm bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded">{{ $role->code }}</code>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @switch($role->role)
                                        @case('TECHNICAL_OFFICIAL')
                                            bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300
                                            @break
                                        @case('COACH')
                                            bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300
                                            @break
                                        @case('ATHLETE')
                                            bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                                            @break
                                        @case('INSTRUCTOR')
                                            bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300
                                            @break
                                        @default
                                            bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300
                                    @endswitch
                                ">
                                    {{ $role->role }}
                                </span>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                {{ $role->committee?->name ?? '-' }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="gap-x-2 flex justify-end">
                                    <x-dynamic-table-buttons type="edit" :route="route('admin.professional-roles.edit', $role)"/>
                                    <x-dynamic-table-buttons type="delete" :route="route('admin.professional-roles.destroy', $role)" method="DELETE"/>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>

                <!-- Pagination -->
                <div class="p-4">
                    {{ $professionalRoles->links() }}
                </div>
            </div>
        @else
            <x-utility.no-data></x-utility.no-data>
        @endif

    </div>
</x-layout>
