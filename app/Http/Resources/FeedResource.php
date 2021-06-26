<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Feed
 */
class FeedResource extends JsonResource
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
            'id' => $this->original_feed_id,
            'title' => $this->original->title,
            'custom_title' => $this->title,
            'description' => $this->original->description,
            'link' => $this->original->link,
            'image' => $this->original->image,
            'created_at' => $this->created_at,
            'updated_at' => $this->original->updated_at,
            'categories' => CategoryResource::collection(
                $this->whenLoaded('categories')
            ),
        ];
    }
}
