<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('Pending Official Documents') }}</h1>
            </div>


        </div>

        <!-- FILTER RESULTS -->

        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            @if (!empty($documents) && $documents->count() > 0)
                <div class="bg-white shadow-lg rounded-sm border border-slate-200 mb-8 w-full">
                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full">
                            <!-- Table header -->
                            <thead
                                class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                                <tr>

                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-center md:text-left">
                                            {{ __('Name') }}
                                        </div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-center md:text-left">{{ __('Doc. Type') }}</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-center md:text-left">{{ __('Date') }}</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-center md:text-left">{{ __('Status') }}</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-center md:text-left">{{ __('Issue Date') }}</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-center md:text-left">{{ __('Expiry Date') }}
                                        </div>
                                    </th>

                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-right">{{ __('Actions') }}</div>
                                    </th>
                                </tr>
                            </thead>
                            <!-- Table body -->
                            <tbody class="text-sm divide-y divide-slate-200">
                                <!-- Row -->
                                @foreach ($documents as $document)
                                    <tr x-data="{ showModal: false }" x-on:keydown.window.escape="showModal = false">

                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                            @if (!empty($document->individual))
                                                <a href="{{ route(request()->segment(1) . '.individual.show', $document->individual?->id) }}"
                                                    target="_blank"
                                                    class="hover:text-cyan-600">{{ $document->individual?->member_code }}</a>
                                            @endif
                                        </td>

                                        <td class="px-2 py-3 whitespace-nowrap w-px text-center md:text-left">
                                            {{ $document->name }}
                                        </td>

                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                            {{ \Carbon\Carbon::parse($document->created_at)->format('d-m-Y') }}
                                        </td>

                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                            <x-tables.badge :status="ucfirst($document->stateName())" :color="$document->stateColor()" />
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

                                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end">
                                            <div class="space-x-1 flex justify-end items-center">
                                                @if ($document->stateName() == 'pending')
                                                    <!-- Button to trigger modal -->
                                                    <div x-on:click="showModal=!showModal"
                                                        class="text-green-500 hover:text-green-600 rounded-full cursor-pointer">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                            class="w-6 h-6">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                            </path>
                                                        </svg>
                                                    </div>

                                                    <!-- Approval Date Modal -->
                                                    <div x-cloak x-show="showModal" x-transition
                                                        class="fixed inset-0 bg-slate-900/75 z-50 flex items-center justify-center">
                                                        <div class="w-screen md:max-w-lg mx-auto card h-auto">

                                                            <x-information-box :title="__('Approval Date')" :body="__(
                                                                'Setting the approval date will change the document status to Active. This action is irreversible, so please double-check the date before proceeding.',
                                                            )">
                                                            </x-information-box>

                                                            <form method="POST"
                                                                action="{{ route(request()->segment(1) . '.official-documents.activate', $document->id) }}">
                                                                @csrf
                                                                @method('PUT')
                                                                <div>
                                                                    <label for="approval-date-{{ $document->id }}"
                                                                        class="block text-sm font-medium text-gray-700">
                                                                        {{ __('Approval Date') }}
                                                                    </label>
                                                                    <input type="date"
                                                                        id="approval-date-{{ $document->id }}"
                                                                        name="approval_date" class="mt-1 p-2 w-full"
                                                                        value="{{ now()->format('Y-m-d') }}">
                                                                </div>
                                                                <div class="justify-end text-right mt-4">
                                                                    <button type="submit" class="btn-primary btn-sm">
                                                                        Activate
                                                                    </button>
                                                                    <button type="button"
                                                                        x-on:click="showModal = false"
                                                                        class="btn btn-info">Close
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <x-utility.no-data></x-utility.no-data>
            @endif
        </div>

        <!-- Pagination -->
        <div class="mt-8">

        </div>

    </div>
</x-layout>
