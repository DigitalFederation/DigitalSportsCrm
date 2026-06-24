@props(['submit'])

<div {{ $attributes->merge(['class' => 'md:grid md:grid-cols-3 md:gap-8']) }}>
    <x-section-title>
        <x-slot name="title">{{ $title }}</x-slot>
        <x-slot name="description">{{ $description }}</x-slot>
    </x-section-title>

    <div class="mt-5 md:mt-0 md:col-span-2">
        <form wire:submit="{{ $submit }}">
            <div
                class="px-6 py-6 bg-white sm:p-7 shadow-sm border border-blue-50 {{ isset($actions) ? 'sm:rounded-t-xl' : 'sm:rounded-xl' }}">
                <div class="grid grid-cols-6 gap-6">
                    {{ $form }}
                </div>
            </div>

            @if (isset($actions))
                <div
                    class="flex items-center justify-end px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 text-right sm:px-7 shadow-sm border-x border-b border-blue-50 sm:rounded-b-xl">
                    {{ $actions }}
                </div>
            @endif
        </form>
    </div>
</div>
