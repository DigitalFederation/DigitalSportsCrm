<x-layout.sidebar />


<div
    class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden @if($attributes['background']){{ $attributes['background'] }}@endif"
    x-ref="contentarea">

    <x-layout.header />

    <main>
        @include('components.layout.banner_message')
        {{ $slot }}
    </main>

</div>
