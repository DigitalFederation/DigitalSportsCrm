@props(['name', 'label', 'options' => [], 'wrapperClass' => 'w-full md:w-1/3 lg:w-1/4'])

<div class="{{ $wrapperClass }}">
    <label class="block text-sm font-medium mb-1" for="{{$name}}">{{ __($label) }}</label>
    <select type="text"
            class="form-select w-full"
            name="filter[{{$name}}]"
            id="{{$name}}">

        @if(isset($options) && count($options) > 0)
            <option value="" selected>{{ __('All') }}</option>

            @foreach($options as $key => $option)
            @php
                // Handle different data structures
                if (is_object($option)) {
                    $optionId = $option->id ?? $key;
                    $optionName = $option->name ?? (string)$option;
                } elseif (is_array($option)) {
                    $optionId = $option['id'] ?? $key;
                    // Fix: Ensure we get a string value for optionName
                    if (isset($option['name'])) {
                        $optionName = $option['name'];
                    } elseif (is_string($key)) {
                        // If key is string and no 'name' key exists, use the first string value in array
                        $optionName = is_string(reset($option)) ? reset($option) : (string)$key;
                    } else {
                        $optionName = '';
                    }
                } else {
                    // Simple key-value pair
                    $optionId = $key;
                    $optionName = $option;
                }
            @endphp
            <option
                value="{{ $optionId }}" {{ isset(request()->filter[$name]) && request()->filter[$name] == $optionId ? 'selected' : '' }}>{{ $optionName }}
            </option>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </select>
</div>
