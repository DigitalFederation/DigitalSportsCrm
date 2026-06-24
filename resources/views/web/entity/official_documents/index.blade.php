@section('title', __('official_documents.entity_legal_documentation'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title">{{ __('official_documents.entity_legal_documentation') }}</h1>
        </div>

        <x-information-box title="{{ __('official_documents.entity_documents') }}"
            body="{{ __('official_documents.entity_documents_info') }}"></x-information-box>

        <div class="sm:space-x-4">
            <livewire:entity.official-documents-upload :entity="$entity" :types="$official_document_types" :federations="$federations" />
        </div>

        @if (!empty($official_documents) && $official_documents->count() > 0)
            <x-dynamic-table :headers="[
                __('official_documents.type'),
                __('official_documents.issue_date'),
                __('official_documents.expiration_date'),
                __('official_documents.status'),
                ''
            ]">
                @foreach ($official_documents as $document)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 text-left">
                            <div class="flex items-center gap-2">
                                @php
                                    $mediaItem = $document->media->first();
                                @endphp
                                @if ($mediaItem)
                                    @if (str_starts_with($mediaItem->mime_type, 'image/'))
                                        <x-heroicon-o-photo class="w-4 h-4 text-blue-500 flex-shrink-0" />
                                    @elseif($mediaItem->mime_type === 'application/pdf')
                                        <x-heroicon-o-document-text class="w-4 h-4 text-red-500 flex-shrink-0" />
                                    @else
                                        <x-heroicon-o-document class="w-4 h-4 text-gray-500 flex-shrink-0" />
                                    @endif
                                @endif
                                <span>{{ \App\Enums\OfficialDocumentTypeEnum::toString($document->type) }}</span>
                            </div>
                        </td>
                        <td class="px-2 py-3 whitespace-nowrap text-left">
                            @if (!empty($document->issue_date))
                                {{ date('d/m/Y', strtotime($document->issue_date)) }}
                            @else
                                <span class="text-gray-400">--</span>
                            @endif
                        </td>
                        <td class="px-2 py-3 whitespace-nowrap text-left">
                            @if (!empty($document->expiry_date))
                                {{ date('d/m/Y', strtotime($document->expiry_date)) }}
                            @else
                                <span class="text-gray-400">--</span>
                            @endif
                        </td>
                        <td class="px-2 py-3 text-left">
                            <x-tables.badge :status="ucfirst($document->stateName())" :color="$document->stateColor()" />
                        </td>

                        <td class="px-2 py-3 text-right flex items-center gap-x-2 justify-end">
                            @if($document->media->count() > 0)
                                <a href="{{ route('entity.official-documents.download', $document->id) }}"
                                   class="btn btn-sm btn-secondary"
                                   title="{{ __('official_documents.download') }}">
                                    <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                                </a>
                            @endif

                            <form method="POST" action="{{ route('entity.official-documents.delete', $document->id) }}"
                                  onsubmit="return confirm('{{ __('official_documents.confirm_delete') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="{{ __('official_documents.delete') }}">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </x-dynamic-table>
        @else
            <x-utility.no-data></x-utility.no-data>
        @endif

    </div>
</x-layout>