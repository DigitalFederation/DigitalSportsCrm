<div>
    <button wire:click="export" wire:loading.attr="disabled" class="btn btn-info" @if($isLoading) disabled @endif>
        <span wire:loading.remove wire:target="export">
            Export {{ $buttonTitle }}
        </span>
        <span wire:loading wire:target="export">Processing...</span>
    </button>
</div>
