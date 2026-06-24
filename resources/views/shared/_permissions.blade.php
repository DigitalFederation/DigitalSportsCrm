<div class="bg-white shadow-lg rounded-sm border border-slate-200 mb-4">

    <div class="px-5 py-4 border-b border-slate-100">
        <h2 class="font-semibold text-slate-800"> {{ $title }} @if(isset($user)) <span class="text-slate-400 font-medium">({{ $user->getDirectPermissions()->count() }})</span> @endif </h2>
    </div>

    <div class="sm:flex flex-wrap items-center justify-between px-5 py-4">
        @foreach($permissions as $perm)

            @php
                $permission_found = null;

                if( isset($role) ) {
                    $permission_found = $role->hasPermissionTo($perm->name);
                }

                if( isset($user)) {
                    $permission_found = $user->hasDirectPermission($perm->name);
                }

            @endphp

            <div class="md:w-1/4 flex items-center">
                <input
                  id="checkbox-{{$perm->name}}"
                  name="permissions[]"
                  type="checkbox"
                  value="{{$perm->name}}"
                  class="form-checkout"
                  @if($permission_found) checked @endif
                  >
                <span class="text-sm ml-2">{{$perm->name}}</span>
            </div>

        @endforeach
    </div>


    <div class="pb-4 ml-2">
        <!-- can('edit_roles') -->
            <button type="submit" class="btn btn-action"> {{ __('Save Permissions') }} </button>
        <!-- endcan -->
    </div>

</div>
