{{-- components/certification/preview.blade.php --}}
<div class="relative transition-all duration-500 gap-y-4 flex flex-col">
    {{-- Front --}}
    <div class="shadow-lg rounded-xl">
        <x-certification_attributed.card_front :certificationAttributed="$certification" />
    </div>

    {{-- Back --}}
    <div class="shadow-lg rounded-xl">
        <x-certification_attributed.card_reverse :certificationAttributed="$certification" />
    </div>
</div>
