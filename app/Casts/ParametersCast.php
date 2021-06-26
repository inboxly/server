<?php

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Inboxly\Receiver\Contracts\Parameters;
use Inboxly\Receiver\Managers\FetcherManager;

class ParametersCast implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * Transform the attribute from the underlying model values.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return \Inboxly\Receiver\Contracts\Parameters|null
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function get($model, string $key, $value, array $attributes): ?Parameters
    {
        /**
         * @var FetcherManager $fetcherManager
         * todo: Use DI in __constructor() when it become possible:
         * https://github.com/laravel/framework/blob/014443/src/Illuminate/Database/Eloquent/Concerns/HasAttributes.php#L1301
         */
        $fetcherManager = app()->make(FetcherManager::class);

        $fetcher = $fetcherManager->getFetcher($attributes['fetcher_key']);

        return $fetcher && isset($attributes[$key])
            ? $fetcher->makeParameters(json_decode($attributes[$key], true))
            : null;
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return array
     */
    public function set($model, string $key, $value, array $attributes): array
    {
        return [$key => json_encode($value)];
    }

    /**
     * Serialize the attribute when converting the model to an array.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return array
     */
    public function serialize($model, string $key, $value, array $attributes): array
    {
        return $value->getArrayCopy();
    }
}
