<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Collection extends Model
{
    use HasFactory;

    /**
     * Possible types of collections
     */
    public const TYPE_READ_LATER = 'read_later';
    public const TYPE_CUSTOM = 'custom';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
     */
    protected $guarded = [];

    /**
     * User - owner of this collection
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Entries in this collection
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function entries(): BelongsToMany
    {
        return $this->belongsToMany(Entry::class, 'collection_entries');
    }
}
