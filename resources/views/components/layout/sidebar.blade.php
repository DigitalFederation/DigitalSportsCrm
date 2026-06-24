<div>
    <!-- Sidebar backdrop (mobile only) -->
    <div
        class="fixed inset-0 bg-slate-900 bg-opacity-30 z-40 lg:hidden lg:z-auto transition-opacity duration-200 opacity-0 pointer-events-none"
        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'"
        aria-hidden="true"
        x-cloak>
    </div>

    <!-- Sidebar -->
    <div id="sidebar"
         class="flex flex-col fixed z-[1001] left-0 top-0 lg:relative lg:left-auto lg:top-auto h-screen overflow-y-auto scrollbar-hide w-full lg:w-72 2xl:w-72 bg-white border-r border-gray-200 transition-all duration-200 ease-in-out"
         :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen, 'lg:translate-x-0': true }"
         @click.outside="sidebarOpen = false"
         @keydown.escape.window="sidebarOpen = false"
         x-on:touchmove.self.prevent="$el.scrollTop += event.touches[0].clientY - $el.getBoundingClientRect().top"
    >
        <!-- Close button (mobile) -->
        <button
            class="absolute top-4 right-4 lg:hidden text-gray-500 hover:text-gray-700"
            @click.stop="sidebarOpen = !sidebarOpen"
            aria-controls="sidebar"
            :aria-expanded="sidebarOpen">
            <span class="sr-only">{{ __('common.close') }}</span>
            <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M18.3 5.7a1 1 0 0 0-1.4 0L12 10.6 7.1 5.7a1 1 0 1 0-1.4 1.4L10.6 12l-4.9 4.9a1 1 0 1 0 1.4 1.4L12 13.4l4.9 4.9a1 1 0 0 0 1.4-1.4L13.4 12l4.9-4.9a1 1 0 0 0 0-1.4z" />
            </svg>
        </button>

        <!-- User Profile Section -->
        <div class="p-3 border-b border-gray-100" x-data="{ profileOpen: false }">
            <div class="relative">
                @php
                    $user = Auth::user();
                    $userGroup = $user->group()->first();
                    $groupCode = $userGroup->code ?? '';
                    
                    // Determine display name based on user type
                    $displayName = $groupCode == 'ENTITY' && $user->entities()->first() 
                        ? $user->entities()->first()->name 
                        : $user->name;
                    
                @endphp
                
                <button 
                    @click="profileOpen = !profileOpen"
                    class="flex items-center w-full p-2 rounded-lg hover:bg-gray-50 transition-colors duration-150"
                >
                    <div class="flex items-center flex-1 min-w-0">
                        <!-- User Avatar with actual photo -->
                        <div class="w-10 h-10 rounded-full overflow-hidden bg-gray-200 flex-shrink-0">
                            <img 
                                src="{{ $user->profile_photo_url }}" 
                                alt="{{ $displayName }}"
                                class="w-full h-full object-cover"
                            >
                        </div>
                        <div class="ml-3 flex-1 min-w-0">
                          
                            <div class="text-sm font-semibold text-gray-900 truncate">{{ $displayName }}</div>
               
                            <div class="text-xs text-gray-500 truncate">{{ $user->email }}</div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 flex-shrink-0" :class="profileOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </button>
                
                <!-- Profile Dropdown -->
                <div 
                    x-show="profileOpen"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    @click.outside="profileOpen = false"
                    class="absolute left-0 right-0 mt-2 bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden z-50"
                    x-cloak
                >
                    <!-- User Roles Section -->
                    @if($user->roles->count() > 0)
                        <div class="px-4 py-3 border-b border-gray-100">
                            <div class="text-xs font-medium text-gray-500 mb-2">{{ __('Your Roles') }}</div>
                            <div class="flex flex-wrap gap-1">
                                @foreach($user->roles as $role)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <!-- Federation Info for Entity Users -->
                    @if($groupCode == 'ENTITY')
                        @php
                            $federation = $user->entities()->first()?->federations()->first();
                        @endphp
                        @if($federation)
                            <div class="px-4 py-2 border-b border-gray-100">
                                <div class="text-xs text-gray-500">{{ __('Federation') }}</div>
                                <div class="text-sm font-medium text-gray-900">{{ $federation->name }}</div>
                            </div>
                        @endif
                    @endif
                    
                    <!-- Quick Links Based on User Type -->
                    <div class="py-1">
                        @if($groupCode == 'INDIVIDUAL')
                            <a href="{{ route('individual.individual.show') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                @svg('heroicon-o-user', 'w-4 h-4 mr-3 text-gray-400')
                                {{ __('Individual Details') }}
                            </a>
                        @elseif($groupCode == 'ENTITY')
                            <a href="{{ route('entity.profile.edit') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                @svg('heroicon-o-building-office', 'w-4 h-4 mr-3 text-gray-400')
                                {{ __('Entity Details') }}
                            </a>
                        @elseif($groupCode == 'FEDERATION')
                            <a href="{{ route('federation.profile.edit') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                @svg('heroicon-o-user-group', 'w-4 h-4 mr-3 text-gray-400')
                                {{ __('Federation Details') }}
                            </a>
                            <a href="{{ route('federation.my-official-documents.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                @svg('heroicon-o-document-text', 'w-4 h-4 mr-3 text-gray-400')
                                {{ __('Legal Documents') }}
                            </a>
                        @endif
                        
                        <!-- User Account Settings -->
                        <a href="{{ route('profile.show') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            @svg('heroicon-o-cog-6-tooth', 'w-4 h-4 mr-3 text-gray-400')
                            {{ __('User account') }}
                        </a>
                        
                        <!-- Changelog for Federation/Admin -->
                        @if(in_array($groupCode, ['FEDERATION', 'ADMIN']))
                            <a href="{{ route('changelog') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                @svg('heroicon-o-document-text', 'w-4 h-4 mr-3 text-gray-400')
                                {{ __('Changelog') }}
                            </a>
                        @endif
                    </div>
                    
                    <!-- Sign Out -->
                    <div class="border-t border-gray-100">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 text-left">
                                @svg('heroicon-o-arrow-right-on-rectangle', 'w-4 h-4 mr-3 text-red-500')
                                {{ __('Sign Out') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <div class="flex-1 px-3 py-4 overflow-y-auto">
            <nav>
                <ul class="list-none space-y-1">
                    @include('components.layout.sidebar.' . \App\Helpers\SidebarHelper::getUserSidebar(Auth::user()))
                </ul>
            </nav>
        </div>

        <!-- Bottom Section -->
        <div class="border-t border-gray-200 p-4">
            <div class="flex items-center justify-between text-xs text-gray-500">
                <span>© {{ date('Y') }} {{ config('app.name') }}</span>
                <span>v{{ config('app.version', '1.0.0') }}</span>
            </div>
        </div>
    </div>
</div>
