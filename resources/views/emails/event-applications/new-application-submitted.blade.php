@component('mail::message')
# {{ __('event_applications.notifications.admin.new_submitted.title') }}

{{ __('event_applications.notifications.admin.new_submitted.greeting') }}

{{ __('event_applications.notifications.admin.new_submitted.intro') }}

@component('mail::panel')
- **{{ __('event_applications.fields.event_name') }}**: {{ $application->event_name }}
- **{{ __('event_applications.fields.event_type') }}**: {{ $application->event_type }}
- **{{ __('event_applications.fields.start_date') }}**: {{ $application->start_date->format('d/m/Y') }}
- **{{ __('event_applications.fields.end_date') }}**: {{ $application->end_date->format('d/m/Y') }}
- **{{ __('event_applications.fields.entity') }}**: {{ $entity->name }} ({{ class_basename($application->entity_type) }})
- **{{ __('event_applications.fields.template') }}**: {{ $template->name ?? 'N/A' }}
- **{{ __('event_applications.fields.submitted_at') }}**: {{ $application->submitted_at->format('d/m/Y H:i') }}
@endcomponent

## {{ __('event_applications.notifications.admin.new_submitted.action_required') }}

{{ __('event_applications.notifications.admin.new_submitted.action_details') }}

@component('mail::button', ['url' => $url])
{{ __('event_applications.notifications.admin.new_submitted.action') }}
@endcomponent

{{ __('event_applications.notifications.admin.new_submitted.outro') }}

{{ __('common.best_regards') }},<br>
{{ config('app.name') }} {{ __('common.team') }}
@endcomponent
