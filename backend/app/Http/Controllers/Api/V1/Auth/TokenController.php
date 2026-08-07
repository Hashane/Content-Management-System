<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class TokenController extends Controller
{
    public function store(LoginRequest $request): JsonResponse
    {
        $user = $request->authenticate();

        $token = $user->createToken('api')->plainTextToken;

        return response()->success(['token' => $token], 'Token issued.', 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        // Resolved from the raw Authorization header directly rather than
        // relying on $request->user()->currentAccessToken():
        $token = PersonalAccessToken::findToken((string) $request->bearerToken());

        abort_unless(
            $token
                && $token->tokenable_type === $request->user()->getMorphClass()
                && $token->tokenable_id === $request->user()->getKey(),
            401,
            'No active API token to revoke.'
        );

        $token->delete();

        return response()->success(null, 'API token revoked successfully.');
    }
}
