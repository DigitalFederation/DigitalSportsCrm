<x-layout>

    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0 flex items-center">


                <h1 class="page-first-title">
                    {{ __('Attribute Rule list for') }} {{ $attribute->name }}
                </h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-info bg-slate-600" href="{{ url()->previous() }}"> {{ __('Back') }}</a>

                <a class="btn btn-info"
                   @if (! empty($attribute)) href="{{ route('admin.evt-events.attribute-rules.create', ['attribute' => $attribute]) }}"
                   @else
                       href="{{ route('admin.evt-events.attribute-rules.create') }}" @endif>
                    <x-svg.plus class="w-4 h-4 fill-current opacity-50 flex-shrink-0" />
                    <span class="ml-2">{{ __('Create Attribute Rule') }}</span>
                </a>

            </div>

        </div>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            <!-- Table -->
            @if (! empty($attributeRules) && $attributeRules->count() > 0)
                <div class="bg-white shadow-lg rounded-sm border border-slate-200 mb-8 w-full">
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
                                            {{ \App\Enums\EvtAttributeRuleOperatorsEnum::getValueFromName($rule->operator) }}
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

                                            <a title="{{ __('Edit') }}" class="text-slate-400 hover:text-slate-500"
                                               href="{{ route('admin.evt-events.attribute-rules.edit', ['attribute' => $attribute, 'attribute_rule' => $rule]) }}">
                                                <span class="sr-only">{{ __('Edit') }}</span>
                                                <x-svg.edit class="w-4 h-4 fill-current" />
                                            </a>

                                            <form class="flex"
                                                  action="{{ route('admin.evt-events.attribute-rules.destroy', ['attribute' => $attribute, 'attribute_rule' => $rule]) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('{{ __('Are you sure you want to delete this event?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-rose-500 hover:text-rose-600 rounded-full">
                                                    <span class="sr-only">{{ __('Delete') }}</span>
                                                    <x-svg.trash class="w-4 h-4 fill-current" />
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
</x-layout>
