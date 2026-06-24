@php
    /**
    * @var string $title
    * @var int $itemFirstCount
    * @var int $itemSecondCount
    * @var string $itemFirstTitle
    * @var string $itemSecondTitle
    * @var string $itemFirstRoute
    * @var string $itemSecondRoute
    */
@endphp
@if(!empty($title))
    <div class="flex flex-col items-center justify-center mt-4">

        <div class="text-lg font-semibold leading-none tracking-tighter mb-4 text-center text-secondary">{{ $title }}</div>

        <div class="flex gap-2">

            @if(isset($itemFirstRoute))
                <div class="flex flex-col justify-center items-center">
                    <div class="text-2xl font-semibold leading-none tracking-tighter">
                        {{ $itemFirstCount }}
                    </div>
                    <a href="{{ $itemFirstRoute }}" class="text-xs btn-xs btn-outline mt-2 text-center text-secondary">{{ $itemFirstTitle }}</a>
                </div>
            @endif

            @if(isset($itemSecondRoute))
                <div class="flex flex-col justify-center items-center">
                    <div class="text-2xl font-semibold leading-none tracking-tighter">
                        {{ $itemSecondCount }}
                    </div>
                    <a href="{{ $itemSecondRoute }}" class="text-xs btn-xs btn-outline mt-2 text-center text-secondary">{{ $itemSecondTitle }}</a>
                </div>
            @endif

        </div>
    </div>
@endif
