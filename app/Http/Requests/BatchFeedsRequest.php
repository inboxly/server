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
        return [
            'ids' => [
                'required',
                'array',
                'min:1',
                'max:1000',
            ],
            'ids.*' => [
                Rule::exists(Feed::newModelInstance()->getTable(), 'id'),
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
        return $this->input('ids', []);
    }
}
