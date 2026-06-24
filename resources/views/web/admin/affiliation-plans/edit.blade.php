<x-layout>
    <div class="previous-layout-classes">
        <div class="mb-8 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Edit Affiliation Plan') }}</h1>
        </div>

        <x-information-box title="{{ __('Edit Affiliation Plan') }}" 
                          body="{{ __('memberships.edit_plan_help') }}">
        </x-information-box>

        <div class="bg-white shadow-md rounded-lg p-6 mt-8">
            <form action="{{ route('admin.affiliation-plans.update', $plan) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('web.admin.affiliation-plans.form-enhanced')

                <div class="mt-8 flex justify-start gap-x-2">
                    <a href="{{ route('admin.affiliation-plans.index') }}" class="btn btn-info">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        {{ __('Update Affiliation Plan') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>