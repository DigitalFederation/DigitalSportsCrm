<section class="card">

    <div class="flex information-box">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-4" width="24" height="24"
             viewBox="0 0 24 24" stroke-width="1.5" stroke="#9e9e9e" fill="none"
             stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <circle cx="12" cy="12" r="9" />
            <line x1="12" y1="8" x2="12.01" y2="8" />
            <polyline points="11 12 12 12 12 16 13 16" />
        </svg>
        <p class="text-sm"> Choose, from the menu, a Federation and a Plan to activate a new
            membership. <br> You *need* to choose a start date and optionaly a end date </p>

    </div>

    <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
        <div class="sm:w-1/3">
            <label class="block text-sm font-medium mb-1"
                   for="name">{{ __('Membership Name') }}</label>
            <input type="text"
                   class="form-input w-full {{ $errors->has('name') ? 'border-rose-300' : '' }}"
                   name="name"
                   id="name" value="{{ old('name', $membership->name ?? '') }}">

            @if($errors->has('name'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('name') }}
                </div>
            @endif

        </div>

        <div class="sm:w-1/3">
            <label class="block text-sm font-medium mb-1"
                   for="current_term_starts_at">{{ __('Start date') }}</label>
            <input type="date"
                   class="form-input w-full {{ $errors->has('current_term_starts_at') ? 'border-rose-300' : '' }}"
                   name="current_term_starts_at"
                   id="current_term_starts_at"
                   value="{{ old('current_term_starts_at', $membership->current_term_starts_at ?? \Carbon\Carbon::today()->toDateString() ) }}">

            @if($errors->has('current_term_starts_at'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('current_term_starts_at') }}
                </div>
            @endif

        </div>

        <div class="sm:w-1/3">
            <label class="block text-sm font-medium mb-1"
                   for="current_term_ends_at">{{ __('Expiration date') }}</label>
            <input type="date"
                   class="form-input w-full {{ $errors->has('current_term_ends_at') ? 'border-rose-300' : '' }}"
                   name="current_term_ends_at"
                   id="current_term_ends_at"
                   value="{{ old('current_term_ends_at', $membership->current_term_ends_at ?? '') }}"
            >

            <div class="text-xs mt-1"> {{ __('*Only if applicable') }} </div>

            @if($errors->has('current_term_ends_at'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('current_term_ends_at') }}
                </div>
            @endif
        </div>
    </div>

    <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
        <div class="sm:w-1/2">
            <label class="block text-sm font-medium mb-1"
                   for="federation_id">{{ __('Federation') }} <span
                    class="text-rose-500">*</span></label>
            @if($edit)
                <select name="federation" id="federation_id"
                        class="form-select w-full {{ $errors->has('federation_id') ? 'border-rose-300' : '' }}"
                        disabled>
                    <option value="" selected disabled> {{ __('-- Select an option --') }} </option>
                    @foreach($federations as $federation)
                        <option value="{{ $federation->id }}"
                                @if(old('federation_id', $membership->federation_id ?? null)==$federation->id) selected @endif>
                            {{ $federation->name }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="federation_id" value="{{ $membership->federation_id ?? null }}">
            @else
                <select name="federation_id" id="federation_id"
                        class="form-select w-full {{ $errors->has('federation_id') ? 'border-rose-300' : '' }}"
                        required>
                    <option value="" selected disabled> {{ __('-- Select an option --') }} </option>
                    @foreach($federations as $federation)
                        <option value="{{ $federation->id }}"
                                @if(old('federation_id', $membership->federation_id ?? null)==$federation->id) selected @endif>
                            {{ $federation->name }}</option>
                    @endforeach
                </select>
            @endif
            @if($errors->has('federation_id'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('federation_id') }}
                </div>
            @endif
        </div>

        <div class="sm:w-1/2">
            <label class="block text-sm font-medium mb-1">{{ __('Plan selection') }} <span
                    class="text-rose-500">*</span></label>
            @if($edit)
                <livewire:membership-edit-plans :plans="$membership->plans" />
                <div class="text-xs mt-1"> {{ __('* Remove plans if you need') }} </div>
            @else
                <livewire:plan-search-select />
                <div
                    class="text-xs mt-1"> {{ __('* Choose one or more plans to be appendend to this Membership') }} </div>
            @endif

            @if($errors->has('plan_id'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('plan_id') }}
                </div>
            @endif
        </div>

        @if($edit == false && auth()->user()->isAdmin())
            <div class="sm:w-1/2 pt-8">
                <label for="blockDocument" class="items-center flex gap-x-2">
                    <input type="checkbox" class="form-checkbox" id="blockDocument" name="blockDocument">
                    {{ __('This order does not need invoice') }}
                </label>
            </div>
        @endif
    </div>

    <x-forms.card-form-submit backRoute="admin.membership.index"
                              :buttonText="__('Save record')"></x-forms.card-form-submit>
</section>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Select the checkbox using its ID
        var checkbox = document.getElementById("blockDocument");

        // Add a click event listener to the checkbox
        checkbox.addEventListener("click", function(event) {
            // Check if the checkbox is being checked
            if (checkbox.checked) {
                // Display a confirmation dialog
                var isSure = confirm("Are you sure you dont want to add a Payment?");
                // If the user is not sure, uncheck the checkbox
                if (!isSure) {
                    // Prevent the default action of the click event to keep the checkbox unchecked
                    checkbox.checked = false;
                }
            }
        });
    });
</script>
