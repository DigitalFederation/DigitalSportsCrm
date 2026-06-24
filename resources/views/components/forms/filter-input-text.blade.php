@props(['name', 'label', 'helpText' => '', 'wrapperClass' => 'w-full md:w-1/3 lg:w-1/4'])

<div class="{{ $wrapperClass }}">
    <label class="block text-sm font-medium mb-1" for="{{$name}}">{{ __($label) }}</label>
    <input type="text"
           class="form-input w-full"
           name="filter[{{$name}}]"
           id="{{$name}}"
           value="{{ is_array(request()->filter[$name] ?? '') ? '' : (request()->filter[$name] ?? '') }}"
           {{ $attributes }}
    >

    @if(!empty($helpText))
        <div class="text-xs mt-1 text-gray-500"> {{$helpText}} </div>
    @endif

</div>
