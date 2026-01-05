<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$permissions
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $user = $request->user();

        // Check if user has any of the required permissions
        foreach ($permissions as $permission) {
            // Support pipe-separated permissions (permission1|permission2)
            $permissionList = explode('|', $permission);
            
            foreach ($permissionList as $perm) {
                if ($user->hasPermission(trim($perm))) {
                    return $next($request);
                }
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Unauthorized. You do not have the required permission.'
        ], 403);
    }
}
