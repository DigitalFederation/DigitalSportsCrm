@section('title', __('Certification Search'))
<x-public-layout>
    @php($brand = config('branding.primary'))
    <main class="relative bg-cover min-h-screen bg-waves-full-bg-one animate-in pb-16">

        <section class="relative">
            <div class="mx-auto pt-4 w-24">
                <img src="{{ asset($brand['logo_path']) }}" class="w-24 " alt="{{ $brand['short_name'] }} Logo">
            </div>

            <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

                <div class="mb-8 text-center">
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800"> {{ __('Certification Search') }} </h1>
                    <p class="text-gray-600 mt-1">{{ __('Use the following form to find certification information') }}</p>
                </div>

                {{-- General Validation Error Area --}}
                @if ($errors->has('search_criteria'))
                    <div class="mb-4 rounded-md bg-red-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-10a1 1 0 10-2 0v4a1 1 0 102 0V8zm-1 7a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">{{ __('Search Error') }}</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <p>{{ $errors->first('search_criteria') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('public.certification.search') }}" method="GET"
                    class="bg-white shadow-md rounded-lg overflow-hidden mb-8">


                    <h3 class="text-lg font-semibold text-gray-700 px-6 py-4 border-b border-gray-200">
                        {{ __('Search by Nº Filiado') }}</h3>

                    <div id="qr-code-scanner-overlay" class="w-full bg-gray-100 hidden"></div>

                    <div class="flex flex-col gap-6 p-6">
                        <div class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                            <div class="flex-shrink-0">
                                <button onclick="toggleQrScanner()" type="button"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                        class="h-5 w-5 mr-2 fill-current text-gray-500">
                                        <path
                                            d="M16 17V16H13V13H16V15H18V17H17V19H15V21H13V18H15V17H16ZM21 21H17V19H19V17H21V21ZM3 3H11V11H3V3ZM5 5V9H9V5H5ZM13 3H21V11H13V3ZM15 5V9H19V5H15ZM3 13H11V21H3V13ZM5 15V19H9V15H5ZM18 13H21V15H18V13ZM6 6H8V8H6V6ZM6 16H8V18H6V16ZM16 6H18V8H16V6Z">
                                        </path>
                                    </svg>
                                    <span>{{ __('Scan QR Code') }}</span>
                                </button>
                            </div>
                            <div class="flex-grow w-full">
                                <label for="member_code"
                                    class="block text-sm font-medium text-gray-700 mb-1">{{ __('main.Member Code') }}</label>
                                <input type="text" id="member_code" name="member_code"
                                    placeholder="{{ __('Enter Nº Filiado') }}"
                                    class="block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-sky-500 focus:border-sky-500 {{ $errors->has('member_code') ? 'border-rose-500' : '' }}"
                                    value="{{ old('member_code') }}">
                                @if ($errors->has('member_code'))
                                    <p class="mt-1 text-xs text-rose-500">{{ $errors->first('member_code') }}</p>
                                @endif
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full flex justify-center items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-[#193044] hover:bg-[#112233] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#193044]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                            <span>{{ __('Search by International Code') }}</span>
                        </button>
                    </div>
                </form>

                <form action="{{ route('public.certification.search') }}" method="GET"
                    class="bg-white shadow-md rounded-lg overflow-hidden">

                    <h3 class="text-lg font-semibold text-gray-700 px-6 py-4 border-b border-gray-200">
                        {{ __('Or Enter Data Manually') }}</h3>

                    <div class="flex flex-col gap-6 p-6">
                        <div>
                            <label for="name"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name') }}</label>
                            <input type="text" id="name" placeholder="{{ __('Enter name') }}"
                                class="block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-sky-500 focus:border-sky-500 {{ $errors->has('name') ? 'border-rose-500' : '' }}"
                                name="name" value="{{ old('name') }}">
                            @if ($errors->has('name'))
                                <p class="mt-1 text-xs text-rose-500">{{ $errors->first('name') }}</p>
                            @endif
                        </div>

                        <div>
                            <label for="surname"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('Surname') }}</label>
                            <input type="text" id="surname" placeholder="{{ __('Enter surname') }}"
                                class="block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-sky-500 focus:border-sky-500 {{ $errors->has('surname') ? 'border-rose-500' : '' }}"
                                name="surname" value="{{ old('surname') }}">
                            @if ($errors->has('surname'))
                                <p class="mt-1 text-xs text-rose-500">{{ $errors->first('surname') }}</p>
                            @endif
                        </div>

                        <div>
                            <label for="birthdate"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('Date of Birth') }}</label>
                            <input type="date" id="birthdate"
                                class="block w-full shadow-sm sm:text-sm border-gray-300 rounded-md focus:ring-sky-500 focus:border-sky-500 {{ $errors->has('birthdate') ? 'border-rose-500' : '' }} text-gray-500"
                                name="birthdate" value="{{ old('birthdate') }}"
                                onfocus="(this.type='date'); this.style.color='black';"
                                onblur="if(!this.value) { this.type='text'; this.style.color='rgb(107 114 128)'; this.value = '{{ __('Select date') }}'; }"
                                @if (!old('birthdate')) type="text" value="{{ __('Select date') }}" @endif>
                            @if ($errors->has('birthdate'))
                                <p class="mt-1 text-xs text-rose-500">{{ $errors->first('birthdate') }}</p>
                            @endif
                        </div>

                        <button type="submit"
                            class="w-full flex justify-center items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-[#193044] hover:bg-[#112233] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#193044]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                            <span>{{ __('Manual Search') }}</span>
                        </button>
                    </div>
                </form>

            </div>
        </section>
    </main>

    @push('head-css')
        <style>
            #qr-code-scanner-overlay:not(:empty) {
                border: 1px dashed #ccc;
                margin-bottom: 1rem;
            }

            #qr-code-scanner-overlay::before {
                content: "";
                display: block;
                position: absolute;
                top: 50%;
                left: 50%;
                /* Ensure the visual guide is a square */
                --size-constraint-vw: 60vw;
                --size-constraint-vh: 60vh;
                --max-size-px: 250px; /* Original cap */
                --calculated-side: min(var(--max-size-px), var(--size-constraint-vw), var(--size-constraint-vh));

                width: calc(var(--calculated-side) - 10px); /* Apply original adjustment */
                height: calc(var(--calculated-side) - 10px); /* Apply original adjustment */

                transform: translate(-50%, -50%);
                background: rgba(255, 255, 255, 0.1);
                border: 1px solid rgba(255, 255, 255, 0.5);
            }

            input[type="date"]:invalid,
            input[type="date"][value=""] {
                color: rgb(107 114 128);
            }
        </style>
    @endpush
    @push('footer-scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
        <script>
            let qrCodeScanner = null;
            const scannerOverlay = document.getElementById('qr-code-scanner-overlay');
            const memberCodeInput = document.getElementById('member_code');
            const form1 = memberCodeInput.closest('form');

            window.toggleQrScanner = function() {
                if (scannerOverlay.classList.contains('hidden')) {
                    scannerOverlay.classList.remove('hidden');
                    scannerOverlay.innerHTML = '';
                    startQrScanner();
                } else {
                    stopQrScanner();
                    scannerOverlay.classList.add('hidden');
                }
            };

            function startQrScanner() {
                if (!qrCodeScanner) {
                    qrCodeScanner = new Html5Qrcode( /* element id */ "qr-code-scanner-overlay");
                }

                const startScannerWithOptions = (options) => {
                    return qrCodeScanner.start(
                        options,
                        { // config
                            fps: 10,
                            qrbox: (viewfinderWidth, viewfinderHeight) => {
                                let minEdgePercentage = 0.7;
                                let minEdgeSize = Math.min(viewfinderWidth, viewfinderHeight);
                                let qrboxSize = Math.floor(minEdgeSize * minEdgePercentage);
                                return { width: qrboxSize, height: qrboxSize };
                            },
                            useBarCodeDetectorIfSupported: true,
                        },
                        (decodedText, decodedResult) => { // success callback
                            memberCodeInput.value = decodedText;
                            console.log(`Code matched = ${decodedText}`, decodedResult);
                            stopQrScanner();
                            scannerOverlay.classList.add('hidden');
                            if (form1) {
                                form1.submit();
                            }
                        },
                        (errorMessage) => { // NOP, errors are caught below
                        }
                    );
                };

                const displayError = (message) => {
                    console.error("QR Scanner Error:", message);
                    let friendlyMessage = "{{ __('Could not initialize QR Scanner.') }}";
                    if (message && typeof message === 'string') {
                        if (message.includes("NotFoundError") || message.includes("Requested device not found")) {
                            friendlyMessage = "{{ __('Camera not found. Please ensure it is enabled and permissions are granted.') }}";
                        } else if (message.includes("NotAllowedError")) {
                            friendlyMessage = "{{ __('Camera access denied. Please grant permission to use the QR scanner.') }}";
                        }
                    } else if (message && message.name === "NotFoundError") {
                         friendlyMessage = "{{ __('Camera not found. Please ensure it is enabled and permissions are granted.') }}";
                    } else if (message && message.name === "NotAllowedError") {
                         friendlyMessage = "{{ __('Camera access denied. Please grant permission to use the QR scanner.') }}";
                    }

                    scannerOverlay.innerHTML = `<p class="text-center text-red-500 p-4">${friendlyMessage}</p>`;
                };

                // Try with environment camera first
                startScannerWithOptions({ facingMode: "environment" })
                    .catch((err) => {
                        console.warn("Failed to start with environment camera:", err);
                        // If environment camera fails (e.g. NotFoundError), try with user camera
                        if (err.name === "NotFoundError" || (typeof err === 'string' && err.includes("NotFoundError"))) {
                            console.log("Attempting to start with user camera as fallback...");
                            startScannerWithOptions({ facingMode: "user" })
                                .catch((fallbackErr) => {
                                    displayError(fallbackErr);
                                });
                        } else {
                            displayError(err); // Display other errors (e.g., NotAllowedError)
                        }
                    });
            }

            function stopQrScanner() {
                if (qrCodeScanner && qrCodeScanner.isScanning) {
                    qrCodeScanner.stop().then((ignore) => {
                        console.log("QR Code scanning stopped.");
                        qrCodeScanner = null;
                    }).catch((err) => {
                        console.error("Failed to stop scanning.", err);
                    });
                }
            }

            const birthdateInput = document.getElementById('birthdate');
            if (birthdateInput && birthdateInput.type === 'text' && birthdateInput.value === '{{ __('Select date') }}') {
                birthdateInput.style.color = 'rgb(107 114 128)';
            }
            if (birthdateInput) {
                birthdateInput.addEventListener('focus', function() {
                    this.type = 'date';
                    this.style.color = 'black';
                    if (this.value === '{{ __('Select date') }}') {
                        this.value = '';
                    }
                });
                birthdateInput.addEventListener('blur', function() {
                    if (!this.value) {
                        this.type = 'text';
                        this.style.color = 'rgb(107 114 128)';
                        this.value = '{{ __('Select date') }}';
                    }
                });
            }
        </script>
    @endpush
</x-public-layout>
