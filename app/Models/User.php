<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Collections owned by user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

    /**
     * User's "read later" collection
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function readLaterCollection(): HasOne
    {
        return $this->hasOne(Collection::class)->where('type', Collection::TYPE_READ_LATER);
    }

    /**
     * Categories owned by user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * User's "main" category
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function mainCategory(): HasOne
    {
        return $this->hasOne(Category::class)->where('type', Category::TYPE_MAIN);
    }

    /**
     * User's read states of entries
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function readStates(): HasMany
    {
        return $this->hasMany(ReadState::class);
    }

    /**
     * User's unread and read entries
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function entries(): BelongsToMany
    {
        return $this->belongsToMany(Entry::class, 'read_states');
    }

    /**
     * Entries marked "unread" by user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function unreadEntries(): BelongsToMany
    {
        return $this->belongsToMany(Entry::class, 'read_states')->wherePivotNull('read_at');
    }

    /**
     * Entries marked "read" by user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function readEntries(): BelongsToMany
    {
        return $this->belongsToMany(Entry::class, 'read_states')->wherePivotNotNull('read_at');
    }

    /**
     * User's subscribed feeds
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subscribedFeeds(): BelongsToMany
    {
        return $this->belongsToMany(Feed::class, 'subscriptions');
    }
}
