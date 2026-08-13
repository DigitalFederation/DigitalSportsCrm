@section('title', __('admin.legal_pages'))
<x-layout>
    <div class="previous-layout-classes">
        <div class="mb-8 flex justify-between">
            <h1 class="page-first-title">{{ __('admin.legal_pages') }}</h1>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="card mb-6">
            <section class="mb-4 w-full">
                <x-information-box
                    title="{{ __('admin.legal_pages') }}"
                    body="{{ __('admin.legal_pages_description') }}">
                </x-information-box>

                @foreach ($types as $type)
                    <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-3">
                        {{ __('admin.legal_pages_type_' . str_replace('-', '_', $type)) }}
                    </h2>

                    <div class="overflow-x-auto">
                        <table class="table-auto w-full text-sm">
                            <thead class="text-xs uppercase text-slate-500 bg-slate-50">
                                <tr>
                                    <th class="px-3 py-2 text-left">{{ __('admin.legal_pages_locale') }}</th>
                                    <th class="px-3 py-2 text-left">{{ __('admin.legal_pages_status') }}</th>
                                    <th class="px-3 py-2 text-left">{{ __('admin.legal_pages_version') }}</th>
                                    <th class="px-3 py-2 text-left">{{ __('legal.last_update') }}</th>
                                    <th class="px-3 py-2 text-right"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($locales as $locale)
                                    @php
                                        $versions = $pages[$type][$locale] ?? collect();
                                        $published = $versions->firstWhere('published_at', '!=', null);
                                        $draft = $versions->firstWhere('published_at', null);
                                    @endphp
                                    <tr>
                                        <td class="px-3 py-2 font-medium text-slate-800">
                                            {{ $localeLabels[$locale]['label'] ?? $locale }}
                                        </td>
                                        <td class="px-3 py-2">
                                            @if ($published)
                                                <span class="inline-flex rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 text-xs">
                                                    {{ __('admin.legal_pages_published_label') }}
                                                </span>
                                            @else
                                                <span class="inline-flex rounded-full bg-slate-100 text-slate-600 px-2 py-0.5 text-xs">
                                                    {{ __('admin.legal_pages_missing') }}
                                                </span>
                                            @endif
                                            @if ($draft)
                                                <span class="ml-1 inline-flex rounded-full bg-amber-100 text-amber-700 px-2 py-0.5 text-xs">
                                                    {{ __('admin.legal_pages_draft_label') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-slate-600">
                                            {{ $published?->version ? 'v' . $published->version : '—' }}
                                        </td>
                                        <td class="px-3 py-2 text-slate-600">
                                            {{ $published?->effective_date?->format('d/m/Y') ?? '—' }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <a class="btn-sm border-slate-200 hover:border-slate-300 text-indigo-500"
                                               href="{{ route('admin.legal-pages.edit', [$type, $locale]) }}">
                                                {{ __('common.edit') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </section>
        </div>
    </div>
</x-layout>
