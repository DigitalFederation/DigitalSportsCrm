<div class="w-full md:w-auto">
    <label class="block text-sm font-medium mb-1" for="{{ $nameStart }}">{{ __($label) }}</label>

    <div class="flex items-center flex-col sm:flex-row">
        <input type="date"
               class="form-input w-full"
               name="{{$nameStart}}"
               id="{{$nameStart}}"
               value="{{ request()->input($nameStart, '') }}">
        <div class="mx-2"> to</div>
        <input type="date"
               class="form-input w-full"
               name="{{$nameEnd}}"
               id="{{$nameEnd}}"
               value="{{ request()->input($nameEnd, '') }}">
    </div>
</div>
