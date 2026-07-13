{{--
    Federation brand mark. Renders the operator's logo image when
    FEDERATION_LOGO_PATH points to an existing file under public/,
    otherwise writes the federation name as text.
--}}
@props(['textClass' => 'text-2xl font-bold text-slate-800'])
@php
    $brandLogoPath = config('branding.primary.logo_path');
    $hasBrandLogo = $brandLogoPath && file_exists(public_path($brandLogoPath));
@endphp
@if ($hasBrandLogo)
    <img src="{{ asset($brandLogoPath) }}" alt="{{ config('branding.primary.short_name') }} logo" {{ $attributes }}>
@else
    <span {{ $attributes->except('class')->merge(['class' => $textClass]) }}>{{ config('branding.primary.name') }}</span>
@endif
