@php
    $cert = $certificationAttributed->certification;
    $imagePath = ! empty($cert?->certification_view)
        ? 'img/cards/' . $cert->certification_view
        : null;
    $imageSrc = $imagePath && Storage::disk('public')->exists($imagePath)
        ? Storage::disk('public')->url($imagePath)
        : asset('img/default_certification_card.jpg');
    $imageAlt = $cert?->name ?? $certificationAttributed->name;
@endphp

@if(Auth::user()->isIndividual())
    <a href="{{ route('individual.certification-card.show', $certificationAttributed->id) }}" class="rounded-lg">
        <img src="{{ $imageSrc }}" alt="{{ $imageAlt }}" class="rounded-lg">
    </a>
@else
    <img src="{{ $imageSrc }}" alt="{{ $imageAlt }}" class="rounded-lg">
@endif
