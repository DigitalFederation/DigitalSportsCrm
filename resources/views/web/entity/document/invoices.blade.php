<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('documents.invoices') }}</h1>
            </div>


        </div>



        <div class="sm:flex sm:justify-center sm:items-center mb-5 mt-4">

            <!-- Table -->
            <div class="bg-white shadow-lg rounded-sm border border-slate-200 mb-8 w-full">
                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="table-auto w-full">
                        <!-- Table header -->
                        <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('documents.id') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-center md:text-left">{{ __('documents.date_label') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-center md:text-left">{{ __('documents.total') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-24">
                                <div class="font-semibold text-right">{{ __('documents.download') }}</div>
                            </th>
                        </tr>
                        </thead>
                        <!-- Table body -->
                        <tbody class="text-sm divide-y divide-slate-200">
                        <!-- Row -->
                        @foreach($documents as $document)
                            <tr>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $document->number_extended }}</td>
                                <td class="px-2 py-3 whitespace-nowrap w-px text-center md:text-left">
                                  {{ \Carbon\Carbon::parse($document->created_at)->format('d-m-Y') }}
                                </td>
                                <td class="px-2 py-3 whitespace-nowrap w-px text-center md:text-left">{{ money($document->total_value, $document->currency) }}</td>
                                <td class="px-2 last:pr-5 py-3 whitespace-nowrap">

                                    <div class="space-x-1 flex justify-end items-end">

                                        <a href="{{ route('federation.document.download', $document->id)}}" target="_blank" class="text-slate-400 hover:text-slate-500 rounded-full mr-6">
                                            <x-svg.filetype-pdf class="h-6 w-6"/>
                                        </a>

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{$documents->links()}}
        </div>

    </div>
</x-layout>
