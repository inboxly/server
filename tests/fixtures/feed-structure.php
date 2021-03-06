<?php

declare(strict_types=1);

return [
    'id',
    'name',
    'summary',
    'url',
    'image',
    'created_at',
    'updated_at',
    'categories' => [
        require base_path('tests/fixtures/category-structure.php')
    ],
];
