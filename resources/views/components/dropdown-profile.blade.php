<div class="relative inline-flex" x-data="{ open: false }">
    <!-- Minimal user button with just avatar -->
    <button 
        class="inline-flex justify-center items-center group p-1.5 hover:bg-primary/5 rounded-full transition-all duration-200 relative" 
        aria-haspopup="true"
        x-on:click.prevent="open = !open" 
        :aria-expanded="open"
    >
        <!-- User avatar -->
        <div class="relative">
            <div class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-primary/20 ring-offset-1 bg-white">
                <img 
                    class="w-full h-full object-cover" 
                    src="{{ Auth::user()->profile_photo_url }}" 
                    alt="{{ Auth::user()->name }}" 
                />
            </div>
            <!-- Online status indicator -->
            <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
        </div>
        
        <!-- Dropdown arrow -->
        <div class="flex items-center ml-1.5">
            <svg class="w-4 h-4 text-gray-500 group-hover:text-primary transition-colors duration-200" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </div>
    </button>

    <!-- Minimal dropdown with just logout -->
    <div
        class="origin-top-right z-10 absolute top-full min-w-48 bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden mt-2 right-0"
        x-on:click.outside="open = false" @keydown.escape.window="open = false" x-ref="dropdown" x-show="open"
        x-transition:enter="transition ease-out duration-200 transform"
        x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-out duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" x-cloak>

        <!-- Simple user info -->
        <div class="p-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full overflow-hidden bg-gray-200 flex-shrink-0">
                    <img 
                        src="{{ Auth::user()->profile_photo_url }}" 
                        alt="{{ Auth::user()->name }}"
                        class="w-full h-full object-cover"
                    >
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-900 truncate">
                        @if (Auth::user()->group()->first()->code == 'ENTITY')
                            {{ Auth::user()->entities()->first()->name }}
                        @else
                            {{ Auth::user()->name }}
                        @endif
                    </div>
                    <div class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</div>
                </div>
            </div>
        </div>

        <!-- Quick logout -->
        <div class="p-2">
            <form method="POST" action="{{ route('logout') }}" x-data>
                @csrf
                <button type="submit" class="w-full flex items-center px-3 py-2 text-sm font-medium text-red-600 rounded-md hover:bg-red-50 transition-colors duration-150" @click.prevent="$root.submit();">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-3 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 001 1h12a1 1 0 001-1V4a1 1 0 00-1-1H3zm11 3a1 1 0 10-2 0v4.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L14 10.586V6z" clip-rule="evenodd" />
                    </svg>
                    {{ __('Sign Out') }}
                </button>
            </form>
        </div>
    </div>
</div>
