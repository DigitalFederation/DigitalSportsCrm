@component('mail::message')
# {{ __('event_applications.notifications.admin.deadline.title') }}

{{ __('event_applications.notifications.admin.deadline.greeting') }}

{{ __('event_applications.notifications.admin.deadline.intro', ['template' => $template->name]) }}

@component('mail::panel')
- **{{ __('event_applications.fields.template') }}**: {{ $template->name }}
- **{{ __('event_applications.fields.submission_end_date') }}**: {{ $template->submission_end_date->format('d/m/Y') }}
- **{{ __('event_applications.notifications.admin.deadline.days_remaining') }}**: {{ $daysRemaining }}
- **{{ __('event_applications.notifications.admin.deadline.applications_received') }}**: {{ $applicationsCount }}
@if($template->max_applications)
- **{{ __('event_applications.fields.max_applications') }}**: {{ $template->max_applications }}
@endif
@endcomponent

## {{ __('event_applications.notifications.admin.deadline.summary') }}

@if($applicationsCount > 0)
{{ __('event_applications.notifications.admin.deadline.summary_details', ['count' => $applicationsCount]) }}
@else
{{ __('event_applications.notifications.admin.deadline.no_applications') }}
@endif

@component('mail::button', ['url' => $url])
{{ __('event_applications.notifications.admin.deadline.action') }}
@endcomponent

{{ __('event_applications.notifications.admin.deadline.outro') }}

{{ __('common.best_regards') }},<br>
{{ config('app.name') }} {{ __('common.team') }}
@endcomponent
