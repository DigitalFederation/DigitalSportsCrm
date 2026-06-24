<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('Invoices') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <!-- Export Button -->
                <a href="{{ route('admin.document.invoices.export', request()->query()) }}" class="btn btn-action ">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4 mr-2" />
                    <span>{{ __('Export Invoice Results') }}</span>
                </a>
            </div>

        </div>

        <!-- FILTER RESULTS -->
        <x-filter-form :post="route('admin.document.invoices')">
            <!-- Federation filter -->
            <x-forms.filter-input-select
                label="Federation"
                name="filter_federations"
                :options="$filter_federations"
            />

            <!-- Date Range filter -->
            <x-forms.filter-input-date-range
                label="Date"
                nameStart="filter_date_start"
                nameEnd="filter_date_end"
            />

            <!-- Year filter -->
            <div class="w-1/3" x-cloak>
                <label for="filter_years">{{ __('Years') }}</label>
                <livewire-input.select-multiple
                    inputName="filter[filter_years][]"
                    id="filter_years"
                    :items="$filter_years"
                    :inputSelected="Request()->query('filter')['filter_years'] ?? null"
                />
            </div>


            <!-- Owner Type filter -->
            <x-forms.filter-input-select
                label="Owner Type"
                name="filter_owner_type"
                :options="$filter_owner_types"
            />

            <!-- Nº Filiado -->
            <x-forms.filter-input-text
                label="{{ __('main.Member Code') }}"
                name="filter_member_code"
                helpText="Filter by Federation, Entity, or Individual Nº ID."
            />

            <!-- Filter Actions -->
            <div class="flex justify-end space-x-2 mt-6">
                <!-- Reset Button -->
                <a href="{{ route('admin.document.invoices') }}"
                   class="btn btn-info">
                    {{ __('Reset') }}
                </a>
            </div>
        </x-filter-form>


        <!-- More actions -->
        @if(Request::segment(3) != 'filter')
            <div class="mb-2 sm:mb-0">
                <h2 class="grow font-semibold text-slate-800 truncate">{{ __('Invoices')}}</h2>
            </div>
        @else
            <div class="mb-2 sm:mb-0">
                <h2 class="grow font-semibold text-slate-800 truncate">{{ __('Filter results')}}</h2>
            </div>
        @endif


        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            <!-- Table -->
            <div class="bg-white shadow-lg rounded-sm border border-slate-200 mb-8 w-full">
                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="table-auto w-full">
                        <!-- Table header -->
                        <thead
                            class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('ID') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-center md:text-left">{{ __('Recipient') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-center md:text-left">{{ __('Payment Date') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-center md:text-left">{{ __('Total') }}</div>
                            </th>
                            <th>
                                <div class="font-semibold text-center md:text-left">{{ __('Payment Type') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="font-semibold text-right">{{ __('Download') }}</div>
                            </th>
                        </tr>
                        </thead>
                        <!-- Table body -->
                        <tbody class="text-sm divide-y divide-slate-200">
                        <!-- Row -->
                        @foreach($paginatedDocuments as $document)
                            @php  $payment = $document->transactions->last(); @endphp
                            <tr>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $document->invoice_extended }}</td>
                                <td class="px-2 py-3 whitespace-nowrap w-px text-center md:text-left">
                                    @if(!empty($document->owner))
                                        {{ $document->owner->getDisplayName() }}

                                    @elseif(!empty($document->customer_name))
                                        {{ $document->customer_name }}
                                    @else
                                        {{ __('No Recipient') }}
                                    @endif
                                </td>
                                <td class="px-2 py-3 whitespace-nowrap w-px text-center md:text-left">
                                    @if ($document->transactions->isNotEmpty())
                                        <!-- Get only the last transaction -->
                                        <div class="flex flex-col md:items-start">
                                            <!-- Payment Date -->
                                            <span>{{ \Carbon\Carbon::parse($payment->created_at)->format('d-m-Y') }}</span>
                                        </div>
                                    @endif

                                </td>
                                <td class="px-2 py-3 whitespace-nowrap w-px text-center md:text-left">
                                    {{ $document->total_value }}€
                                </td>

                                <td class="pl-0 py-3 break-words w-px text-center md:text-left">
                                    <!-- Payment Method Icon or Badge -->
                                    @if (!empty($payment->paymentMethod))
                                        <div>{{ $payment->paymentMethod?->name }}</div>
                                    @endif

                                </td>

                                <td class="px-2 py-3 md:text-right justify-end flex">
                                    <a href="{{ route('admin.document.download', $document->id)}}" target="_blank"
                                       class="text-slate-400 hover:text-slate-500 rounded-full mr-6">
                                        <x-svg.filetype-pdf class="h-6 w-6" />
                                    </a>
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
            {{$paginatedDocuments->links()}}
        </div>

    </div>
</x-layout>
