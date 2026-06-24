<div>
    <select wire:model.live="status_class" class="form-select">
        <option value="Domain\EvtEvents\States\ActiveEventState">Active</option>
        <option value="Domain\EvtEvents\States\ArchiveEventState">Archive</option>
        <option value="Domain\EvtEvents\States\PreparationEventState">Preparation</option>
        <option value="Domain\EvtEvents\States\CanceledEventState">Canceled</option>
    </select>
    <p class="text-xs text-gray-400">Update event status</p>
</div>
