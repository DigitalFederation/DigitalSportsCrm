@php
    $conflicts = [];

    if ($application->template) {
        $sameTemplateApplications = $application->template->activeApplications()
            ->where('id', '!=', $application->id)
            ->where('entity_id', $application->entity_id)
            ->get();

        if ($sameTemplateApplications->count() > 0) {
            $conflicts[] = [
                'type' => 'warning',
                'title' => __('Multiple Applications'),
                'message' => __('This entity has submitted :count other application(s) for the same template.', ['count' => $sameTemplateApplications->count()]),
            ];
        }
    }

    $overlappingEvents = \Domain\EventApplications\Models\EventApplication::query()
        ->where('entity_id', $application->entity_id)
        ->where('id', '!=', $application->id)
        ->whereNotNull('start_date')
        ->whereNotNull('end_date')
        ->where(function($query) use ($application) {
            $query->whereBetween('start_date', [$application->start_date, $application->end_date])
                ->orWhereBetween('end_date', [$application->start_date, $application->end_date])
                ->orWhere(function($q) use ($application) {
                    $q->where('start_date', '<=', $application->start_date)
                      ->where('end_date', '>=', $application->end_date);
                });
        })
        ->get();

    if ($overlappingEvents->count() > 0) {
        $conflicts[] = [
            'type' => 'info',
            'title' => __('Date Overlap'),
            'message' => __('This entity has :count other event(s) with overlapping dates.', ['count' => $overlappingEvents->count()]),
        ];
    }
@endphp

@if(count($conflicts) > 0)
    <div class="card">
        <h3 class="grow font-semibold text-slate-800 truncate mb-4">{{ __('Alerts & Conflicts') }}</h3>

        <div class="space-y-3">
            @foreach($conflicts as $conflict)
                <div class="p-3 rounded-lg border {{ $conflict['type'] === 'warning' ? 'bg-yellow-50 border-yellow-200' : 'bg-blue-50 border-blue-200' }}">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 {{ $conflict['type'] === 'warning' ? 'text-yellow-600' : 'text-blue-600' }} mt-0.5 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <h4 class="text-sm font-semibold {{ $conflict['type'] === 'warning' ? 'text-yellow-800' : 'text-blue-800' }}">
                                {{ $conflict['title'] }}
                            </h4>
                            <p class="text-sm {{ $conflict['type'] === 'warning' ? 'text-yellow-700' : 'text-blue-700' }} mt-1">
                                {{ $conflict['message'] }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
