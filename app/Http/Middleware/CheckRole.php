<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $user = $request->user();

        // Check if user has any of the required roles
        foreach ($roles as $role) {
            // Support pipe-separated roles (role1|role2)
            $roleList = explode('|', $role);
            
            foreach ($roleList as $r) {
                if ($user->hasRole(trim($r))) {
                    return $next($request);
                }
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Unauthorized. You do not have the required role.'
        ], 403);
    }
}
