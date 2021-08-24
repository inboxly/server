<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Collection
 */
class CollectionResource extends JsonResource
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
            'is_customizable' => $this->type === Collection::TYPE_CUSTOM,
        ];
    }
}
