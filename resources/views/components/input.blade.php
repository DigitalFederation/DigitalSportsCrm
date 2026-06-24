@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-[#7FA1C3] focus:border-[#6482AD] focus:ring focus:ring-[#7FA1C3] focus:ring-opacity-30 rounded-lg shadow-sm w-full']) !!}>
