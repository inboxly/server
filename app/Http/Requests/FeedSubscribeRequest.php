<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @method User user($guard = null)
 */
class FeedSubscribeRequest extends FormRequest
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
                'array',
                'max:100',
            ],
            'ids.*' => [
                Rule::exists(Category::newModelInstance()->getTable(), 'id')
                    ->where('user_id', $this->user()->getKey()),
            ],
        ];
    }

    /**
     * Get array of category ids
     *
     * @return array
     */
    public function categoryIds(): array
    {
        return $this->input('ids', []);
    }
}
