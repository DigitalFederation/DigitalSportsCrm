<x-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <h1 class="text-2xl md:text-3xl text-slate-800 font-bold mb-6">{{ __('subscriptions.subscribe_to_package') }}</h1>

        @if($selectedPackage)
            <div class="bg-white shadow-lg rounded-sm border border-slate-200 mb-8">
                <div class="px-5 py-4">
                    <h2 class="font-semibold text-slate-800 mb-4">{{ __('subscriptions.selected_package') }}: {{ $selectedPackage->name }}</h2>
                    <p class="text-sm text-slate-600 mb-4">{{ $selectedPackage->description }}</p>
                    <div class="text-sm text-slate-800 mb-4">
                        <div><span class="font-semibold">{{ __('subscriptions.price') }}:</span> ${{ number_format($selectedPackage->calculatePrice(), 2) }}/year</div>
                        <div class="mt-2"><span class="font-semibold">{{ __('subscriptions.includes') }}:</span></div>
                        <ul class="list-disc list-inside ml-2">
                            @foreach($selectedPackage->affiliationPlans as $affiliation)
                                <li>{{ __('subscriptions.affiliation') }}: {{ $affiliation->name }}</li>
                            @endforeach
                            @foreach($selectedPackage->insurancePlans as $insurance)
                                <li>{{ __('subscriptions.insurance') }}: {{ $insurance->name }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <form action="{{ route('individual.subscriptions.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="package_id" value="{{ $selectedPackageId }}">
                        <button type="submit" class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                            {{ __('subscriptions.confirm') }}
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="bg-white shadow-lg rounded-sm border border-slate-200 mb-8">
                <div class="px-5 py-4">
                    <h2 class="font-semibold text-slate-800 mb-4">{{ __('subscriptions.available_packages') }}</h2>
                    <div class="grid md:grid-cols-3 gap-6">
                        @foreach($availablePackages as $package)
                            <div class="bg-slate-50 p-4 rounded-sm">
                                <h3 class="font-semibold text-slate-800 mb-2">{{ $package->name }}</h3>
                                <p class="text-sm text-slate-600 mb-4">{{ Str::limit($package->description, 100) }}</p>
                                <div class="text-sm text-slate-800 mb-4">
                                    <div><span class="font-semibold">{{ __('subscriptions.price') }}:</span> ${{ number_format($package->calculatePrice(), 2) }}/year</div>
                                    <div class="mt-2"><span class="font-semibold">{{ __('subscriptions.includes') }}:</span></div>
                                    <ul class="list-disc list-inside ml-2">
                                        @foreach($package->affiliationPlans as $affiliation)
                                            <li>{{ __('subscriptions.affiliation') }}: {{ $affiliation->name }}</li>
                                        @endforeach
                                        @foreach($package->insurancePlans as $insurance)
                                            <li>{{ __('subscriptions.insurance') }}: {{ $insurance->name }}</li>
                                        @endforeach
                                        @foreach($package->licenses as $license)
                                            <li>{{ __('subscriptions.license') }}: {{ $license->name }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <form action="{{ route('individual.subscriptions.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="membership_package_id" value="{{ $package->id }}">
                                    <button type="submit" class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white w-full">
                                        {{ __('subscriptions.select_package') }}
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layout>