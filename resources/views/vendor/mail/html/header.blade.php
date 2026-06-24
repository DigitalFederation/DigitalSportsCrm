@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === 'Laravel')
                <img src="https://laravel.com/img/notification-logo.png" class="logo" alt="Laravel Logo">
            @else
                <img src="{{ asset(config('branding.primary.logo_path', 'img/project-logo.svg')) }}" class="logo" alt="{{ config('branding.primary.short_name', 'DF') }} Logo">
            @endif
        </a>
    </td>
</tr>
