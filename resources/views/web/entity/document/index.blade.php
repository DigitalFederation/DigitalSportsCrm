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
        <x-filter-form :post="route('entity.document.index')">
            <x-forms.filter-input-select :label="__('documents.category')" name="owner_type" :options="$filter_owner_types" />
            <x-forms.filter-input-select :label="__('documents.status')" name="filter_status" :options="$filter_status" />
        </x-filter-form>


        <div class="sm:flex sm:justify-center sm:items-center mb-5 mt-8">

            <x-dynamic-table
                :headers="[__('documents.number'), __('documents.date_label'), __('documents.type'), __('documents.status'), ['text'=>__('documents.total'), 'alignment'=>'text-right'], '']">
                @foreach($documents as $document)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @if($document->stateName() == 'paid')
                                {{ $document->invoice_extended }}
                            @else
                                {{ $document->number_extended }}
                            @endif
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            {{ \Carbon\Carbon::parse($document->created_at)->format('d/m/Y') }}
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px ">
                            {{-- Display the document's details --}}
                            <div class="flex flex-col gap-y-1">
                                @foreach ($document->owner_type_names as $ownerTypeName)
                                    <x-ux-badge-component :status="__('documents.categories.' . ucfirst($ownerTypeName), [], app()->getLocale()) !== 'documents.categories.' . ucfirst($ownerTypeName) ? __('documents.categories.' . ucfirst($ownerTypeName)) : ucfirst($ownerTypeName)" color="gray-400" />
                                @endforeach
                            </div>

                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <x-ux-badge-component :status="__('documents.states.' . $document->stateName())"
                                                  :color="$document->stateColor()" />
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-right">

                            {{ money($document->total_value, $document->currency) }}
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end">
                            <div class="space-x-1 flex justify-end items-end">
                                @if($document->moloniInvoice)
                                    <x-dynamic-table-buttons type="document.moloni"
                                                             :route="route('entity.document.moloni-pdf', $document->id)"
                                                             target="_blank" />
                                @endif
                                <x-dynamic-table-buttons type="document.pdf"
                                                         :route="route('entity.document.download', $document->id)"
                                                         target="_blank" />
                                <x-dynamic-table-buttons type="show"
                                                         :route="route('entity.document.show', $document->id)" />
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
