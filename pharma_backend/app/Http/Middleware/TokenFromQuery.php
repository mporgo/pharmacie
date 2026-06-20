<?php
// app/Http/Middleware/TokenFromQuery.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TokenFromQuery
{
    public function handle(Request $request, Closure $next)
    {
        // Si token passé en query string → l'injecter dans le header
        if ($request->query('token') && !$request->bearerToken()) {
            $request->headers->set(
                'Authorization',
                'Bearer ' . $request->query('token')
            );
        }

        return $next($request);
    }
}
