<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\Category
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property bool $is_default
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Feed[] $feeds
 * @property-read int|null $feeds_count
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\CategoryFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Collection
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Entry[] $entries
 * @property-read int|null $entries_count
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\CollectionFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Collection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Collection query()
 */
	class Collection extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Entry
 *
 * @property int $id
 * @property int $user_id
 * @property int $feed_id
 * @property int $original_entry_id
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property \Illuminate\Support\Carbon|null $saved_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Collection[] $collections
 * @property-read int|null $collections_count
 * @property-read \App\Models\Feed $feed
 * @property-read \App\Models\OriginalEntry $original
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\EntryFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Entry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Entry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Entry query()
 */
	class Entry extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Feed
 *
 * @property int $id
 * @property int $user_id
 * @property int $original_feed_id
 * @property string|null $title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Category[] $categories
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Entry[] $entries
 * @property-read int|null $entries_count
 * @property-read \App\Models\OriginalFeed $original
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\FeedFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Feed newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Feed newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Feed query()
 */
	class Feed extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\OriginalEntry
 *
 * @mixin \App\Models\OriginalEntry (hotfix for phpstorm typehints)
 * @property int $id
 * @property int $original_feed_id
 * @property string $external_id
 * @property string $hash
 * @property string $title
 * @property string|null $description
 * @property string|null $text
 * @property string|null $link
 * @property string|null $image
 * @property object|null $author
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\OriginalFeed $originalFeed
 * @method static \Database\Factories\OriginalEntryFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|OriginalEntry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OriginalEntry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OriginalEntry query()
 */
	class OriginalEntry extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\OriginalFeed
 *
 * @mixin \App\Models\OriginalFeed (hotfix for phpstorm typehints)
 * @property int $id
 * @property string $fetcher_key
 * @property string $fetcher_feed_id
 * @property \Inboxly\Receiver\Contracts\Parameters|null $parameters
 * @property string $title
 * @property string|null $description
 * @property string|null $link
 * @property string|null $image
 * @property string|null $author
 * @property string|null $language
 * @property \Illuminate\Support\Carbon|null $next_update_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\OriginalEntry[] $originalEntries
 * @property-read int|null $original_entries_count
 * @method static \Database\Factories\OriginalFeedFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|OriginalFeed newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OriginalFeed newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OriginalFeed query()
 */
	class OriginalFeed extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string $api_token
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Category[] $categories
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Collection[] $collections
 * @property-read int|null $collections_count
 * @property-read \App\Models\Category|null $defaultCategory
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Entry[] $entries
 * @property-read int|null $entries_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Feed[] $feeds
 * @property-read int|null $feeds_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 */
	class User extends \Eloquent {}
}

