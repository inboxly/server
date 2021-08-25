<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Entry
 */
class EntryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'summary' => $this->summary,
            'content' => $this->content,
            'url' => $this->url,
            'image' => $this->image,
            'author' => $this->author,
            'is_read' => (bool)$this->whenLoaded(
                'userReadState',
                fn() => $this->userReadState->read_at,
            ),
            'is_read_later' => (bool)$this->whenLoaded(
                'userCollections',
                fn() => $this->userCollections->where('type', Collection::TYPE_READ_LATER)->isNotEmpty(),
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'feed' => FeedResource::make(
                $this->whenLoaded('feed')
            ),
            'collections' => $this->whenLoaded(
                'userCollections',
                fn() => CollectionResource::collection(
                    $this->userCollections->where('type', Collection::TYPE_CUSTOM)
                )
            ),
        ];
    }
}
