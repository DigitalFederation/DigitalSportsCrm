<div class="space-y-4">
    {{-- Racing Status Section (OUTOFRACE) --}}
    @if (!empty($attributes['status']))
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-3 py-2 bg-amber-50 border-b border-amber-100">
                <h4 class="text-sm font-semibold text-amber-800 flex items-center gap-2">
                    <x-heroicon-o-flag class="w-4 h-4" />
                    {{ __('events.racing_status') }}
                </h4>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach ($attributes['status'] as $attr)
                    <div class="px-3 py-2 flex items-center justify-between">
                        <span class="text-sm text-gray-600">{{ $attr['name'] }}</span>
                        @php
                            $isOutOfRace = strtolower($attr['value']) === 'yes';
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $isOutOfRace ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                            @if ($isOutOfRace)
                                <x-heroicon-m-x-circle class="w-3 h-3 mr-1" />
                                {{ __('events.out_of_race') }}
                            @else
                                <x-heroicon-m-check-circle class="w-3 h-3 mr-1" />
                                {{ __('events.racing') }}
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Global/Team Attributes Section (e.g., Relay times) --}}
    @if (!empty($attributes['global']))
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-3 py-2 bg-purple-50 border-b border-purple-100">
                <h4 class="text-sm font-semibold text-purple-800 flex items-center gap-2">
                    <x-heroicon-o-user-group class="w-4 h-4" />
                    {{ __('events.team_attributes') }}
                </h4>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach ($attributes['global'] as $attr)
                    <div class="px-3 py-2 flex items-center justify-between">
                        <span class="text-sm text-gray-600">{{ $attr['name'] }}</span>
                        @if (in_array($attr['type'] ?? '', ['TIME', 'BESTTIME']))
                            <span class="text-sm font-mono font-semibold text-gray-900 bg-gray-100 px-2 py-0.5 rounded">
                                {{ $attr['value'] }}
                            </span>
                        @else
                            <span class="text-sm font-medium text-gray-900">{{ $attr['value'] }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Empty State --}}
    @if (empty($attributes['status']) && empty($attributes['global']))
        <div class="text-center py-6">
            <x-heroicon-o-document class="w-10 h-10 mx-auto text-gray-300" />
            <p class="mt-2 text-sm text-gray-500">{{ __('events.no_additional_attributes') }}</p>
        </div>
    @endif
</div>
