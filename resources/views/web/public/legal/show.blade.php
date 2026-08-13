@section('title', $title)
<x-public-layout>
    <main>
        <div class="mx-auto pt-4 w-24">
            <x-brand-logo class="w-24" text-class="text-2xl font-bold text-slate-800" />
        </div>

        <div class="max-w-4xl mx-auto p-6 text-gray-700">
            <h1 class="text-3xl font-bold mb-4">{{ $title }}</h1>

            @if ($effectiveDate)
                <p class="text-sm text-gray-500 mb-6">
                    {{ __('legal.last_update') }}: {{ $effectiveDate->format('d/m/Y') }}
                </p>
            @endif

            {{-- $body is sanitized on save by LegalHtmlSanitizer and token-resolved by
                 LegalPageRenderer; both passes run before it reaches this view. --}}
            <div class="prose prose-slate max-w-none prose-headings:font-semibold prose-a:text-indigo-600">
                {!! $body !!}
            </div>
        </div>
    </main>
</x-public-layout>
