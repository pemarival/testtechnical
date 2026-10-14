<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
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
            $claims = JWT::decode($matches[1], new Key((string) config('auth.jwt_secret'), 'HS256'));
        } catch (\Throwable) {
            return $this->error('UNAUTHENTICATED', 'Se requiere un token válido.', 401);
        }

        $request->attributes->set('library_user_id', (string) ($claims->sub ?? ''));
        $request->attributes->set('library_role', (string) ($claims->role ?? ''));
        return $next($request);
    }

    private function error(string $code, string $message, int $status): Response
    {
        return response()->json(['code' => $code, 'message' => $message, 'details' => []], $status);
    }
}
