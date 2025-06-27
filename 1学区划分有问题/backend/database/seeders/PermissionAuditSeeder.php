<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Organization;
use App\Models\PermissionAuditLog;
use App\Models\PermissionConflict;

class PermissionAuditSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createAuditLogs();
        $this->createPermissionConflicts();
    }

    /**
     * 创建权限审计日志
     */
    private function createAuditLogs(): void
    {
        $users = User::all();
        $roles = Role::all();
        $permissions = Permission::all();
        $organizations = Organization::all();

        $actions = ['grant', 'revoke', 'modify', 'inherit', 'override'];
        $targetTypes = ['user', 'role', 'organization'];

        // 创建50条审计日志记录
        for ($i = 0; $i < 50; $i++) {
            $user = $users->random();
            $role = $roles->random();
            $permission = $permissions->random();
            $organization = $organizations->random();
            $action = $actions[array_rand($actions)];
            $targetType = $targetTypes[array_rand($targetTypes)];

            $targetName = match($targetType) {
                'user' => $user->name,
                'role' => $role->display_name,
                'organization' => $organization->name,
                default => '未知'
            };

            PermissionAuditLog::create([
                'user_id' => $user->id,
                'target_user_id' => $targetType === 'user' ? $user->id : null,
                'role_id' => $targetType === 'role' ? $role->id : null,
                'permission_id' => $permission->id,
                'organization_id' => $organization->id,
                'action' => $action,
                'target_type' => $targetType,
                'target_name' => $targetName,
                'old_values' => $this->generateOldValues($action),
                'new_values' => $this->generateNewValues($action),
                'ip_address' => $this->generateRandomIP(),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'reason' => $this->generateReason($action),
                'status' => 'success',
                'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59))
            ]);
        }
    }

    /**
     * 创建权限冲突记录
     */
    private function createPermissionConflicts(): void
    {
        $users = User::all();
        $permissions = Permission::all();
        $organizations = Organization::all();

        $conflictTypes = ['role_user', 'role_role', 'inheritance', 'explicit_deny'];
        $priorities = ['high', 'medium', 'low'];
        $statuses = ['unresolved', 'resolved', 'ignored'];

        // 创建20条权限冲突记录
        for ($i = 0; $i < 20; $i++) {
            $user = $users->random();
            $permission = $permissions->random();
            $organization = $organizations->random();
            $conflictType = $conflictTypes[array_rand($conflictTypes)];
            $priority = $priorities[array_rand($priorities)];
            $status = $statuses[array_rand($statuses)];

            PermissionConflict::create([
                'user_id' => $user->id,
                'permission_id' => $permission->id,
                'organization_id' => $organization->id,
                'conflict_type' => $conflictType,
                'conflict_sources' => $this->generateConflictSources($conflictType),
                'priority' => $priority,
                'status' => $status,
                'resolution_strategy' => 'manual',
                'resolution_notes' => $status === 'resolved' ? '手动解决冲突' : null,
                'resolved_by' => $status === 'resolved' ? $users->random()->id : null,
                'resolved_at' => $status === 'resolved' ? now()->subDays(rand(0, 10)) : null,
                'created_at' => now()->subDays(rand(0, 60))->subHours(rand(0, 23))
            ]);
        }
    }

    /**
     * 生成操作描述
     */
    private function getActionDescription(string $action, string $targetType, string $targetName, string $permissionName): string
    {
        return match($action) {
            'grant' => "为{$targetType} \"{$targetName}\" 授予权限 \"{$permissionName}\"",
            'revoke' => "从{$targetType} \"{$targetName}\" 撤销权限 \"{$permissionName}\"",
            'modify' => "修改{$targetType} \"{$targetName}\" 的权限 \"{$permissionName}\"",
            'inherit' => "{$targetType} \"{$targetName}\" 继承权限 \"{$permissionName}\"",
            'override' => "覆盖{$targetType} \"{$targetName}\" 的权限 \"{$permissionName}\"",
            default => "对{$targetType} \"{$targetName}\" 执行权限操作"
        };
    }

    /**
     * 生成冲突描述
     */
    private function getConflictDescription(string $conflictType, string $userName, string $permissionName): string
    {
        return match($conflictType) {
            'role_user' => "用户 \"{$userName}\" 的角色权限与直接权限在 \"{$permissionName}\" 上存在冲突",
            'role_role' => "用户 \"{$userName}\" 的多个角色在 \"{$permissionName}\" 权限上存在冲突",
            'inheritance' => "用户 \"{$userName}\" 的权限继承在 \"{$permissionName}\" 上存在冲突",
            'explicit_deny' => "用户 \"{$userName}\" 被显式拒绝 \"{$permissionName}\" 权限",
            default => "权限冲突"
        };
    }

    /**
     * 生成状态标签
     */
    private function getStatusLabel(string $status): string
    {
        return match($status) {
            'unresolved' => '未解决',
            'resolved' => '已解决',
            'ignored' => '已忽略',
            default => '未知'
        };
    }

    /**
     * 生成旧值
     */
    private function generateOldValues(string $action): ?array
    {
        if ($action === 'modify') {
            return [
                'status' => 'inactive',
                'permissions' => ['user.view', 'organization.view']
            ];
        }
        return null;
    }

    /**
     * 生成新值
     */
    private function generateNewValues(string $action): ?array
    {
        return match($action) {
            'grant' => ['status' => 'granted'],
            'revoke' => ['status' => 'revoked'],
            'modify' => [
                'status' => 'active',
                'permissions' => ['user.view', 'user.create', 'organization.view']
            ],
            'inherit' => ['source' => 'parent_organization'],
            'override' => ['overridden' => true],
            default => null
        };
    }

    /**
     * 生成随机IP地址
     */
    private function generateRandomIP(): string
    {
        return rand(192, 223) . '.' . rand(168, 255) . '.' . rand(1, 254) . '.' . rand(1, 254);
    }

    /**
     * 生成操作原因
     */
    private function generateReason(string $action): string
    {
        $reasons = [
            'grant' => '用户职责变更，需要新增权限',
            'revoke' => '用户离职，撤销相关权限',
            'modify' => '权限调整，优化权限配置',
            'inherit' => '组织架构调整，权限自动继承',
            'override' => '特殊需求，手动覆盖权限'
        ];

        return $reasons[$action] ?? '系统操作';
    }

    /**
     * 生成冲突源数据
     */
    private function generateConflictSources(string $conflictType): array
    {
        return match($conflictType) {
            'role_user' => [
                [
                    'source_type' => 'role',
                    'source_id' => 1,
                    'source_name' => '管理员角色'
                ],
                [
                    'source_type' => 'direct',
                    'source_id' => null,
                    'source_name' => '直接分配'
                ]
            ],
            'role_role' => [
                [
                    'source_type' => 'role',
                    'source_id' => 1,
                    'source_name' => '管理员角色'
                ],
                [
                    'source_type' => 'role',
                    'source_id' => 2,
                    'source_name' => '教师角色'
                ]
            ],
            'inheritance' => [
                [
                    'source_type' => 'inheritance',
                    'source_id' => 1,
                    'source_name' => '上级组织继承'
                ]
            ],
            'explicit_deny' => [
                [
                    'source_type' => 'deny',
                    'source_id' => null,
                    'source_name' => '显式拒绝'
                ]
            ],
            default => []
        };
    }
}
