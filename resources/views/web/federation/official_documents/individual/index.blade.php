@section('title',  __('Members Official Documentation'))

<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title"> {{ ucfirst($committee) }} {{ __('Official Documents') }}</h1>
            </div>

            <!-- Right: Create Button -->
            <div class="flex items-center space-x-2">
                <a href="{{ route('federation.official-documents.create', ['committee' => $committee]) }}"
                   class="btn btn-primary">
                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                    {{ __('Upload Document') }}
                </a>
            </div>
        </div>

        <div class="sm:flex flex-row gap-4">
            <x-utility.card-total :title="__('main.Documents')" :count="$documents->total()"></x-utility.card-total>

            <x-filter-form :post="route('federation.official-documents.index')">
                <input type="hidden" name="filter[committee]" value="{{ $committee }}">
                <x-forms.filter-input-select label="{{ __('official_documents.document_types') }}" name="filter_type" :options="$types" />
                <x-forms.filter-input-select label="{{ __('main.status') }}" name="filter_status" :options="$status" />
            </x-filter-form>
        </div>

        <!-- FILTER RESULTS -->

        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            @if(!empty($documents) && $documents->count() > 0)

                <x-dynamic-table
                    :headers="[__('main.name'), __('main.Type'), __('main.Role'), __('official_documents.date_sent'), __('official_documents.expiration_date'), __('official_documents.issue_date'), __('main.status'), '']">
                    @foreach($documents as $document)
                        <tr x-data="{ showModal: false }" x-on:keydown.window.escape="showModal = false">

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                @if($document->individual)
                                    <a href="{{ route('federation.individual.show', $document->individual->id) }}"
                                       target="_blank" class="hover:text-cyan-600 flex gap-x-2 items-center">
                                        <x-svg.box-arrow-up-right class="w-4 h-4"></x-svg.box-arrow-up-right>
                                        <span>{{ $document->individual->full_name }} ({{$document->individual->member_code}})</span>
                                    </a>
                                @else
                                    <span class="text-gray-400">{{ __('official_documents.individual_not_found') }}</span>
                                @endif
                            </td>

                            <td class="px-2 py-3 whitespace-nowrap w-px text-center md:text-left">
                                {{ \App\Enums\OfficialDocumentTypeEnum::toString($document->type) }}
                            </td>

                            <td class="px-2 py-3 whitespace-nowrap w-px text-center md:text-left">
                                {{ $document->roleLabel() }}
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ \Carbon\Carbon::parse($document->created_at)->format('d/m/Y') }}
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-left">
                                @if(!empty($document->expiry_date))
                                    {{ date('d/m/Y', strtotime($document->expiry_date)) }}
                                @else
                                    <span class="text-xs">--</span>
                                @endif
                            </td>
                            <!-- Issue Date -->
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-left">
                                @if(!empty($document->issue_date))
                                    {{ date('d/m/Y', strtotime($document->issue_date)) }}
                                @else
                                    <span class="text-xs">--</span>
                                @endif
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <x-tables.badge :status="ucfirst($document->stateName())"
                                                :color="$document->stateColor()" />
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end">
                                <div class=" flex justify-end items-center gap-x-2">
                                    <x-dynamic-table-buttons
                                        type="download"
                                        method="POST"
                                        route="{{ route('federation.official-documents.download', $document->id) }}"
                                        title="{{ __('Download') }}"
                                        class="text-green-500 hover:text-green-600 rounded-full cursor-pointer"></x-dynamic-table-buttons>

                                    <!-- Button to trigger modal -->
                                    @if($document->state->isPending())

                                        <!-- Button to trigger modal -->
                                        <div x-on:click="showModal=!showModal"
                                             class="text-green-500 hover:text-green-600 rounded-full cursor-pointer"
                                             title="Confirm">
                                            <x-svg.check class="w-5 h-5"></x-svg.check>
                                        </div>

                                        <!-- Approval Date Modal -->
                                        <div x-cloak x-show="showModal" x-transition
                                             class="fixed inset-0 bg-slate-900/75 z-50 flex items-center justify-center">
                                            <div class="w-screen md:max-w-lg mx-auto card h-auto">

                                                <x-information-box
                                                    :title="__('Expire Date')"
                                                    :body="__('Setting the expiration date will change the document status to Active. This action is irreversible, so please double-check the date before proceeding.')">
                                                </x-information-box>

                                                <form method="POST"
                                                      action="{{ route(request()->segment(1).'.official-documents.activate', $document->id) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div>
                                                        <label for="approval-date-{{ $document->id }}"
                                                               class="block text-sm font-medium text-gray-700">
                                                            {{ __('Expiration Date') }}
                                                        </label>
                                                        <input type="date" id="approval-date-{{ $document->id }}"
                                                               name="expire_date" class="mt-1 p-2 w-full"
                                                               value="{{ now()->format('Y-m-d') }}">
                                                    </div>
                                                    <div class="justify-end text-right mt-4">
                                                        <button type="submit" class="btn-primary btn-sm">{{ __('official_documents.activate') }}
                                                        </button>
                                                        <button type="button" x-on:click="showModal = false"
                                                                class="btn btn-info">{{ __('official_documents.close') }}
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>


                                        <!-- Edit Dates Button -->
                                        <div x-on:click="showModal=!showModal"
                                            class="text-blue-500 hover:text-blue-600 rounded-full cursor-pointer"
                                            title="{{ __('Edit Dates') }}">
                                            <x-svg.edit class="w-5 h-5"></x-svg.edit>
                                        </div>
                                        <!-- Edit Dates Modal -->
                                        <div x-cloak x-show="showModal" x-transition
                                            class="fixed inset-0 bg-slate-900/75 z-50 flex items-center justify-center">
                                            <div class="w-screen md:max-w-lg mx-auto card h-auto">
                                                <x-information-box :title="__('Edit Document Dates')" :body="__('Update the issue and expiry dates for this document.')">
                                                </x-information-box>

                                                <form method="POST"
                                                    action="{{ route('federation.official-documents.update-dates', $document->id) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="space-y-4">
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700">
                                                                {{ __('Issue Date') }}
                                                            </label>
                                                            <input type="date" name="issue_date"
                                                                value="{{ $document->issue_date }}"
                                                                class="mt-1 p-2 w-full">
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700">
                                                                {{ __('Expiry Date') }}
                                                            </label>
                                                            <input type="date" name="expiry_date"
                                                                value="{{ $document->expiry_date }}"
                                                                class="mt-1 p-2 w-full">
                                                        </div>
                                                    </div>
                                                    <div class="justify-end text-right mt-4">
                                                        <button type="submit" class="btn-primary btn-sm">
                                                            {{ __('Update') }}
                                                        </button>
                                                        <button type="button" x-on:click="showModal = false"
                                                            class="btn btn-info">{{ __('Close') }}
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <x-dynamic-table-buttons
                                            type="reject"
                                            method="PUT"
                                            route="{{ route('federation.official-documents.reject', $document->id) }}"
                                            title="{{ __('Reject') }}"></x-dynamic-table-buttons>
                                    @endif

                                    <x-dynamic-table-buttons
                                        type="delete"
                                        method="DELETE"
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
