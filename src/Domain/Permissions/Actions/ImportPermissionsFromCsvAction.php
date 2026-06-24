<?php

namespace Domain\Permissions\Actions;

use League\Csv\Reader;

class ImportPermissionsFromCsvAction
{
    public static function execute(string $filePath, bool $skipExisting = true): array
    {
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);

        $permissions = [];
        foreach ($csv as $record) {
            // Clean and prepare data
            $permissionData = [
                'name' => trim($record['name'] ?? ''),
                'description' => trim($record['description'] ?? ''),
                'category' => trim($record['category'] ?? ''),
                'guard_name' => trim($record['guard_name'] ?? 'web'),
            ];

            // Skip empty rows
            if (empty($permissionData['name'])) {
                continue;
            }

            $permissions[] = $permissionData;
        }

        // Use bulk create action
        return BulkCreatePermissionsAction::execute($permissions);
    }
}
