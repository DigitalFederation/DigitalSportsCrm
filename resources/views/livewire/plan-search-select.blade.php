<div x-data="{ open: false }" class="relative">

    <input type="text" class="form-input w-full" placeholder="Search for a plan..." wire:model.live="search"
           @focus="open = true"
           @click.away="open = false">

    <div x-show="open" class="absolute bg-white border mt-2 z-10 w-full h-56 overflow-y-auto">
        <ul>
            @foreach ($plans as $plan)
                <li class="p-2 hover:bg-gray-100 cursor-pointer"
                    wire:click="addPlan({{ $plan->id }})">{{ $plan->name }}</li>
            @endforeach
        </ul>
    </div>

    <div class="mt-3">
        <ul>
            @foreach ($selectedPlans as $index => $plan)
                <li class="flex justify-between items-center p-2 bg-slate-100 rounded-md mb-1 text-sm">
                    {{ $plan['name'] }}
                    <button type="button" class="text-red-500" wire:click="removePlan({{ $index }})">&times;</button>
                </li>
            @endforeach
        </ul>
    </div>

    <!-- Hidden inputs to submit with form -->
    @foreach ($selectedPlans as $plan)
        <input type="hidden" name="plans[]" value="{{ $plan['id'] }}">
    @endforeach
</div>
