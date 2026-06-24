<div>

    <div class="md:relative" x-data="{ open: false }">

        <div
            id="showNotificationDrawer"
            class="bell"
            @click.prevent="open = !open">
            @if($new_notifications == false)

                <div class="without-notifications cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                         stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                </div>
            @else

                <div class="with-notifications cursor-pointer">

                    <button
                        class="py-4 px-1 relative border-2 border-transparent text-gray-800 rounded-full hover:text-gray-400 focus:outline-none focus:text-gray-500 transition duration-150 ease-in-out"
                        aria-label="Bell">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0M3.124 7.5A8.969 8.969 0 015.292 3m13.416 0a8.969 8.969 0 012.168 4.5" />
                        </svg>
                        <span class="absolute inset-0 object-right-top -mr-6">
                            <div
                                class="inline-flex items-center border-2 border-white rounded-full text-xs font-semibold px-1.5 bg-red-500 text-white">
                            {{count($notifications)}}
                            </div>
                        </span>
                    </button>

                </div>
            @endif
        </div>


        <!-- Notification dropdown -->
        <div id="drawer-notifications"
             x-show="open"
             @click.outside="open = false"
             @keydown.escape.window="open = false"
             class="fixed z-50 right-0 top-0 w-72 h-screen bg-white border-l shadow-lg"
             tabindex="-1"
             aria-labelledby="drawer-label"
             aria-hidden="true"
             x-transition:enter="transform transition ease-in-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in-out duration-300"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             x-cloak>

            <div class="p-4">
                <h4 class="mb-1.5 leading-none text-lg font-bold text-gray-900">{{ __('common.notifications') }}</h4>
                <!-- Add Mark All as Read Button -->
                <button class="text-sm font-semibold text-blue-500 hover:text-blue-600" wire:click="markAllAsRead">
                    <span wire:loading.remove wire:target="markAllAsRead">{{ __('common.mark_all_as_read') }}</span>
                    <span wire:loading wire:target="markAllAsRead">{{ __('common.marking') }}</span>
                </button>
            </div>

            @if(empty($notifications) || count($notifications) == 0)
                <div class="border-b border-b-slate-400">
                    <div class="ml-4 py-2 text-slate-400">{{ __('common.no_unread_notifications') }}</div>
                </div>
            @else

                <div>
                    @foreach ($notifications as $notification )
                        @php($data = $notification->data)
                        <div class="first:bg-slate-100 border-b border-b-slate-300">
                            <div class="px-4 py-4 w-full border-l-2 border-orange-400 flex justify-between items-start">

                                <div class="w-full">
                                    @if(!empty($data['url']))
                                        <a href="{{ $data['url'] }}">
                                            <div class="text-gray-800 text-sm">
                                                {{ $data['message'] }}
                                            </div>
                                        </a>
                                    @else
                                        <div class="text-gray-800 text-sm">
                                            {{ $data['message'] }}
                                        </div>
                                    @endif

                                    <span
                                        class="text-xs mr-2 text-gray-400 ">{{ $notification->created_at->diffForHumans()}} </span>
                                </div>

                                <div class="flex justify-end text-right">
                                    <button class="p-0 m-0" wire:click="markAsRead('{{ $notification->id }}')">
                                        <x-svg.x-circle wire:target="markAsRead('{{ $notification->id }}')"
                                                        class="w-4 h-4 text-gray-400 hover:text-gray-600 cursor-pointer" />
                                    </button>
                                </div>
                            </div>
                        </div>

                    @endforeach
                </div>

            @endif

        </div>


    </div>
</div>
