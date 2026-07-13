<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="view-transition" content="same-origin" />
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>@yield('title') {{ config('app.name', 'Digital Sports CRM') }}</title>

<!-- Critical CSS to prevent UI flashing -->
<style>
    [x-cloak] { display: none !important; }
</style>

<!-- Fonts -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

<!-- Styles -->
@livewireStyles
@filamentStyles
<!-- Scripts -->
<script>
    document.addEventListener("alpine:init", () => {
        Alpine.store("pageState", {
            loading: true,
            transition: false
        });
    });
</script>
@vite([
  'resources/css/app.css',
  'resources/css/custom-styles.css',
  'resources/js/app.js',
  'resources/js/qr-code-scanner.js',
])

@stack('head-css')
@stack('head-scripts')
