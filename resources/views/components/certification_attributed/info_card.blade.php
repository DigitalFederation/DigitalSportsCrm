<div class="card flex flex-auto max-w-sm">
    <div class="card-body">
        <p class="text-sm"><strong>CMAS nº:</strong> {{ $certificationAttributed->license_number }}</p>
        <p class="text-sm"><strong>{{ __('Nacional Code') }}:</strong> {{ $certificationAttributed->national_code }}</p>
        <p class="text-sm"><strong>{{ __('Certification date') }}:</strong> {{ Carbon\Carbon::parse($certificationAttributed->current_term_starts_at)->translatedFormat('d M Y') }}</p>

        <p class="text-sm"><strong>{{ __('Expiration date') }}:</strong>
            @if($certificationAttributed->current_term_ends_at)
                {{ Carbon\Carbon::parse($certificationAttributed->certification_attributed)->translatedFormat('d M Y') }}
            @else
                {{ __('No expiration date') }}
            @endif
        </p>
    </div>
</div>
