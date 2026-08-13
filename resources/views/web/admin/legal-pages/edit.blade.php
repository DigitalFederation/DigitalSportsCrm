@section('title', __('admin.legal_pages'))
<x-layout>
    <div class="previous-layout-classes">
        <div class="mb-8 flex justify-between">
            <h1 class="page-first-title">
                {{ __('admin.legal_pages_type_' . str_replace('-', '_', $type)) }}
                <span class="text-slate-400 font-normal">— {{ $locale }}</span>
            </h1>
            <a class="btn border-slate-200 hover:border-slate-300 text-slate-600"
               href="{{ route('admin.legal-pages.index') }}">
                {{ __('common.back') }}
            </a>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-700">
                <ul class="list-disc ml-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.legal-pages.update') }}" method="POST" id="legal-page-form">
            @csrf
            @method('PUT')
            <input type="hidden" name="type" value="{{ $type }}" />
            <input type="hidden" name="locale" value="{{ $locale }}" />

            <div class="card mb-6">
                <section class="mb-4 w-full">
                    @if ($draft)
                        <div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                            {{ __('admin.legal_pages_draft_notice') }}
                        </div>
                    @endif

                    <div class="flex flex-wrap -mx-4 mb-6">
                        <div class="w-full px-4 md:w-2/3">
                            <label class="block text-sm font-medium mb-1" for="title">
                                {{ __('admin.legal_pages_title_field') }}
                            </label>
                            <input id="title" class="form-input w-full" type="text" name="title"
                                   value="{{ old('title', $page?->title) }}" required />
                        </div>
                        <div class="w-full px-4 md:w-1/3">
                            <label class="block text-sm font-medium mb-1" for="effective_date">
                                {{ __('admin.legal_pages_effective_date') }}
                            </label>
                            <input id="effective_date" class="form-input w-full" type="date" name="effective_date"
                                   value="{{ old('effective_date', $page?->effective_date?->format('Y-m-d')) }}" />
                        </div>
                    </div>

                    <div class="px-4 mb-6">
                        <label class="block text-sm font-medium mb-1" for="body">
                            {{ __('admin.legal_pages_body') }}
                        </label>
                        <x-forms.tinymce-editor-static name="body" :value="old('body', $page?->body ?? '')" />
                        <p class="text-xs text-slate-500 mt-2">
                            {{ __('admin.legal_pages_tokens_hint') }}
                            <code class="text-slate-600">{{ implode('  ', $tokens) }}</code>
                        </p>
                    </div>

                    @if ($versions->isNotEmpty())
                        <div class="px-4">
                            <h2 class="text-sm font-semibold text-slate-800 mb-2">
                                {{ __('admin.legal_pages_history') }}
                            </h2>
                            <ul class="text-xs text-slate-500 space-y-1">
                                @foreach ($versions as $version)
                                    <li>
                                        v{{ $version->version }} —
                                        {{ $version->published_at?->format('d/m/Y H:i') }}
                                        @if ($loop->first)
                                            <span class="text-emerald-600">({{ __('admin.legal_pages_published_label') }})</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </section>

                <footer>
                    <div class="flex flex-col px-6 py-5 border-t border-slate-200">
                        <div class="flex self-end gap-2">
                            <a href="{{ route($type === 'terms-of-service' ? 'terms-of-service' : ($type === 'privacy-policy' ? 'privacy-policy' : 'data-sharing-policy')) }}"
                               target="_blank" rel="noopener"
                               class="btn border-slate-200 hover:border-slate-300 text-slate-600">
                                {{ __('admin.legal_pages_view_public') }}
                            </a>
                            <button type="submit" class="btn border-slate-200 hover:border-slate-300 text-slate-600">
                                {{ __('admin.legal_pages_save_draft') }}
                            </button>
                            <button type="submit" form="legal-page-publish"
                                    class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                                {{ __('admin.legal_pages_publish') }}
                            </button>
                        </div>
                    </div>
                </footer>
            </div>
        </form>

        {{-- Publish posts the same fields to a different action. --}}
        <form action="{{ route('admin.legal-pages.publish') }}" method="POST" id="legal-page-publish" class="hidden">
            @csrf
            @method('PUT')
        </form>

        <script>
            document.getElementById('legal-page-publish').addEventListener('submit', function (event) {
                const source = document.getElementById('legal-page-form');
                // Push the editor's current value into the hidden textarea first.
                if (window.tinymce) {
                    window.tinymce.triggerSave();
                }
                for (const name of ['type', 'locale', 'title', 'effective_date', 'body']) {
                    const field = source.querySelector('[name="' + name + '"]');
                    if (!field) continue;
                    let clone = this.querySelector('[name="' + name + '"]');
                    if (!clone) {
                        clone = document.createElement('input');
                        clone.type = 'hidden';
                        clone.name = name;
                        this.appendChild(clone);
                    }
                    clone.value = field.value;
                }
            });
        </script>
    </div>
</x-layout>
