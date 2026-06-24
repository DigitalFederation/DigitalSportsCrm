<?php

return [
    'models' => [
        'user' => \App\Models\User::class,
        'individual' => \Domain\Individuals\Models\Individual::class,
        'foreign_key' => 'user_id',
    ],
    'student' => [
        'parent_model' => \Domain\Individuals\Models\Individual::class,
        'foreign_key' => 'id',
    ],
    'table_names' => [
        'users' => 'individuals',
    ],
    'main_layout' => 'components.layouts.app',
];
