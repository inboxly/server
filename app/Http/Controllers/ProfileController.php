<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\ProfileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\ProfileResource
     */
    public function __invoke(Request $request): JsonResource
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->load(['mainCategory', 'savedCollection']);

        return ProfileResource::make($user);
    }
}
