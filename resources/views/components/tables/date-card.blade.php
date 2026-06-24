<div class="bg-gray-50 rounded py-2 px-4 text-center w-fit flex flex-col">
    <span class="font-bold">{{ \Carbon\Carbon::parse($date)->translatedFormat('d') }}</span>
    <span class="font-normal">{{ \Carbon\Carbon::parse($date)->translatedFormat('M') }}</span>
    <span class="font-normal text-xs">{{ \Carbon\Carbon::parse($date)->translatedFormat('Y') }}</span>
</div>
