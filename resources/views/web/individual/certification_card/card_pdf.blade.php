<x-pdf>
    @php
        $certificationAttributed = $certification_attributed;
    @endphp
    <div class="mx-auto card-pdf">

        <div id="inverse-id-header" class="items-center border-b border-gray-600 mb-2 pb-1">

            <div style="width: 85%" class="float-left">
                <h3 class="text-lg text-gray-900">
                    {{ $certificationAttributed->certification_name }}
                </h3>

                <div class="">
                    <p class="text-sm text-gray-500">
                        {{ @$certificationAttributed->individual->name }} {{ @$certificationAttributed->individual->surname }}
                    </p>
                </div>
            </div>

            @php
                $committee = optional($certificationAttributed->certification)->committee;
                $logoPath = $committee?->getLogoPath() ?? config('branding.primary.logo_path');
                $logoAlt = $committee?->isInternational()
                    ? config('branding.international.short_name', 'IF')
                    : config('branding.primary.short_name', 'DF');
            @endphp
            <div class="float-right">
                @if($logoPath)
                    <img class="w-12" src="{{ asset($logoPath) }}" alt="{{ $logoAlt }} Logo">
                @else
                    <div class="text-sm font-bold">{{ $logoAlt }}</div>
                @endif
            </div>

            <div class="clear-both"></div>
        </div>

        <div id="inverse-id-body">
            <div class="flex-shrink-0 float-left">
                <div>
                    <img src="{{ $certificationAttributed->individual->getFirstMediaUrl('profile', 'thumb') }}"
                         alt="" class="w-12 h-12">
                </div>
                <div class="mt-1">
                    <img src="{{ $certificationAttributed->individual->qrcode_path }}"
                         alt="" class="w-12 h-12">
                </div>
                <p style="color: #1d4ed8; text-decoration: underline" class="text-xs mt-1">{{ config('app.url') }}</p>
            </div>

            <div style="width: 83%" class="float-right">
                <div class="text-xs font-bold text-gray-800"> {{ __("International Nº") }} <span
                        class="font-normal">{{ $certificationAttributed->license_number }}</span></div>
                <div style="white-space: nowrap" class="text-xs font-bold text-gray-800"> {{ __("National Nº") }} <span
                        class="font-normal">{{ $certificationAttributed->national_code }}</span></div>
                <div class="text-xs font-bold text-gray-800"> {{ __("Country") }} <span
                        class="font-normal">{{ optional($certificationAttributed->individual?->country)->name ?? '-' }}</span></div>
                <div class="text-xs font-bold text-gray-800"> {{ __("Issued Date") }}
                    <span
                        class="font-normal">{{ Carbon\Carbon::parse($certificationAttributed->current_term_starts_at)->translatedFormat('d M Y') }}</span>
                </div>
                @if(!empty($certificationAttributed->mainInstructor->first()))
                    <div class="text-xs font-semibold text-gray-800"> {{ __("Instructor") }}
                        <span class="font-normal"> {{ $certificationAttributed->mainInstructor->first()->name }} </span>
                    </div>
                @endif
            </div>

            <div class="clear-both"></div>
        </div>

    </div>
</x-pdf>
