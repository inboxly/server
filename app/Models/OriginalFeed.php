<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\ParametersCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Inboxly\Receiver\Feed as ReceiverFeed;

/**
 * @mixin \App\Models\OriginalFeed (hotfix for phpstorm typehints)
 */
class OriginalFeed extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'original_feeds';

    /**
     * The name of the "updated at" column.
     * Set null for disable default timestamp behavior
     *
     * @var string|null
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'fetcher_key',
        'fetcher_feed_id',
        'parameters',
        'title',
        'description',
        'link',
        'image',
        'author',
        'language',
        'next_update_at',
        'updated_at',
    ];

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
     * Original entries in this original feed
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function originalEntries(): HasMany
    {
        return $this->hasMany(OriginalEntry::class);
    }

    /**
     * Update or create OriginalFeed by mapping ReceiverFeed instance
     *
     * @param \Inboxly\Receiver\Feed $receiverFeed
     * @return static
     */
    public static function fromReceiverFeed(ReceiverFeed $receiverFeed): self
    {
        return self::updateOrCreate([
            'fetcher_key' => $receiverFeed->parameters->getFetcherKey(),
            'fetcher_feed_id' => $receiverFeed->parameters->getFeedId(),
        ], [
            'parameters' => $receiverFeed->parameters,
            'title' => $receiverFeed->title,
            'description' => $receiverFeed->description,
            'link' => $receiverFeed->link,
            'image' => $receiverFeed->image,
            'author' => $receiverFeed->authorName,
            'language' => $receiverFeed->language,
            'updated_at' => $receiverFeed->updatedAt,
            'next_update_at' => $receiverFeed->nextUpdateAt,
        ]);
    }
}
