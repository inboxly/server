<?php /** @noinspection PhpIncludeInspection */

declare(strict_types=1);

return [
    'original_id',
    'name',
    'custom_name',
    'summary',
    'url',
    'image',
    'created_at',
    'updated_at',
    'categories' => [],
    'entries' => [
        require base_path('tests/fixtures/original-entry-structure.php'),
    ],
];
