<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * 获取用户列表
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = User::query();

        // 根据用户权限过滤数据范围
        $accessScope = $user->getDataAccessScope();
        if ($accessScope['type'] === 'specific') {
            $query->whereHas('organizations', function ($q) use ($accessScope) {
                $q->whereIn('organizations.id', $accessScope['organizations']);
            });
        } elseif ($accessScope['type'] === 'none') {
            // 如果用户没有任何权限，返回空结果
            $query->whereRaw('1 = 0');
        }
        // 如果是 'all' 类型，不添加任何过滤条件

        // 搜索条件
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('real_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // 组织过滤
        if ($request->filled('organization_id')) {
            $query->whereHas('organizations', function ($q) use ($request) {
                $q->where('organizations.id', $request->input('organization_id'));
            });
        }

        // 角色过滤
        if ($request->filled('role_id')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('roles.id', $request->input('role_id'));
            });
        }

        // 状态过滤
        if ($request->filled('status')) {
            $query->where('status', $request->boolean('status'));
        }

        // 排序
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // 分页
        $perPage = $request->input('per_page', 15);
        $users = $query->with(['organizations', 'roles', 'primaryOrganization'])->paginate($perPage);

        return response()->json([
            'message' => '获取用户列表成功',
            'data' => $users,
            'code' => 200
        ]);
    }

    /**
     * 获取单个用户详情
     */
    public function show(User $user): JsonResponse
    {
        $currentUser = Auth::user();
        
        // 检查用户是否有权限访问该用户信息
        if (!$currentUser->canAccessOrganization($user->primary_organization_id)) {
            return response()->json([
                'message' => '无权访问该用户信息',
                'code' => 403
            ], 403);
        }

        $user->load(['organizations', 'roles', 'permissions', 'primaryOrganization']);

        return response()->json([
            'message' => '获取用户详情成功',
            'data' => $user,
            'code' => 200
        ]);
    }

    /**
     * 创建用户
     */
    public function store(UserRequest $request): JsonResponse
    {
        $currentUser = Auth::user();
        $data = $request->validated();

        // 检查用户是否有权限在指定组织下创建用户
        if (isset($data['primary_organization_id'])) {
            if (!$currentUser->canAccessOrganization($data['primary_organization_id'])) {
                return response()->json([
                    'message' => '无权在指定组织下创建用户',
                    'code' => 403
                ], 403);
            }
        }

        try {
            DB::beginTransaction();

            // 加密密码
            $data['password'] = Hash::make($data['password']);

            $user = User::create($data);

            // 关联组织
            if (isset($data['organization_ids'])) {
                $organizationData = [];
                foreach ($data['organization_ids'] as $orgId) {
                    $organizationData[$orgId] = [
                        'is_primary' => $orgId == $data['primary_organization_id'],
                        'status' => true
                    ];
                }
                $user->organizations()->attach($organizationData);
            }

            // 分配角色
            if (isset($data['role_ids'])) {
                $roleData = [];
                foreach ($data['role_ids'] as $roleId) {
                    $roleData[$roleId] = [
                        'organization_id' => $data['primary_organization_id'],
                        'assigned_by' => $currentUser->id,
                        'assigned_at' => now(),
                        'status' => true
                    ];
                }
                $user->roles()->attach($roleData);
            }

            DB::commit();

            $user->load(['organizations', 'roles', 'primaryOrganization']);

            return response()->json([
                'message' => '创建用户成功',
                'data' => $user,
                'code' => 201
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => '创建用户失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 更新用户
     */
    public function update(UserRequest $request, User $user): JsonResponse
    {
        $currentUser = Auth::user();
        $data = $request->validated();

        // 检查用户是否有权限修改该用户
        if (!$currentUser->canAccessOrganization($user->primary_organization_id)) {
            return response()->json([
                'message' => '无权修改该用户',
                'code' => 403
            ], 403);
        }

        try {
            DB::beginTransaction();

            // 如果更新了密码，需要加密
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user->update($data);

            // 更新组织关联
            if (isset($data['organization_ids'])) {
                $organizationData = [];
                foreach ($data['organization_ids'] as $orgId) {
                    $organizationData[$orgId] = [
                        'is_primary' => $orgId == $data['primary_organization_id'],
                        'status' => true
                    ];
                }
                $user->organizations()->sync($organizationData);
            }

            // 更新角色关联
            if (isset($data['role_ids'])) {
                $roleData = [];
                foreach ($data['role_ids'] as $roleId) {
                    $roleData[$roleId] = [
                        'organization_id' => $data['primary_organization_id'],
                        'assigned_by' => $currentUser->id,
                        'assigned_at' => now(),
                        'status' => true
                    ];
                }
                $user->roles()->sync($roleData);
            }

            DB::commit();

            $user->load(['organizations', 'roles', 'primaryOrganization']);

            return response()->json([
                'message' => '更新用户成功',
                'data' => $user,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => '更新用户失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 删除用户
     */
    public function destroy(User $user): JsonResponse
    {
        $currentUser = Auth::user();

        // 检查用户是否有权限删除该用户
        if (!$currentUser->canAccessOrganization($user->primary_organization_id)) {
            return response()->json([
                'message' => '无权删除该用户',
                'code' => 403
            ], 403);
        }

        // 不能删除自己
        if ($user->id === $currentUser->id) {
            return response()->json([
                'message' => '不能删除自己的账户',
                'code' => 422
            ], 422);
        }

        try {
            $user->delete();

            return response()->json([
                'message' => '删除用户成功',
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '删除用户失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 分配角色给用户
     */
    public function assignRole(Request $request, User $user): JsonResponse
    {
        $currentUser = Auth::user();
        
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'organization_id' => 'nullable|exists:organizations,id'
        ]);

        $roleId = $request->input('role_id');
        $organizationId = $request->input('organization_id', $user->primary_organization_id);

        // 检查权限
        if (!$currentUser->canAccessOrganization($organizationId)) {
            return response()->json([
                'message' => '无权在该组织下分配角色',
                'code' => 403
            ], 403);
        }

        try {
            $user->assignRole($roleId, $organizationId, $currentUser->id);

            return response()->json([
                'message' => '分配角色成功',
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '分配角色失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 移除用户角色
     */
    public function removeRole(Request $request, User $user): JsonResponse
    {
        $currentUser = Auth::user();
        
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'organization_id' => 'nullable|exists:organizations,id'
        ]);

        $roleId = $request->input('role_id');
        $organizationId = $request->input('organization_id', $user->primary_organization_id);

        // 检查权限
        if (!$currentUser->canAccessOrganization($organizationId)) {
            return response()->json([
                'message' => '无权在该组织下移除角色',
                'code' => 403
            ], 403);
        }

        try {
            $user->removeRole($roleId, $organizationId);

            return response()->json([
                'message' => '移除角色成功',
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '移除角色失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 直接分配权限给用户
     */
    public function givePermission(Request $request, User $user): JsonResponse
    {
        $currentUser = Auth::user();
        
        $request->validate([
            'permission_id' => 'required|exists:permissions,id',
            'organization_id' => 'nullable|exists:organizations,id'
        ]);

        $permissionId = $request->input('permission_id');
        $organizationId = $request->input('organization_id', $user->primary_organization_id);

        // 检查权限
        if (!$currentUser->canAccessOrganization($organizationId)) {
            return response()->json([
                'message' => '无权在该组织下分配权限',
                'code' => 403
            ], 403);
        }

        try {
            $user->givePermission($permissionId, $organizationId, $currentUser->id);

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
     * 移除用户直接权限
     */
    public function revokePermission(Request $request, User $user): JsonResponse
    {
        $currentUser = Auth::user();
        
        $request->validate([
            'permission_id' => 'required|exists:permissions,id',
            'organization_id' => 'nullable|exists:organizations,id'
        ]);

        $permissionId = $request->input('permission_id');
        $organizationId = $request->input('organization_id', $user->primary_organization_id);

        // 检查权限
        if (!$currentUser->canAccessOrganization($organizationId)) {
            return response()->json([
                'message' => '无权在该组织下移除权限',
                'code' => 403
            ], 403);
        }

        try {
            $user->revokePermission($permissionId, $organizationId);

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
     * 获取用户权限
     */
    public function permissions(Request $request, User $user): JsonResponse
    {
        $currentUser = Auth::user();
        $organizationId = $request->input('organization_id', $user->primary_organization_id);

        // 检查权限
        if (!$currentUser->canAccessOrganization($organizationId)) {
            return response()->json([
                'message' => '无权查看该用户权限',
                'code' => 403
            ], 403);
        }

        $permissions = $user->getAllPermissions($organizationId);

        return response()->json([
            'message' => '获取用户权限成功',
            'data' => $permissions,
            'code' => 200
        ]);
    }

    /**
     * 获取用户角色
     */
    public function roles(Request $request, User $user): JsonResponse
    {
        $currentUser = Auth::user();
        $organizationId = $request->input('organization_id', $user->primary_organization_id);

        // 检查权限
        if (!$currentUser->canAccessOrganization($organizationId)) {
            return response()->json([
                'message' => '无权查看该用户角色',
                'code' => 403
            ], 403);
        }

        $roles = $user->rolesInOrganization($organizationId)->get();

        return response()->json([
            'message' => '获取用户角色成功',
            'data' => $roles,
            'code' => 200
        ]);
    }

    /**
     * 修改密码
     */
    public function changePassword(Request $request, User $user): JsonResponse
    {
        $currentUser = Auth::user();
        
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
            'new_password_confirmation' => 'required|string'
        ]);

        // 只能修改自己的密码，除非有特殊权限
        if ($user->id !== $currentUser->id && !$currentUser->hasPermission('user.reset-password')) {
            return response()->json([
                'message' => '只能修改自己的密码',
                'code' => 403
            ], 403);
        }

        // 验证当前密码
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json([
                'message' => '当前密码错误',
                'code' => 422
            ], 422);
        }

        try {
            $user->update([
                'password' => Hash::make($request->input('new_password'))
            ]);

            return response()->json([
                'message' => '修改密码成功',
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '修改密码失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 重置密码
     */
    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $currentUser = Auth::user();
        
        $request->validate([
            'new_password' => 'required|string|min:6'
        ]);

        // 检查重置密码权限
        if (!$currentUser->hasPermission('user.reset-password')) {
            return response()->json([
                'message' => '无权重置用户密码',
                'code' => 403
            ], 403);
        }

        try {
            $user->update([
                'password' => Hash::make($request->input('new_password'))
            ]);

            return response()->json([
                'message' => '重置密码成功',
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '重置密码失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }
} 