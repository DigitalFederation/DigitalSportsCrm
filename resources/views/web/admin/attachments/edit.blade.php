<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('attachments.edit_file') }}</h1>
            </div>
        </div>

        <div class="card md:-mr-px mb-8 w-full">
            <form action="{{ route('admin.attachments.update', $attachment->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="flex flex-col md:flex-row justify-start items-start gap-x-4 mb-4">
                    <div class="w-full md:w-1/3 flex flex-col">
                        <label class="block text-sm font-medium mb-1" for="name">{{ __('attachments.filters.file_name') }}</label>
                        <input class="form-input w-full" type="text" name="name" id="name"
                               value="{{ old('name', $attachment->name) }}" required />
                        @error('name')
                            <div class="text-red-400 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="w-full md:w-1/3 flex flex-col">
                        <label class="block text-sm font-medium mb-1" for="category_id">{{ __('attachments.filters.category') }}</label>
                        <select class="form-select w-full" name="category_id" id="category_id" required>
                            @foreach ($categories as $id => $name)
                                <option value="{{ $id }}" @selected(old('category_id', $attachment->category_id) == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="text-red-400 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="w-full md:w-1/3 flex flex-col">
                        <label class="block text-sm font-medium mb-1" for="language_id">{{ __('attachments.filters.language') }}</label>
                        <select class="form-select w-full" name="language_id" id="language_id">
                            <option value="">--</option>
                            @foreach ($languages as $id => $name)
                                <option value="{{ $id }}" @selected(old('language_id', $attachment->language_id) == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('language_id')
                            <div class="text-red-400 text-xs mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="flex gap-x-4 mt-6">
                    <a class="btn bg-slate-500 text-white" href="{{ route('admin.attachments.index') }}">
                        {{ __('common.back') }}
                    </a>
                    <button type="submit" class="btn btn-action">
                        {{ __('common.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>
