@if($message)
    <div class="flex gap-4 {{ $bgColor }} p-4 rounded-md mb-4 items-center mt-2">
        <div class="w-max">
            <div class="flex rounded-full text-white">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="text-sm">
            <p class="text-white leading-tight">{{ $message }}</p>
        </div>
    </div>
@endif
