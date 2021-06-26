<?php

declare(strict_types=1);

return [
    'id',
    'title',
    'description',
    'text',
    'link',
    'image',
    'author' => [
        'name',
        'image',
        'link',
    ],
    'is_read',
    'is_saved',
    'created_at',
    'updated_at',
    'feed' => [
        'id',
        'title',
        'custom_title',
        'link',
        'image',
        'created_at',
        'updated_at',
    ],
    'collections' => [
        [
            'id',
            'title',
        ]
    ],
];
