<?php

namespace App\Http\Middleware;

use Closure;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LibraryAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = (string) $request->header('Authorization');
        if (!preg_match('/^Bearer\\s+(\\S+)$/i', $header, $matches)) {
            return $this->error('UNAUTHENTICATED', 'Se requiere un token válido.', 401);
        }

        try {
            $jwt = Configuration::forSymmetricSigner(
                new Sha256(),
                InMemory::plainText((string) config('auth.jwt_secret'))
            );
            $token = $jwt->parser()->parse($matches[1]);
            $jwt->validator()->assert(
                $token,
                new SignedWith($jwt->signer(), $jwt->verificationKey()),
                new StrictValidAt(SystemClock::fromSystemTimezone())
            );
        } catch (\Throwable) {
            return $this->error('UNAUTHENTICATED', 'Se requiere un token válido.', 401);
        }

        $request->attributes->set('library_user_id', $token->claims()->get('sub', ''));
        $request->attributes->set('library_role', $token->claims()->get('role', ''));
        return $next($request);
    }

    private function error(string $code, string $message, int $status): Response
    {
        return response()->json(['code' => $code, 'message' => $message, 'details' => []], $status);
    }
}
