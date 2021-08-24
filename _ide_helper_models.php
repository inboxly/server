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
 * @property string $name
 * @property string $type
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
 * @property string $name
 * @property string $type
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
 * @method \Illuminate\Database\Eloquent\Relations\BelongsToMany userCollections()
 * @method \Illuminate\Database\Eloquent\Relations\HasOne userReadState()
 * @property-read \App\Models\Collection[]|\Illuminate\Database\Eloquent\Collection|null $userCollections
 * @property-read \App\Models\ReadState|null $userReadState
 * @see \App\Http\Middleware\RegisterDynamicRelations::entryUserCollections()
 * @see \App\Http\Middleware\RegisterDynamicRelations::entryUserReadState()
 * @property string $id
 * @property string $feed_id
 * @property string $external_id
 * @property string $name
 * @property string|null $summary
 * @property string|null $content
 * @property string|null $url
 * @property string|null $image
 * @property object|null $author
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Feed $feed
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
 * @method \Illuminate\Database\Eloquent\Relations\BelongsToMany userCategories()
 * @property-read \App\Models\Category[]|\Illuminate\Database\Eloquent\Collection|null $userCategories
 * @see \App\Http\Middleware\RegisterDynamicRelations::feedUserCategories()
 * @property string $id
 * @property string $name
 * @property string $fetcher_key
 * @property string $fetcher_feed_id
 * @property \Inboxly\Receiver\Contracts\Parameters|null $parameters
 * @property string|null $summary
 * @property string|null $url
 * @property string|null $image
 * @property string|null $author
 * @property string|null $language
 * @property \Illuminate\Support\Carbon|null $next_update_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Category[] $categories
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Entry[] $entries
 * @property-read int|null $entries_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $subscribers
 * @property-read int|null $subscribers_count
 * @method static \Database\Factories\FeedFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Feed newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Feed newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Feed query()
 */
	class Feed extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ReadState
 *
 * @property int $id
 * @property int $user_id
 * @property string $entry_id
 * @property string $feed_id
 * @property string|null $read_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Entry $entry
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\ReadStateFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|ReadState newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReadState newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReadState query()
 * @method static \Illuminate\Database\Eloquent\Builder|ReadState today()
 */
	class ReadState extends \Eloquent {}
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Entry[] $entries
 * @property-read int|null $entries_count
 * @property-read \App\Models\Category|null $mainCategory
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Entry[] $readEntries
 * @property-read int|null $read_entries_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ReadState[] $readStates
 * @property-read int|null $read_states_count
 * @property-read \App\Models\Collection|null $savedCollection
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Feed[] $subscribedFeeds
 * @property-read int|null $subscribed_feeds_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Entry[] $unreadEntries
 * @property-read int|null $unread_entries_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 */
	class User extends \Eloquent {}
}

