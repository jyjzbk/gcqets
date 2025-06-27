<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission, ?string $guard = null): Response
    {
        $user = Auth::guard($guard)->user();

        if (!$user) {
            return response()->json([
                'message' => '未认证的用户',
                'code' => 401
            ], 401);
        }

        // 获取当前请求的组织ID（从URL参数或请求体中）
        $organizationId = $this->getOrganizationIdFromRequest($request);
        
        // 检查用户是否有指定权限
        if (!$user->hasPermission($permission, $organizationId)) {
            return response()->json([
                'message' => '权限不足',
                'code' => 403,
                'permission' => $permission,
                'organization_id' => $organizationId
            ], 403);
        }

        return $next($request);
    }

    /**
     * 从请求中获取组织ID
     */
    private function getOrganizationIdFromRequest(Request $request): ?int
    {
        // 从URL参数中获取
        if ($request->route('organization')) {
            return $request->route('organization');
        }

        // 从请求体中获取
        if ($request->has('organization_id')) {
            return $request->input('organization_id');
        }

        // 从查询参数中获取
        if ($request->has('organization_id')) {
            return $request->query('organization_id');
        }

        // 从用户的主要组织中获取
        $user = Auth::user();
        if ($user && $user->primaryOrganization) {
            return $user->primaryOrganization->id;
        }

        return null;
    }
} 