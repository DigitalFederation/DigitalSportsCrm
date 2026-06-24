<div x-data="{ showModal: false, isLoading: false, animation: '{{ $animation }}',buttonClass: '{{ $buttonClass }}' }"
     @keydown.escape.window="showModal = false"
     @close-modal.window="showModal = false">

    <div x-on:click="showModal = true" :class="buttonClass" class="cursor-pointer">
        {{ __($buttonLabel) }}
    </div>


    <div
        x-cloak
        x-show="showModal"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/75"
        @click.away="showModal = false">
    </div>

    <div
        x-cloak
        x-show="showModal"
        :class="animation"
        class="fixed md:top-10 left-0 right-0 z-[1001] w-full overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full md:pl-36">
        <div class="w-screen max-w-4xl my-8 mx-auto card rounded-lg h-auto" x-ref="modal" @click.stop>
            <div class="flex items-start justify-between border-b rounded-t">
                @if($headerView)
                    @if($headerTitle)
                        <h3 class="text-xl font-semibold text-gray-800 ">
                            {{ $headerTitle }}
                        </h3>
                    @endif
                    <button
                        type="button"
                        x-on:click="showModal = false"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>

                @endif
            </div>

            <!-- Loading State -->
            <div x-show="isLoading" class="absolute inset-0 flex items-center justify-center">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                          d="M4 12a8 8 0 018-8V0C1.8 0 0 1.8 0 4c0 3.3 1.4 6.3 3.6 8.4l.4.6z"></path>
                </svg>
            </div>

            <!-- Content -->
            <div x-show="!isLoading" class="modal-content overflow-y-auto">
                @if($isLivewire)
                    @livewire($viewName, ['params' => $params])
                @else
                    {!! $content !!}
                @endif
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('modal', () => ({
                init() {
                    this.$watch('showModal', value => {
                        if (value) {
                            this.$refs.modal.querySelector('input,select,textarea').focus();
                        }
                    });
                }
            }));
        });
    </script>
</div>
