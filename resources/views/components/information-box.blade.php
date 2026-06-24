<div class="bg-gradient-to-r from-blue-50 to-blue-100 border-l-4 border-primary rounded-lg shadow-md p-4 mb-5 w-full">
    <div class="flex gap-x-3 md:gap-x-4">
        <div class="bg-white p-1.5 rounded-full shadow-sm flex-shrink-0 self-start">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="relative">
            <h4 class="font-semibold text-primary mb-1.5">{{ $title }}</h4>
            <div class="text-sm text-gray-700" style="white-space: normal;">{!! $body !!}</div>
        </div>
    </div>
</div>
