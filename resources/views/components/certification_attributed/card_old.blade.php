<div>
    <a href="{{ route('individual.certification-card.show', $certification->id) }}">
        <div class="
    @if($certification->certification->certification_view == 'Yellow') bg-card-waves-yellow @endif
    @if($certification->certification->certification_view == 'Blue') bg-card-waves-blue @endif
    @if($certification->certification->certification_view == 'Green') bg-card-waves-green @endif
    @if($certification->certification->certification_view == 'Grey') bg-card-waves-grey @endif
    rounded-xl shadow-lg bg-white w-full md:h-60 overflow-hidden bg-bottom md:bg-[center_top_-5rem] cursor-pointer">

            <div class="h-14 flex justify-between px-4 text-center py-2">
                <h3 class="text-[0.5rem] md:text-[0.6rem] font-bold  font-inter text-admin_brown uppercase">
                    Brevet International
                </h3>
                <h3 class="text-[0.5rem] md:text-[0.6rem] font-bold font-inter text-admin_brown uppercase">
                    International Certificate
                </h3>
                <h3 class="text-[0.5rem] md:text-[0.6rem] font-bold font-inter text-admin_brown uppercase">
                    Certificacion Internacional
                </h3>
            </div>

            <div class="h-auto flex justify-end px-4 items-center">

                <div class="max-w-20 break-words font-inter font-bold text-gray-700 mr-4 text-right">
                    <div class="leading-4 text-[0.8rem] uppercase"> {{ $certification->certification->name }} </div>
                    <div class="leading-4 text-[0.8rem] uppercase"> {{ $certification->certification->name_es }} </div>
                    <div class="leading-4 text-[0.8rem] uppercase"> {{ $certification->certification->name_fr }} </div>
                </div>

                <div class="rounded-md shadow-lg bg-slate-50 p-1">
                    <img src="/img/cmas-logo-blue.png" alt="CMAS" class="h-16 md:h-18">
                </div>

            </div>

            <div class="h-24 md:h-auto text-center mt-2 md:mt-3">

                <div class="text-[0.6rem] font-bold font-inter text-gray-600">
                    CONFÉDÉRATION MONDIALE DES ACTIVITÉS SUBAQUATIQUES <br> WORLD UNDERWATER FEDERATION
                </div>

                <div class="mx-4">

                    @if($certification->certification->certification_view == 'Blue')
                        <img src="/img/cmas-blue.png" alt="CMAS" class="w-full">
                    @endif
                    @if($certification->certification->certification_view == 'Grey')
                        <img src="/img/cmas-gray.png" alt="CMAS" class="w-full">
                    @endif
                    @if($certification->certification->certification_view == 'Green')
                        <img src="/img/cmas-green.png" alt="CMAS" class="w-full">
                    @endif
                    @if($certification->certification->certification_view == 'Yellow')
                        <img src="/img/cmas-orange.png" alt="CMAS" class="w-full">
                    @endif


                </div>
            </div>

        </div>

        {{--<div
            role="dialog"
            tabindex="-1"
            x-show="isModalOpen"
            x-on:click.away="isModalOpen = false"
            x-cloak
            x-transition
            class="rounded-xl shadow-lg bg-white bg-bottom md:h-auto p-4 fixed mr-4 md:mr-0 md:left-1/3 md:right-1/3 top-1/4 md:w-[520px]">

            <div class="h-full flex flex-col items-start relative w-full overflow-hidden">
                <!-- Profile Pic -->
                <div class="flex items-center justify-between w-full">

                    <figure class="max-w-24">
                        @if($individual->getFirstMediaUrl('profile', 'thumb'))
                            <img class="w-24 rounded-full" src="{{ $individual->getFirstMediaUrl('profile', 'thumb') }}" alt="Avatar"/>
                        @else
                            <img class="w-24 rounded-full" src="{{ asset('img/user_placeholder.png') }}" alt="Avatar"/>
                        @endif
                    </figure>

                    <div class="w-full ml-4">
                        <h3 class="text-base md:text-xl font-bold font-inter text-admin_brown">{{ $certification->certification_name }} </h3>
                        <p class="text-sm md:text-base font-bold font-inter text-neutral-800">{{ $individual->name }} {{ $individual->surname }}</p>
                    </div>

                    <div class="w-20 md:w-24 flex items-center">
                        <figure class="relative w-10 rounded-full overflow-hidden">
                            <img src="{{ asset('img/flags/' . strtolower($individual->country->iso) . '.svg') }}" class="object-cover"/>
                        </figure>
                        <figure class="relative w-12 ml-2">
                            <img src="/img/cmas-logo-blue.png" alt="CMAS" class="h-12 md:h-12">
                        </figure>
                    </div>

                </div>

                <!-- Certification Info -->
                <div class="flex w-full mt-8">

                    <ul class="border-t-2 border-gray-600 w-full">
                        <li class="flex justify-between my-1 pt-4 items-center">
                            <div class="font-bold text-xs md:text-sm">CMAS nº</div>
                            <div class="text-xs md:text-sm">{{ $certification->license_number}}</div>
                        </li>
                        <li class="flex justify-between my-1 items-center">
                            <div class="font-bold text-sm">National Code</div>
                            <div class="text-xs md:text-sm">{{ $certification->national_code}}</div>
                        </li>
                        <li class="flex justify-between my-1">
                            <div class="font-bold text-sm">Country</div>
                            <div class="text-xs md:text-sm">{{ $individual->country->name}}</div>
                        </li>
                        <li class="flex justify-between my-1">
                            <div class="font-bold text-sm">Cert Date</div>
                            <div class="text-xs md:text-sm">{{ date('d/m/Y', strtotime($certification->current_term_starts_at)) }} </div>
                        </li>
                        <li class="flex justify-between my-1 items-center">
                            <div class="font-bold text-sm">Expiration Date</div>
                            <div class="text-sm">
                                @if(empty($certification->current_term_ends_at))
                                    <span class="text-xs md:text-sm">{{ __('No Expire Date') }}</span>
                                @else
                                    {{ date('d/m/Y', strtotime($certification->current_term_ends_at)) }}
                                @endif
                            </div>
                        </li>

                        <li class="justify-between my-1">
                            <div class="font-bold text-sm">Organization</div>
                            <div class="text-xs md:text-sm"> {{ $certification->federation->name }} </div>
                        </li>
                    </ul>

                </div>



            </div>

        </div>--}}
    </a>
</div>
