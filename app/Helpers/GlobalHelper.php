<?php

if (! function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }
}

if (! function_exists('categorizePermissions')) {
    function categorizePermissions($permissions)
    {
        $categories = [
            'diving' => [],
            'sport' => [],
            'scientific' => [],
            'certification' => [],
            'license' => [],
            'membership' => [],
            'coach' => [],
            'instructor' => [],
            'individual' => [],
            'other' => [],
        ];

        foreach ($permissions as $permission) {
            $found = false;
            foreach ($categories as $category => $perms) {
                if (str_contains($permission->name, $category)) {
                    $categories[$category][] = $permission;
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $categories['other'][] = $permission;
            }
        }

        return $categories;
    }
}

if (! function_exists('getSimplifiedFileType')) {
    function getSimplifiedFileType($mimeType)
    {
        $types = [
            'application/pdf' => 'PDF',
            'application/zip' => 'Zip File',
            'image/jpeg' => 'Image',
            'image/png' => 'Image',
            'image/gif' => 'Image',
            'application/msword' => 'Document',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Document',
            'application/vnd.ms-excel' => 'Spreadsheet',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Spreadsheet',
            // Add more mime types and their simplified versions as needed
        ];

        return $types[$mimeType] ?? 'Other';
    }
}
