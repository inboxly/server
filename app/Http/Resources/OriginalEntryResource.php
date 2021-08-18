<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\OriginalEntry
 */
class OriginalEntryResource extends JsonResource
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
            'name' => $this->name,
            'summary' => $this->summary,
            'content' => $this->content,
            'url' => $this->url,
            'image' => $this->image,
            'author' => $this->author,
            'is_read' => false,
            'is_saved' => false,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'feed' => $this->whenLoaded('originalFeed',
                OriginalFeedResource::make($this->originalFeed->withoutRelations())->only(['name', 'author'])
            ),
            'collections' => [],
        ];
    }
}
