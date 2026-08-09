@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === 'Laravel')
                <img src="https://laravel.com/img/notification-logo.png" class="logo" alt="Laravel Logo">
            @else
                @php
                    $brandLogoPath = config('branding.primary.logo_path');
                @endphp
                @if ($brandLogoPath && file_exists(public_path($brandLogoPath)))
                    <img src="{{ asset($brandLogoPath) }}" class="logo" alt="{{ config('branding.primary.short_name') }} Logo">
                @else
                    <span style="font-size: 19px; font-weight: bold; color: #3d4852;">{{ config('branding.primary.name') }}</span>
                @endif
            @endif
        </a>
    </td>
</tr>
