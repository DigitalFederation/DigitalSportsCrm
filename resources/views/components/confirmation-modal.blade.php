

<div x-data="{ open: false }" @open-modal.window="if ($event.detail.id === 'modal-id') open = true" >
    <div x-show="open" x-transition class="fixed inset-0 overflow-hidden z-50">
        <div class="absolute inset-0 bg-black bg-opacity-75 transition-opacity duration-150 ease-in-out"></div>

        <div class="absolute inset-x-0 top-1/2 transform -translate-y-1/2 bg-white rounded-xl overflow-hidden shadow-xl p-4">
            <h2 class="text-xl font-bold mb-4">{{ $title }}</h2>

            <div>{!! $content !!}</div>

            <div class="flex justify-end mt-4">
                <button type="button" @click="open = false" class="btn btn-gray mr-2">Cancel</button>

                @if($isLivewireAction)
                    <button type="button" wire:click="{{ $action }}" class="btn btn-primary">Confirm</button>
                @else
                    <a href="{{ route($action) }}" class="btn btn-primary">Confirm</a>
                @endif
            </div>

        </div>
    </div>
</div>
