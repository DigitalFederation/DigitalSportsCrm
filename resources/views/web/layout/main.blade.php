<x-layout.sidebar />


<div
    class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden @if($attributes['background']){{ $attributes['background'] }}@endif"
    x-ref="contentarea">

    <x-layout.header />

    <main class="page-wrapper px-4 sm:px-6 lg:px-8 py-6">
        @include('components.layout.banner_message')
        <div class="max-w-7xl mx-auto">
            {{ $slot }}
        </div>
    </main>

</div>
