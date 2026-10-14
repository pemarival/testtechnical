<?php

namespace App\Http\Controllers;

use App\Models\User;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('username', mb_strtolower(trim($credentials['username'])))->first();

        if (!$user || !Hash::check($credentials['password'], $user->password_hash)) {
            throw ValidationException::withMessages([
                'credentials' => ['Las credenciales no son válidas.'],
            ])->status(401);
        }

        $now = now()->utc()->timestamp;
        $expiresIn = (int) config('auth.jwt_ttl', 480) * 60;
        $jwt = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText((string) config('auth.jwt_secret'))
        );
        $issuedAt = new \DateTimeImmutable('@'.$now);
        $token = $jwt->builder()
            ->issuedBy((string) config('app.url'))
            ->relatedTo((string) $user->id)
            ->withClaim('role', $user->role)
            ->issuedAt($issuedAt)
            ->canOnlyBeUsedAfter($issuedAt)
            ->expiresAt($issuedAt->modify('+'.$expiresIn.' seconds'))
            ->getToken($jwt->signer(), $jwt->signingKey())
            ->toString();

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $expiresIn,
            'role' => $user->role,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'id' => $request->attributes->get('library_user_id'),
            'role' => $request->attributes->get('library_role'),
        ]);
    }
}
