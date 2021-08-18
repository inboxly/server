<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Inboxly\Receiver\Entry as ReceiverEntry;

/**
 * @mixin \App\Models\OriginalEntry (hotfix for phpstorm typehints)
 */
class OriginalEntry extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'original_entries';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'original_feed_id',
        'external_id',
        'name',
        'summary',
        'content',
        'url',
        'image',
        'author',
        'created_at',
        'updated_at',
    ];

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
     * Original feed to which this original entry belongs
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function originalFeed(): BelongsTo
    {
        return $this->belongsTo(OriginalFeed::class);
    }

    /**
     * Get hash of general attributes of the entry
     *
     * @return string
     */
    public function getHash(): string
    {
        return hash('sha256', join('', [
            $this->external_id,
            $this->name,
            $this->summary,
            $this->content,
            $this->url,
            $this->image,
            $this->author->name ?? '',
            $this->author->url ?? '',
        ]));
    }

    /**
     * Update or create OriginalEntry by mapping ReceiverEntry instance
     *
     * @param \Inboxly\Receiver\Entry $receiverEntry
     * @param \App\Models\OriginalFeed $originalFeed
     * @return static
     */
    public static function fromReceiverEntry(ReceiverEntry $receiverEntry, OriginalFeed $originalFeed): self
    {
        return self::updateOrCreate([
            'original_feed_id' => $originalFeed->getKey(),
            'external_id' => $receiverEntry->externalId,
        ], [
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
