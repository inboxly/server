<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\OriginalFeed
 */
class OriginalFeedResource extends JsonResource
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
            'original_id' => $this->id,
            'title' => $this->title,
            'custom_title' => $this->title,
            'description' => $this->description,
            'link' => $this->link,
            'image' => $this->image,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'categories' => [],
            'entries' => OriginalEntryResource::collection(
                $this->whenLoaded('originalEntries')
            ),
        ];
    }
}
