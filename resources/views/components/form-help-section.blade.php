@props(['key'])

<div x-data="{ open: false }" class="relative align-middle mb-3 w-full flex justify-end">
    {{-- Help Trigger Button --}}
    <button
        type="button"
        @click="open = !open"
        :aria-expanded="open.toString()"
        :aria-controls="'help-content-' . $key"
        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 transition duration-150 ease-in-out bg-blue-50 border border-blue-200 rounded-lg hover:text-blue-800 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 focus:bg-blue-100 focus:text-blue-700 shadow-sm"
        title="{{ __('Show help for this section') }}"
    >
        {{-- Help icon with background --}}
        <div class="bg-white rounded-full p-0.5 mr-1.5 shadow-sm">
            <svg class="w-3.5 h-3.5 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
        </div>
        {{-- Visible Text --}}
        <span>{{ __('Show help') }}</span>
    </button>

    {{-- Help Content Area (Collapsible Card) --}}
    {{-- Using Alpine's transition helpers directly for finer control --}}
    <div
        x-ref="panel"
        x-show="open"
        x-trap.noscroll="open"             {{-- alpine-focus-trap plugin --}}
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
        x-cloak {{-- Prevents flash of unstyled content if CSS loads late --}}
        @click.outside="open = false" {{-- Close when clicking outside --}}
        @keydown.escape.window="open = false" {{-- Close on escape key --}}
        id="help-content-{{ $key }}"
        class="absolute z-30 w-screen max-w-2xl mt-2 overflow-hidden origin-top-right transform bg-white border border-blue-100 rounded-xl shadow-lg right-0 top-full focus:outline-none"
        {{-- Position origin-top-right and right-0 for right alignment --}}
        tabindex="-1" {{-- Allow focus for escape key handling --}}
        role="tooltip"
     >
         <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-blue-100 flex items-center justify-between">
             <h3 class="text-sm font-medium text-blue-800">{{ __('Help Information') }}</h3>
             <button @click="open = false" class="text-blue-500 hover:text-blue-700">
                 <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                     <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                 </svg>
             </button>
         </div>
         {{-- Ensure Tailwind Typography plugin is installed and configured --}}
         <div class="prose prose-sm max-w-none prose-blue p-5 max-h-96 overflow-y-auto">
             {!! $content !!} {{-- Render the pre-parsed HTML --}}
         </div>
    </div>
</div>
