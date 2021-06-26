<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @method User user($guard = null)
 */
class ExploreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'query' => [
                'required',
                'string',
                'min:1',
                'max:100',
            ],
        ];
    }

    /**
     * Get query string for exploring
     *
     * @return string
     */
    public function exploreQuery(): string
    {
        return $this->input('query');
    }
}
