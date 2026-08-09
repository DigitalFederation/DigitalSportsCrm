@php
    $committee = optional($certificationAttributed->certification)->committee;
    $primaryBrand = config('branding.primary');
    $internationalBrand = config('branding.international');
    $logoPath = $committee?->getLogoPath() ?? $primaryBrand['logo_path'];
    $websiteUrlParts = $committee?->getWebsiteUrlParts() ?? ['prefix' => '', 'domain' => $primaryBrand['website_label'], 'suffix' => ''];
    $showInternationalInfo = $committee?->isInternational() ?? false;
@endphp
<!-- Responsive container for the card -->
<div class="relative">
    <div
        class="reverse-card card w-full p-2 overflow-hidden bg-white h-full flex flex-col justify-between">

        <!-- Card content -->
        <div class="flex justify-between items-end gap-x-2">
            <!-- Profile image -->
            <div class="flex-shrink-0 w-16 h-16 md:w-20 md:h-20 rounded-md overflow-hidden">
                @if($certificationAttributed->individual)
                    <x-secure-profile-image :individual="$certificationAttributed->individual" size="thumb" class="w-full h-full object-cover" />
                @endif
            </div>

            <!-- Certification details -->
            <div class="flex flex-col justify-start items-start text-left flex-grow">
                <div
                    class="text-sm md:text-lg font-semibold text-admin_blue md:!leading-5">
                    {{ $certificationAttributed->certification_name ?: optional($certificationAttributed->certification)->name ?? '' }}
                </div>
                <div
                    class="text-sm font-semibold text-slate-500">
                    @if($certificationAttributed->individual)
                        {{ $certificationAttributed->individual->name }} {{ $certificationAttributed->individual->surname }}
                    @else
                        {{ $certificationAttributed->holder_name }}
                    @endif
                </div>
            </div>

            @if($showInternationalInfo)
                <div class="flex-shrink-0 w-12 md:w-16">
                    <img class="w-full object-contain"
                         src="{{ asset($internationalBrand['secondary_logo_path']) }}"
                         alt="{{ $internationalBrand['name'] }} Logo">
                </div>
            @endif
        </div>

        <div class="w-full h-[2px] bg-admin_blue my-2"></div>

        <div class="flex justify-start items-start gap-x-2">
            <div class="flex flex-col w-16 md:w-20 items-center p-2 md:p-3">
                @if($showInternationalInfo)
                    @if($certificationAttributed->entity?->getFirstMediaUrl('profile'))
                        <img
                            class="w-full max-h-14 md:max-h-16 object-contain"
                            src="{{ $certificationAttributed->entity->getFirstMediaUrl('profile') }}"
                            alt="{{ $certificationAttributed->entity->name }}">
                    @endif
                @else
                    @if($logoPath)
                        <img class="w-full object-contain"
                             src="{{ asset($logoPath) }}"
                             alt="{{ $primaryBrand['short_name'] }} Logo">
                    @else
                        <div class="text-xs font-bold text-slate-800 text-center">{{ $primaryBrand['short_name'] }}</div>
                    @endif
                @endif
            </div>

            @php
                $hasDirector = !empty($certificationAttributed->mainInstructor->first());
            @endphp
            <div class="flex flex-col justify-start items-start text-left">
                <div class="text-xxs font-medium"><strong>{{ __('certifications.certification_number') }}</strong> {{ $certificationAttributed->national_code }}</div>
                <div class="text-xxs font-medium">
                    <strong>{{ __('certifications.card_country') }}</strong> {{ optional($certificationAttributed->federation)->country?->name }}</div>
                <div class="text-xxs font-medium"><strong>{{ __('certifications.issue_date') }}</strong> {{ Carbon\Carbon::parse($certificationAttributed->current_term_starts_at)->format('d/m/Y') }}
                </div>
                @if($certificationAttributed->current_term_ends_at)
                    <div class="text-xxs font-medium"><strong>{{ __('certifications.pdf.expire_date') }}</strong> {{ Carbon\Carbon::parse($certificationAttributed->current_term_ends_at)->format('d/m/Y') }}
                    </div>
                @endif
                <div class="text-xxs font-medium">
                    <strong>{{ __('certifications.organization') }}</strong> {{ $certificationAttributed->organizationDisplay() }}</div>
                @if($hasDirector || $showInternationalInfo)
                    <div class="text-xxs">
                        <strong class="uppercase">{{ __('certifications.course_director') }}</strong>
                        {{ $hasDirector ? $certificationAttributed->mainInstructor->first()->name . ' ' . $certificationAttributed->mainInstructor->first()->surname : __('certifications.national_technical_committee') }}
                    </div>
                @endif
                @if($certificationAttributed->entity)
                    <div class="text-xxs font-medium">
                        <strong class="uppercase">{{ __('certifications.school') }}</strong> {{ $certificationAttributed->entity->name }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
