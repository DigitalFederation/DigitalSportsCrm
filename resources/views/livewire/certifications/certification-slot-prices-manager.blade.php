<div>
    <form wire:submit.prevent="save">
        <select wire:model.live="certificationId" class="form-input w-full">
            <option value="">Select Certification</option>
            @foreach($certifications as $certification)
                <option value="{{ $certification->id }}">{{ $certification->name }}</option>
            @endforeach
        </select>

        @foreach($prices as $index => $price)
            <div class="flex flex-col md:flex-row gap-x-2 my-2">
                <input wire:model="prices.{{ $index }}.quantity" type="number" placeholder="Quantity"
                       class="form-input w-full" required>
                <input wire:model="prices.{{ $index }}.unit_price" type="number" placeholder="Unit Price"
                       class="form-input w-full" step="0.01" required>
                <select wire:model="prices.{{ $index }}.slot_type" class="form-select">
                    <option value="" disabled selected>Select Slot Type</option>
                    @foreach($slotTypes as $type)
                        <option value="{{ $type->id }}"> {{ $type->name }} </option>
                    @endforeach
                </select>
                <button type="button" class="btn-sm btn-danger" wire:click="removePrice({{ $index }})">Remove</button>
            </div>
        @endforeach

        <div class="mt-4">
            <button type="button" class="btn btn-info" wire:click="addPrice">Add Price Bracket</button>
            <button type="submit" wire:loading.attr="disabled" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>
