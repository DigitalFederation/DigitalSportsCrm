<section class="md:w-96 w-full rounded-lg p-4 shadow-md bg-wave-gray-stacked bg-cover">

    <div class="-mt-12 mb-0 sm:mb-3">
        <figure class="h-10 w-10 md:h-20 md:w-20">
            <img src="{{ asset('img/flags/' . strtolower($individual->country->iso) . '.svg') }}" class="h-10 w-10 md:h-20 md:w-20 object-cover rounded-full border-4 border-white" alt="Country Flag"/>
        </figure>
    </div>

    <div class="flex gap-x-4 justify-between">
        <!-- Profile content -->
        <div class="flex flex-col gap-y-2">

            @if(!empty($individualType) && $individualType != 'Individual')
                <div>
                    <div class="text-secondary font-semibold text-sm">{{ __('Type')}}</div>
                    <p class="text-white font-bold">{{ $individualType }} </p>
                </div>
            @endif

            <div>
                <div class="text-secondary font-semibold text-sm">{{ __('Name')}}</div>
                <a href="{{ route(Request::segment(1).'.individual.show', $individual->id)}}" target="_blank" class="hover:underline text-white font-bold">
                    {{ $individual->native_name }}
                </a>

            </div>

            <div>
                <div class="text-secondary font-semibold text-sm">{{ __('Nationality / Country')}}</div>
                <p class="text-white font-bold">{{ $individual->country->name }}</p>
            </div>

            <div>
                <div class="text-secondary font-semibold text-sm">{{ __('Birthdate')}}</div>
                <p class="text-white font-bold">
                    {{ $individual->birthdate ? Carbon\Carbon::parse($individual->birthdate)->format('d/m/Y') : '---' }}
                </p>
            </div>

        </div>


        <!-- Profiel avatar -->
        <div class="flex flex-col gap-y-4">
            <div class="inline-flex">
                <a href="{{ route(Request::segment(1).'.individual.show', $individual->id)}}" target="_blank" class="hover:underline flex">
                    <figure class="text-center w-28 h-28">
                        <x-secure-profile-image :individual="$individual" size="thumb" class="object-fit rounded-full border-4 border-white" />
                    </figure>
                </a>
            </div>

            <!-- QR Code if exists -->
            @if(!empty($individual->qrcode_path))
            <div class="inline-flex">
                <figure class="text-center w-28 h-28">
                    <img src="{{ $individual->qrcode_path }}" class="object-cover rounded-full border-4 border-white " alt="QRCode"/>
                </figure>
            </div>
            @endif
        </div>

    </div>

</section>

