<x-layout>
    <div class="previous-layout-classes">

        <!-- Section Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <div>
                <h1 class="page-first-title">
                    {{ __('Discipline Templates') }}
                </h1>
            </div>
            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('admin.evt-events.events.index') }}"
                   class="btn-info btn-xs">{{ __('Event List') }}</a>
                <a href="{{ route('admin.evt-events.attributes.index') }}"
                   class="btn-info btn-xs">{{ __('Attribute List') }}</a>
                <a href="{{ route('admin.evt-events.disciplines.index') }}"
                   class="btn-info btn-xs">{{ __('Discipline List') }}</a>
            </div>
        </div>

        <!-- Template Creation Form -->
        <x-information-box
            :title="__('Create a Discipline Template')"
            :body="__('Use this form to create a new Discipline Template by giving it a name and selecting the relevant disciplines. These templates help streamline the event creation process by pre-defining sets of disciplines that can be applied to new events with a single selection.')" />

        <div class="mb-4 mt-2 card">
            <form action="{{ route('admin.evt-events.discipline-templates.store') }}" method="POST"
                  class="">
                @csrf

                <div class="w-auto mb-2">
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Template Name') }}</label>
                    <input type="text" id="name" name="name" required
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                </div>

                <!-- SelectMultiple Livewire Component for Discipline Selection -->
                <div class="w-auto mb-2">
                    <label class="block mb-2 text-sm font-medium text-gray-900">{{ __('Select Disciplines') }}</label>
                    @livewire('input.select-multiple', [
                        'items' => $disciplines->pluck('name', 'id')->toArray(),
                        'inputName' => 'disciplines[]',
                        'inputId' => 'disciplineSelection',
                        'inputSelected' => old('disciplines', []),
                        'identifier' => 'disciplineTemplateCreation',
                    ])
                </div>

                <button type="submit" class="btn-primary btn-xs mt-2">{{ __('Create Template') }}</button>
            </form>
        </div>

        <!-- Templates Listing -->
        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            <x-dynamic-table :headers="[__('Template Name'), __('Disciplines'), __('Actions')]">
                @forelse ($templates as $template)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">{{ $template->name }}</td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            {{ $template->disciplines->pluck('name')->join(', ') }}
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="flex justify-end items-center space-x-1">
                                <x-dynamic-table-buttons type="edit"
                                                         :route="route('admin.evt-events.discipline-templates.edit', $template->id)" />

                                <x-dynamic-table-buttons type="delete"
                                                         :route="route('admin.evt-events.discipline-templates.destroy', $template->id)"
                                                         method="DELETE" />


                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap" colspan="3">
                            {{ __('No templates found.') }}
                        </td>
                    </tr>
                @endforelse
            </x-dynamic-table>
        </div>


    </div>
</x-layout>
