@if ($method === 'GET')

    <a href="{{ $route }}" class="{{ $classes ?? $getClassForType($type) }}" title="{{ ucfirst($type) }}"
        target="{{ $target }}">
        <span class="sr-only">{{ ucfirst($type) }}</span>
        @if ($svg)
            {!! $svg !!}
        @else
            @include($getDefaultSvg($type), ['class' => $svgClass])
        @endif
    </a>
@else
    <form class="flex items-center" action="{{ $route }}" method="POST"
        onsubmit="return confirm('{{ $confirmText }}')">
        @csrf
        @method($method)
        <button type="submit" class="{{ $classes ?? $getClassForType($type) }}" title="{{ ucfirst($type) }}">
            <span class="sr-only">{{ ucfirst($type) }}</span>
            @if ($svg)
                {!! $svg !!}
            @else
                @include($getDefaultSvg($type), ['class' => $svgClass])
            @endif
        </button>
    </form>
@endif
