<div>
    <!-- Display Success Message -->
    <x-alert-message/>


    <form wire:submit.prevent="save" class="mt-4">

        <input type="hidden" wire:model="competition_id">

        <!-- Delegate Name -->
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium mb-1">Delegate Name <span class="text-rose-500">*</span></label>
            <input type="text" wire:model="name" id="name" class="form-input w-full" required>
            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Federation ID -->
        <div class="mb-4">
            <label for="federationId" class="block text-sm font-medium mb-1">Federation <span
                    class="text-rose-500">*</span></label>
            <select wire:model="federationId" id="federationId" class="form-input w-full" required>
                <option value=""> -- Select an option -- </option>
                @foreach($federations as $key=>$federation)
                    <option value="{{$key}}"> {{ $federation }}</option>
                @endforeach
            </select>
            @error('federationId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>


        <div class="flex flex-col md:flex-row justify-between gap-x-2">

            <!-- international Delegate Code -->
            <div class="w-full md:w-1/3">
                <label for="memberCodeDelegateFederation" class="block text-sm font-medium mb-1">CMAS Delegate Code <span
                        class="text-rose-500">*</span></label>
                <input type="text" wire:model="memberCodeDelegateFederation" id="memberCodeDelegateFederation"
                       class="form-input w-full" required>
                @error('memberCodeDelegateFederation') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Appointment by BOD Number -->
            <div class="w-full md:w-1/3">
                <label for="appointmentByBodNumber" class="block text-sm font-medium mb-1">Appointment by BOD Number
                    <span class="text-rose-500">*</span></label>
                <input type="text" wire:model="appointmentByBodNumber" id="appointmentByBodNumber"
                       class="form-input w-full" required>
                @error('appointmentByBodNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Date of BOD Appointment -->
            <div class="w-full md:w-1/3">
                <label for="dateOfBodAppointment" class="block text-sm font-medium mb-1">Date of BOD Appointment <span
                        class="text-rose-500">*</span></label>
                <input type="date" wire:model="dateOfBodAppointment" id="dateOfBodAppointment" class="form-input w-full"
                       required>
                @error('dateOfBodAppointment') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

        </div>

        <!-- Submit Button -->
        <div class="mt-4 justify-end text-right">
            <button type="button" x-on:click="showModal = false" class="btn btn-info">{{ __('Close') }}</button>
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        </div>
    </form>


</div>
