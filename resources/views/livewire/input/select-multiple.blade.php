<div class="w-full">
    <select
        x-data="{}"
        x-init="function() {
            let choicesInstance = new Choices($el, {{$jsonOptions}});
            choicesInstance.passedElement.element.addEventListener('change', function(event) {
                let allChoices = choicesInstance.getValue(true);
                @this.set('inputSelected', allChoices);
                
                // Update the actual select element's options to ensure form submission works
                Array.from($el.options).forEach(option => {
                    option.selected = allChoices.includes(option.value);
                });
            });
        }"
        name="{{$inputName}}"
        id="{{$inputId}}"
        class="w-full form-select"
        multiple>
        @foreach($items as $key => $item)
            <option value="{{ $key }}"
                    @if(!empty($inputSelected) && in_array($key, $inputSelected)) selected @endif>{{ $item }}</option>
        @endforeach
    </select>


    <div class="text-xs text-gray-400"> Click on the input to search and select multiple values</div>
</div>
