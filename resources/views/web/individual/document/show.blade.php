<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('documents.document_detail') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <a class="btn-info btn-sm" href="{{ route('individual.document.index') }}">
                    {{ __('common.back') }}
                </a>

            </div>
        </div>

        <div class="flex gap-x-4 items-start">

            <div class="@if($document->stateName() == 'paid') w-2/3 @else w-full @endif">
                <x-document.detail :document="$document"></x-document.detail>
            </div>


            @if($document->stateName() == 'pending')
                <div class="card"
                     x-data="{
                         submitting: false,
                         methodId: '{{ array_key_first($paymentMethods) }}',
                         async submitPayment() {
                            if (this.submitting) {
                                return;
                            }

                            this.submitting = true;

                            const controller = new AbortController();

                            const timeout = window.setTimeout(() => {
                                controller.abort();
                            }, 25000);

                            try {
                                const response = await fetch(
                                    '{{ route('individual.document.pay', $document->id, false) }}',
                                    {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        },
                                        body: JSON.stringify({
                                            method_id: this.methodId
                                        }),
                                        signal: controller.signal
                                    }
                                );

                                const contentType =
                                    response.headers.get('content-type') ?? '';

                                let data;

                                if (contentType.includes('application/json')) {
                                    data = await response.json();
                                } else {
                                    data = {
                                        type: 'error',
                                        message: `Unexpected HTTP response (${response.status}).`
                                    };
                                }

                                if (!response.ok || data.type === 'error') {
                                    throw new Error(
                                        data.message
                                        ?? `Payment request failed (${response.status}).`
                                    );
                                }

                                if (data.type === 'redirect' && data.url) {
                                    /*
                                    * Use the same tab.
                                    *
                                    * window.open() after an asynchronous fetch can be blocked as
                                    * a popup by the browser.
                                    */
                                    window.location.assign(data.url);
                                    return;
                                }

                                if (data.type === 'message') {
                                    alert(data.message);

                                    if (data.redirect) {
                                        window.location.assign(data.redirect);
                                    }

                                    return;
                                }

                                throw new Error('Unexpected payment response.');

                            } catch (error) {
                                if (error.name === 'AbortError') {
                                    alert(
                                        'A comunicação com o serviço de pagamento demorou mais de 25 segundos. ' +
                                        'O pagamento foi destravado. Tente novamente ou consulte os logs.'
                                    );
                                } else {
                                    alert(
                                        error.message
                                        ?? '{{ __('payments.payment_failed') }}'
                                    );
                                }
                            } finally {
                                window.clearTimeout(timeout);
                                this.submitting = false;
                            }
                        }
                     }">
                    <div class=" font-bold border-b border-slate-200 pb-2 mb-2 p-0">
                        {{ __('documents.payment') }}
                    </div>
                    <form @submit.prevent="submitPayment()">
                        <label for="method_id" class="block text-sm font-medium mb-1">{{ __('documents.select_method') }}</label>
                        <select x-model="methodId" name="method_id" id="method_id" class="form-select w-full">
                            @foreach($paymentMethods as $key => $paymentMethod)
                                <option value="{{ $key }}">{{ $paymentMethod }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-primary w-full mt-4" :disabled="submitting">
                            <span x-show="!submitting">{{ __('documents.proceed_to_payment') }}</span>
                            <span x-show="submitting" x-cloak>{{ __('common.processing') }}...</span>
                        </button>
                    </form>
                </div>
            @endif


            @if($document->stateName() == 'paid')
                <div class="w-1/3">

                    <div class="card h-full">

                        @if($document->stateName() != 'pending')
                            <x-document.card_is_paid
                                :document="$document"
                                :relatedDocuments="$relatedDocuments"></x-document.card_is_paid>
                        @endif

                    </div>

                </div>
            @endif

        </div>
    </div>
</x-layout>
