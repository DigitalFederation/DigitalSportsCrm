{{-- components/certification/status.blade.php --}}
<span
    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
    <svg class="-ml-1 mr-1.5 h-2 w-2 text-{{ $color }}-400" fill="currentColor" viewBox="0 0 8 8">
        <circle cx="4" cy="4" r="3" />
    </svg>
    {{ __(ucfirst($status)) }}
</span>
