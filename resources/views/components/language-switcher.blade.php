@php
    $availableLocales = config('app.available_locales', []);
    $currentLocale = app()->getLocale();
    $current = $availableLocales[$currentLocale] ?? ['label' => strtoupper($currentLocale), 'flag' => $currentLocale];
@endphp

<div class="relative" x-data="{ open: false }">
    <button
        @click="open = !open"
        type="button"
        class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900"
    >
        <img src="{{ asset('images/flags/' . ($current['flag'] ?? $currentLocale) . '.svg') }}"
             alt="{{ $current['label'] }}" class="w-5 h-5 rounded-sm object-cover">
        <span class="hidden sm:inline">{{ $current['label'] }}</span>
        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </button>

    <div
        x-show="open"
        x-cloak
        @click.away="open = false"
        class="absolute right-0 z-20 w-56 mt-2 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
        role="menu"
    >
        @foreach($availableLocales as $code => $meta)
            <a
                href="{{ route('language.switch', $code) }}"
                class="flex items-center gap-3 px-4 py-2 text-sm hover:bg-gray-100 {{ $code === $currentLocale ? 'font-semibold text-primary bg-gray-50' : 'text-gray-700' }}"
                role="menuitem"
            >
                <img src="{{ asset('images/flags/' . ($meta['flag'] ?? $code) . '.svg') }}"
                     alt="{{ $meta['label'] }}" class="w-5 h-5 rounded-sm object-cover">
                <span>{{ $meta['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>
