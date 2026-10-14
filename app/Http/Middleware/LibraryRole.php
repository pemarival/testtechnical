<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LibraryRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if ($request->attributes->get('library_role') !== $role) {
            return response()->json([
                'code' => 'FORBIDDEN_ROLE',
                'message' => 'No tiene permisos para realizar esta operación.',
                'details' => [],
            ], 403);
        }

        return $next($request);
    }
}
