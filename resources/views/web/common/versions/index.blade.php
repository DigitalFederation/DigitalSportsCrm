<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title"> {{ __('Version & Changelog') }}</h1>
        </div>

        <h2 class="mb-4">{{ __(' Current version') }}: {{ $version  }}</h2>

        <section class="card w-full">
            <div class="sm:space-x-4 prose prose-sm">
                {!! $htmlChangelog !!}
            </div>
        </section>

    </div>
</x-layout>
