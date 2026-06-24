<div class="card">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-xl leading-snug text-slate-800 font-bold">{{ __('event_applications.sections.comments') }}</h2>
        <button type="button"
                wire:click="toggleForm"
                class="btn btn-sm btn-primary">
            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
            </svg>
            <span class="ml-2">{{ __('event_applications.actions.add_comment') }}</span>
        </button>
    </div>

    @if($showForm)
        <div class="mb-6 p-4 bg-slate-50 rounded-lg"
             x-data
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">

            <form wire:submit="addComment">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2" for="comment">
                        {{ __('Comment') }} <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="comment"
                              wire:model="comment"
                              rows="3"
                              class="form-textarea w-full @error('comment') border-rose-300 @enderror"
                              required></textarea>
                    @error('comment')
                        <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox"
                               wire:model="is_internal"
                               class="form-checkbox">
                        <span class="text-sm ml-2">{{ __('Internal Note') }}</span>
                        <span class="text-xs text-slate-500 ml-2">({{ __('Not visible to applicant') }})</span>
                    </label>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button"
                            wire:click="toggleForm"
                            class="btn btn-sm btn-secondary">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="submit"
                            class="btn btn-sm btn-primary"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove>{{ __('event_applications.actions.add_comment') }}</span>
                        <span wire:loading>{{ __('Adding...') }}</span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="space-y-4" wire:poll.30s>
        @forelse($application->comments->sortByDesc('created_at') as $comment)
            <div class="p-4 rounded-lg {{ $comment->is_internal ? 'bg-yellow-50 border border-yellow-200' : 'bg-slate-50' }}">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <div class="font-medium text-slate-800">{{ $comment->user->name ?? 'System' }}</div>
                        @if($comment->is_internal)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-200 text-yellow-800">
                                {{ __('Internal') }}
                            </span>
                        @endif
                    </div>
                    <span class="text-xs text-slate-500">{{ $comment->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-sm text-slate-600 whitespace-pre-wrap">{{ $comment->comment }}</p>
            </div>
        @empty
            <div class="text-center py-8 text-slate-500">
                {{ __('No comments yet') }}
            </div>
        @endforelse
    </div>
</div>
