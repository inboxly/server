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
    'feed' => [
        'id',
        'name',
        'custom_name',
        'url',
        'image',
        'created_at',
        'updated_at',
    ],
    'collections' => [
        [
            'id',
            'name',
        ]
    ],
];
