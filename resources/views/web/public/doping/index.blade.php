<x-public-layout>
    @php($brand = config('branding.primary'))


    <main class="pb-12">

        <div class="mx-auto pt-4 w-24">
            <img src="{{ asset($brand['logo_path']) }}" class="w-24 " alt="{{ $brand['short_name'] }}">
        </div>

        <div class="w-full md:w-2/3 md:mx-auto">
            @include('components.layout.banner_message')
        </div>

        <div class="w-full px-8 md:mx-auto mt-8">
            <h1 class="text-white text-3xl font-bold">{{__('Doping Control')}}</h1>
            <p class="text-white">{{__('List of events for doping control access.')}}</p>
        </div>

        <!-- Table of events -->
        <div class="w-full px-8 md:mx-auto mt-8">


            <div class="card justify-end flex flex-col gap-y-4">

                <form action="{{ route('public.anti-doping.download-list') }}"
                      method="POST"
                      class="w-auto self-end justify-end">
                    @csrf
                    <button type="submit" class="btn btn-info flex gap-x-1 w-auto">
                        <x-svg.box-arrow-down class="inline-block w-4 h-4 align-middle" />
                        {{ __('Export Report') }}
                    </button>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full leading-normal table table-auto">
                        <thead class="text-xs font-semibold uppercase text-slate-700 bg-slate-100 p-4">
                        <tr class="items-start">
                            <th class="text-left py-3 pl-2 whitespace-wrap w-auto">Name</th>
                            <th class="text-left py-3 pl-4">Event Start</th>
                            <th class="text-left py-3 pl-4">Event End</th>
                            <th class="text-left py-3 pl-4">Country</th>
                            <th class="text-left py-3 pl-4">Venue Address</th>
                            <th class="text-right py-3 pl-4">Contact Name</th>
                            <th class="text-right py-3 pl-4">Contact Phone</th>
                            <th class="text-right py-3 pl-4">Contact Email</th>
                            <th class="text-right py-3 pl-4">Nº Planned Controls</th>
                            <th class="text-right py-3 pl-4 pr-2">Nº of Controls</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($competitions as $competition)
                            <tr class="hover:bg-slate-50 border-b">
                                <td class="text-sm text-slate-600 font-bold w-auto py-4 pl-2">
                                    {{ $competition->name ?? $competition->event->name }}
                                </td>
                                <td class="text-sm px-4">{{ date('d/m/Y', strtotime($competition->start_date)) }}</td>
                                <td class="text-sm px-4">{{ date('d/m/Y', strtotime($competition->end_date)) }}</td>
                                <td class="text-sm px-4">{{ $competition->venueCountry->name ?? 'N/A' }}</td>
                                <td class="text-sm px-4">{{ $competition->event->venue }}
                                    | {{ $competition->event->venue_address }}
                                    - {{ $competition->event->venue_city }} </td>
                                <td class="text-sm px-4 text-right"> {{ $competition->antiDopingRecord?->responsible_name }}  </td>
                                <td class="text-sm px-4 text-right"> {{ $competition->antiDopingRecord?->responsible_phone }}  </td>
                                <td class="text-sm px-4 text-right"> {{ $competition->antiDopingRecord?->responsible_email }}  </td>

                                <td class="text-sm px-4 text-right"> {{ $competition->antiDopingRecord?->num_controls_planned }}  </td>
                                <td class="text-sm px-4 text-right"> {{ $competition->antiDopingRecord?->number_of_controls }}  </td>


                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


    </main>


    <!-- Table of events -->
    <style>
        body {
            background: #1e3a8a;
        }
    </style>

</x-public-layout>
