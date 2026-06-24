<div class="w-full my-6 rounded-xl shadow-sm border border-[#E2DAD6]" x-data="{ filterOpen: window.innerWidth > 640 }">
    <form action="{{ $post }}" method="GET" x-data="filterForm()" x-ref="filterForm"
          id="{{ $formId ?? 'filter_form' }}">

        <div
            :class="filterOpen ? 'rounded-t-xl' : 'rounded-xl'"
            class="flex px-5 py-3.5 text-sm font-medium text-[#6482AD] bg-gradient-to-r from-[#F5EDED] to-[#E2DAD6] border-b border-[#E2DAD6] items-center">
            <div class="flex flex-row items-center gap-2">
                <div class="bg-white p-1 rounded-full shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#7FA1C3]" viewBox="0 0 24 24"
                        stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.414 4.414v7l-6 2v-9l-4.414 -4.414a2 2 0 0 1 -.586 -1.414v-2.172z" />
                    </svg>
                </div>
                <span>{{ __('Filter results') }}</span>
            </div>

            <div class="flex-1">
                <button type="button" x-on:click="filterOpen = !filterOpen"
                        class="flex justify-end w-full text-right text-[#7FA1C3] hover:text-[#6482AD] transition-colors duration-150">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         :class="filterOpen ? 'rotate-180' : 'rotate-0'"
                         class="h-5 w-5 transition-transform duration-200" 
                         viewBox="0 0 24 24"
                         stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <polyline points="6 9 12 15 18 9" />
                    </svg>

                </button>
            </div>

        </div>

        <div x-show="filterOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="bg-white flex flex-wrap gap-5 p-5 whitespace-nowrap items-start box-border">
                {{ $slot }}
            </div>

            <div
                class="md:flex px-5 py-3 bg-gradient-to-r from-[#F5EDED] to-[#E2DAD6] border-t border-[#E2DAD6] items-center justify-between rounded-b-xl">
                <p class="hidden md:block text-[#7FA1C3] text-sm">{{ __('Use the above fields to create the rules necessary to filter the results.') }}</p>
                <div class="flex gap-3">
                    <button type="button" x-on:click="clearForm()" class="px-4 py-2 bg-white border border-[#7FA1C3] rounded-lg text-[#6482AD] font-medium hover:bg-[#F5EDED] transition-colors duration-150 shadow-sm text-sm flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        {{ __('Clear') }}
                    </button>
                    <button class="px-5 py-2 bg-gradient-to-r from-[#6482AD] to-[#7FA1C3] text-white font-medium rounded-lg hover:from-[#5a76a0] hover:to-[#7295b7] transition-colors duration-150 shadow-sm text-sm flex items-center"
                            type="submit">
                        {{ __('Filter Results') }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
    function filterForm() {
        return {
            clearForm() {
                Array.from(this.$refs.filterForm.elements).forEach(element => {
                    if (element.type === "text" || element.type === "date" || element.type === "number") {
                        element.value = "";
                    } else if (element.type === "select-one") {
                        element.selectedIndex = 0;
                    }
                });

                // Remove filter parameters from the URL and reload page
                const url = window.location.href.split("?")[0];
                window.location.href = url;
            }
        };
    }

</script>
