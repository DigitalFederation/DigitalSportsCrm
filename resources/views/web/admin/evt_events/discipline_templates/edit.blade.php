<x-layout>
    <div class="previous-layout-classes">
        <h1 class="page-first-title">{{ __('Edit Discipline Template') }}</h1>

        <div class="mb-4 mt-2 card">
            <form action="{{ route('admin.evt-events.discipline-templates.update', $disciplineTemplate) }}" method="POST"
                  class="">
                @csrf
                @method('PUT')

                <div class="w-auto mb-2">
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Template Name') }}</label>
                    <input type="text" id="name" name="name" required
                           value="{{ old('name', $disciplineTemplate->name) }}"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                </div>

                <div class="w-auto mb-2">
                    <label for="description" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Description') }}</label>
                    <textarea id="description" name="description"
                              class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">{{ old('description', $disciplineTemplate->description) }}</textarea>
                </div>

                <div class="w-auto mb-2">
                    <label class="block mb-2 text-sm font-medium text-gray-900">{{ __('Select Disciplines') }}</label>
                    @livewire('input.select-multiple', [
                        'items' => $disciplines->pluck('name', 'id')->toArray(),
                        'inputName' => 'disciplines[]',
                        'inputId' => 'disciplineSelection',
                        'inputSelected' => old('disciplines', $disciplineTemplate->disciplines->pluck('id')->toArray()),
                        'identifier' => 'disciplineTemplateEdit',
                    ])
                </div>

                <button type="submit" class="btn-primary btn-xs mt-2">{{ __('Update Template') }}</button>
            </form>
        </div>
    </div>
</x-layout>
