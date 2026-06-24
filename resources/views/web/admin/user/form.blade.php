<div class="card w-full">
    <div class=" flex flex-col gap-y-4 mb-4" x-data="{ selectedGroup: '{{ old('group_id', $user->group_id) }}' }">
        <!-- Panel body -->

        <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 border-b border-slate-200 pb-4 ">
            <div>
                <label for="name" class="block text-sm font-medium mb-1">{{ __('common.username') }} *</label>
                <input type="text" name="name" class="form-input w-full" value="{{ old('name', $user->name) }}">
                @if ($errors->has('name'))
                    <div class="text-xs mt-1 text-rose-500 h-2">
                        {{ $errors->first('name') }}
                    </div>
                @endif
            </div>
            <div>
                <label for="email" class="block text-sm font-medium mb-1">{{ __('common.email') }} *</label>
                <input type="email" name="email" class="form-input w-full" value="{{ old('email', $user->email) }}"
                       required>
                @if ($errors->has('email'))
                    <div class="text-xs mt-1 text-rose-500 h-2">
                        {{ $errors->first('email') }}
                    </div>
                @endif
            </div>

            <div>
                <label for="active" class="block text-sm font-medium mb-1">{{ __('common.account_active') }}</label>
                <select name="active" id="active" class="form-select w-full">
                    <option value="">{{ __('common.choose') }}</option>

                    <option
                        value="1"
                        @if(old('active', $user->active) == true) selected @endif>
                        {{ __('common.yes') }}
                    </option>
                    <option
                        value="0"
                        @if(old('active', $user->active) == false) selected @endif>
                        {{ __('common.no') }}
                    </option>
                </select>
            </div>

            <!-- user group select -->
            <div>
                <label for="group_id" class="block text-sm font-medium mb-1">{{ __('common.user_group') }} *</label>
                <select name="group_id" id="group_id" class="form-select w-full" x-model="selectedGroup">
                    <option value="">{{ __('common.choose') }}</option>
                    @foreach ($groups as $key => $user_group)
                        <option
                            value="{{$key}}"
                            @if(old('group_id', $user->group_id) == $key) selected @endif>
                            {{ $user_group }}
                        </option>
                    @endforeach
                </select>
            </div>


        </div>

        <div class="sm:flex items-start gap-4">
            <div class="w-1/2">

                <label
                    for="roles"
                    class="block text-sm font-medium mb-1"> {{ __('common.roles') }} * </label>

                <!-- Simple Scrollable Checkbox List for Roles -->
                <div class="border border-gray-300 rounded-md p-3 bg-white max-h-60 overflow-y-auto">
                    @php
                        $selectedRoles = old('roles', $manualRoleIds ?? []);
                        // Ensure selectedRoles is an array
                        if (!is_array($selectedRoles)) {
                            $selectedRoles = [];
                        }
                    @endphp
                    
                    @forelse($roles as $roleId => $roleName)
                        <label class="flex items-center py-2 hover:bg-gray-50 cursor-pointer rounded px-2">
                            <input 
                                type="checkbox" 
                                name="roles[]" 
                                value="{{ $roleId }}"
                                @if(in_array($roleId, $selectedRoles)) checked @endif
                                class="form-checkbox h-4 w-4 text-blue-600 rounded mr-3"
                            >
                            <span class="text-sm">{{ $roleName }}</span>
                        </label>
                    @empty
                        <p class="text-gray-400 text-sm">{{ __('common.no_roles_available') }}</p>
                    @endforelse
                </div>

                <div class="text-xs text-gray-400 mt-1">{{ __('common.select_roles_hint') }}</div>

                @if ($errors->has('roles'))
                    <div class="text-xs mt-1 text-rose-500 h-2">
                        {{ $errors->first('roles') }}
                    </div>
                @endif
                


            </div>

            <!-- Select Federation IF group is Federation -->
            <div class="w-1/2">
                <div x-show="selectedGroup == 3" x-cloak>
                    <label for="federation"
                           class="block text-sm font-medium mb-1">{{ __('common.choose_federation_for_user') }}</label>
                    <select name="federation" id="federation" class="form-select w-full">
                        <option value="">{{ __('common.choose') }}</option>
                        @foreach ($federations as $key => $federation)
                            <option
                                class="whitespace-nowrap"
                                value="{{ $key}}"
                                @if (old('federation', optional($user->federations->first())->id) == $key) selected @endif>
                                {{ $federation }}
                            </option>
                        @endforeach
                    </select>
                    @if ($errors->has('federation'))
                        <div class="text-xs mt-1 text-rose-500 h-2">
                            {{ $errors->first('federation') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <x-forms.card-form-submit backRoute="admin.users.index" :buttonText="__('common.save_user')"></x-forms.card-form-submit>

</div>
