@props([
    'disabled' => false,
    'rows' => 3
])

<textarea {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) !!} rows="{{ $rows }}">{{ $slot }}</textarea>
