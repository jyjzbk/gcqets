<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PermissionRequest;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    /**
     * 获取权限列表
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Permission::query();

        // 搜索条件
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 分组过滤
        if ($request->filled('group')) {
            $query->group($request->input('group'));
        }

        // 模块过滤
        if ($request->filled('module')) {
            $query->module($request->input('module'));
        }

        // 级别过滤
        if ($request->filled('level')) {
            $query->level($request->input('level'));
        }

        // 状态过滤
        if ($request->filled('status')) {
            $query->where('status', $request->boolean('status'));
        }

        // 菜单权限过滤
        if ($request->filled('is_menu')) {
            $query->where('is_menu', $request->boolean('is_menu'));
        }

        // 系统权限过滤
        if ($request->filled('is_system')) {
            $query->where('is_system', $request->boolean('is_system'));
        }

        // 排序
        $sortBy = $request->input('sort_by', 'sort_order');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // 分页
        $perPage = $request->input('per_page', 15);
        $permissions = $query->with(['parent', 'children'])->paginate($perPage);

        return response()->json([
            'message' => '获取权限列表成功',
            'data' => $permissions,
            'code' => 200
        ]);
    }

    /**
     * 获取权限树形结构
     */
    public function tree(Request $request): JsonResponse
    {
        $parentId = $request->input('parent_id');
        $activeOnly = $request->boolean('active_only', true);

        $permissions = Permission::getTree($parentId, $activeOnly);

        return response()->json([
            'message' => '获取权限树成功',
            'data' => $permissions,
            'code' => 200
        ]);
    }

    /**
     * 获取菜单权限
     */
    public function menu(Request $request): JsonResponse
    {
        $parentId = $request->input('parent_id');
        $permissions = Permission::getMenuTree($parentId);

        return response()->json([
            'message' => '获取菜单权限成功',
            'data' => $permissions,
            'code' => 200
        ]);
    }

    /**
     * 获取单个权限详情
     */
    public function show(Permission $permission): JsonResponse
    {
        $permission->load(['parent', 'children', 'roles']);

        return response()->json([
            'message' => '获取权限详情成功',
            'data' => $permission,
            'code' => 200
        ]);
    }

    /**
     * 创建权限
     */
    public function store(PermissionRequest $request): JsonResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $data['created_by'] = $user->id;
            $permission = Permission::create($data);

            // 更新路径和层级
            $permission->updatePath();
            $permission->updateChildrenLevel();

            DB::commit();

            $permission->load(['parent', 'children']);

            return response()->json([
                'message' => '创建权限成功',
                'data' => $permission,
                'code' => 201
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => '创建权限失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 更新权限
     */
    public function update(PermissionRequest $request, Permission $permission): JsonResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        // 不能修改系统权限
        if ($permission->isSystem()) {
            return response()->json([
                'message' => '不能修改系统权限',
                'code' => 422
            ], 422);
        }

        try {
            DB::beginTransaction();

            $data['updated_by'] = $user->id;
            $permission->update($data);

            // 更新路径和层级
            $permission->updatePath();
            $permission->updateChildrenLevel();

            DB::commit();

            $permission->load(['parent', 'children']);

            return response()->json([
                'message' => '更新权限成功',
                'data' => $permission,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => '更新权限失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 删除权限
     */
    public function destroy(Permission $permission): JsonResponse
    {
        // 不能删除系统权限
        if ($permission->isSystem()) {
            return response()->json([
                'message' => '不能删除系统权限',
                'code' => 422
            ], 422);
        }

        // 检查是否有子权限
        if ($permission->children()->count() > 0) {
            return response()->json([
                'message' => '该权限下还有子权限，无法删除',
                'code' => 422
            ], 422);
        }

        // 检查是否有关联的角色
        if ($permission->roles()->count() > 0) {
            return response()->json([
                'message' => '该权限已被角色使用，无法删除',
                'code' => 422
            ], 422);
        }

        try {
            $permission->delete();

            return response()->json([
                'message' => '删除权限成功',
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '删除权限失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取权限分组
     */
    public function groups(): JsonResponse
    {
        $groups = Permission::$groupMap;

        return response()->json([
            'message' => '获取权限分组成功',
            'data' => $groups,
            'code' => 200
        ]);
    }

    /**
     * 获取权限模块
     */
    public function modules(): JsonResponse
    {
        $modules = Permission::distinct()->pluck('module')->filter()->values();

        return response()->json([
            'message' => '获取权限模块成功',
            'data' => $modules,
            'code' => 200
        ]);
    }
} 