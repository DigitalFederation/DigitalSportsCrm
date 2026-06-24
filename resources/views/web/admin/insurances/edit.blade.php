<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title">{{ __('Edit Insurance') }}</h1>
        </div>

        <form action="{{ route('admin.insurances.update', $insurance) }}" method="POST">
            @csrf
            @method('PUT')
            <section class="card">
                <x-information-box
                        title="{{ __('Information') }}"
                        body="{{ __('Update the insurance details by modifying the fields below.') }}"
                >
                </x-information-box>

                <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                    <div class="w-1/2">
                        <label class="block text-sm font-medium mb-1" for="start_date">{{ __('Start Date') }} <span class="text-rose-500">*</span></label>
                        <input type="date" id="start_date" name="start_date"
                               class="form-input w-full {{ $errors->has('start_date') ? 'border-rose-300' : '' }}"
                               value="{{ old('start_date', $insurance->start_date->format('Y-m-d')) }}" required>
                        @if($errors->has('start_date'))
                            <div class="text-xs mt-1 text-rose-500">
                                {{ $errors->first('start_date') }}
                            </div>
                        @endif
                    </div>

                    <div class="w-1/2">
                        <label class="block text-sm font-medium mb-1" for="end_date">{{ __('End Date') }} <span class="text-rose-500">*</span></label>
                        <input type="date" id="end_date" name="end_date"
                               class="form-input w-full {{ $errors->has('end_date') ? 'border-rose-300' : '' }}"
                               value="{{ old('end_date', $insurance->end_date->format('Y-m-d')) }}" required>
                        @if($errors->has('end_date'))
                            <div class="text-xs mt-1 text-rose-500">
                                {{ $errors->first('end_date') }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                    <div class="w-1/3">
                        <label class="block text-sm font-medium mb-1" for="fee">{{ __('Fee') }} <span class="text-rose-500">*</span></label>
                        <input type="number" id="fee" name="fee" step="0.01"
                               class="form-input w-full {{ $errors->has('fee') ? 'border-rose-300' : '' }}"
                               value="{{ old('fee', $insurance->fee) }}" required>
                        @if($errors->has('fee'))
                            <div class="text-xs mt-1 text-rose-500">
                                {{ $errors->first('fee') }}
                            </div>
                        @endif
                    </div>

                    <div class="w-1/3">
                        <label class="block text-sm font-medium mb-1" for="policy_number">{{ __('Policy Number') }}</label>
                        <input type="text" id="policy_number" name="policy_number"
                               class="form-input w-full {{ $errors->has('policy_number') ? 'border-rose-300' : '' }}"
                               value="{{ old('policy_number', $insurance->policy_number) }}">
                        @if($errors->has('policy_number'))
                            <div class="text-xs mt-1 text-rose-500">
                                {{ $errors->first('policy_number') }}
                            </div>
                        @endif
                    </div>

                    <div class="w-1/3">
                        <label class="block text-sm font-medium mb-1" for="is_external">{{ __('External Insurance') }}</label>
                        <select id="is_external" name="is_external"
                                class="form-select w-full {{ $errors->has('is_external') ? 'border-rose-300' : '' }}">
                            <option value="0" {{ old('is_external', $insurance->is_external) ? '' : 'selected' }}>{{ __('No') }}</option>
                            <option value="1" {{ old('is_external', $insurance->is_external) ? 'selected' : '' }}>{{ __('Yes') }}</option>
                        </select>
                        @if($errors->has('is_external'))
                            <div class="text-xs mt-1 text-rose-500">
                                {{ $errors->first('is_external') }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col mt-5 px-6 pt-5 border-t border-slate-200">
                    <div class="flex self-end">
                        <a class="btn self-center bg-slate-500 text-white"
                           href="{{ route('admin.insurances.index') }}">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit"
                                class="btn bg-blue-500 hover:bg-blue-600 text-white ml-3 px-3 py-2 rounded">
                            {{ __('Update') }}
                        </button>
                    </div>
                </div>
            </section>
        </form>
    </div>
</x-layout>