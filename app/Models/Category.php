<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    /**
     * Possible types of categories
     */
    public const TYPE_MAIN = 'main';
    public const TYPE_CUSTOM = 'custom';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
     */
    protected $guarded = [];

    /**
     * User - owner of this category
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Feeds in this category
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function feeds(): BelongsToMany
    {
        return $this->belongsToMany(Feed::class, 'category_feeds');
    }
}
