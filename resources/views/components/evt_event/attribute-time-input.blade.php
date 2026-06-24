<div x-data="{
    minutes: '',
    seconds: '',
    milliseconds: '',

    init() {
        if (this.$wire.get('{{ $inputName }}')) {
            const parts = this.$wire.get('{{ $inputName }}').split(':');
            if (parts.length === 2) {
                this.minutes = parts[0];
                const secParts = parts[1].split('.');
                this.seconds = secParts[0];
                this.milliseconds = secParts[1] || '';
            }
        }
    },

    updateWireModel() {
        const m = String(this.minutes || 0).padStart(2, '0');
        const s = String(this.seconds || 0).padStart(2, '0');
        const ms = String(this.milliseconds || 0).padStart(2, '0');
        this.$wire.set('{{ $inputName }}', `${m}:${s}.${ms}`);
    },

    validateNumber(event, max) {
        // Allow only numbers
        event.target.value = event.target.value.replace(/[^\d]/g, '');

        // Convert to number and validate range
        let value = parseInt(event.target.value) || 0;
        if (value > max) value = max;
        if (value < 0) value = 0;

        // Update the input with the validated value
        event.target.value = value.toString();

        return value;
    },

    handleKeyPress(event) {
        // Allow only numbers and specific control keys
        if (!/[\d]/.test(event.key) &&
            !['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight'].includes(event.key)) {
            event.preventDefault();
        }
    },

    focusNext(event, nextId) {
        if (event.target.value.length >= event.target.maxLength) {
            const next = document.getElementById(nextId);
            if (next) next.focus();
        }
    }
}"
     class="flex items-center space-y-1 space-x-1 max-w-xs"
>
    <!-- Minutes -->
    <div class="relative flex-1">
        <input
            type="text"
            x-model="minutes"
            id="time-minutes-{{ $attributeId }}"
            maxlength="2"
            @keypress="handleKeyPress($event)"
            @input="minutes = validateNumber($event, 59); updateWireModel(); focusNext($event, 'time-seconds-{{ $attributeId }}')"
            placeholder="00"
            class="form-input p-1 w-full text-center text-lg font-mono"
            {{ $isRequired ? 'required' : '' }}
        >
        <span class="absolute -bottom-4 left-0 text-xs text-gray-500">Min</span>
    </div>

    <span class="text-lg font-bold">:</span>

    <!-- Seconds -->
    <div class="relative flex-1">
        <input
            type="text"
            x-model="seconds"
            id="time-seconds-{{ $attributeId }}"
            maxlength="2"
            @keypress="handleKeyPress($event)"
            @input="seconds = validateNumber($event, 59); updateWireModel(); focusNext($event, 'time-ms-{{ $attributeId }}')"
            placeholder="00"
            class="form-input p-1 w-full text-center text-lg font-mono"
            {{ $isRequired ? 'required' : '' }}
        >
        <span class="absolute -bottom-4 left-0 text-xs text-gray-500">Sec</span>
    </div>

    <span class="text-lg font-bold">.</span>

    <!-- Milliseconds -->
    <div class="relative flex-1">
        <input
            type="text"
            x-model="milliseconds"
            id="time-ms-{{ $attributeId }}"
            maxlength="2"
            @keypress="handleKeyPress($event)"
            @input="milliseconds = validateNumber($event, 99); updateWireModel()"
            placeholder="00"
            class="form-input p-1 w-full text-center text-lg font-mono"
            {{ $isRequired ? 'required' : '' }}
        >
        <span class="absolute -bottom-4 left-0 text-xs text-gray-500">Ms</span>
    </div>

    <!-- Help Icon -->
    <div class="relative group ml-2">
        <x-heroicon-m-question-mark-circle
            class="h-5 w-5 text-gray-400 hover:text-gray-600 cursor-help"
        />
        <div
            class="absolute hidden group-hover:block right-0 w-64 p-2 mt-2 space-y-1 text-sm bg-white border rounded-lg shadow-lg z-10">
            <p class="font-bold">Time Format Help:</p>
            <ul class="text-xs text-gray-600 space-y-1">
                <li>• Minutes: 00-59</li>
                <li>• Seconds: 00-59</li>
                <li>• Milliseconds: 00-99</li>
            </ul>
        </div>
    </div>
</div>
