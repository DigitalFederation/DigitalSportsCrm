@component('mail::message')
# {{ __('event_applications.notifications.published.title') }}

{{ __('event_applications.notifications.published.greeting', ['entity' => $entity->name]) }}

{{ __('event_applications.notifications.published.intro') }}

@component('mail::panel')
- **{{ __('event_applications.fields.event_name') }}**: {{ $application->event_name }}
- **{{ __('event_applications.fields.event_type') }}**: {{ $application->event_type }}
- **{{ __('event_applications.fields.start_date') }}**: {{ $application->start_date->format('d/m/Y') }}
- **{{ __('event_applications.fields.end_date') }}**: {{ $application->end_date->format('d/m/Y') }}
- **{{ __('event_applications.fields.location') }}**: {{ $application->municipality }}, {{ $application->district->name ?? 'N/A' }}
@endcomponent

{{ __('event_applications.notifications.published.visibility') }}

@component('mail::button', ['url' => $eventUrl])
{{ __('event_applications.notifications.published.view_public') }}
@endcomponent

@component('mail::button', ['url' => $url, 'color' => 'success'])
{{ __('event_applications.notifications.published.manage') }}
@endcomponent

{{ __('event_applications.notifications.published.outro') }}

{{ __('common.best_regards') }},<br>
{{ config('app.name') }} {{ __('common.team') }}
@endcomponent
