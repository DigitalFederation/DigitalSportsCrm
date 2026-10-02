<x-guest-layout>
    <div class="w-full sm:max-w-xl mt-6 px-6 py-6 bg-white shadow-md overflow-hidden sm:rounded-lg">
        <h1 class="text-xl font-semibold text-gray-900">PagBank PIX</h1>

        @if ($transaction->status === 'success')
            <div class="mt-5 rounded-md bg-green-50 p-4 text-green-800">
                Payment confirmed. You may close this page.
            </div>
        @elseif ($transaction->status === 'failed')
            <div class="mt-5 rounded-md bg-red-50 p-4 text-red-800">
                This payment was declined or canceled. Please choose another payment method.
            </div>
        @else
            <p class="mt-3 text-sm text-gray-600">
                Scan the QR Code with your banking app or use the PIX copy-and-paste code.
            </p>

            @if ($qrCodeImageUrl)
                <div class="mt-5 flex justify-center">
                    <img
                        src="{{ $qrCodeImageUrl }}"
                        alt="PagBank PIX QR Code"
                        class="w-64 h-64"
                    >
                </div>
            @endif

            @if ($qrCodeText)
                <label for="pix-code" class="block mt-5 text-sm font-medium text-gray-700">
                    PIX copy-and-paste
                </label>

                <textarea
                    id="pix-code"
                    rows="4"
                    readonly
                    class="mt-1 block w-full rounded-md border-gray-300 text-xs"
                >{{ $qrCodeText }}</textarea>

                <button
                    type="button"
                    id="copy-pix-code"
                    class="mt-3 inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white"
                >
                    Copy PIX code
                </button>
            @endif

            @if ($expirationDate)
                <p class="mt-4 text-xs text-gray-500">
                    QR Code expires at {{ $expirationDate }}.
                </p>
            @endif

            <p class="mt-4 text-xs text-gray-500">
                This page refreshes automatically while payment confirmation is pending.
            </p>
        @endif
    </div>
    <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">

        <a
            href="{{ route('individual.document.show', $transaction->document_id) }}"
            class="inline-flex items-center justify-center rounded-md
                bg-blue-600 px-4 py-2 text-sm font-semibold text-white
                hover:bg-blue-700 transition"
        >
            Voltar para a fatura
        </a>

        <a
            href="/dashboard"
            class="inline-flex items-center justify-center rounded-md
                bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700
                hover:bg-gray-200 transition"
        >
            Ir para o início
        </a>

    </div>

    @if ($transaction->status === 'pending')
        <script>
            window.setTimeout(() => window.location.reload(), 10000);

            document.getElementById('copy-pix-code')?.addEventListener('click', async () => {
                const code = document.getElementById('pix-code')?.value ?? '';

                if (!code) {
                    return;
                }

                await navigator.clipboard.writeText(code);
            });
        </script>
    @endif
</x-guest-layout>
