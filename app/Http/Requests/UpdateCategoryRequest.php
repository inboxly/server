<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** @method User user($guard = null) */
class UpdateCategoryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => [
                'min:1',
                'max:20',
            ],
            'is_default' => [
                'boolean',
                Rule::in([true]),
            ]
        ];
    }
}
