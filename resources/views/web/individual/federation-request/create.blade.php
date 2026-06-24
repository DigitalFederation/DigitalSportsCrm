<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('federation_request.request_federation_association') }}</h1>
                <p class="text-slate-600">{{ __('federation_request.select_federation_to_request') }}</p>
            </div>
        </div>

        @if($errors->any())
            <div class="bg-rose-100 border border-rose-200 rounded-sm p-4 mb-4">
                <div class="flex">
                    <svg class="w-4 h-4 fill-current text-rose-500 shrink-0" viewBox="0 0 16 16">
                        <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm3.5 10.1l-1.4 1.4L8 9.4l-2.1 2.1-1.4-1.4L6.6 8 4.5 5.9l1.4-1.4L8 6.6l2.1-2.1 1.4 1.4L9.4 8l2.1 2.1z" />
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-rose-800">{{ $errors->first() }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if($availableFederations->isEmpty())
            <!-- Empty state -->
            <div class="card">
                <div class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-slate-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h3 class="text-lg font-semibold text-slate-800 mb-1">{{ __('federation_request.no_federations_available') }}</h3>
                    <p class="text-slate-600">{{ __('federation_request.no_federations_description') }}</p>
                </div>
            </div>
        @else
            <!-- Form -->
            <form action="{{ route('individual.federation-request.store') }}" method="POST">
                @csrf

                <div class="card">
                    <div class="grid grid-cols-1 gap-4">
                        <!-- Federation selection -->
                        <div>
                            <label class="block text-sm font-medium mb-1" for="federation_id">
                                {{ __('federation_request.select_federation') }} <span class="text-rose-500">*</span>
                            </label>
                            <select
                                id="federation_id"
                                name="federation_id"
                                class="form-select w-full @error('federation_id') border-rose-300 @enderror"
                                required
                            >
                                <option value="">{{ __('federation_request.choose_federation') }}</option>
                                @foreach($availableFederations as $federation)
                                    <option value="{{ $federation->id }}" {{ old('federation_id') == $federation->id ? 'selected' : '' }}>
                                        {{ $federation->name }}
                                        @if($federation->member_code)
                                            ({{ $federation->member_code }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('federation_id')
                                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Information box -->
                <div class="information-box mt-4">
                    <h3 class="text-sm font-semibold text-slate-800 mb-2">{{ __('federation_request.important_information') }}</h3>
                    <ul class="text-sm text-slate-600 space-y-1">
                        <li>• {{ __('federation_request.info_pending_approval') }}</li>
                        <li>• {{ __('federation_request.info_payment_required') }}</li>
                        <li>• {{ __('federation_request.info_contact_federation') }}</li>
                    </ul>
                </div>

                <!-- Form actions -->
                <div class="flex flex-wrap justify-end space-x-2 mt-6">
                    <a href="{{ route('individual.dashboard') }}" class="btn btn-secondary">
                        {{ __('common.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        {{ __('federation_request.submit_request') }}
                    </button>
                </div>
            </form>
        @endif
    </div>
</x-layout>