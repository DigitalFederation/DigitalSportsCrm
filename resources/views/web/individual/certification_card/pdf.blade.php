<x-pdf>
    @php
        $individual = $certification_attributed->individual;
        $certification = $certification_attributed;
        $committeeId = optional($certification->certification)->committee_id;
        $isSportCommittee = $committeeId == 2;
        $isDivingOrScientific = in_array($committeeId, [3, 4]); // SCIENTIFIC=3, DIVING=4
        $mainInstructor = $certification->mainInstructor->first();
        $hasDirector = !empty($mainInstructor);

        $primaryBrand = config('branding.primary');
        $internationalBrand = config('branding.international');
        $internationalLogoPath = $internationalBrand['logo_path'];
        $primaryLogoPath = $primaryBrand['logo_path'];
        $logoSrc = $isSportCommittee ? $primaryLogoPath : (file_exists(public_path($internationalLogoPath)) ? $internationalLogoPath : $primaryLogoPath);
        $logoSrc = ($logoSrc && file_exists(public_path($logoSrc))) ? $logoSrc : null;
        $logoAlt = $isSportCommittee ? $primaryBrand['short_name'] : $internationalBrand['short_name'];
        $organizationStandard = $isSportCommittee ? $primaryBrand['short_name'] : $internationalBrand['short_name'];
        $websiteUrl = $isSportCommittee ? $primaryBrand['website_label'] : ($isDivingOrScientific ? $internationalBrand['website_label'] : $primaryBrand['website_label']);

        $internationalOrganization = [
            'name' => $internationalBrand['name'],
            'country' => $internationalBrand['country'],
            'code' => $internationalBrand['code'],
            'email' => $internationalBrand['email'],
            'address' => $internationalBrand['address'],
            'postal' => $internationalBrand['postal'],
        ];
    @endphp
    <div class="inner-container">
        <div class="inner-container-border">
            {{-- Header with Logo --}}
            <div class="text-center mb-8">
                @if($logoSrc)
                    <img src="{{ $logoSrc }}" alt="{{ $logoAlt }}" class="card_logo mx-auto mb-4">
                @else
                    <div class="text-lg font-bold mb-4">{{ $logoAlt }}</div>
                @endif
                <h1 class="text-sm letter-spacing-lg text-blue-cmas">{{ __('certifications.pdf.title') }}</h1>
                <div class="horizontal-separator my-4"></div>
            </div>

            {{-- Card Images Section --}}
            <div class="cards-section">
                <table class="card-container">
                    <tr>
                        {{-- Card Front --}}
                        <td class="card-image">
                            @php
                                $cardFrontPath = $certification->certification && $certification->certification->certification_view
                                    ? storage_path('app/public/img/cards/' . $certification->certification->certification_view)
                                    : public_path('img/default_certification_card.jpg');
                            @endphp
                            @if(file_exists($cardFrontPath))
                                <img src="{{ $cardFrontPath }}" alt="Card Front" class="card-img">
                            @endif
                        </td>

                        {{-- Card Reverse (generated PNG) --}}
                        <td class="card-image">
                            @php
                                $cardReversePath = storage_path('app/public/certifications/certification_card_' . $certification->id . '.png');
                            @endphp
                            @if(file_exists($cardReversePath))
                                <img src="{{ $cardReversePath }}" alt="Card Reverse" class="card-img">
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            {{-- Student Name and Certification --}}
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold mb-4">{{ $individual->name }} {{ $individual->surname }}</h2>
                <p class="text-base mb-4">{{ __('certifications.pdf.statement', ['organization' => $organizationStandard]) }}</p>
                <h3 class="text-2xl font-bold letter-spacing-lg text-blue-cmas">{{ $certification->certification_name }}</h3>
                <div class="horizontal-separator my-4"></div>
            </div>

            {{-- Two Column Layout --}}
            <table class="info-table">
                <tr>
                    {{-- Left Column: Certification Details --}}
                    <td class="column-left">
                        <h2 class="section-title">{{ __('certifications.pdf.certification_details') }}</h2>
                        <table class="data-table">
                            @if(!$isSportCommittee && $certification->international_code)
                            <tr>
                                <td class="label">{{ __('certifications.pdf.cmas_number') }}</td>
                                <td class="value">{{ $certification->international_code }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="label">{{ __('certifications.pdf.national_number') }}</td>
                                <td class="value">{{ $certification->national_code }}</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('certifications.pdf.issue_date') }}</td>
                                <td class="value">{{ Carbon\Carbon::parse($certification->current_term_starts_at)->translatedFormat('d M Y') }}</td>
                            </tr>
                            <tr>
                                <td class="label">{{ __('certifications.pdf.expire_date') }}</td>
                                <td class="value">
                                    @if($certification->current_term_ends_at)
                                        {{ Carbon\Carbon::parse($certification->current_term_ends_at)->translatedFormat('d M Y') }}
                                    @else
                                        {{ __('certifications.pdf.no_expiration') }}
                                    @endif
                                </td>
                            </tr>
                            @if($hasDirector || !$isSportCommittee)
                            <tr>
                                <td class="label">{{ __('certifications.pdf.course_director') }}</td>
                                <td class="value">
                                    @if($hasDirector)
                                        {{ $mainInstructor->name }} {{ $mainInstructor->surname }}
                                    @else
                                        {{ __('certifications.pdf.national_technical_committee') }}
                                    @endif
                                </td>
                            </tr>
                            @endif
                        </table>
                    </td>

                    {{-- Right Column: Organization Details --}}
                    <td class="column-right">
                        <h2 class="section-title">{{ __('certifications.pdf.organization') }}</h2>
                        <table class="data-table">
                            @if($isDivingOrScientific)
                                {{-- Configured international federation data for DIVING/SCIENTIFIC --}}
                                <tr>
                                    <td class="label">{{ __('certifications.pdf.federation') }}</td>
                                    <td class="value">{{ $internationalOrganization['name'] }}</td>
                                </tr>
                                <tr>
                                    <td class="label">{{ __('certifications.pdf.country') }}</td>
                                    <td class="value">{{ $internationalOrganization['country'] }}</td>
                                </tr>
                                <tr>
                                    <td class="label">{{ __('certifications.pdf.federation_code') }}</td>
                                    <td class="value">{{ $internationalOrganization['code'] }}</td>
                                </tr>
                                <tr>
                                    <td class="label">{{ __('certifications.pdf.email') }}</td>
                                    <td class="value">{{ $internationalOrganization['email'] }}</td>
                                </tr>
                            @else
                                {{-- Dynamic federation data for SPORT/DIVINGSERVICES --}}
                                <tr>
                                    <td class="label">{{ __('certifications.pdf.federation') }}</td>
                                    <td class="value">{{ ucwords(strtolower($certification->federation->name)) }}</td>
                                </tr>
                                @if($certification->federation->country)
                                <tr>
                                    <td class="label">{{ __('certifications.pdf.country') }}</td>
                                    <td class="value">{{ $certification->federation->country->name }}</td>
                                </tr>
                                @endif
                                @if($certification->federation->member_code)
                                <tr>
                                    <td class="label">{{ __('certifications.pdf.federation_code') }}</td>
                                    <td class="value">{{ $certification->organizationDisplay() }}</td>
                                </tr>
                                @endif
                                @if($certification->federation->email)
                                <tr>
                                    <td class="label">{{ __('certifications.pdf.email') }}</td>
                                    <td class="value">{{ $certification->federation->email }}</td>
                                </tr>
                                @endif
                            @endif
                        </table>

                        {{-- Address Block --}}
                        @if($isDivingOrScientific)
                        <div class="address-block">
                            <span class="label">{{ __('certifications.pdf.address') }}:</span>
                            <span class="value">
                                {{ $internationalOrganization['address'] }}<br>
                                {{ $internationalOrganization['postal'] }}
                            </span>
                        </div>
                        @elseif($certification->federation->address)
                        <div class="address-block">
                            <span class="label">{{ __('certifications.pdf.address') }}:</span>
                            <span class="value">
                                {{ $certification->federation->address }}
                                @if($certification->federation->zip_code || $certification->federation->location)
                                    <br>{{ $certification->federation->zip_code }} {{ $certification->federation->location }}
                                @endif
                            </span>
                        </div>
                        @endif
                    </td>
                </tr>
            </table>

            {{-- Footer --}}
            <div class="mt-8 text-center">
                <div class="horizontal-separator my-4"></div>
                <p class="text-xs text-muted">{{ $websiteUrl }}</p>
                <p class="text-xs text-muted ref-id">Ref: {{ $certification->id }}</p>
            </div>
        </div>
    </div>

    <style>
        .card_logo {
            width: 80px;
            height: auto;
        }
        .cards-section {
            margin-bottom: 20px;
        }
        .card-container {
            width: 100%;
            table-layout: fixed;
        }
        .card-image {
            width: 50%;
            padding: 10px;
            text-align: center;
            vertical-align: middle;
        }
        .card-img {
            max-width: 100%;
            height: auto;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .text-base {
            font-size: 0.95rem;
            line-height: 1.6;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table > tr > td {
            vertical-align: top;
            padding: 0 10px;
        }
        .column-left {
            width: 50%;
            border-right: 1px solid #e5e7eb;
        }
        .column-right {
            width: 50%;
        }
        .section-title {
            font-size: 0.875rem;
            font-weight: bold;
            color: #0f6eb5;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #0f6eb5;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .data-table tr {
            border-bottom: 1px solid #f3f4f6;
        }
        .data-table td {
            padding: 6px 4px;
            font-size: 0.8rem;
        }
        .data-table .label {
            color: #6b7280;
            font-weight: 600;
            width: 45%;
        }
        .data-table .value {
            color: #1f2937;
        }
        .address-block {
            margin-top: 12px;
            padding: 8px;
            background-color: #f9fafb;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        .address-block .label {
            color: #6b7280;
            font-weight: 600;
        }
        .address-block .value {
            color: #1f2937;
        }
        .text-muted {
            color: #9ca3af;
        }
        .ref-id {
            margin-top: 4px;
            font-size: 0.65rem;
        }
    </style>
</x-pdf>
