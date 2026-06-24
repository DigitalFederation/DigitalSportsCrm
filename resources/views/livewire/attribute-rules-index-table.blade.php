<div class="previous-layout-classes">

    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-4">

        <!-- Left: Title -->
        <div class="mb-4 sm:mb-0 flex items-center">
            <h1 class="text-xl text-slate-800 font-bold">
                {{ __('Rules for: ') }}
            </h1>
            <div class="text-slate-700 text-lg">{{ $attribute->name }}</div>
        </div>

        <!-- Right: Actions -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <a class="btn btn-info btn-sm"
               @if (!empty($attribute)) href="{{ route('admin.evt-events.attribute-rules.create', ['attribute' => $attribute]) }}"
               @else
                   href="{{ route('admin.evt-events.attribute-rules.create') }}" @endif>
                <span>{{ __('Create Rule') }}</span>
            </a>

        </div>

    </div>

    <div class="sm:flex sm:justify-center sm:items-center mb-5">

        <!-- Table -->
        @if (!empty($attributeRules) && $attributeRules->count() > 0)
            <div class="bg-white shadow-sm rounded-sm border border-slate-200 mb-8 w-full">
                <div class="overflow-x-auto">
                    <table class="table-auto w-full">
                        <!-- Table header -->
                        <thead
                            class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3">
                                <div class="font-semibold text-left">{{ __('Rule ID') }}</div>
                            </th>

                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('Rule operator') }}</div>
                            </th>

                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('Default value') }}</div>
                            </th>

                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('Comparison field') }}</div>
                            </th>

                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-right">{{ __('Actions') }}</div>
                            </th>
                        </tr>
                        </thead>
                        <!-- Table body -->
                        <tbody class="text-sm divide-y divide-slate-200">
                        <!-- Row -->
                        @foreach ($attributeRules as $rule)
                            <tr>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    <div class="inline-flex gap-2 items-center">
                                        {{ $rule->id }}
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    <div class="inline-flex gap-2 items-center">
                                        {{ $rule->operator }}
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    <div class="inline-flex gap-2 items-center">
                                        {{ $rule->default_value }}
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    <div class="inline-flex gap-2 items-center">
                                        {{ $rule->comparison_field }}
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end">
                                    <div class="space-x-1 flex justify-end items-center">

                                        <a title="Edit" class="text-slate-400 hover:text-slate-500"
                                           href="{{ route('admin.evt-events.attribute-rules.edit', ['attribute' => $attribute, 'attribute_rule' =>$rule]) }}">
                                            <span class="sr-only">{{ __('Edit') }}</span>
                                            <x-svg.edit class="w-5 h-5 fill-current" />
                                        </a>

                                        <form class="flex"
                                              action="{{ route('admin.evt-events.attribute-rules.destroy',  ['attribute' => $attribute, 'attribute_rule' =>$rule]) }}"
                                              method="POST"
                                              onsubmit="return confirm('{{ __('Are you sure you want to delete this event?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-rose-500 hover:text-rose-600 rounded-full">
                                                <span class="sr-only">{{ __('Delete') }}</span>
                                                <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                                    <path d="M13 15h2v6h-2zM17 15h2v6h-2z" />
                                                    <path
                                                        d="M20 9c0-.6-.4-1-1-1h-6c-.6 0-1 .4-1 1v2H8v2h1v10c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V13h1v-2h-4V9zm-6 1h4v1h-4v-1zm7 3v9H11v-9h10z" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <p class="text-gray-400 text-2xl mt-12">{{ __('No attributes added yet') }}</p>
        @endif

    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $attributeRules->links() }}
    </div>

</div>

