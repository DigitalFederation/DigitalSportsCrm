@component('mail::message')
# {{ __('event_applications.notifications.rejected.title') }}

{{ __('event_applications.notifications.rejected.greeting', ['entity' => $entity->name]) }}

{{ __('event_applications.notifications.rejected.intro') }}

@component('mail::panel')
- **{{ __('event_applications.fields.event_name') }}**: {{ $application->event_name }}
- **{{ __('event_applications.fields.event_type') }}**: {{ $application->event_type }}
- **{{ __('event_applications.fields.start_date') }}**: {{ $application->start_date->format('d/m/Y') }}
@endcomponent

@if($adminNotes)
## {{ __('event_applications.notifications.rejected.reason') }}

@component('mail::panel')
{{ $adminNotes }}
@endcomponent
@endif

{{ __('event_applications.notifications.rejected.support') }}

@component('mail::button', ['url' => $url])
{{ __('event_applications.notifications.rejected.action') }}
@endcomponent

{{ __('event_applications.notifications.rejected.outro') }}

{{ __('common.best_regards') }},<br>
{{ config('app.name') }} {{ __('common.team') }}
@endcomponent
