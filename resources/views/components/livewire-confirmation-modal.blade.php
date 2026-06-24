<!-- Confirmation Modal -->
@if($isOpen)
    <!-- Overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-40"></div>
    <div class="fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white p-4 rounded-lg shadow-lg border border-slate-200">
            <h3 class="font-bold text-lg">{{ $title }}</h3>
            <p class="mb-2">{{ $message }}</p>
            @if(isset($description))
                <p class="text-sm text-gray-600 mb-4 text-left">{{ $description }}</p>
            @endif

            <div class="flex justify-end mt-4 gap-x-2">
                <button type="button" wire:click="{{ $confirmMethod }}"
                        class="btn btn-sm text-white {{ $buttonColor }}">{{ $confirmText }}</button>
                <button type="button" wire:click="{{ $cancelMethod }}"
                        class="btn btn-sm btn-info">{{ $cancelText }}</button>
            </div>
        </div>
    </div>
@endif
