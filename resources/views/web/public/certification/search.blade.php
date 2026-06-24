@section('title', 'Certifications search results')
<x-public-layout>
    @php($brand = config('branding.primary'))
    <main class="relative bg-cover min-h-screen bg-waves-full-bg-one animate-in pb-16">
        <div class="mx-auto pt-4 w-24">
            <img src="{{ asset($brand['logo_path']) }}" class="w-24 " alt="{{ $brand['short_name'] }} Logo">
        </div>

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
            @if(!empty($individual))
                {{-- Individual Header --}}
                <div class="bg-white shadow-lg rounded-lg p-4 sm:p-6 mb-8 flex items-center space-x-4 sm:space-x-6">
                    <x-secure-profile-image :individual="$individual" size="thumb" class="h-20 w-20 sm:h-24 sm:w-24 rounded-full object-cover border-4 border-gray-200 flex-shrink-0" />
                    <div class="flex-1 min-w-0">
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 truncate">{{ $individual->full_name }}</h2>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 mt-2 text-sm text-gray-600">
                            @if($individual->country)
                            <span class="flex items-center whitespace-nowrap">
                                <img src="{{ asset('img/flags/' . strtolower($individual->country->iso) . '.svg') }}" alt="{{ $individual->country->name }} flag" class="w-5 h-auto mr-1.5 sm:mr-2 rounded-sm flex-shrink-0">
                                {{ $individual->country->name }}
                            </span>
                            @endif
                            <span class="whitespace-nowrap mt-1 sm:mt-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Born: {{ Carbon\Carbon::parse($individual->birthdate)->format('d M Y') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Certifications List --}}
                @if ($certificationsByCommittee)
                    @foreach ($certificationsByCommittee as $key => $certifications)
                        @if ($certifications->count() > 0)
                            <div class="mb-8">
                                {{-- Committee Header (Outside the card) --}}
                                <h3 class="text-lg sm:text-xl font-semibold text-gray-800 mb-3">{{ ucfirst($key) }} Certifications</h3>

                                {{-- Certifications for this committee (Inside the card) --}}
                                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                                    <ul class="divide-y divide-gray-200">
                                        @foreach ($certifications as $certification)

                                            <li class="p-3 sm:p-4 md:p-6 hover:bg-gray-50 transition ease-in-out duration-150">
                                                <div class="flex items-center space-x-3 sm:space-x-4">
                                                     <img src="{{ $certification->card_url }}"
                                                         loading="lazy"
                                                         alt="{{ $certification->certification_name }} card image"
                                                         class="w-12 h-auto sm:w-16 flex-shrink-0 shadow-sm rounded">
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-base sm:text-lg font-semibold text-sky-800">
                                                            {{ $certification->certification_name }}
                                                        </p>
                                                        <p class="text-sm text-gray-500 mt-1 flex md:flex-row flex-col">
                                                            <span class="font-semibold text-gray-600 text-sm">National Certification Nº: </span>
                                                            <span class="font-medium md:ml-1">{{ $certification->national_code }}</span>
                                                        </p>
                                                        <div class="text-gray-500 flex md:flex-row flex-col">
                                                            <div class="font-semibold text-gray-600 text-sm">Issued:</div>
                                                            <div class="font-medium text-xs md:text-sm md:ml-1">
                                                                {{ Carbon\Carbon::parse($certification->current_term_starts_at)->format('d M Y') }}
                                                             @if($certification->federation)
                                                                by <span class="font-medium">{{ $certification->federation->member_code }}</span>
                                                                @if($certification->federation->country)
                                                                    <img src="{{ asset('img/flags/' . strtolower($certification->federation->country->iso) . '.svg') }}"
                                                                    alt=""
                                                                    class="w-4 h-auto inline ml-1 rounded-sm" />
                                                                @endif
                                                             @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex-shrink-0 ml-3 sm:ml-4">
                                                         <span class="px-2.5 sm:px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                                            @switch($certification->stateName())
                                                                @case('active')
                                                                    bg-green-100 text-green-800
                                                                    @break
                                                                @case('pending')
                                                                    bg-yellow-100 text-yellow-800
                                                                    @break
                                                                @case('rejected')
                                                                    bg-red-100 text-red-800
                                                                    @break
                                                                @case('suspended')
                                                                    bg-orange-100 text-orange-800
                                                                    @break
                                                                @case('canceled')
                                                                    bg-gray-100 text-gray-800
                                                                    @break
                                                                @default
                                                                    bg-gray-100 text-gray-800
                                                            @endswitch
                                                         ">
                                                            {{ ucfirst($certification->stateName()) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    {{-- No Certifications Found (Overall) --}}
                    <div class="bg-white shadow-md rounded-lg p-6 text-center">
                         <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">{{__('No certifications found')}}</h3>
                        <p class="mt-1 text-sm text-gray-500">No certifications match the provided details for this individual.</p>
                    </div>
                @endif

            @else
                 {{-- No Individual Found (implies no certifications either) --}}
                 <div class="bg-white shadow-lg rounded-lg p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No Results Found') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __("We couldn't find any records matching your search criteria.") }}</p>
                    <div class="mt-6">
                        <a href="{{ route('public.certification.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-[#193044] hover:bg-[#112233] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#193044]">
                           {{ __('Go Back to Search') }}
                        </a>
                    </div>
                </div>
            @endif

            {{-- Back Button --}}
            @if(!empty($individual)) {{-- Show back button only if we displayed results --}}
            <div class="flex justify-center mt-8">
                <a href="{{ route('public.certification.index') }}"
                   class="bg-[#193044] w-full sm:w-auto text-center text-white py-2 px-6 rounded-md text-base sm:text-lg font-semibold hover:bg-[#112233] transition ease-in-out duration-150">
                   Back to Search
                </a>
            </div>
            @endif
        </div>
    </main>
</x-public-layout>
