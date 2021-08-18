<?php

declare(strict_types=1);

namespace App\Http\Resources;

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
            'name' => $this->original->name,
            'summary' => $this->original->summary,
            'content' => $this->original->content,
            'url' => $this->original->url,
            'image' => $this->original->image,
            'author' => $this->original->author,
            'is_read' => (bool)$this->read_at,
            'is_saved' => (bool)$this->saved_at,
            'created_at' => $this->original->created_at,
            'updated_at' => $this->original->updated_at,
            'feed' => FeedResource::make(
                $this->whenLoaded('feed')
            ),
            'collections' => CollectionResource::collection(
                $this->whenLoaded('collections')
            ),
        ];
    }
}
