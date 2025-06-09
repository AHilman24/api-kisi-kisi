<?php

namespace App\Http\Middleware;

use App\Models\Administrator;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Administrator::where('username',Auth::user()->username)->exists();
        if (!$admin) {
            return response()->json([
                "status"=> "insufficient_permissions",
                "message"=> "Access forbidden"
            ],403);
        }
        return $next($request);
    }
}
