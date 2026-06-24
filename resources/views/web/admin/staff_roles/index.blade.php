@section('title', __('main.cmas_staff_roles'))
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('main.staff_roles') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('admin.professional-roles.index', ['filter' => ['role' => 'STAFF']]) }}"
                   class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                    {{ __('professional_roles.manage_roles') }}
                </a>
            </div>

        </div>

        <div class="sm:flex sm:justify-center sm:items-center">

            <div class="card w-full">
                <h3 class="text-base text-slate-800 font-semibold mb-2">{{ __('main.add_staff_role') }}</h3>
                <form action="{{ route('admin.staff-roles.store') }}" method="POST">
                    @csrf

                    <div class="flex flex-col md:flex-row gap-2 items-end">
                        <div class="w-full md:w-1/3 flex flex-col">
                            <label for="professional_role_id">{{ __('main.staff_role') }}</label>
                            <select name="professional_role_id" id="professional_role_id" class="form-select" required>
                                <option value="">{{ __('main.select_role') }}</option>
                                @foreach ($staff_roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-full md:w-1/3 flex flex-col">
                            <label for="member_code">{{ __('main.member_number') }}</label>
                            <input type="text" name="member_number" id="member_number" class="form-input" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-auto">{{ __('main.assign_role') }}</button>
                    </div>

                </form>
            </div>
        </div>


        <div class="sm:flex flex-row gap-4">
            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('admin.staff-roles.index')">
                <x-forms.filter-input-select :label="__('main.staff_role')" name="professionalRole.id" :options="$staff_roles" />
            </x-filter-form>

        </div>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            @if(!$professional_roles->isEmpty())
                <x-dynamic-table
                    :headers="[__('common.name'),__('main.member_number'),__('common.roles'), '']">
                    @foreach($professional_roles as $professional_role)

                        <tr class="hover:bg-gray-100 transition duration-200 ease-in-out">
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ optional($professional_role->individual)->name }} {{ optional($professional_role->individual)->surname }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $professional_role->individual->member_number }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $professional_role->professionalRole->name }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end">
                                <div class="space-x-1 flex items-center justify-end">

                                    <x-dynamic-table-buttons type="delete"
                                                             :route="route('admin.staff-roles.destroy', $professional_role->id)"
                                                             method="DELETE" />
                                </div>
                            </td>
                        </tr>

                    @endforeach
                </x-dynamic-table>
            @else
                <x-utility.no-data :in_card="true"></x-utility.no-data>
            @endif

        </div>

        <div class="mt-8">
            {{$professional_roles->links()}}
        </div>
    </div>
</x-layout>
