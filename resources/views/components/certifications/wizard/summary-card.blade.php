@props([
    'title',
    'individual' => null,
    'individuals' => collect(),
])

@if($individual)
    {{-- Single individual display (Course Director) --}}
    <div class="flex items-center gap-4 p-4 border rounded-lg bg-gradient-to-r from-primary-50 to-white shadow-sm">
        <div class="flex-shrink-0">
            <x-secure-profile-image :individual="$individual" size="medium" class="w-16 h-16 rounded-full ring-2 ring-primary-200 shadow" />
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-xs font-medium text-primary-600 uppercase tracking-wide mb-1">{{ $title }}</p>
            <p class="text-lg font-semibold text-gray-900 truncate">
                {{ $individual->name }} {{ $individual->surname }}
            </p>
            <p class="text-sm text-gray-500">
                {{ __('certifications.member_number') }}: <span class="font-medium text-gray-700">{{ $individual->member_number ?? __('certifications.n_a') }}</span>
            </p>
        </div>
    </div>
@elseif($individuals->isNotEmpty())
    {{-- Multiple individuals display (Assistants) --}}
    <div class="p-3 border rounded bg-white text-sm shadow-sm">
        <p class="font-medium mb-2">{{ $title }}:</p>
        <ul class="space-y-2">
            @foreach($individuals as $ind)
                <li class="flex items-center gap-2">
                    <x-secure-profile-image :individual="$ind" size="thumb" class="w-8 h-8 rounded-full" />
                    <div>
                        <span class="font-medium">{{ $ind->name }} {{ $ind->surname }}</span>
                        <span class="text-gray-500 text-xs ml-1">({{ $ind->member_number ?? $ind->member_code }})</span>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endif
