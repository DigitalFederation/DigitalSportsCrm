@section('title', __('My Official Documents'))

<x-layout>
    <div class="previous-layout-classes">

        <div class="sm:space-x-4">
            <livewire:official-documents-federation-upload :types="$official_document_types" :federations="collect([$federation])" :individuals="null"
                :role="null" />
        </div>

        <!-- FILTER RESULTS -->

        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            @if (!empty($documents) && $documents->count() > 0)

                <x-dynamic-table :headers="[__('official_documents.filename'), __('main.Type'), __('official_documents.date_sent'), __('official_documents.issue_date'), __('official_documents.expiration_date'), __('main.status'), '']">
                    @foreach ($documents as $document)
                        <tr x-data="{ showModal: false }" x-on:keydown.window.escape="showModal = false">

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-wrap w-px">
                                <!-- Get file name -->
                                @if ($document->getMedia('media')->first())
                                    {{ $document->getMedia('media')->first()->file_name }}
                                @else
                                    <span class="text-xs">{{ __('official_documents.no_file') }}</span>
                                @endif
                            </td>

                            <td class="px-2 py-3 whitespace-nowrap w-px text-center md:text-left">
                                {{ \App\Enums\OfficialDocumentTypeEnum::toString($document->type) }}
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ \Carbon\Carbon::parse($document->created_at)->format('d/m/Y') }}
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-left">
                                @if (!empty($document->issue_date))
                                    {{ date('d/m/Y', strtotime($document->issue_date)) }}
                                @else
                                    <span class="text-xs">--</span>
                                @endif
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-left">
                                @if (!empty($document->expiry_date))
                                    {{ date('d/m/Y', strtotime($document->expiry_date)) }}
                                @else
                                    <span class="text-xs">--</span>
                                @endif
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <x-tables.badge :status="ucfirst($document->stateName())" :color="$document->stateColor()" />
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                @if ($document->individual)
                                    <a href="{{ route('federation.individual.show', $document->individual->id) }}"
                                        target="_blank" class="hover:text-cyan-600 flex gap-x-2 items-center">
                                        <x-svg.box-arrow-up-right class="w-4 h-4"></x-svg.box-arrow-up-right>
                                        <span>{{ $document->individual->full_name }}
                                            ({{ $document->individual->member_code }})</span>
                                    </a>
                                @else
                                    <span class="text-gray-400">{{ __('official_documents.individual_not_found') }}</span>
                                @endif
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end">
                                <div class=" flex justify-end items-center gap-x-2">
                                    <x-dynamic-table-buttons type="download" method="POST"
                                        route="{{ route('federation.my-official-documents.download', $document->id) }}"
                                        title="{{ __('Download') }}"
                                        class="text-green-500 hover:text-green-600 rounded-full cursor-pointer"></x-dynamic-table-buttons>





                                    <x-dynamic-table-buttons type="delete" method="DELETE"
                                        route="{{ route('federation.official-documents.delete', $document->id) }}"
                                        title="{{ __('Delete') }}"></x-dynamic-table-buttons>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>
            @else
                <x-utility.no-data in_card="true"></x-utility.no-data>
            @endif
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $documents->links() }}
        </div>

    </div>
</x-layout>
