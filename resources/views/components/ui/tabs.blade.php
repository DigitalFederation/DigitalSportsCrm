@props([
    'tabs' => [],
    'defaultTab' => null,
    'tabsId' => 'tabs-' . uniqid()
])

<div x-data="{
    activeTab: '{{ $defaultTab ?? array_key_first($tabs) }}',
    tabs: {{ json_encode(array_keys($tabs)) }},
    showDropdown: false
}" class="w-full bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Mobile Tab Dropdown -->
    <div class="sm:hidden border-b border-gray-200">
        <div class="px-4 py-3">
            <button
                @click="showDropdown = !showDropdown"
                class="w-full flex items-center justify-between text-left"
            >
                <div class="flex items-center space-x-2">
                    @php
                        $currentTab = $tabs[$defaultTab ?? array_key_first($tabs)];
                    @endphp
                    @if(isset($currentTab['icon']))
                        <span class="flex-shrink-0" x-show="!showDropdown">{!! $currentTab['icon'] !!}</span>
                    @endif
                    <span class="text-sm font-medium text-gray-900" x-text="tabs.find(t => t === activeTab) ? {{ json_encode(array_map(fn($tab) => $tab['label'] ?? '', $tabs)) }}[activeTab] : ''"></span>
                </div>
                <svg class="h-5 w-5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        </div>
        
        <!-- Mobile Dropdown Menu -->
        <div x-show="showDropdown" 
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95"
             @click.away="showDropdown = false"
             class="absolute z-10 left-0 right-0 bg-white border-b border-gray-200 shadow-lg">
            @foreach($tabs as $key => $tab)
                <button
                    @click="activeTab = '{{ $key }}'; showDropdown = false"
                    :class="{
                        'bg-gray-50 text-primary': activeTab === '{{ $key }}',
                        'text-gray-700': activeTab !== '{{ $key }}'
                    }"
                    class="w-full px-4 py-3 text-left text-sm font-medium hover:bg-gray-50 focus:outline-none focus:bg-gray-50 transition-colors duration-150"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            @if(isset($tab['icon']))
                                <span class="flex-shrink-0">{!! $tab['icon'] !!}</span>
                            @endif
                            <span>{{ $tab['label'] ?? $key }}</span>
                        </div>
                        @if(isset($tab['badge']))
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                :class="{
                                    'bg-primary/10 text-primary': activeTab === '{{ $key }}',
                                    'bg-gray-100 text-gray-600': activeTab !== '{{ $key }}'
                                }"
                            >
                                {{ $tab['badge'] }}
                            </span>
                        @endif
                    </div>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Desktop Tab Navigation -->
    <div class="hidden sm:block border-b border-gray-200">
        <nav class="flex flex-wrap -mb-px px-2 sm:px-4 lg:px-6" aria-label="Tabs">
            @foreach($tabs as $key => $tab)
                <button
                    @click="activeTab = '{{ $key }}'"
                    :class="{
                        'border-b-2 border-primary text-primary': activeTab === '{{ $key }}',
                        'border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== '{{ $key }}'
                    }"
                    class="px-2 sm:px-3 lg:px-4 py-3 text-xs sm:text-sm font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 whitespace-nowrap"
                    :aria-selected="activeTab === '{{ $key }}'"
                    role="tab"
                >
                    <div class="flex items-center space-x-1 sm:space-x-2">
                        @if(isset($tab['icon']))
                            <span class="flex-shrink-0 hidden lg:inline-block">{!! $tab['icon'] !!}</span>
                        @endif
                        <span>{{ $tab['label'] ?? $key }}</span>
                        @if(isset($tab['badge']))
                            <span class="ml-1 sm:ml-2 inline-flex items-center px-1.5 sm:px-2 py-0.5 rounded-full text-xs font-medium"
                                :class="{
                                    'bg-primary/10 text-primary': activeTab === '{{ $key }}',
                                    'bg-gray-100 text-gray-600': activeTab !== '{{ $key }}'
                                }"
                            >
                                {{ $tab['badge'] }}
                            </span>
                        @endif
                    </div>
                </button>
            @endforeach
        </nav>
    </div>

    <!-- Tab Content -->
    <div>
        @foreach($tabs as $key => $tab)
            <div
                x-show="activeTab === '{{ $key }}'"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform translate-y-1"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform translate-y-1"
                role="tabpanel"
                class="p-6"
            >
                @php
                    $slotName = 'tab_' . $key;
                @endphp
                @if(isset($$slotName))
                    {{ $$slotName }}
                @endif
            </div>
        @endforeach
    </div>
</div>