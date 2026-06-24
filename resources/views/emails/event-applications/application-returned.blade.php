@component('mail::message')
# {{ __('event_applications.notifications.returned.title') }}

{{ __('event_applications.notifications.returned.greeting', ['entity' => $entity->name]) }}

{{ __('event_applications.notifications.returned.intro') }}

@component('mail::panel')
- **{{ __('event_applications.fields.event_name') }}**: {{ $application->event_name }}
- **{{ __('event_applications.fields.event_type') }}**: {{ $application->event_type }}
- **{{ __('event_applications.fields.start_date') }}**: {{ $application->start_date->format('d/m/Y') }}
@endcomponent

## {{ __('event_applications.notifications.returned.admin_feedback') }}

@component('mail::panel')
{{ $adminNotes ?? __('event_applications.notifications.returned.no_notes') }}
@endcomponent

{{ __('event_applications.notifications.returned.action_required') }}

@component('mail::button', ['url' => $url])
{{ __('event_applications.notifications.returned.action') }}
@endcomponent

{{ __('event_applications.notifications.returned.outro') }}

{{ __('common.best_regards') }},<br>
{{ config('app.name') }} {{ __('common.team') }}
@endcomponent
