<div class="relative" x-data="{ open: false }">
    <button
        @click="open = !open"
        class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900"
    >
        <span class="flex items-center">
            <img src="{{ asset('images/flags/' . app()->getLocale() . '.svg') }}" alt="{{ app()->getLocale() }}" class="w-5 h-5">
            <span class="ml-2">{{ strtoupper(app()->getLocale()) }}</span>
        </span>
        <svg class="w-5 h-5 ml-2 -mr-1" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </button>

    <div
        x-show="open"
        @click.away="open = false"
        class="absolute right-0 w-32 mt-2 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
        role="menu"
    >
        @foreach(config('app.locales') as $locale)
            <a
                href="{{ route('language.switch', $locale) }}"
                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                role="menuitem"
            >
                <img src="{{ asset('images/flags/' . $locale . '.svg') }}" alt="{{ $locale }}" class="w-5 h-5">
                <span class="ml-2">{{ strtoupper($locale) }}</span>
            </a>
        @endforeach
    </div>
</div>
