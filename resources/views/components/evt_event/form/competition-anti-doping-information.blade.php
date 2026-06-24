<div class="flex flex-col gap-4">

        <div class="flex flex-col md:flex-row items-end gap-4 ">
            <div class="w-full md:w-1/2">
                <label for="num_controls_planned" class="block text-sm font-medium mb-1">
                    Planned Nº of controls
                </label>
                <input type="number"
                       min="0"
                       id="num_controls_planned"
                       class="form-input w-full"
                       name="anti_doping[num_controls_planned]"
                       value="{{ old('anti_doping.num_controls_planned', $antiDoping?->num_controls_planned) }}">
                @error('anti_doping.num_controls_planned') <span
                    class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="w-full md:w-1/2">
                <label for="number_of_controls" class="block text-sm font-medium mb-1">
                    Number of controls
                </label>
                <input type="number"
                       min="0"
                       id="number_of_controls"
                       class="form-input w-full"
                       name="anti_doping[number_of_controls]"
                       value="{{ old('anti_doping.number_of_controls', $antiDoping?->number_of_controls) }}">
                @error('anti_doping.number_of_controls') <span
                    class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>
        <!-- Responsible name -->
        <div class="w-full">
            <label for="responsible_name" class="block text-sm font-medium mb-1">
                Responsible name
            </label>
            <input type="text"
                   id="responsible_name"
                   placeholder="Name of Doping contact"
                   class="form-input w-full"
                   name="anti_doping[responsible_name]"
                   value="{{ old('anti_doping.responsible_name', $antiDoping?->responsible_name) }}">
            @error('anti_doping.responsible_name') <span
                class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Responsible email -->
        <div class="w-full">
            <label for="responsible_email" class="block text-sm font-medium mb-1">
                Responsible email
            </label>
            <input type="email"
                   id="responsible_email"
                   class="form-input w-full"
                   placeholder="Email for Doping contact"
                   name="anti_doping[responsible_email]"
                   value="{{ old('anti_doping.responsible_email', $antiDoping?->responsible_email) }}">
            @error('anti_doping.responsible_email') <span
                class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Responsible phone -->
        <div class="w-full">
            <label for="responsible_phone" class="block text-sm font-medium mb-1">
                Responsible phone
            </label>
            <input type="text"
                   id="responsible_phone"
                   placeholder="Phone of Doping contact"
                   class="form-input w-full"
                   name="anti_doping[responsible_phone]"
                   value="{{ old('anti_doping.responsible_phone', $antiDoping?->responsible_phone) }}">
            @error('anti_doping.responsible_phone') <span
                class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Expected Athletes -->
        <div class="w-full">
            <label for="expected_athletes" class="block text-sm font-medium mb-1">
                Expected Athletes
            </label>
            <input type="number"
                   id="expected_athletes"
                   class="form-input w-full"
                   name="anti_doping[expected_athletes]"
                   value="{{ old('anti_doping.expected_athletes', $antiDoping?->expected_athletes) }}">
            <div class="text-xs text-gray-400">Number of athletes expected when <u>there are no registrations</u>.</div>
            @error('anti_doping.expected_athletes') <span
                class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!--
        <div class="w-full">
            <label class="block text-sm font-medium mb-1" for="n_control">Number of controls</label>
            <input type="number" name="n_control" id="n_control" wire:model="n_control"
                   class="form-input w-full">
        </div>
        -->
</div>
