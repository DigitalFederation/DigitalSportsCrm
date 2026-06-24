@section('title', __('Members Official Documentation'))

<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('Official Documents') }}</h1>
            </div>
        </div>

        <div class="sm:flex flex-row gap-4">
            <x-utility.card-total title="{{ __('official_documents.documents') }}" :count="$official_documents->total()"></x-utility.card-total>

            <x-filter-form :post="route('admin.official-documents.index', $type)">
                <x-forms.filter-input-select label="{{ __('official_documents.document_types') }}" name="filter_type" :options="$types" />
                <x-forms.filter-input-select label="{{ __('official_documents.status') }}" name="filter_status" :options="$status" />
                @if ($type == 'individual')
                    <x-forms.filter-input-text label="{{ __('official_documents.member_number') }}" name="filter_member_number" />
                    <x-forms.filter-input-text label="{{ __('official_documents.given_name') }}" name="filter_name" />
                    <x-forms.filter-input-text label="{{ __('official_documents.family_name') }}" name="filter_surname" />
                @elseif($type == 'entity')
                    <x-forms.filter-input-text label="{{ __('official_documents.entity_name') }}" name="filter_entity_name" />
                    <x-forms.filter-input-text label="{{ __('official_documents.member_number') }}" name="filter_entity_member_number" />
                @endif
            </x-filter-form>
        </div>

        <!-- FILTER RESULTS -->
        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            @if (!empty($official_documents) && $official_documents->count() > 0)

                @php
                    if ($type == 'individual') {
                        $headers = [
                            __('official_documents.given_name'),
                            __('official_documents.family_name'),
                            __('official_documents.member_number'),
                            __('official_documents.type'),
                            __('official_documents.role'),
                            __('official_documents.date_sent'),
                            __('official_documents.issue_date'),
                            __('official_documents.expiration_date'),
                            __('official_documents.status'),
                            '',
                        ];
                    } elseif ($type == 'federation') {
                        $headers = [
                            __('official_documents.name'),
                            __('official_documents.type'),
                            __('official_documents.date_sent'),
                            __('official_documents.issue_date'),
                            __('official_documents.expiration_date'),
                            __('official_documents.status'),
                            '',
                        ];
                    } elseif ($type == 'entity') {
                        $headers = [
                            __('official_documents.entity'),
                            __('official_documents.member_number'),
                            __('official_documents.type'),
                            __('official_documents.date_sent'),
                            __('official_documents.issue_date'),
                            __('official_documents.expiration_date'),
                            __('official_documents.status'),
                            '',
                        ];
                    }
                @endphp

                <x-dynamic-table :headers="$headers">
                    @foreach ($official_documents as $document)
                        @php
                            $mediaItem = $document->getMedia('media')->first();
                            $canPreview = $mediaItem && in_array($mediaItem->mime_type, ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp']);
                        @endphp
                        <tr x-data="{ showModal: false, showPreview: false }"
                            x-on:keydown.window.escape="showModal = false; showPreview = false">

                            @if ($type == 'individual')
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    @if (!empty($document->individual))
                                        <a href="{{ route('admin.individual.show', $document->individual->id) }}"
                                            target="_blank" class="hover:text-cyan-600 flex gap-x-2 items-center">
                                            <x-svg.box-arrow-up-right class="w-4 h-4 flex-shrink-0"></x-svg.box-arrow-up-right>
                                            <span class="truncate max-w-[200px]">{{ $document->individual->name }}</span>
                                        </a>
                                    @endif
                                </td>
                                <td class="px-2 py-3 whitespace-nowrap">
                                    <span class="truncate max-w-[200px]">{{ $document->individual?->surname }}</span>
                                </td>
                                <td class="px-2 py-3 whitespace-nowrap">
                                    {{ $document->individual?->member_number }}
                                </td>
                            @elseif($type == 'federation')
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    @if (!empty($document->federation))
                                        <a href="{{ route('admin.federation.show', $document->federation->id) }}"
                                            target="_blank" class="hover:text-cyan-600 flex gap-x-2 items-center">
                                            <x-svg.box-arrow-up-right class="w-4 h-4"></x-svg.box-arrow-up-right>
                                            <span>{{ $document->federation->member_code }}</span>
                                        </a>
                                    @endif
                                </td>
                            @elseif($type == 'entity')
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    @if (!empty($document->owner))
                                        <a href="{{ route('admin.entity.show', $document->owner->id) }}" target="_blank"
                                            class="hover:text-cyan-600 flex gap-x-2 items-center">
                                            <x-svg.box-arrow-up-right class="w-4 h-4 flex-shrink-0"></x-svg.box-arrow-up-right>
                                            <span class="truncate max-w-[200px]">{{ $document->owner->name }}</span>
                                        </a>
                                    @endif
                                </td>
                                <td class="px-2 py-3 whitespace-nowrap">
                                    {{ $document->owner?->member_number }}
                                </td>
                            @endif

                            <td class="px-2 py-3 whitespace-nowrap text-left">
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
                                    <span class="truncate max-w-[180px]">
                                        {{ App\Enums\OfficialDocumentTypeEnum::toString($document->type) }}
                                    </span>
                                </div>
                            </td>

                            @if ($type == 'individual')
                                <td class="px-2 py-3 whitespace-nowrap text-left">
                                    @if ($document->role)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $document->roleLabel() }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">--</span>
                                    @endif
                                </td>
                            @endif

                            <td class="px-2 py-3 whitespace-nowrap text-left">
                                <div class="text-sm">
                                    {{ \Carbon\Carbon::parse($document->created_at)->format('d/m/Y') }}
                                </div>
                                <div class="text-xs text-gray-400">
                                    {{ \Carbon\Carbon::parse($document->created_at)->diffForHumans() }}
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
                                    @php
                                        $isExpired = \Carbon\Carbon::parse($document->expiry_date)->isPast();
                                        $isExpiringSoon = !$isExpired && \Carbon\Carbon::parse($document->expiry_date)->diffInDays(now(), absolute: true) <= 30;
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

                            <td class="px-2 py-3 whitespace-nowrap">
                                <x-tables.badge :status="ucfirst($document->stateName())" :color="$document->stateColor()" />
                            </td>

                            <td class="px-2 py-3 whitespace-nowrap">
                                <div class="flex justify-end items-center gap-x-1">
                                    {{-- Preview Button --}}
                                    @if ($canPreview)
                                        <button type="button" x-on:click="showPreview = true"
                                            class="p-1.5 text-indigo-500 hover:text-indigo-700 hover:bg-indigo-50 rounded-full transition-colors"
                                            title="{{ __('Preview') }}">
                                            <x-heroicon-o-eye class="w-5 h-5" />
                                        </button>
                                    @endif

                                    {{-- Edit Button --}}
                                    <a href="{{ route('admin.official-documents.edit', $document->id) }}"
                                        class="p-1.5 text-blue-500 hover:text-blue-700 hover:bg-blue-50 rounded-full transition-colors"
                                        title="{{ __('Edit') }}">
                                        <x-heroicon-o-pencil class="w-5 h-5" />
                                    </a>

                                    {{-- Download Button --}}
                                    <x-dynamic-table-buttons type="download" method="POST"
                                        route="{{ route('admin.official-documents.download', $document->id) }}"
                                        title="{{ __('Download') }}"
                                        class="p-1.5 text-green-500 hover:text-green-700 hover:bg-green-50 rounded-full transition-colors" />

                                    {{-- Approve Button (only for pending) --}}
                                    @if ($document->state->isPending())
                                        <button type="button" x-on:click="showModal = true"
                                            class="p-1.5 text-emerald-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-full transition-colors"
                                            title="{{ __('Activate') }}">
                                            <x-heroicon-o-check-circle class="w-5 h-5" />
                                        </button>

                                        {{-- Approval Date Modal --}}
                                        <div x-cloak x-show="showModal" x-transition:enter="ease-out duration-300"
                                            x-transition:enter-start="opacity-0"
                                            x-transition:enter-end="opacity-100"
                                            x-transition:leave="ease-in duration-200"
                                            x-transition:leave-start="opacity-100"
                                            x-transition:leave-end="opacity-0"
                                            class="fixed inset-0 bg-slate-900/75 z-50 flex items-center justify-center p-4">
                                            <div x-on:click.outside="showModal = false"
                                                x-transition:enter="ease-out duration-300"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                class="w-full max-w-lg mx-auto bg-white rounded-xl shadow-xl overflow-hidden">

                                                <div class="px-6 py-4 bg-gray-50 border-b">
                                                    <h3 class="text-lg font-semibold text-gray-900">
                                                        {{ __('Activation Dates') }}
                                                    </h3>
                                                </div>

                                                <div class="p-6">
                                                    <x-information-box :title="__('Important')"
                                                        :body="__('Setting the start and expiration dates will change the document status to Active. This action is irreversible, so please double-check the dates before proceeding.')" />

                                                    <form method="POST"
                                                        action="{{ route('admin.official-documents.activate', $document->id) }}"
                                                        class="mt-4 space-y-4">
                                                        @csrf
                                                        @method('PUT')

                                                        <div>
                                                            <label for="start-date-{{ $document->id }}"
                                                                class="block text-sm font-medium text-gray-700 mb-1">
                                                                {{ __('Start Date') }} <span class="text-red-500">*</span>
                                                            </label>
                                                            <input type="date" id="start-date-{{ $document->id }}"
                                                                name="start_date"
                                                                value="{{ $document->issue_date ? \Carbon\Carbon::parse($document->issue_date)->format('Y-m-d') : '' }}"
                                                                class="form-input w-full rounded-lg" required>
                                                        </div>

                                                        <div>
                                                            <label for="approval-date-{{ $document->id }}"
                                                                class="block text-sm font-medium text-gray-700 mb-1">
                                                                {{ __('Expiration Date') }}
                                                            </label>
                                                            <input type="date"
                                                                id="approval-date-{{ $document->id }}"
                                                                name="expire_date" class="form-input w-full rounded-lg">
                                                        </div>

                                                        <div class="flex justify-end gap-3 pt-4">
                                                            <button type="button" x-on:click="showModal = false"
                                                                class="btn bg-gray-100 hover:bg-gray-200 text-gray-700">
                                                                {{ __('Cancel') }}
                                                            </button>
                                                            <button type="submit" class="btn btn-primary">
                                                                {{ __('Activate') }}
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Reject Button --}}
                                        <x-dynamic-table-buttons type="reject" method="PUT"
                                            route="{{ route('admin.official-documents.reject', $document->id) }}"
                                            title="{{ __('Reject') }}"
                                            class="p-1.5 text-orange-500 hover:text-orange-700 hover:bg-orange-50 rounded-full transition-colors" />
                                    @endif

                                    {{-- Delete Button --}}
                                    <x-dynamic-table-buttons type="delete" method="DELETE"
                                        route="{{ route('admin.official-documents.delete', $document->id) }}"
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
                                                {{ App\Enums\OfficialDocumentTypeEnum::toString($document->type) }}
                                            </h3>
                                            <p class="text-sm text-gray-300">
                                                @if ($type == 'individual' && $document->individual)
                                                    {{ $document->individual->full_name }}
                                                @elseif($type == 'entity' && $document->owner)
                                                    {{ $document->owner->name }}
                                                @endif
                                            </p>
                                        </div>

                                        {{-- Download button in modal --}}
                                        <div class="absolute bottom-4 right-4 z-10">
                                            <form method="POST"
                                                action="{{ route('admin.official-documents.download', $document->id) }}">
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
                                                    src="{{ route('admin.official-documents.preview', $document->id) }}"
                                                    class="w-full h-full rounded-lg bg-white"
                                                    title="{{ __('Document Preview') }}">
                                                </iframe>
                                            @else
                                                <img src="{{ route('admin.official-documents.preview', $document->id) }}"
                                                    alt="{{ App\Enums\OfficialDocumentTypeEnum::toString($document->type) }}"
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
                <x-utility.no-data in_card="true"></x-utility.no-data>
            @endif

        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $official_documents->links() }}
        </div>

    </div>
</x-layout>
