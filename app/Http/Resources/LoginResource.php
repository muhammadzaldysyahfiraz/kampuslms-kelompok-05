<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class LoginResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'token' => $this['token'],
            'user' => new UserResource($this['user']),
        ];
    }
}
