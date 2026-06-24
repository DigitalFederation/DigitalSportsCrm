@component('mail::message')
# {{ __('event_applications.notifications.approved.title') }}

{{ __('event_applications.notifications.approved.greeting', ['entity' => $entity->name]) }}

{{ __('event_applications.notifications.approved.intro') }}

@component('mail::panel')
- **{{ __('event_applications.fields.event_name') }}**: {{ $application->event_name }}
- **{{ __('event_applications.fields.event_type') }}**: {{ $application->event_type }}
- **{{ __('event_applications.fields.start_date') }}**: {{ $application->start_date->format('d/m/Y') }}
- **{{ __('event_applications.fields.end_date') }}**: {{ $application->end_date->format('d/m/Y') }}
- **{{ __('event_applications.fields.location') }}**: {{ $application->municipality }}, {{ $application->district->name ?? 'N/A' }}
@endcomponent

## {{ __('event_applications.notifications.approved.next_steps') }}

{{ __('event_applications.notifications.approved.next_steps_details') }}

@component('mail::button', ['url' => $url])
{{ __('event_applications.notifications.approved.action') }}
@endcomponent

{{ __('event_applications.notifications.approved.outro') }}

{{ __('common.best_regards') }},<br>
{{ config('app.name') }} {{ __('common.team') }}
@endcomponent
