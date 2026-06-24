<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserCreateRequest;
use App\Models\Group;
use App\Models\User;
use App\Notifications\CreatedUserNotification;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Users\Actions\CreateUserAction;
use Domain\Users\Actions\ResendUserNotificationAction;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UsersController extends Controller
{
    /**
     * Only last X records
     */
    public function index(): View
    {
        $users = QueryBuilder::for(User::class)
            ->with('roles', 'federations', 'individuals', 'entities', 'group')
            ->allowedFilters([
                AllowedFilter::scope('filter_date'),
                AllowedFilter::scope('filter_email'),
                AllowedFilter::scope('filter_status'),
                AllowedFilter::callback('filter_cmas_admin', function ($query, $value) {
                    if ($value === 'cmas_admin') {
                        return $query->whereHas('group', function ($q) {
                            $q->where('code', 'ADMIN');
                        });
                    }

                    return $query;
                }),
                AllowedFilter::callback('filter_relationship', function ($query, $value) {
                    return $query->filterRelationship($value);
                }),
                AllowedFilter::callback('filter_federation', function ($query, $value) {
                    return $query->whereHas('federations', function ($q) use ($value) {
                        $q->where('federation.id', $value);
                    });
                }),
                AllowedFilter::callback('filter_entity', function ($query, $value) {
                    return $query->whereHas('entities', function ($q) use ($value) {
                        $q->where('entity.id', $value);
                    });
                }),
            ])
            ->latest()
            ->paginate()
            ->appends(request()->query());

        $filter_status = [
            'active' => ['id' => '1', 'name' => __('Active')],
            'inactive' => ['id' => '0', 'name' => __('Inactive')],
        ];

        // Get federations with country name prepended
        $federations = Federation::join('country', 'federation.country_id', '=', 'country.id')
            ->orderBy('country.name')
            ->orderBy('federation.name')
            ->select('federation.id', 'federation.name', 'country.name as country_name')
            ->get()
            ->mapWithKeys(function ($item) {
                $key = $item->id;
                $value = $item->country_name . ' - ' . $item->name;

                return [$key => $value];
            });

        $entities = Entity::orderBy('name')->pluck('name', 'id');

        $filter_relationships = [
            'federation' => ['id' => 'federation', 'name' => __('common.federation')],
            'entity' => ['id' => 'entity', 'name' => __('common.entity')],
            'individual' => ['id' => 'individual', 'name' => __('common.individual')],
        ];

        $filter_cmas_admin = [
            'cmas_admin' => ['id' => 'cmas_admin', 'name' => __('common.cmas_admin_users')],
        ];

        return view('web.admin.user.index', compact(
            'users',
            'filter_status',
            'federations',
            'entities',
            'filter_relationships',
            'filter_cmas_admin',
        ));
    }

    public function create()
    {
        $federations = Federation::join('country', 'federation.country_id', '=', 'country.id')
            ->orderBy('country.name')->orderBy('federation.legal_name')
            ->select('federation.*', 'country.name AS couuntry_name')->get()
            ->mapWithKeys(function ($item) {
                $key = $item->id;
                $value = $item->couuntry_name . ' - ' . $item->legal_name;

                return [$key => $value];
            });
        $roles = Role::pluck('name', 'id');
        $groups = Group::pluck('name', 'id');
        $user = new User;
        $manualRoleIds = []; // Empty array for new users

        return view('web.admin.user.create', compact('federations', 'roles', 'groups', 'user', 'manualRoleIds'));
    }

    public function edit($id)
    {
        $user = User::with('federations', 'entities', 'roles')->findOrFail($id);

        // Get the role IDs the user currently has
        $manualRoleIds = $user->roles->pluck('id')->toArray();

        $federations = Federation::pluck('legal_name', 'id');
        $roles = Role::pluck('name', 'id');
        $groups = Group::pluck('name', 'id');

        return view('web.admin.user.edit', compact('user', 'federations', 'roles', 'groups', 'manualRoleIds'));
    }

    public function store(
        UserCreateRequest $request,
        CreateUserAction $createUserAction
    ) {

        try {
            DB::beginTransaction();

            // Create the USER
            $createUserResult = $createUserAction([
                'email' => $request->email,
                'name' => $request->name,
                'group_id' => $request->group_id,
                'bypass_verification' => true,
            ], true);
            $user = $createUserResult['user'];

            // Find ClassGroup ID check if it is Federation
            $group = Group::findOrFail($request->group_id);
            if ($group->code == 'FEDERATION' && ! empty($request->federation)) {
                $user->federations()->attach($request->federation);
            }

            if ($request->filled('roles')) {
                // Convert role IDs to role names
                $roleNames = Role::whereIn('id', $request->roles)->pluck('name')->toArray();
                $user->syncRoles($roleNames);
            }

            if (! empty($user)) {
                $user->notify(new CreatedUserNotification($user, $createUserResult['token']));
            }

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());

            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.users.index')->with('success', __('User created successfully'));
    }

    // Update the data from the edit form
    public function update(
        UserCreateRequest $request,
        $id
    ) {

        $user = User::findOrFail($id);

        try {

            DB::beginTransaction();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->active = $request->has('active') && $request->active == '1'; // Update active status
            $user->save();

            // Find ClassGroup ID check if it is Federation
            $group = Group::findOrFail($request->group_id);
            if ($group->code == 'FEDERATION') {
                if (! empty($request->federation)) {
                    $user->federations()->sync([$request->federation]);
                } else {
                    $user->federations()->detach();
                }
            }

            // Update roles - sync to exactly what was selected
            // Convert role IDs to role names
            $roleIds = $request->roles ?? [];
            $roleNames = $roleIds ? Role::whereIn('id', $roleIds)->pluck('name')->toArray() : [];
            $user->syncRoles($roleNames);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());

            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.users.index')->with('success', __('User edited successfully'));
    }

    public function resendUserCreationEmail($id, ResendUserNotificationAction $resendUserNotificationAction)
    {
        $user = User::findOrFail($id);

        $success = $resendUserNotificationAction->execute($user);

        if ($success) {
            return redirect()->route('admin.users.index')->with('success', __('User creation email resent successfully'));
        }

        return redirect()->route('admin.users.index')->with('error', __('Failed to resend user creation email'));
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        try {
            DB::transaction(function () use ($user) {
                // Handle Individuals
                $user->individuals->each(function (Individual $individual) {

                    // Check if the individual can be deleted
                    if (! $individual->licenses()->exists() && ! $individual->certifications()->exists()) {
                        // Soft delete the individual
                        $individual->delete();
                    } else {
                        // If it can't be deleted, just remove the user association
                        $individual->update(['user_id' => null]);
                    }
                });

                // Handle Federations
                $user->federations()->detach();

                // Handle Entities
                $user->entities()->detach();

                // Delete the user
                $user->delete();
            });

            return redirect()->route('admin.users.index')->with('success', __('User and associated records deleted successfully'));
        } catch (Exception $exception) {
            Log::error('Error deleting user: ' . $exception->getMessage());

            return redirect()->route('admin.users.index')->with('error', __('An error occurred while deleting the user'));
        }
    }
}
