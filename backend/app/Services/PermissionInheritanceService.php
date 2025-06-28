<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use App\Models\Permission;
use App\Models\PermissionAuditLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PermissionInheritanceService
{
    /**
     * 计算用户的有效权限（包括继承权限）
     */
    public function calculateUserEffectivePermissions(User $user, ?int $organizationId = null): Collection
    {
        $permissions = collect();
        
        // 1. 直接分配的权限
        $directPermissions = $this->getUserDirectPermissions($user, $organizationId);
        $permissions = $permissions->merge($directPermissions);
        
        // 2. 角色权限
        $rolePermissions = $this->getUserRolePermissions($user, $organizationId);
        $permissions = $permissions->merge($rolePermissions);
        
        // 3. 组织继承权限
        if ($organizationId) {
            $inheritedPermissions = $this->getOrganizationInheritedPermissions($organizationId);
            $permissions = $permissions->merge($inheritedPermissions);
        }
        
        // 去重并返回
        return $permissions->unique('id');
    }

    /**
     * 获取用户直接分配的权限
     */
    public function getUserDirectPermissions(User $user, ?int $organizationId = null): Collection
    {
        $query = $user->permissions()
            ->wherePivot('source', 'direct')
            ->wherePivot('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
            
        if ($organizationId) {
            $query->wherePivot('organization_id', $organizationId);
        }
        
        return $query->get();
    }

    /**
     * 获取用户角色权限
     */
    public function getUserRolePermissions(User $user, ?int $organizationId = null): Collection
    {
        $permissions = collect();
        
        $roles = $user->roles()
            ->when($organizationId, function ($query) use ($organizationId) {
                $query->wherePivot('organization_id', $organizationId);
            })
            ->where('status', 'active')
            ->get();
        
        foreach ($roles as $role) {
            $rolePermissions = $role->permissions()
                ->wherePivot('access_type', 'allow')
                ->get();
            $permissions = $permissions->merge($rolePermissions);
        }
        
        return $permissions->unique('id');
    }

    /**
     * 获取组织继承的权限
     */
    public function getOrganizationInheritedPermissions(int $organizationId): Collection
    {
        $organization = Organization::find($organizationId);
        if (!$organization) {
            return collect();
        }
        
        $permissions = collect();
        $current = $organization->parent;
        
        // 向上遍历组织层级，收集继承的权限
        while ($current) {
            $orgPermissions = $current->permissions()
                ->wherePivot('access_type', 'allow')
                ->get();
            $permissions = $permissions->merge($orgPermissions);
            $current = $current->parent;
        }
        
        return $permissions->unique('id');
    }

    /**
     * 检查权限冲突
     */
    public function checkPermissionConflicts(User $user, ?int $organizationId = null): array
    {
        $conflicts = [];
        
        // 获取用户的所有权限来源
        $directPermissions = $this->getUserDirectPermissions($user, $organizationId);
        $rolePermissions = $this->getUserRolePermissions($user, $organizationId);
        $inheritedPermissions = $this->getOrganizationInheritedPermissions($organizationId);
        
        // 检查是否有拒绝权限与允许权限冲突
        foreach ($directPermissions as $permission) {
            $pivot = $permission->pivot;
            if ($pivot->access_type === 'deny') {
                // 检查是否有其他来源给予了该权限
                if ($rolePermissions->contains('id', $permission->id) || 
                    $inheritedPermissions->contains('id', $permission->id)) {
                    $conflicts[] = [
                        'permission_id' => $permission->id,
                        'permission_name' => $permission->name,
                        'conflict_type' => 'deny_vs_allow',
                        'description' => "权限 {$permission->display_name} 被直接拒绝，但通过角色或继承获得"
                    ];
                }
            }
        }
        
        return $conflicts;
    }

    /**
     * 应用权限继承规则
     */
    public function applyInheritanceRules(int $organizationId): void
    {
        $organization = Organization::find($organizationId);
        if (!$organization) {
            return;
        }
        
        DB::beginTransaction();
        try {
            // 获取父组织的权限
            $parentPermissions = $this->getOrganizationInheritedPermissions($organizationId);
            
            // 获取当前组织的所有用户
            $users = $organization->users;
            
            foreach ($users as $user) {
                foreach ($parentPermissions as $permission) {
                    // 检查用户是否已经有该权限
                    $existingPermission = $user->permissions()
                        ->where('permission_id', $permission->id)
                        ->wherePivot('organization_id', $organizationId)
                        ->first();
                    
                    if (!$existingPermission) {
                        // 添加继承权限
                        $user->permissions()->attach($permission->id, [
                            'organization_id' => $organizationId,
                            'granted_by' => null,
                            'granted_at' => now(),
                            'status' => 'active',
                            'source' => 'inherited',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        
                        // 记录审计日志
                        PermissionAuditLog::create([
                            'user_id' => $user->id,
                            'permission_id' => $permission->id,
                            'organization_id' => $organizationId,
                            'action' => 'inherited',
                            'old_value' => null,
                            'new_value' => json_encode(['source' => 'inherited']),
                            'reason' => '权限继承',
                            'performed_by' => null,
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->userAgent()
                        ]);
                    }
                }
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('权限继承应用失败: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 获取组织的权限继承路径
     */
    public function getInheritancePath(int $organizationId): array
    {
        $organization = Organization::find($organizationId);
        if (!$organization) {
            return [];
        }
        
        $path = [];
        $current = $organization;
        
        while ($current) {
            $permissions = $current->permissions()->count();
            $users = $current->users()->count();
            
            $path[] = [
                'id' => $current->id,
                'name' => $current->name,
                'type' => $current->type,
                'level' => $current->level,
                'permissions_count' => $permissions,
                'users_count' => $users,
                'direct_permissions' => $current->permissions()
                    ->wherePivot('access_type', 'allow')
                    ->get()
                    ->map(function ($p) {
                        return [
                            'id' => $p->id,
                            'name' => $p->name,
                            'display_name' => $p->display_name
                        ];
                    })
            ];
            
            $current = $current->parent;
        }
        
        return array_reverse($path);
    }

    /**
     * 清理过期的继承权限
     */
    public function cleanupExpiredInheritedPermissions(): int
    {
        $count = DB::table('user_permissions')
            ->where('source', 'inherited')
            ->where('expires_at', '<=', now())
            ->delete();
            
        Log::info("清理了 {$count} 个过期的继承权限");
        
        return $count;
    }

    /**
     * 重新计算组织的所有继承权限
     */
    public function recalculateOrganizationInheritance(int $organizationId): void
    {
        DB::beginTransaction();
        try {
            // 删除现有的继承权限
            DB::table('user_permissions')
                ->where('organization_id', $organizationId)
                ->where('source', 'inherited')
                ->delete();
            
            // 重新应用继承规则
            $this->applyInheritanceRules($organizationId);
            
            // 递归处理子组织
            $organization = Organization::find($organizationId);
            if ($organization && $organization->children) {
                foreach ($organization->children as $child) {
                    $this->recalculateOrganizationInheritance($child->id);
                }
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('重新计算组织继承权限失败: ' . $e->getMessage());
            throw $e;
        }
    }
}
