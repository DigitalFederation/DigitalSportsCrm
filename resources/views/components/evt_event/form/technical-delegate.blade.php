@props(['technical_delegate', 'federations'])
<div class="card">

    <div class="flex gap-x-2 items-center border-b border-gray-300 pb-2 mb-4">
        <x-svg.info class="w-6 h-6 text-slate-600" />
        <span class="font-bold">{{ __('Technical Delegate') }}</span>
    </div>

    <div class="mb-4">
        <label for="name" class="block text-sm font-medium mb-1">Delegate Name</label>
        <input type="text" name="technical_delegate[name]" id="name" class="form-input w-full"
               value="{{ old('technical_delegate.name', $technical_delegate->name ?? '') }}">
        @error('technical_delegate.name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
    </div>

    <div class="flex flex-col md:flex-row justify-between gap-x-2 items-end mb-4">
        <div class="w-full md:w-1/2">
            <label for="memberCodeDelegateFederation" class="block text-sm font-medium mb-1">{{ __('certifications.member_code') }}</label>
            <input type="text" name="technical_delegate[member_code_delegate_federation]" id="memberCodeDelegateFederation"
                   class="form-input w-full"
                   value="{{ old('technical_delegate.member_code_delegate_federation', $technical_delegate->member_code_delegate_federation ?? '') }}"
            >
            @error('technical_delegate.member_code_delegate_federation') <span
                class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class=" md:w-1/2">
            <label for="federationId" class="block text-sm font-medium mb-1">Federation</label>
            <select name="technical_delegate[federation_id]" id="federationId" class="form-input w-full">
                <option value=""> -- Select an option --</option>
                @foreach($federations as $key => $federation)
                    <option
                        value="{{ $key }}" {{ old('technical_delegate.federation_id', $technical_delegate->federation_id ?? '') == $key ? 'selected' : '' }}>
                        {{ $federation }}
                    </option>
                @endforeach
            </select>
            @error('technical_delegate.federation_id') <span
                class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="flex flex-col md:flex-row justify-between gap-x-2 items-end">


        <div class="w-full md:w-1/2">
            <label for="appointmentByBodNumber" class="block text-sm font-medium mb-1">Appointment by BOD Number</label>
            <input type="text" name="technical_delegate[appointment_by_bod_number]" id="appointmentByBodNumber"
                   class="form-input w-full"
                   value="{{ old('technical_delegate.appointment_by_bod_number', $technical_delegate->appointment_by_bod_number ?? '') }}"
            >
            @error('technical_delegate.appointment_by_bod_number') <span
                class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="w-full md:w-1/2">
            <label for="dateOfBodAppointment" class="block text-sm font-medium mb-1">Date of BOD Appointment</label>
            <input type="date" name="technical_delegate[date_of_bod_appointment]" id="dateOfBodAppointment"
                   class="form-input w-full"
                   value="{{ old('technical_delegate.date_of_bod_appointment', $technical_delegate->date_of_bod_appointment ?? '') }}"
            >
            @error('technical_delegate.date_of_bod_appointment') <span
                class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
