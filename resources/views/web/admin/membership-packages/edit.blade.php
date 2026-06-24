<x-layout>
    <div class="previous-layout-classes">
        <div class="mb-8 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Edit Membership Package') }}</h1>
        </div>

        <x-information-box title="Information" body="This screen allows you to edit an existing membership package. Membership packages define the combination of affiliations, insurances, and licenses that can be offered to members.">
        </x-information-box>

        <div class="bg-white shadow-md rounded-lg p-6 mt-8">
            <form action="{{ route('admin.membership-packages.update', $package->id) }}" method="POST">
                @method('PUT')
                @include('web.admin.membership-packages.form', ['package' => $package])

                <!-- Submit Button -->
                <div class="mt-8 flex justify-start gap-x-2">
                    <a href="{{ route('admin.membership-packages.index') }}" class="btn btn-info">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        {{ __('Update Membership Package') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>