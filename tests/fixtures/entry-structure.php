<?php

declare(strict_types=1);

return [
    'id',
    'name',
    'summary',
    'content',
    'url',
    'image',
    'author' => [
        'name',
        'image',
        'url',
    ],
    'is_read',
    'is_saved',
    'created_at',
    'updated_at',
    'feed' => require base_path('tests/fixtures/feed-structure.php'),
    'collections' => [
        require base_path('tests/fixtures/collection-structure.php')
    ],
];
