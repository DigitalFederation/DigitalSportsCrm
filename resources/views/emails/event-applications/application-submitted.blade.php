@component('mail::message')
# {{ __('event_applications.notifications.submitted.title') }}

{{ __('event_applications.notifications.submitted.greeting', ['entity' => $entity->name]) }}

{{ __('event_applications.notifications.submitted.intro') }}

@component('mail::panel')
- **{{ __('event_applications.fields.event_name') }}**: {{ $application->event_name }}
- **{{ __('event_applications.fields.event_type') }}**: {{ $application->event_type }}
- **{{ __('event_applications.fields.start_date') }}**: {{ $application->start_date->format('d/m/Y') }}
- **{{ __('event_applications.fields.end_date') }}**: {{ $application->end_date->format('d/m/Y') }}
- **{{ __('event_applications.notifications.submitted.reference') }}**: #{{ $application->id }}
@endcomponent

{{ __('event_applications.notifications.submitted.details') }}

@component('mail::button', ['url' => $url])
{{ __('event_applications.notifications.submitted.action') }}
@endcomponent

{{ __('event_applications.notifications.submitted.outro') }}

{{ __('common.best_regards') }},<br>
{{ config('app.name') }} {{ __('common.team') }}
@endcomponent
