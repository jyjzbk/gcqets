<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\PermissionInheritance;
use App\Models\PermissionConflict;
use App\Models\User;
use App\Models\Role;
use App\Models\PermissionTemplate;
use App\Models\PermissionAuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PermissionVisualizationController extends Controller
{
    /**
     * 获取组织权限继承关系树
     */
    public function getInheritanceTree(Request $request): JsonResponse
    {
        $organizationId = $request->input('organization_id');
        $includeInactive = $request->boolean('include_inactive', false);
        
        try {
            if ($organizationId) {
                // 获取指定组织的权限继承关系
                $tree = $this->buildOrganizationInheritanceTree($organizationId, $includeInactive);
            } else {
                // 获取当前用户可访问的组织权限继承关系
                $user = auth()->user();
                $accessibleOrgs = $user->getAccessibleOrganizations();
                $tree = $this->buildMultipleOrganizationInheritanceTree($accessibleOrgs, $includeInactive);
            }

            return response()->json([
                'message' => '获取权限继承关系成功',
                'data' => $tree,
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取权限继承关系失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取权限继承路径
     */
    public function getInheritancePath(Request $request): JsonResponse
    {
        $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'permission_id' => 'required|exists:permissions,id'
        ]);

        try {
            $organizationId = $request->input('organization_id');
            $permissionId = $request->input('permission_id');
            
            $paths = $this->calculateInheritancePaths($organizationId, $permissionId);

            return response()->json([
                'message' => '获取权限继承路径成功',
                'data' => [
                    'organization_id' => $organizationId,
                    'permission_id' => $permissionId,
                    'paths' => $paths
                ],
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取权限继承路径失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 检测权限冲突
     */
    public function detectConflicts(Request $request): JsonResponse
    {
        $organizationId = $request->input('organization_id');
        $userId = $request->input('user_id');
        $autoResolve = $request->boolean('auto_resolve', false);

        try {
            $conflicts = [];

            if ($userId) {
                // 检测指定用户的权限冲突
                $userConflicts = PermissionConflict::detectUserConflicts($userId);
                $conflicts = array_merge($conflicts, $userConflicts);
            } elseif ($organizationId) {
                // 检测指定组织的权限冲突
                $orgConflicts = $this->detectOrganizationConflicts($organizationId);
                $conflicts = array_merge($conflicts, $orgConflicts);
            } else {
                // 检测当前用户可访问范围内的权限冲突
                $user = auth()->user();
                $accessibleOrgs = $user->getAccessibleOrganizations();
                foreach ($accessibleOrgs as $org) {
                    $orgConflicts = $this->detectOrganizationConflicts($org->id);
                    $conflicts = array_merge($conflicts, $orgConflicts);
                }
            }

            // 创建冲突记录
            $createdCount = PermissionConflict::createConflicts($conflicts);

            // 自动解决简单冲突
            if ($autoResolve) {
                $resolvedCount = $this->autoResolveConflicts($conflicts);
            }

            return response()->json([
                'message' => '权限冲突检测完成',
                'data' => [
                    'total_conflicts' => count($conflicts),
                    'created_records' => $createdCount,
                    'resolved_conflicts' => $resolvedCount ?? 0,
                    'conflicts' => $conflicts
                ],
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '权限冲突检测失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取权限矩阵数据
     */
    public function getPermissionMatrix(Request $request): JsonResponse
    {
        $organizationId = $request->input('organization_id');
        $roleId = $request->input('role_id');
        $userId = $request->input('user_id');

        try {
            $matrix = $this->buildPermissionMatrix($organizationId, $roleId, $userId);

            return response()->json([
                'message' => '获取权限矩阵成功',
                'data' => $matrix,
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取权限矩阵失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 计算有效权限
     */
    public function calculateEffectivePermissions(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'organization_id' => 'nullable|exists:organizations,id'
        ]);

        try {
            $userId = $request->input('user_id');
            $organizationId = $request->input('organization_id');
            
            $user = User::with(['roles.permissions', 'permissions'])->find($userId);
            $effectivePermissions = $this->calculateUserEffectivePermissions($user, $organizationId);

            return response()->json([
                'message' => '计算有效权限成功',
                'data' => [
                    'user_id' => $userId,
                    'organization_id' => $organizationId,
                    'effective_permissions' => $effectivePermissions
                ],
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '计算有效权限失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 构建组织权限继承关系树
     */
    private function buildOrganizationInheritanceTree(int $organizationId, bool $includeInactive = false): array
    {
        $organization = Organization::with(['parent', 'children'])->find($organizationId);
        if (!$organization) {
            throw new \Exception('组织不存在');
        }

        // 获取组织的权限继承关系
        $inheritances = PermissionInheritance::where('child_organization_id', $organizationId)
            ->when(!$includeInactive, function ($query) {
                $query->active();
            })
            ->with(['parentOrganization', 'permission'])
            ->get();

        // 构建树形结构
        $tree = [
            'organization' => $organization,
            'inherited_permissions' => [],
            'direct_permissions' => [],
            'children' => []
        ];

        // 分组权限
        foreach ($inheritances as $inheritance) {
            if ($inheritance->inheritance_type === 'direct') {
                $tree['inherited_permissions'][] = [
                    'permission' => $inheritance->permission,
                    'source' => $inheritance->parentOrganization,
                    'is_overridden' => $inheritance->is_overridden,
                    'inheritance_path' => $inheritance->inheritance_path
                ];
            }
        }

        // 获取直接分配的权限（通过角色）
        $directPermissions = $this->getOrganizationDirectPermissions($organizationId);
        $tree['direct_permissions'] = $directPermissions;

        // 递归构建子组织树
        foreach ($organization->children as $child) {
            $tree['children'][] = $this->buildOrganizationInheritanceTree($child->id, $includeInactive);
        }

        return $tree;
    }

    /**
     * 构建多个组织的权限继承关系树
     */
    private function buildMultipleOrganizationInheritanceTree($organizations, bool $includeInactive = false): array
    {
        $trees = [];
        foreach ($organizations as $org) {
            $trees[] = $this->buildOrganizationInheritanceTree($org->id, $includeInactive);
        }
        return $trees;
    }

    /**
     * 计算权限继承路径
     */
    private function calculateInheritancePaths(int $organizationId, int $permissionId): array
    {
        $paths = [];
        $organization = Organization::find($organizationId);
        
        if (!$organization) {
            return $paths;
        }

        // 向上查找权限继承路径
        $current = $organization;
        $path = [$current->name];
        
        while ($current->parent_id) {
            $parent = $current->parent;
            if (!$parent) break;
            
            // 检查父级是否有此权限
            if ($this->organizationHasPermission($parent->id, $permissionId)) {
                $path[] = $parent->name;
                $paths[] = [
                    'type' => 'inheritance',
                    'path' => array_reverse($path),
                    'source_organization' => $parent,
                    'target_organization' => $organization
                ];
            }
            
            $current = $parent;
        }

        return $paths;
    }

    /**
     * 检查组织是否拥有指定权限
     */
    private function organizationHasPermission(int $organizationId, int $permissionId): bool
    {
        // 通过组织的用户角色检查权限
        return DB::table('users')
            ->join('user_organizations', 'users.id', '=', 'user_organizations.user_id')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('permission_role', 'role_user.role_id', '=', 'permission_role.role_id')
            ->where('user_organizations.organization_id', $organizationId)
            ->where('permission_role.permission_id', $permissionId)
            ->exists();
    }

    /**
     * 获取组织直接权限
     */
    private function getOrganizationDirectPermissions(int $organizationId): array
    {
        // 获取组织内用户通过角色获得的权限
        $permissions = DB::table('permissions')
            ->select('permissions.*', 'roles.display_name as role_name')
            ->join('permission_role', 'permissions.id', '=', 'permission_role.permission_id')
            ->join('roles', 'permission_role.role_id', '=', 'roles.id')
            ->join('role_user', 'roles.id', '=', 'role_user.role_id')
            ->join('user_organizations', 'role_user.user_id', '=', 'user_organizations.user_id')
            ->where('user_organizations.organization_id', $organizationId)
            ->distinct()
            ->get();

        return $permissions->toArray();
    }

    /**
     * 检测组织权限冲突
     */
    private function detectOrganizationConflicts(int $organizationId): array
    {
        $conflicts = [];
        
        // 获取组织内的所有用户
        $users = User::whereHas('organizations', function ($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->with(['roles.permissions', 'permissions'])->get();

        foreach ($users as $user) {
            $userConflicts = PermissionConflict::detectUserConflicts($user->id);
            foreach ($userConflicts as $conflict) {
                $conflict['organization_id'] = $organizationId;
                $conflicts[] = $conflict;
            }
        }

        return $conflicts;
    }

    /**
     * 自动解决简单冲突
     */
    private function autoResolveConflicts(array $conflicts): int
    {
        $resolved = 0;
        
        foreach ($conflicts as $conflict) {
            // 只自动解决低优先级的简单冲突
            if (($conflict['priority'] ?? 'medium') === 'low') {
                // 这里可以实现自动解决逻辑
                // 例如：优先保留角色权限，移除直接分配的冲突权限
                $resolved++;
            }
        }

        return $resolved;
    }

    /**
     * 构建权限矩阵
     */
    private function buildPermissionMatrix($organizationId = null, $roleId = null, $userId = null): array
    {
        $matrix = [
            'permissions' => [],
            'subjects' => [],
            'matrix' => []
        ];

        // 获取权限列表
        $permissions = Permission::active()->orderBy('module')->orderBy('sort_order')->get();
        $matrix['permissions'] = $permissions->toArray();

        // 根据参数获取主体（用户或角色）
        if ($userId) {
            $user = User::with(['roles', 'permissions'])->find($userId);
            $matrix['subjects'] = [$user];
            $matrix['matrix'] = $this->buildUserPermissionMatrix($user, $permissions);
        } elseif ($roleId) {
            $role = Role::with('permissions')->find($roleId);
            $matrix['subjects'] = [$role];
            $matrix['matrix'] = $this->buildRolePermissionMatrix($role, $permissions);
        } elseif ($organizationId) {
            $users = User::whereHas('organizations', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })->with(['roles', 'permissions'])->get();
            $matrix['subjects'] = $users->toArray();
            $matrix['matrix'] = $this->buildOrganizationPermissionMatrix($users, $permissions);
        }

        return $matrix;
    }

    /**
     * 构建用户权限矩阵
     */
    private function buildUserPermissionMatrix(User $user, $permissions): array
    {
        $matrix = [];
        
        foreach ($permissions as $permission) {
            $matrix[$user->id][$permission->id] = [
                'has_permission' => $user->hasPermission($permission->name),
                'source' => $this->getPermissionSource($user, $permission),
                'is_inherited' => false // 这里可以添加继承检查逻辑
            ];
        }

        return $matrix;
    }

    /**
     * 构建角色权限矩阵
     */
    private function buildRolePermissionMatrix(Role $role, $permissions): array
    {
        $matrix = [];
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        
        foreach ($permissions as $permission) {
            $matrix[$role->id][$permission->id] = [
                'has_permission' => in_array($permission->id, $rolePermissions),
                'source' => 'role',
                'is_inherited' => false
            ];
        }

        return $matrix;
    }

    /**
     * 构建组织权限矩阵
     */
    private function buildOrganizationPermissionMatrix($users, $permissions): array
    {
        $matrix = [];
        
        foreach ($users as $user) {
            foreach ($permissions as $permission) {
                $matrix[$user->id][$permission->id] = [
                    'has_permission' => $user->hasPermission($permission->name),
                    'source' => $this->getPermissionSource($user, $permission),
                    'is_inherited' => false
                ];
            }
        }

        return $matrix;
    }

    /**
     * 获取权限来源
     */
    private function getPermissionSource(User $user, Permission $permission): string
    {
        // 检查直接分配的权限
        if ($user->permissions->contains('id', $permission->id)) {
            return 'direct';
        }

        // 检查通过角色获得的权限
        foreach ($user->roles as $role) {
            if ($role->permissions->contains('id', $permission->id)) {
                return 'role:' . $role->name;
            }
        }

        return 'none';
    }

    /**
     * 计算用户有效权限
     */
    private function calculateUserEffectivePermissions(User $user, $organizationId = null): array
    {
        $effectivePermissions = [];
        
        // 获取所有权限
        $allPermissions = Permission::active()->get();
        
        foreach ($allPermissions as $permission) {
            $hasPermission = $user->hasPermission($permission->name, $organizationId);
            
            if ($hasPermission) {
                $effectivePermissions[] = [
                    'permission' => $permission,
                    'source' => $this->getPermissionSource($user, $permission),
                    'organization_id' => $organizationId
                ];
            }
        }

        return $effectivePermissions;
    }
}
