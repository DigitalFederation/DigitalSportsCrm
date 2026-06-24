<?php

namespace Domain\Permissions\Actions;

use App\Models\Permission;
use League\Csv\Writer;

class ExportPermissionsToCsvAction
{
    public static function execute(array $filters = []): string
    {
        $query = Permission::query();

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        $permissions = $query->orderBy('category')->orderBy('name')->get();

        // Create CSV
        $csv = Writer::createFromString('');
        $csv->insertOne(['name', 'guard_name', 'category', 'description', 'created_at']);

        foreach ($permissions as $permission) {
            $csv->insertOne([
                $permission->name,
                $permission->guard_name,
                $permission->category,
                $permission->description,
                $permission->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        return $csv->toString();
    }
}
