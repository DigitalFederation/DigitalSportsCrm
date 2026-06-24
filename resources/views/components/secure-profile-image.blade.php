@props(['individual', 'size' => 'thumb', 'class' => '', 'fallback' => '/img/user_placeholder.png', 'loading' => 'lazy'])

@php
    $imageUrl = $size === 'thumb'
        ? $individual->getSecureProfileThumbnailUrl()
        : $individual->getSecureProfileImageUrl();
@endphp

@if($imageUrl)
    <img src="{{ $imageUrl }}"
         alt="{{ $individual->full_name }}"
         class="{{ $class }}"
         loading="{{ $loading }}"
         onerror="this.onerror=null; this.src='{{ asset($fallback) }}';">
@else
    <img src="{{ asset($fallback) }}"
         alt="{{ __('common.default_profile_image') }}"
         class="{{ $class }}">
@endif