<?php /** @noinspection PhpIncludeInspection */

declare(strict_types=1);

return [
    'original_id',
    'title',
    'custom_title',
    'description',
    'link',
    'image',
    'created_at',
    'updated_at',
    'categories' => [],
    'entries' => [
        require base_path('tests/fixtures/original-entry-structure.php'),
    ],
];
