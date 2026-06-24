{{-- public/location-map/partials/_loading.blade.php --}}
<div wire:loading class="absolute top-6 right-6 z-20">
    <div class="bg-white/95 backdrop-blur-md rounded-xl shadow-lg px-4 py-3 border border-gray-100">
        <div class="flex items-center gap-3">
            <div class="animate-spin rounded-full h-5 w-5 border-2 border-blue-600 border-t-transparent"></div>
            <span class="text-sm font-medium text-gray-700">{{ __('location-map.Updating map...') }}</span>
        </div>
    </div>
</div>
