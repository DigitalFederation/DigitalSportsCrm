@php
    $sectionComments = $application->comments
        ->where('section', $section)
        ->where('is_internal', false)
        ->sortByDesc('created_at');
@endphp

@if($sectionComments->isNotEmpty())
    <div class="mt-4 pt-4 border-t border-slate-200">
        <h4 class="text-sm font-medium text-slate-600 mb-3">{{ __('event_applications.labels.section_comments') }}</h4>
        <div class="space-y-2">
            @foreach($sectionComments as $comment)
                <div class="p-3 rounded-lg text-sm bg-yellow-50 border border-yellow-200">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-medium text-slate-700">{{ $comment->user->name ?? 'System' }}</span>
                        <span class="text-xs text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-slate-600 whitespace-pre-wrap">{{ $comment->comment }}</p>
                </div>
            @endforeach
        </div>
    </div>
@endif
