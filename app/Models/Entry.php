<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Inboxly\Receiver\Entry as ReceiverEntry;

/**
 * @method \Illuminate\Database\Eloquent\Relations\BelongsToMany userCollections()
 * @method \Illuminate\Database\Eloquent\Relations\HasOne userReadState()
 * @property-read \App\Models\Collection[]|\Illuminate\Database\Eloquent\Collection|null $userCollections
 * @property-read \App\Models\ReadState|null $userReadState
 * @see \App\Http\Middleware\RegisterDynamicRelations::entryUserCollections()
 * @see \App\Http\Middleware\RegisterDynamicRelations::entryUserReadState()
 */
class Entry extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'entries';

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
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

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
        'author' => 'object',
        'created_at' => 'datetime',
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
        static::addGlobalScope(fn(Builder $query) => $query->latest("$table.created_at"));
    }

    /**
     * Feed to which this entry belongs
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }

    /**
     * Update or create OriginalEntry by mapping ReceiverEntry instance
     *
     * @param \Inboxly\Receiver\Entry $receiverEntry
     * @param \App\Models\Feed $feed
     * @return static
     */
    public static function fromReceiverEntry(ReceiverEntry $receiverEntry, Feed $feed): self
    {
        return self::updateOrCreate([
            'id' => sha1(join('', [
                $feed->getKey(),
                $receiverEntry->externalId,
            ])),
        ], [
            'feed_id' => $feed->getKey(),
            'external_id' => $receiverEntry->externalId,
            'name' => $receiverEntry->name,
            'summary' => $receiverEntry->summary,
            'content' => $receiverEntry->content,
            'url' => $receiverEntry->url,
            'image' => $receiverEntry->image,
            'author' => $receiverEntry->authorName
                ? [
                    'name' => $receiverEntry->authorName,
                    'url' => $receiverEntry->authorUrl,
                ]
                : null,
            'created_at' => $receiverEntry->createdAt,
            'updated_at' => $receiverEntry->updatedAt,
        ]);
    }
}
