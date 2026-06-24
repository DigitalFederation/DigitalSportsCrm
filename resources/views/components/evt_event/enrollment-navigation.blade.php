@props([
    'event',
    'active',
    'links' => null,
])

@php
    $groups = $links ?? [
        [
            'label' => __('events.athletes'),
            'color' => 'primary',
            'icon' => 'users',
            'links' => [
                [
                    'url' => route('admin.evt-events.events.enrollments.athlete.registered', $event),
                    'text' => __('events.to_confirm'),
                    'key' => 'athlete.registered',
                ],
                [
                    'url' => route('admin.evt-events.events.enrollments.athlete.index', $event),
                    'text' => __('events.confirmed'),
                    'key' => 'athlete.index',
                ],
            ],
        ],
        [
            'label' => __('events.coaches'),
            'color' => 'blue',
            'icon' => 'academic-cap',
            'links' => [
                [
                    'url' => route('admin.evt-events.events.enrollments.coach.registered', $event),
                    'text' => __('events.to_confirm'),
                    'key' => 'coach.registered',
                ],
                [
                    'url' => route('admin.evt-events.events.enrollments.coach.index', $event),
                    'text' => __('events.confirmed'),
                    'key' => 'coach.index',
                ],
            ],
        ],
        [
            'label' => __('events.team_officials'),
            'color' => 'emerald',
            'icon' => 'identification',
            'links' => [
                [
                    'url' => route('admin.evt-events.events.officials-enrollment.registered', $event),
                    'text' => __('events.to_confirm'),
                    'key' => 'official.registered',
                ],
                [
                    'url' => route('admin.evt-events.events.officials-enrollment.index', $event),
                    'text' => __('events.confirmed'),
                    'key' => 'official.index',
                ],
            ],
        ],
    ];
@endphp

<nav class="mb-5 flex flex-col gap-2 sm:flex-row sm:gap-3">
    @foreach ($groups as $group)
        <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-4 py-2.5">
            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-{{ $group['color'] }}-100 ring-1 ring-{{ $group['color'] }}-200/50">
                @if ($group['icon'] === 'users')
                    <x-heroicon-s-users class="w-4 h-4 text-{{ $group['color'] }}-600" />
                @elseif ($group['icon'] === 'academic-cap')
                    <x-heroicon-s-academic-cap class="w-4 h-4 text-{{ $group['color'] }}-600" />
                @else
                    <x-heroicon-s-identification class="w-4 h-4 text-{{ $group['color'] }}-600" />
                @endif
            </div>
            <span class="text-sm font-semibold text-slate-700 whitespace-nowrap">{{ $group['label'] }}</span>
            <div class="flex gap-1">
                @foreach ($group['links'] as $link)
                    <a
                        href="{{ $link['url'] }}"
                        @class([
                            'rounded-md px-3 py-1.5 text-sm transition-colors duration-150 whitespace-nowrap',
                            'bg-' . $group['color'] . '-100 text-' . $group['color'] . '-700 font-semibold' => $active === $link['key'],
                            'text-slate-600 hover:bg-slate-100' => $active !== $link['key'],
                        ])
                    >
                        {{ $link['text'] }}
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
</nav>
