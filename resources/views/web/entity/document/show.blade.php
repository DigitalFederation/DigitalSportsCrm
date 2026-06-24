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

                <a class="btn-info btn-sm" href="{{ route('entity.document.index') }}">
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
                             this.submitting = true;
                             try {
                                 const response = await fetch('{{ route('entity.document.pay', $document->id) }}', {
                                     method: 'POST',
                                     headers: {
                                         'Content-Type': 'application/json',
                                         'Accept': 'application/json',
                                         'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                     },
                                     body: JSON.stringify({ method_id: this.methodId })
                                 });
                                 const data = await response.json();
                                 if (data.type === 'redirect') {
                                     window.open(data.url, '_blank');
                                     this.submitting = false;
                                 } else if (data.type === 'message') {
                                     alert(data.message);
                                     if (data.redirect) {
                                         window.location.href = data.redirect;
                                     }
                                 } else if (data.type === 'error') {
                                     alert(data.message);
                                     this.submitting = false;
                                 }
                             } catch (error) {
                                 alert('{{ __('payments.payment_failed') }}');
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
