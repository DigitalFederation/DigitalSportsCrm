<div>
    <label class="block text-sm font-medium mb-1" for="{{ $name }}">{{ __($label) }}</label>
    <input type="number"
           class="form-input w-full"
           name="filter[{{ $name }}]"
           id="{{ $name }}"
           min="{{ $min ?? '' }}"
           max="{{ $max ?? '' }}"
           value="{{ request()->filter[$name] ?? '' }}">

    @if(!empty($helpText))
        <div class="text-xs mt-1 text-gray-500"> {{$helpText}} </div>
    @endif
</div>
