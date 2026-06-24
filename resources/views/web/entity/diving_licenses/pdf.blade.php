<x-pdf>
    @php
        $entity = $licenseAttributed->owner;
        $license = $licenseAttributed->license;

        // Get the technical directors for this license - only show names, not full personal details
        $technicalDirectors = $licenseAttributed->divingTechnicalDirectors()
            ->where('status_class', 'Domain\\Diving\\States\\AssignedDivingTechnicalDirectorState')
            ->with('individual:id,name,surname') // Only load necessary fields
            ->get();

        // Load professional certifications for each TD, grouped by [individual_id][certification_system]
        $tdIndividualIds = $technicalDirectors->pluck('individual_id')->filter()->unique()->values();
        $certificationsByTd = \Domain\Diving\Models\DivingProfessionalCertification::active()
            ->whereIn('individual_id', $tdIndividualIds)
            ->get()
            ->groupBy(fn ($cert) => $cert->individual_id . '|' . $cert->certification_system);

        // Build combined list: each entry is [system, td_name, certification_level, certification_number]
        $trainingSystemEntries = [];
        foreach ($technicalDirectors as $director) {
            if (!$director->individual || !is_array($director->certification_systems)) {
                continue;
            }
            $tdName = $director->individual->name . ' ' . $director->individual->surname;
            foreach ($director->certification_systems as $system) {
                $key = $director->individual_id . '|' . $system;
                $cert = $certificationsByTd->get($key)?->first();
                $trainingSystemEntries[] = [
                    'system' => $system,
                    'td_name' => $tdName,
                    'certification_level' => $cert?->certification_level ? __('diving.' . $cert->certification_level) : null,
                    'certification_number' => $cert?->certification_number,
                ];
            }
        }

        $brand = config('branding.primary');
        $brandLogoPath = public_path($brand['logo_path']);
        $brandLogoDataUri = null;
        if (file_exists($brandLogoPath)) {
            $brandLogoDataUri = 'data:' . mime_content_type($brandLogoPath) . ';base64,' . base64_encode(file_get_contents($brandLogoPath));
        }
    @endphp

    <style>
        /* Diploma border container */
        .diploma-border {
            border: 3px double #0066cc;
            position: relative;
            min-height: 100vh;
            background: white;
            padding: 20px;
            margin: 0;
        }

        /* Watermark background */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.06;
            z-index: 0;
            pointer-events: none;
        }

        .watermark img {
            width: 400px;
            height: auto;
        }

        /* Content wrapper to be above watermark */
        .content-wrapper {
            position: relative;
            z-index: 1;
        }

        .license-header {
            background-color: #0066cc;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: -20px -20px 30px -20px;
        }

        .license-title {
            text-align: center;
            font-size: 32px;
            font-weight: bold;
            margin: 40px 0;
            letter-spacing: 0.05em;
        }

        .license-content {
            margin: 30px 0;
            line-height: 1.8;
            text-align: justify;
        }

        .license-field {
            margin: 25px 0;
            text-align: center;
        }

        .license-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .license-value {
            font-size: 20px;
            font-weight: bold;
            color: #000;
        }

        .license-number {
            position: absolute;
            bottom: 25px;
            left: 20px;
            font-size: 12px;
            color: #666;
        }

        .logo-section {
            position: absolute;
            bottom: 25px;
            right: 20px;
            text-align: right;
        }

        .logo-section img {
            height: 80px;
        }

        .date-location {
            margin: 40px 0;
            font-size: 14px;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .training-systems-list {
            margin: 20px auto;
            max-width: 90%;
        }

        .training-system-entry {
            text-align: center;
            margin: 10px 0;
            font-size: 16px;
        }

        .training-system-entry strong {
            font-weight: bold;
        }
    </style>

    <div class="diploma-border">
        <!-- Watermark -->
        <div class="watermark">
            @if($brandLogoDataUri)
                <img src="{{ $brandLogoDataUri }}" alt="">
            @endif
        </div>

        <!-- Main content -->
        <div class="content-wrapper">
        <div class="license-header">
            {{ __('diving.license_header_recreational_diving_services') }}
        </div>

        <h1 class="license-title">{{ __('diving.diving_school') }}</h1>

        <div class="license-content">
            <p class="text-center">{{ __('diving.license_declaration_text') }}</p>

            <div class="license-field">
                <div class="license-value">{{ $entity->name }}</div>
            </div>

            <p class="text-center">
                {{ __('diving.license_legal_text_part1') }} 25º {{ __('diving.license_legal_text_and') }} 26º,
                {{ __('diving.license_legal_text_part2') }} 3 {{ __('diving.license_legal_text_of_article') }} 27º
                {{ __('diving.license_legal_text_part3') }} 24/2013, {{ __('diving.license_legal_text_date') }},
                {{ __('diving.license_legal_text_part4') }}
            </p>

            @if(count($trainingSystemEntries) > 0)
                <p class="text-center" style="margin-top: 30px;">
                    {{ __('diving.technical_director_responsible') }}
                </p>

                <div class="training-systems-list">
                    @foreach($trainingSystemEntries as $entry)
                        <div class="training-system-entry">
                            <strong>{{ __('diving.training_system_label') }}: {{ $entry['system'] }}</strong>
                            &mdash; {{ $entry['td_name'] }}
                            @if($entry['certification_level'])
                                | {{ $entry['certification_level'] }}
                            @endif
                            @if($entry['certification_number'])
                                | {{ $entry['certification_number'] }}
                            @endif
                        </div>
                    @endforeach
                </div>
            @elseif($technicalDirectors->count() > 0)
                <p class="text-center" style="margin-top: 30px;">
                    {{ __('diving.technical_director_responsible') }}
                </p>

                @foreach($technicalDirectors as $director)
                    @if($director->individual)
                        <div class="license-field">
                            <div class="license-value">
                                {{ $director->individual->name }} {{ $director->individual->surname }}
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif
        </div>

        <div class="date-location">
            {{ __('diving.location_lisbon') }}, {{ now()->format('d') }} {{ __('diving.of') }} {{ now()->translatedFormat('F') }} {{ __('diving.of') }} {{ now()->format('Y') }}
        </div>

            <div class="license-number">
                {{ $licenseAttributed->license_number }}
            </div>

            <div class="logo-section">
                @if($brandLogoDataUri)
                    <img src="{{ $brandLogoDataUri }}" alt="{{ $brand['short_name'] }}">
                @else
                    <div style="font-size: 10px; color: #999;">{{ $brand['short_name'] }}</div>
                @endif
            </div>
        </div>
    </div>
</x-pdf>
