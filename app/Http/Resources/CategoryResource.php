<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Category
 */
class CategoryResource extends JsonResource
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
            'feeds' => SimpleFeedResource::collection(
                $this->whenLoaded('feeds')
            ),
            'is_customizable' => $this->type === Category::TYPE_CUSTOM,
        ];
    }
}
