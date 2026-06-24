@if (!empty($isOrganizer) || auth()->user()->isAdmin())
    <div class="card mt-4 border-purple-600 w-full p-6">
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-xl font-semibold text-gray-900" id="management-heading">
                    {{ __('events.event_management') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ !empty($isOrganizer) ? __('events.organizer_controls') : __('Admin Controls') }}
                </p>
            </div>
            <span class="px-4 py-1.5 bg-purple-100 text-purple-700 rounded-full text-sm font-medium">
                {{ !empty($isOrganizer) ? __('Organizer') : __('Admin') }}
            </span>
        </div>

        <div class="space-y-6">
            <!-- Staff Registration Section -->
            @if ($event->isOrganizationEvent())
                <a href="{{ route(
                    !auth()->user()->isAdmin()
                        ? ($isEntity
                            ? 'entity.evt-events.events.staff-enrollment.index'
                            : 'federation.evt-events.events.staff-enrollment.index')
                        : 'cmas.evt-events.events.staff-enrollment.index',
                    [
                        'event' => $event,
                        'type' => \App\Enums\EvtEventEnrollmentRoleEnum::toSlug(\App\Enums\EvtEventEnrollmentRoleEnum::STAFF->value),
                    ],
                ) }}"
                    class="block p-4 bg-white border-2 border-gray-200 rounded-lg hover:border-purple-500 hover:shadow-lg transition-all group"
                    aria-labelledby="staff-heading">
                    <div class="flex items-center">
                        <div
                            class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-purple-100 rounded-lg group-hover:bg-purple-200">
                            <x-heroicon-o-users class="w-6 h-6 text-purple-600" />
                        </div>
                        <div class="ml-4">
                            <h3 class="font-medium text-gray-900" id="staff-heading">{{ __('events.staff_registration') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('events.manage_event_staff_members') }}</p>
                        </div>
                        <x-heroicon-o-chevron-right class="w-5 h-5 ml-auto text-gray-400 group-hover:text-purple-500" />
                    </div>
                </a>
            @endif

            <!-- Registration Lists Section -->
            <div class="bg-white border-2 border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('events.registration_lists') }}</h3>

                @if ($event->isOrganizationEvent())
                    <!-- Single List for Organization Events -->
                    <div class="flex items-center justify-between p-4 bg-purple-50 rounded-lg">
                        <div class="flex items-center">
                            <x-heroicon-o-user-group class="w-6 h-6 text-purple-600 mr-3" />
                            <div>
                                <h4 class="font-medium text-gray-900">{{ __('events.members_list') }}</h4>
                                <p class="text-sm text-gray-500">{{ __('events.manage_view_member_registrations') }}
                                </p>
                            </div>
                        </div>
                        <a href="{{ route(
                            !empty($isEntity)
                                ? 'entity.evt-events.events.organizer-enrollments.index'
                                : (auth()->user()->isAdmin()
                                    ? 'cmas.evt-events.events.organizer-enrollments.index'
                                    : 'federation.evt-events.events.organizer-enrollments.index'),
                            ['event' => $event, 'enrollmentType' => 'individual']
                        ) }}"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-purple-600 bg-white border border-purple-600 rounded-lg hover:bg-purple-50 transition-colors duration-200">
                            {{ __('events.view_list') }}
                            <x-heroicon-o-arrow-right class="w-4 h-4 ml-2" />
                        </a>
                    </div>
                @else
                    <!-- Tabbed Interface for Sport Events -->
                    <div x-data="{ activeTab: 'athlete' }" class="space-y-4">
                        <!-- Navigation Tabs -->
                        <nav class="flex space-x-4 border-b border-gray-200" role="tablist">
                            @php
                                $tabs = [
                                    'athlete' => [
                                        'label' => __('events.athletes'),
                                        'icon' => 'user-group',
                                        'listLabel' => __('events.athletes_enrollments_list'),
                                        'description' => __('events.manage_view_athlete_registrations'),
                                    ],
                                    'coach' => [
                                        'label' => __('events.coaches'),
                                        'icon' => 'academic-cap',
                                        'listLabel' => __('events.coaches_enrollments_list'),
                                        'description' => __('events.manage_view_coach_registrations'),
                                    ],
                                    'official' => [
                                        'label' => __('events.team_officials'),
                                        'icon' => 'clipboard-document-check',
                                        'listLabel' => __('events.officials_enrollments_list'),
                                        'description' => __('events.manage_view_official_registrations'),
                                    ],
                                    'staff' => [
                                        'label' => __('events.staff_members'),
                                        'icon' => 'user-group',
                                        'listLabel' => __('events.staff_enrollment_list'),
                                        'description' => __('events.manage_view_staff_registrations'),
                                    ],
                                ];
                            @endphp

                            @foreach ($tabs as $tab => $details)
                                <button @click="activeTab = '{{ $tab }}'"
                                    :class="{
                                        'text-purple-600 border-b-2 border-purple-600': activeTab === '{{ $tab }}',
                                        'text-gray-500 hover:text-gray-700': activeTab !== '{{ $tab }}'
                                    }"
                                    class="flex items-center px-4 py-2 text-sm font-medium transition-colors duration-200"
                                    :aria-selected="activeTab === '{{ $tab }}'" role="tab">
                                    <x-dynamic-component :component="'heroicon-o-' . $details['icon']" class="w-5 h-5 mr-2" />
                                    {{ $details['label'] }}
                                </button>
                            @endforeach
                        </nav>

                        <!-- Tab Panels -->
                        @foreach ($tabs as $tab => $details)
                            <div x-show="activeTab === '{{ $tab }}'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                role="tabpanel" class="space-y-4">
                                <div class="flex items-center justify-between p-4 bg-purple-50 rounded-lg">
                                    <div class="flex items-center">
                                        <x-dynamic-component :component="'heroicon-o-' . $details['icon']" class="w-6 h-6 text-purple-600 mr-3" />
                                        <div>
                                            <h4 class="font-medium text-gray-900">{{ $details['listLabel'] }}</h4>
                                            <p class="text-sm text-gray-500">
                                                {{ $details['description'] }}
                                            </p>
                                        </div>
                                    </div>
                                    @php
                                        if (!empty($isEntity)) {
                                            $routeName = $tab === 'staff'
                                                ? 'entity.evt-events.events.staff-enrollment.index'
                                                : 'entity.evt-events.events.organizer-enrollments.index';
                                        } else {
                                            $prefix = auth()->user()->isAdmin() ? 'admin' : 'federation';
                                            $routeName = $tab === 'staff' && !empty($isOrganizer)
                                                ? $prefix . '.evt-events.events.staff-enrollment.index'
                                                : $prefix . '.evt-events.events.organizer-enrollments.index';
                                        }
                                    @endphp
                                    <a href="{{ route($routeName, ['event' => $event, 'enrollmentType' => $tab]) }}"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-purple-600 bg-white border border-purple-600 rounded-lg hover:bg-purple-50 transition-colors duration-200">
                                        {{ __('events.view_list') }}
                                        <x-heroicon-o-arrow-right class="w-4 h-4 ml-2" />
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
