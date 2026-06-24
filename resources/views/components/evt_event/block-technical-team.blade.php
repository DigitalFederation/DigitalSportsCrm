@php
    $officials = [
        ['individual' => $event->technicalDelegate?->individual, 'label' => 'events.technical_delegate'],
        ['individual' => $event->chiefJudge?->individual, 'label' => 'events.chief_judge'],
        ['individual' => $event->competitionDirector?->individual, 'label' => 'events.competition_director'],
    ];
    $hasAnyOfficial = collect($officials)->contains(fn ($o) => $o['individual'] !== null);
@endphp

<div class="card h-full">
    <div class="flex gap-x-2 items-center border-b border-gray-300 pb-2 mb-4">
        <x-svg.person-lines class="w-6 h-6 text-slate-600" />
        <span class="font-bold">{{ __('events.technical_team') }}</span>
    </div>

    @if($hasAnyOfficial)
        <div class="flex flex-col gap-4">
            @foreach($officials as $official)
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full overflow-hidden bg-slate-200 shrink-0">
                        @if($official['individual'])
                            <img src="{{ $official['individual']->avatar_url }}"
                                 alt="{{ $official['individual']->full_name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <x-heroicon-o-user class="w-6 h-6 text-slate-400" />
                            </div>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wide">{{ __($official['label']) }}</p>
                        @if($official['individual'])
                            <p class="text-sm font-medium text-slate-700">{{ $official['individual']->full_name }}</p>
                        @else
                            <p class="text-sm text-slate-400 italic">{{ __('events.not_assigned') }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <x-utility.no-data :inCard="true" />
    @endif
</div>
