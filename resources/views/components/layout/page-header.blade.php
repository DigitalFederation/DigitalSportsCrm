{{--
Usage example:
<x-page-header
:title=""
:actions="[
    [
        'type' => 'link',
        'class' => 'btn-primary',
        'url' => "",
        'text' => 'Assign Certification'
    ],
    [
        'type' => 'form',
        'class' => 'btn-danger',
        'url' => "",
        'method' => 'DELETE',  // Optional, defaults to 'POST'
        'text' => 'Delete Certification'
    ]
]"
></x-page-header>
--}}

@php
    $actions = $actions ?? [];
@endphp

<div class="sm:flex sm:justify-between sm:items-center mb-4">

    <!-- Left: Title -->
    <div class="mb-4 sm:mb-0">
        <h1 class="page-first-title">{{ $title }}</h1>
        @if(!empty($subtitle))
            <div class="text-slate-600 text-lg">
                {{ $subtitle }}
            </div>
        @endif
    </div>

    <!-- Right: Actions -->
    <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
        @foreach($actions as $action)
            @if($action['type'] === 'link')
                <a class="{{ $action['class'] }}" href="{{ $action['url'] }}">
                    <span>{{ $action['text'] }}</span>
                </a>
            @elseif($action['type'] === 'form')
                <form method="POST" action="{{ $action['url'] }}">
                    @csrf
                    @method($action['method'] ?? 'POST')

                    <button class="{{ $action['class'] }}" type="submit">
                        {{ $action['text'] }}
                    </button>
                </form>
            @endif
        @endforeach
    </div>

</div>
