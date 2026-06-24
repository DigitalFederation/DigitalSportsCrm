@component('mail::message')
# {{ __('event_applications.notifications.admin.limit_reached.title') }}

{{ __('event_applications.notifications.admin.limit_reached.greeting') }}

{{ __('event_applications.notifications.admin.limit_reached.intro', ['template' => $template->name]) }}

@component('mail::panel')
- **{{ __('event_applications.fields.template') }}**: {{ $template->name }}
- **{{ __('event_applications.fields.max_applications') }}**: {{ $maxApplications }}
- **{{ __('event_applications.notifications.admin.limit_reached.total_received') }}**: {{ $totalApplications }}
- **{{ __('event_applications.fields.submission_end_date') }}**: {{ $template->submission_end_date->format('d/m/Y') }}
@endcomponent

## {{ __('event_applications.notifications.admin.limit_reached.action_required') }}

{{ __('event_applications.notifications.admin.limit_reached.action_details') }}

@component('mail::button', ['url' => $url])
{{ __('event_applications.notifications.admin.limit_reached.action') }}
@endcomponent

{{ __('event_applications.notifications.admin.limit_reached.outro') }}

{{ __('common.best_regards') }},<br>
{{ config('app.name') }} {{ __('common.team') }}
@endcomponent
