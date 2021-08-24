<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\ParametersCast;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Inboxly\Receiver\Feed as ReceiverFeed;

/**
 * @method \Illuminate\Database\Eloquent\Relations\BelongsToMany userCategories()
 * @property-read \App\Models\Category[]|\Illuminate\Database\Eloquent\Collection|null $userCategories
 * @see \App\Http\Middleware\RegisterDynamicRelations::feedUserCategories()
 */
class Feed extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'feeds';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'strings';

    /**
     * The name of the "updated at" column.
     * Set null for disable default timestamp behavior
     *
     * @var string|null
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'parameters' => ParametersCast::class,
        'next_update_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s.u';

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted(): void
    {
        parent::booted();

        $table = self::newModelInstance()->getTable();
        static::addGlobalScope(fn(Builder $query) => $query->oldest("$table.created_at"));
    }

    /**
     * Entries in this feed
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }

    /**
     * Users who subscribed to this feed
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'subscriptions');
    }

    /**
     * Categories in which this feed has been added
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_feeds');
    }

    /**
     * Update or create Feed by mapping ReceiverFeed instance
     *
     * @param \Inboxly\Receiver\Feed $receiverFeed
     * @return static
     */
    public static function fromReceiverFeed(ReceiverFeed $receiverFeed): self
    {
        return self::updateOrCreate([
            'id' => sha1(join('', [
                $receiverFeed->parameters->getFetcherKey(),
                $receiverFeed->parameters->getFeedId(),
            ])),
        ], [
            'fetcher_key' => $receiverFeed->parameters->getFetcherKey(),
            'fetcher_feed_id' => $receiverFeed->parameters->getFeedId(),
            'parameters' => $receiverFeed->parameters,
            'name' => $receiverFeed->name,
            'summary' => $receiverFeed->summary,
            'url' => $receiverFeed->url,
            'image' => $receiverFeed->image,
            'author' => $receiverFeed->authorName,
            'language' => $receiverFeed->language,
            'updated_at' => $receiverFeed->updatedAt,
            'next_update_at' => $receiverFeed->nextUpdateAt, // todo: Need? What if it has no subscribers?
        ]);
    }

    /**
     * Enable feed auto-updating feed
     *
     * @return void
     */
    public function enableUpdating(): void
    {
        $this->update(['next_update_at' => Carbon::now()]);
    }

    /**
     * Disable feed auto-updating
     *
     * @return void
     */
    public function disableUpdating(): void
    {
        $this->update(['next_update_at' => null]);
    }
}
