<div>
    <form wire:submit="save">
        <div class="space-y-6">
            <!-- Enable/Disable Toggle -->
            <div class="flex items-center justify-between">
                <div>
                    <label class="block text-sm font-medium text-slate-800" for="backupEnabled">
                        {{ __('backups.settings_enabled_label') }}
                    </label>
                    <p class="text-xs text-slate-500">{{ __('backups.settings_enabled_help') }}</p>
                </div>
                <div class="flex items-center">
                    <button
                        type="button"
                        x-data="{ enabled: @entangle('backupEnabled') }"
                        x-on:click="enabled = !enabled"
                        :class="enabled ? 'bg-primary-600' : 'bg-slate-200'"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                        role="switch"
                        :aria-checked="enabled.toString()"
                    >
                        <span
                            :class="enabled ? 'translate-x-5' : 'translate-x-0'"
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                        ></span>
                    </button>
                </div>
            </div>

            <!-- Frequency -->
            <div>
                <label class="block text-sm font-medium text-slate-800 mb-1" for="backupFrequency">
                    {{ __('backups.settings_frequency_label') }}
                </label>
                <select
                    id="backupFrequency"
                    wire:model="backupFrequency"
                    class="form-select w-full"
                >
                    <option value="daily">{{ __('backups.frequency_daily') }}</option>
                    <option value="twice_daily">{{ __('backups.frequency_twice_daily') }}</option>
                    <option value="every_six_hours">{{ __('backups.frequency_every_six_hours') }}</option>
                    <option value="weekly">{{ __('backups.frequency_weekly') }}</option>
                </select>
                <p class="text-xs text-slate-500 mt-1">{{ __('backups.settings_frequency_help') }}</p>
                @error('backupFrequency')
                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                @enderror
            </div>

            <!-- Backup Time -->
            <div>
                <label class="block text-sm font-medium text-slate-800 mb-1" for="backupTime">
                    {{ __('backups.settings_time_label') }}
                </label>
                <input
                    type="time"
                    id="backupTime"
                    wire:model="backupTime"
                    class="form-input w-full @error('backupTime') border-rose-300 @enderror"
                >
                <p class="text-xs text-slate-500 mt-1">{{ __('backups.settings_time_help') }}</p>
                @error('backupTime')
                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                @enderror
            </div>

            <!-- Retention Days -->
            <div>
                <label class="block text-sm font-medium text-slate-800 mb-1" for="retentionDays">
                    {{ __('backups.settings_retention_label') }}
                </label>
                <input
                    type="number"
                    id="retentionDays"
                    wire:model="retentionDays"
                    min="1"
                    max="365"
                    class="form-input w-full @error('retentionDays') border-rose-300 @enderror"
                >
                <p class="text-xs text-slate-500 mt-1">{{ __('backups.settings_retention_help') }}</p>
                @error('retentionDays')
                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                @enderror
            </div>

            <!-- Max Storage -->
            <div>
                <label class="block text-sm font-medium text-slate-800 mb-1" for="maxStorageMb">
                    {{ __('backups.settings_max_storage_label') }}
                </label>
                <input
                    type="number"
                    id="maxStorageMb"
                    wire:model="maxStorageMb"
                    min="100"
                    max="50000"
                    class="form-input w-full @error('maxStorageMb') border-rose-300 @enderror"
                >
                <p class="text-xs text-slate-500 mt-1">{{ __('backups.settings_max_storage_help') }}</p>
                @error('maxStorageMb')
                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Save Button -->
        <div class="mt-6 flex justify-end">
            <button type="submit" class="btn btn-primary">
                <span wire:loading.remove wire:target="save">
                    {{ __('backups.settings_save') }}
                </span>
                <span wire:loading wire:target="save">
                    {{ __('backups.settings_saving') }}...
                </span>
            </button>
        </div>
    </form>
</div>
