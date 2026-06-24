@php
    $sectionComments = $application->comments
        ->where('section', $section)
        ->sortByDesc('created_at');
@endphp

<div class="mt-4 pt-4 border-t border-slate-200" x-data="{ showForm: false }">
    <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-medium text-slate-600">{{ __('event_applications.labels.section_comments') }}</h4>
        <button type="button"
                @click="showForm = !showForm"
                class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            {{ __('event_applications.labels.add_section_comment') }}
        </button>
    </div>

    <div x-show="showForm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="mb-4 p-3 bg-slate-50 rounded-lg">

        <form action="{{ route($routeNamespace . '.event-applications.comment', ['application' => $application->id]) }}" method="POST">
            @csrf
            <input type="hidden" name="section" value="{{ $section }}">

            <div class="mb-3">
                <textarea name="comment"
                          rows="2"
                          class="form-textarea w-full text-sm"
                          placeholder="{{ __('event_applications.placeholders.admin_notes') }}"
                          required></textarea>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox" name="is_internal" value="1" class="form-checkbox">
                    <span class="text-xs ml-2">{{ __('event_applications.comment_types.internal') }}</span>
                </label>

                <div class="flex items-center gap-2">
                    <button type="button" @click="showForm = false" class="btn btn-xs btn-secondary">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-xs btn-primary">
                        {{ __('event_applications.labels.add_section_comment') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if($sectionComments->isNotEmpty())
        <div class="space-y-2">
            @foreach($sectionComments as $comment)
                <div class="p-3 rounded-lg text-sm {{ $comment->is_internal ? 'bg-yellow-50 border border-yellow-200' : 'bg-slate-50' }}">
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-slate-700">{{ $comment->user->name ?? 'System' }}</span>
                            @if($comment->is_internal)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-yellow-200 text-yellow-800">
                                    {{ __('event_applications.comment_types.internal') }}
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                            <form action="{{ route($routeNamespace . '.event-applications.comment.delete', ['application' => $application->id, 'comment' => $comment->id]) }}"
                                  method="POST"
                                  x-data
                                  @submit.prevent="if (confirm('{{ __('event_applications.confirmations.delete_comment') }}')) { $el.submit() }">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 transition-colors">
                                    {{ __('event_applications.labels.delete_comment') }}
                                </button>
                            </form>
                        </div>
                    </div>
                    <p class="text-slate-600 whitespace-pre-wrap">{{ $comment->comment }}</p>
                </div>
            @endforeach
        </div>
    @endif
</div>
