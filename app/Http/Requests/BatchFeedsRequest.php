<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Feed;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @method User user($guard = null)
 */
class BatchFeedsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $table = Feed::newModelInstance()->getTable();

        return [
            'ids' => [
                'required',
                'array',
                'min:1',
                'max:1000',
            ],
            'ids.*' => [
                Rule::exists($table, 'original_feed_id')->where('user_id', $this->user()->getKey()),
            ],
        ];
    }

    /**
     * Get array of feed ids
     *
     * @return array
     */
    public function ids(): array
    {
        return Feed::query()
            ->where('user_id', $this->user()->getKey())
            ->whereIn('original_feed_id', $this->input('ids', []))
            ->pluck('id')
            ->toArray();
    }
}
