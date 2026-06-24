<!-- Venue -->
<div class="card">
    <div class="flex gap-x-2 items-center border-b border-gray-300 pb-2 mb-4">
        <x-svg.geo-alt class="w-6 h-6 text-slate-600" />
        <span class="font-bold">{{ __('Venue Information') }}</span>
    </div>


    <div class="flex flex-col gap-y-2">
        <div class="w-full">
            <label class="block text-sm font-medium mb-1" for="venue">Venue Name</label>
            <input type="text"
                   name="venue"
                   id="venue"
                   class="form-input w-full"
                   value="{{ old('venue', $event->venue) }}">
        </div>

        <div class="w-full">
            <label class="block text-sm font-medium mb-1" for="venue_address">Venue Address</label>
            <input type="text"
                   name="venue_address"
                   id="venue_address"
                   class="form-input w-full"
                   value="{{ old('venue_address', $event->venue_address) }}">
        </div>

        <div class="w-full">
            <label class="block text-sm font-medium mb-1" for="venue_city">Venue City</label>
            <input type="text"
                   name="venue_city"
                   id="venue_city"
                   class="form-input w-full"
                   value="{{ old('venue_city', $event->venue_city) }}">
        </div>

        <div class="w-full">
            <label class="block text-sm font-medium mb-1" for="venue_district">{{ __('Venue District') }}</label>
            <select name="venue_district_id"
                    id="venue_district"
                    class="form-input w-full {{ $errors->has('venue_district_id') ? 'border-rose-300' : '' }}">
                <option value="" selected> {{ __('-- Select a district --') }} </option>
                @if(isset($districts))
                    @foreach($districts as $key => $district)
                        <option
                            value="{{ $key }}"
                            @if(old('venue_district_id', $event->venue_district_id) == $key) selected @endif
                        >{{ $district }}</option>
                    @endforeach
                @endif
            </select>
            <p class="text-xs text-gray-400">{{ __('Select the Portuguese district where the venue is located') }}</p>
        </div>


    </div>
</div>
