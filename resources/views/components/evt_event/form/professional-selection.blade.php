<div class="w-full" x-cloak>

    <!-- Professional Roles -->
    <label class="flex items-center text-sm font-medium mb-1" for="professional_role">
        {{ __('events.form.professional_role_filter') }}
        <sl-tooltip
            content="{{ __('events.form.professional_role_filter_tooltip') }}">
            <sl-button>
                <x-svg.info class="h-5 w-5 text-gray-400" />
            </sl-button>
        </sl-tooltip>
    </label>

    <p class="text-xs text-slate-600 mb-2 ml-1  ">
        {{ __('events.form.professional_role_filter_hint') }}
    </p>

    <livewire:input.select-multiple
        identifier="professional_roles"
        :inputSelected="$event->professionalRoles->pluck('id')->toArray()"
        :items="$professionalRoles"
        inputId="professional_roles"
        inputName="professional_roles[]"
    />


    @if($errors->has('selected_professional_roles'))
        <div class="text-xs mt-1 text-rose-500 h-2">
            {{ $errors->first('selected_professional_roles') }}
        </div>
    @endif


</div>
