<x-layout>
    <div class="previous-layout-classes">
        <div class="mb-8 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Create Affiliation Plan') }}</h1>
        </div>

        <x-information-box title="{{ __('Create Affiliation Plan') }}" 
                          body="{{ __('memberships.create_plan_help') }}">
        </x-information-box>

        <div class="bg-white shadow-md rounded-lg p-6 mt-8">
            <form action="{{ route('admin.affiliation-plans.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('web.admin.affiliation-plans.form-enhanced')

                <div class="mt-8 flex justify-start gap-x-2">
                    <a href="{{ route('admin.affiliation-plans.index') }}" class="btn btn-info">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        {{ __('Create Affiliation Plan') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>