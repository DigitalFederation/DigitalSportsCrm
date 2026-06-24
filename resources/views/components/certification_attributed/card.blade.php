<div class="rounded-xl">
    <a href="{{ route('individual.certification-card.show', $certification->id) }}">
        @php
            $imagePath = 'img/cards/' . $certification->certification->certification_view;
            $imageExists = Storage::disk('public')->exists($imagePath);
            $imageUrl = $imageExists ? Storage::disk('public')->url($imagePath) : asset('img/default_certification_card.jpg');
        @endphp
        <img src="{{ $imageUrl }}" alt="{{ $certification->name }}" class="rounded-xl shadow-md">

        <!-- Display the certification name if default image is used -->
        @unless($imageExists)
            <div class="text-sm italic">
                {{ $certification->certification->name }}
            </div>
        @endunless
    </a>
</div>
