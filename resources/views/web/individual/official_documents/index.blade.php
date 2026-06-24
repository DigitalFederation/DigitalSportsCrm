@section('title', __('official_documents.role_documentation', ['role' => $title]))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8">
            <h1 class="page-first-title">{{ __('official_documents.role_documentation', ['role' => $title]) }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('official_documents.role_documentation_subtitle', ['role' => strtolower($title)]) }}</p>
        </div>

        <div id="official-document-form" class="sm:space-x-4">
            <livewire:official-documents-upload :individual="$individual" :types="$official_document_types" :federations="$federations"
                :role="$role" />
        </div>

        @if (!empty($official_documents) && $official_documents->count() > 0)
            <x-dynamic-table :headers="[
                __('documents.type'),
                __('official_documents.date_sent'),
                __('documents.issue_date'),
                __('documents.expiration_date'),
                __('documents.status'),
                '',
            ]">
                @foreach ($official_documents as $document)
                    @php
                        $mediaItem = $document->getMedia('media')->first();
                        $canPreview = $mediaItem && in_array($mediaItem->mime_type, ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp']);
                    @endphp
                    <tr x-data="{ showPreview: false }"
                        x-on:keydown.window.escape="showPreview = false">

                        {{-- Type column with file icon --}}
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-left">
                            <div class="flex items-center gap-2">
                                @if ($mediaItem)
                                    @if (str_starts_with($mediaItem->mime_type, 'image/'))
                                        <x-heroicon-o-photo class="w-4 h-4 text-blue-500 flex-shrink-0" />
                                    @elseif($mediaItem->mime_type === 'application/pdf')
                                        <x-heroicon-o-document-text class="w-4 h-4 text-red-500 flex-shrink-0" />
                                    @else
                                        <x-heroicon-o-document class="w-4 h-4 text-gray-500 flex-shrink-0" />
                                    @endif
                                @endif
                                <span class="truncate max-w-[200px]">
                                    {{ \App\Enums\OfficialDocumentTypeEnum::toString($document->type) }}
                                </span>
                            </div>
                        </td>

                        {{-- Date Sent column --}}
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-left">
                            <div class="text-sm">
                                {{ \Carbon\Carbon::parse($document->created_at)->format('d/m/Y') }}
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ \Carbon\Carbon::parse($document->created_at)->diffForHumans() }}
                            </div>
                        </td>

                        {{-- Issue Date column --}}
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-left">
                            @if (!empty($document->issue_date))
                                {{ date('d/m/Y', strtotime($document->issue_date)) }}
                            @else
                                <span class="text-gray-400">--</span>
                            @endif
                        </td>

                        {{-- Expiration Date column with warnings --}}
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-left">
                            @if (!empty($document->expiry_date))
                                @php
                                    $isExpired = \Carbon\Carbon::parse($document->expiry_date)->isPast();
                                    $isExpiringSoon = !$isExpired && \Carbon\Carbon::parse($document->expiry_date)->diffInDays(now()) <= 30;
                                @endphp
                                <span class="{{ $isExpired ? 'text-red-600 font-medium' : ($isExpiringSoon ? 'text-amber-600' : '') }}">
                                    {{ date('d/m/Y', strtotime($document->expiry_date)) }}
                                </span>
                                @if ($isExpiringSoon && !$isExpired)
                                    <div class="text-xs text-amber-600">
                                        {{ __('official_documents.expires_soon') }}
                                    </div>
                                @endif
                            @else
                                <span class="text-gray-400">--</span>
                            @endif
                        </td>

                        {{-- Status column --}}
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <x-tables.badge :status="__('official_documents.status_' . $document->stateName())" :color="$document->stateColor()" />
                        </td>

                        {{-- Actions column --}}
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="flex justify-end items-center gap-x-1">
                                {{-- Preview Button --}}
                                @if ($canPreview)
                                    <button type="button" x-on:click="showPreview = true"
                                        class="p-1.5 text-indigo-500 hover:text-indigo-700 hover:bg-indigo-50 rounded-full transition-colors"
                                        title="{{ __('Preview') }}">
                                        <x-heroicon-o-eye class="w-5 h-5" />
                                    </button>
                                @endif

                                {{-- Download Button --}}
                                <x-dynamic-table-buttons type="download" method="POST"
                                    route="{{ route('individual.official-documents.download', $document->id) }}"
                                    title="{{ __('Download') }}"
                                    class="p-1.5 text-green-500 hover:text-green-700 hover:bg-green-50 rounded-full transition-colors" />

                                {{-- Delete Button --}}
                                <x-dynamic-table-buttons type="delete" method="DELETE"
                                    route="{{ route('individual.official-documents.delete', $document->id) }}"
                                    title="{{ __('Delete') }}"
                                    class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-full transition-colors" />
                            </div>

                            {{-- Preview Modal --}}
                            @if ($canPreview)
                                <div x-cloak x-show="showPreview" x-transition:enter="ease-out duration-300"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    x-transition:leave="ease-in duration-200"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    class="fixed inset-0 bg-slate-900/90 z-50 flex items-center justify-center p-4">

                                    {{-- Close button --}}
                                    <button x-on:click="showPreview = false"
                                        class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
                                        <x-heroicon-o-x-mark class="w-8 h-8" />
                                    </button>

                                    {{-- Document info header --}}
                                    <div class="absolute top-4 left-4 text-white z-10">
                                        <h3 class="text-lg font-semibold">
                                            {{ \App\Enums\OfficialDocumentTypeEnum::toString($document->type) }}
                                        </h3>
                                    </div>

                                    {{-- Download button in modal --}}
                                    <div class="absolute bottom-4 right-4 z-10">
                                        <form method="POST"
                                            action="{{ route('individual.official-documents.download', $document->id) }}">
                                            @csrf
                                            <button type="submit"
                                                class="btn bg-white text-gray-800 hover:bg-gray-100 flex items-center gap-2">
                                                <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                                                {{ __('Download') }}
                                            </button>
                                        </form>
                                    </div>

                                    {{-- Preview content --}}
                                    <div x-on:click.outside="showPreview = false"
                                        class="w-full h-full max-w-6xl max-h-[90vh] flex items-center justify-center">
                                        @if ($mediaItem->mime_type === 'application/pdf')
                                            <iframe
                                                src="{{ route('individual.official-documents.preview', $document->id) }}"
                                                class="w-full h-full rounded-lg bg-white"
                                                title="{{ __('Document Preview') }}">
                                            </iframe>
                                        @else
                                            <img src="{{ route('individual.official-documents.preview', $document->id) }}"
                                                alt="{{ \App\Enums\OfficialDocumentTypeEnum::toString($document->type) }}"
                                                class="max-w-full max-h-full object-contain rounded-lg shadow-2xl" />
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-dynamic-table>
        @else
            <div class="mt-4">
                <x-utility.no-data :inCard="true" :title="__('official_documents.no_records_message')" />
                <div class="mt-2 flex justify-center">
                    <a href="#official-document-form" class="btn btn-info btn-sm">{{ __('official_documents.add_record') }}</a>
                </div>
            </div>
        @endif

    </div>
</x-layout>
