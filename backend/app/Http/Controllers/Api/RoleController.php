<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    /**
     * 获取角色列表
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Role::query();

        // 根据用户权限过滤数据范围
        $accessScope = $user->getDataAccessScope();
        if ($accessScope['type'] === 'specific') {
            // 只显示用户可管理级别的角色
            $manageableLevels = $user->getManageableLevels();
            $query->whereIn('level', $manageableLevels);
        } elseif ($accessScope['type'] === 'none') {
            // 如果用户没有任何权限，返回空结果
            $query->whereRaw('1 = 0');
        }
        // 如果是 'all' 类型，不添加任何过滤条件

        // 搜索条件
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 级别过滤
        if ($request->filled('level')) {
            $query->level($request->input('level'));
        }

        // 状态过滤
        if ($request->filled('status')) {
            $query->where('status', $request->boolean('status'));
        }

        // 系统角色过滤
        if ($request->filled('is_system')) {
            $query->where('is_system', $request->boolean('is_system'));
        }

        // 排序
        $sortBy = $request->input('sort_by', 'sort_order');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // 分页
        $perPage = $request->input('per_page', 15);
        $roles = $query->with(['permissions'])->paginate($perPage);

        return response()->json([
            'message' => '获取角色列表成功',
            'data' => $roles,
            'code' => 200
        ]);
    }

    /**
     * 获取单个角色详情
     */
    public function show(Role $role): JsonResponse
    {
        $user = Auth::user();
        
        // 检查用户是否有权限查看该角色
        if (!$user->canManageLevel($role->level)) {
            return response()->json([
                'message' => '无权查看该角色',
                'code' => 403
            ], 403);
        }

        $role->load(['permissions', 'users']);

        return response()->json([
            'message' => '获取角色详情成功',
            'data' => $role,
            'code' => 200
        ]);
    }

    /**
     * 创建角色
     */
    public function store(RoleRequest $request): JsonResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        // 检查用户是否有权限创建该级别的角色
        if (!$user->canManageLevel($data['level'])) {
            return response()->json([
                'message' => '无权创建该级别的角色',
                'code' => 403
            ], 403);
        }

        try {
            DB::beginTransaction();

            $role = Role::create($data);

            // 分配权限
            if (isset($data['permission_ids'])) {
                $role->syncPermissions($data['permission_ids'], $user->id);
            }

            DB::commit();

            $role->load(['permissions']);

            return response()->json([
                'message' => '创建角色成功',
                'data' => $role,
                'code' => 201
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => '创建角色失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 更新角色
     */
    public function update(RoleRequest $request, Role $role): JsonResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        // 检查用户是否有权限修改该角色
        if (!$user->canManageLevel($role->level)) {
            return response()->json([
                'message' => '无权修改该角色',
                'code' => 403
            ], 403);
        }

        // 不能修改系统角色
        if ($role->isSystemRole()) {
            return response()->json([
                'message' => '不能修改系统角色',
                'code' => 422
            ], 422);
        }

        try {
            DB::beginTransaction();

            $role->update($data);

            // 更新权限
            if (isset($data['permission_ids'])) {
                $role->syncPermissions($data['permission_ids'], $user->id);
            }

            DB::commit();

            $role->load(['permissions']);

            return response()->json([
                'message' => '更新角色成功',
                'data' => $role,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => '更新角色失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 删除角色
     */
    public function destroy(Role $role): JsonResponse
    {
        $user = Auth::user();

        // 检查用户是否有权限删除该角色
        if (!$user->canManageLevel($role->level)) {
            return response()->json([
                'message' => '无权删除该角色',
                'code' => 403
            ], 403);
        }

        // 不能删除系统角色
        if ($role->isSystemRole()) {
            return response()->json([
                'message' => '不能删除系统角色',
                'code' => 422
            ], 422);
        }

        // 检查是否有用户使用该角色
        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => '该角色下还有用户，无法删除',
                'code' => 422
            ], 422);
        }

        try {
            $role->delete();

            return response()->json([
                'message' => '删除角色成功',
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '删除角色失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 分配权限给角色
     */
    public function assignPermissions(Request $request, Role $role): JsonResponse
    {
        $user = Auth::user();
        
        $request->validate([
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id'
        ]);

        // 检查用户是否有权限管理该角色
        if (!$user->canManageLevel($role->level)) {
            return response()->json([
                'message' => '无权管理该角色',
                'code' => 403
            ], 403);
        }

        try {
            $role->syncPermissions($request->input('permission_ids'), $user->id);

            return response()->json([
                'message' => '分配权限成功',
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '分配权限失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 移除角色权限
     */
    public function removePermissions(Request $request, Role $role): JsonResponse
    {
        $user = Auth::user();
        
        $request->validate([
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id'
        ]);

        // 检查用户是否有权限管理该角色
        if (!$user->canManageLevel($role->level)) {
            return response()->json([
                'message' => '无权管理该角色',
                'code' => 403
            ], 403);
        }

        try {
            foreach ($request->input('permission_ids') as $permissionId) {
                $role->revokePermissionTo($permissionId);
            }

            return response()->json([
                'message' => '移除权限成功',
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '移除权限失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取角色权限
     */
    public function permissions(Role $role): JsonResponse
    {
        $user = Auth::user();
        
        // 检查用户是否有权限查看该角色
        if (!$user->canManageLevel($role->level)) {
            return response()->json([
                'message' => '无权查看该角色',
                'code' => 403
            ], 403);
        }

        $permissions = $role->getActivePermissions();

        return response()->json([
            'message' => '获取角色权限成功',
            'data' => $permissions,
            'code' => 200
        ]);
    }

    /**
     * 获取角色用户
     */
    public function users(Request $request, Role $role): JsonResponse
    {
        $user = Auth::user();
        
        // 检查用户是否有权限查看该角色
        if (!$user->canManageLevel($role->level)) {
            return response()->json([
                'message' => '无权查看该角色',
                'code' => 403
            ], 403);
        }

        $query = $role->users();

        // 搜索条件
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('real_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 组织过滤
        if ($request->filled('organization_id')) {
            $query->wherePivot('organization_id', $request->input('organization_id'));
        }

        $perPage = $request->input('per_page', 15);
        $users = $query->with(['organizations', 'primaryOrganization'])->paginate($perPage);

        return response()->json([
            'message' => '获取角色用户成功',
            'data' => $users,
            'code' => 200
        ]);
    }

    /**
     * 获取角色选项
     */
    public function options(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Role::active();

        // 根据用户权限过滤
        $accessScope = $user->getDataAccessScope();
        if ($accessScope['type'] === 'specific') {
            $manageableLevels = $user->getManageableLevels();
            $query->whereIn('level', $manageableLevels);
        } elseif ($accessScope['type'] === 'none') {
            // 如果用户没有任何权限，返回空结果
            $query->whereRaw('1 = 0');
        }
        // 如果是 'all' 类型，不添加任何过滤条件

        // 级别过滤
        if ($request->filled('level')) {
            $query->level($request->input('level'));
        }

        $roles = $query->select(['id', 'name', 'display_name', 'level'])
                      ->orderBy('sort_order')
                      ->get();

        return response()->json([
            'message' => '获取角色选项成功',
            'data' => $roles,
            'code' => 200
        ]);
    }
} 