<div class="md:col-span-1 flex justify-between">
    <div class="px-4 sm:px-0">
        <h3 class="text-lg font-semibold text-blue-800">{{ $title }}</h3>

        <p class="mt-2 text-sm text-gray-600 bg-blue-50 inline-block px-3 py-2 rounded-lg">
            {{ $description }}
        </p>
    </div>

    <div class="px-4 sm:px-0">
        {{ $aside ?? '' }}
    </div>
</div>
