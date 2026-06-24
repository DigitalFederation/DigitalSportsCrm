<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <h1 class="page-first-title">{{ __('professional_roles.edit_title') }}</h1>
        </div>

        <form action="{{ route('admin.professional-roles.update', $professionalRole) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="bg-white dark:bg-slate-800 shadow-lg rounded-sm mb-8">
                <div class="flex flex-col md:flex-row md:-mr-px">
                    <div class="grow">
                        <section class="p-6 space-y-6">
                            <!-- Information Box -->
                            <x-information-box
                                :title="__('professional_roles.information_title')"
                                :body="__('professional_roles.edit_info_body')"
                            />

                            <!-- Input Fields -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Name -->
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="name">
                                        {{ __('professional_roles.name') }} <span class="text-rose-500">*</span>
                                    </label>
                                    <input id="name" class="form-input w-full @error('name') border-rose-500 @enderror" type="text" name="name" value="{{ old('name', $professionalRole->name) }}" required />
                                    @error('name')
                                        <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Code -->
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="code">
                                        {{ __('professional_roles.code') }} <span class="text-rose-500">*</span>
                                    </label>
                                    <input id="code" class="form-input w-full @error('code') border-rose-500 @enderror" type="text" name="code" value="{{ old('code', $professionalRole->code) }}" required />
                                    @error('code')
                                        <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                    @enderror
                                    <p class="text-xs text-slate-500 mt-1">{{ __('professional_roles.code_hint') }}</p>
                                </div>

                                <!-- Role Type -->
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="role">
                                        {{ __('professional_roles.role_type') }} <span class="text-rose-500">*</span>
                                    </label>
                                    <select id="role" class="form-select w-full @error('role') border-rose-500 @enderror" name="role" required>
                                        <option value="">{{ __('common.select') }}</option>
                                        @foreach($roleTypes as $roleType)
                                            <option value="{{ $roleType }}" {{ old('role', $professionalRole->role) === $roleType ? 'selected' : '' }}>{{ __('professional_roles.role_types.' . $roleType) }}</option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                        <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                    @enderror
                                    <p class="text-xs text-slate-500 mt-1">{{ __('professional_roles.role_type_hint') }}</p>
                                </div>

                                <!-- Committee -->
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="committee_id">
                                        {{ __('professional_roles.committee') }}
                                    </label>
                                    <select id="committee_id" class="form-select w-full @error('committee_id') border-rose-500 @enderror" name="committee_id">
                                        <option value="">{{ __('professional_roles.committee_all') }}</option>
                                        @foreach($committees as $committee)
                                            <option value="{{ $committee->id }}" {{ old('committee_id', $professionalRole->committee_id) == $committee->id ? 'selected' : '' }}>{{ $committee->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('committee_id')
                                        <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        <!-- Submit Button Section -->
                        <section>
                            <div class="flex flex-col px-6 py-5 border-t border-slate-200 dark:border-slate-700">
                                <div class="flex self-end">
                                    <a class="btn bg-slate-500 text-white" href="{{ route('admin.professional-roles.index') }}">{{ __('common.back') }}</a>
                                    <button type="submit" class="btn btn-primary ml-3">{{ __('professional_roles.update') }}</button>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </form>

    </div>
</x-layout>
