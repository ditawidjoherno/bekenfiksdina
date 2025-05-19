<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo($request)
{
    if (! $request->expectsJson()) {
        // Untuk API kita tidak redirect, cukup kirim error 401
        abort(401, 'Unauthorized.');
    }
}

}
