@section('title', __('documents.payment_documents'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('documents.payment_documents') }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ __('documents.payment_documents_disclaimer') }}</p>
            </div>


            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-primary" href="{{ route('admin.document.create') }}">
                    {{ __('documents.create_manual_order') }}
                </a>
            </div>
        </div>

        <!-- FILTER RESULTS -->
        <x-filter-form :post="route('admin.document.index')">
            <x-forms.filter-input-select label="{{ __('documents.filters.status') }}" name="filter_status" :options="$filter_status" />
            <x-forms.filter-input-select
                label="{{ __('documents.filters.type') }}"
                name="filter_member_type"
                :options="$filter_member_types"
            />
            <x-forms.filter-input-select
                label="{{ __('documents.entities') }}"
                name="filter_entity"
                :options="$filter_entities"
            />
            <x-forms.filter-input-select
                label="{{ __('documents.filters.category') }}"
                name="owner_type"
                :options="$filter_owner_types"
            />
            <!-- Nº Filiado -->
            <x-forms.filter-input-text
                label="{{ __('main.Member Code') }}"
                name="filter_member_code"
                helpText="{{ __('documents.filter_member_code_help') }}"
            />
            <x-forms.filter-input-text label="{{ __('documents.total') }}" name="filter_total" />
            <x-forms.filter-input-text
                label="{{ __('documents.member') }}"
                name="filter_member_name"
                placeholder="{{ __('documents.filter_member_placeholder') }}"
            />

            <x-forms.filter-input-select
                label="{{ __('documents.year') }}"
                name="filter_years"
                :options="$filter_years"
            />
            <x-forms.filter-input-text
                label="{{ __('documents.document_number') }}"
                name="filter_number"
                placeholder="e.g. 2024-001"
            />
            <x-forms.filter-input-date-range
                label="documents.document_period"
                nameStart="filter_date_start"
                nameEnd="filter_date_end"
            />

        </x-filter-form>

        <!-- More actions -->
        @if(Request::segment(3) != 'filter')
            <div class="mb-2 sm:mb-0">
                <h2 class="grow font-semibold text-slate-800 truncate">{{ __('documents.latest_documents')}}</h2>
            </div>
        @else
            <div class="mb-2 sm:mb-0">
                <h2 class="grow font-semibold text-slate-800 truncate">{{ __('documents.filtered_results')}}</h2>
            </div>
        @endif

        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            <x-dynamic-table
                :headers="[
                    __('documents.id'),
                    __('documents.created_at'),
                    __('documents.member'),
                    __('documents.category'),
                    __('documents.status'),
                    __('documents.payment_date'),
                    __('documents.total'),
                    __('main.actions')
                ]">

                @foreach($documents as $document)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <a href="{{ route('admin.document.show', $document->id)}}"
                               class="text-slate-600 hover:text-slate-500 hover:underline">
                                {{ $document->number_extended }}
                            </a>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-sm">
                            {{ \Carbon\Carbon::parse($document->created_at)->format('d-m-Y') }}
                        </td>
                        <td class="px-2 py-3 min-w-[200px] max-w-[300px]">
                            <div class="text-sm font-medium text-gray-900 break-words">
                                {{ $document->getOrganizationName() }}
                            </div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            @foreach ($document->owner_type_names as $ownerTypeName)
                                @if($ownerTypeName != 'ShippingMethod')
                                    @php
                                        $categoryLabel = __('documents.categories.' . $ownerTypeName) !== 'documents.categories.' . $ownerTypeName
                                            ? __('documents.categories.' . $ownerTypeName)
                                            : $ownerTypeName;
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 mb-1">
                                        {{ $categoryLabel }}
                                    </span><br>
                                @endif
                            @endforeach
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            @php
                                $stateName = $document->stateName();
                                $stateColor = match($stateName) {
                                    'paid' => 'bg-emerald-100 text-emerald-600',
                                    'pending' => 'bg-amber-100 text-amber-600',
                                    'draft' => 'bg-slate-100 text-slate-500',
                                    'canceled' => 'bg-rose-100 text-rose-600',
                                    'partially_paid' => 'bg-blue-100 text-blue-600',
                                    default => 'bg-slate-100 text-slate-500'
                                };
                                $stateLabel = __('documents.states.' . $stateName);
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $stateColor }}">
                                {{ $stateLabel }}
                            </span>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-sm">
                            @if($document->isPaid() && $document->transactions->isNotEmpty())
                                {{ $document->transactions->sortByDesc('created_at')->first()->created_at->format('d-m-Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-sm font-medium">
                            {{ $document->total_value }}€
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="space-x-1 flex justify-end">
                                <a href="{{ route('admin.document.download', $document->id)}}" target="_blank"
                                   class="text-slate-400 hover:text-slate-500 rounded-full">
                                    <x-svg.filetype-pdf class="h-5 w-5" />
                                </a>
                                <x-dynamic-table-buttons type="show"
                                                         :route="route(Request::segment(1).'.document.show', $document->id)" />
                                @if($document->isDraft() || $document->isPending())
                                    <x-dynamic-table-buttons type="edit"
                                                             :route="route(Request::segment(1).'.document.edit', $document->id)" />
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach

            </x-dynamic-table>

        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{$documents->links()}}
        </div>

    </div>
</x-layout>
