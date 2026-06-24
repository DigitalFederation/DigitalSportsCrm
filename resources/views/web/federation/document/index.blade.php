@section('title', __('documents.payment_documents'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('documents.payment_documents') }}</h1>
            </div>
        </div>

        <!-- FILTER RESULTS -->
        <x-filter-form :post="route('federation.document.index')">
            <x-forms.filter-input-select label="{{ __('main.status') }}" name="filter_status" :options="$filter_status" />
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
            <x-forms.filter-input-select label="{{ __('main.Category') }}" name="owner_type" :options="$filter_owner_types" />
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

        <div class="sm:flex sm:justify-center sm:items-center mb-5 mt-4">

            <x-dynamic-table
                :headers="[__('documents.number'), __('documents.organization'), __('main.Date'), __('main.Category'), __('main.status'), ['text'=>__('main.Total'), 'alignment'=>'text-right'], __('main.Actions')]">
                @foreach($documents as $document)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <a href="{{ route('federation.document.show', $document->id) }}"
                               class="text-slate-600 hover:text-slate-500 hover:underline font-medium">
                                @if($document->stateName() == 'paid')
                                    {{ $document->invoice_extended }}
                                @else
                                    {{ $document->number_extended }}
                                @endif
                            </a>
                        </td>
                        <td class="px-2 py-3 whitespace-wrap w-px text-center md:text-left">
                            {{ $document->getOrganizationName() }}
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            {{ \Carbon\Carbon::parse($document->created_at)->format('d/m/Y') }}
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <div class="flex flex-col gap-y-1">
                                @foreach ($document->owner_type_names as $ownerTypeName)
                                    @if($ownerTypeName != 'ShippingMethod')
                                        <x-ux-badge-component :status="ucfirst($ownerTypeName)" color="gray-400" />
                                    @endif
                                @endforeach
                            </div>
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <x-ux-badge-component :status="str_replace('_', ' ', ucfirst($document->stateName()))"
                                                  :color="$document->stateColor()" />
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-right">
                            {{ number_format($document->total_value, 2, ',', '.') }}€
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end">
                            <div class="space-x-1 flex justify-end items-end">
                                <x-dynamic-table-buttons type="document.pdf"
                                                         :route="route('federation.document.download', $document->id)"
                                                         target="_blank" />
                                <x-dynamic-table-buttons type="show"
                                                         :route="route('federation.document.show', $document->id)" />
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
