<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 系统管理权限
        Permission::updateOrCreate(
            ['name' => 'system.manage'],
            [
            'display_name' => '系统管理',
            'module' => 'system',
            'action' => 'manage',
            'description' => '系统管理权限',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3, 4, 5]),
            'scope_type' => 'all_subordinates',
            'is_system' => true,
            'status' => 'active',
            'sort_order' => 1
            ]
        );

        // 组织机构管理权限
        Permission::updateOrCreate(
            ['name' => 'organization.view'],
            [
            'display_name' => '查看组织机构',
            'module' => 'organization',
            'action' => 'view',
            'description' => '查看组织机构信息',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3, 4, 5]),
            'scope_type' => 'all_subordinates',
            'is_system' => false,
            'status' => 'active',
            'sort_order' => 2
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'organization.create'],
            [
            'display_name' => '创建组织机构',
            'module' => 'organization',
            'action' => 'create',
            'description' => '创建下级组织机构',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3, 4]),
            'scope_type' => 'direct_subordinates',
            'is_system' => false,
            'status' => 'active',
            'sort_order' => 3
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'organization.edit'],
            [
            'display_name' => '编辑组织机构',
            'module' => 'organization',
            'action' => 'edit',
            'description' => '编辑组织机构信息',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3, 4, 5]),
            'scope_type' => 'all_subordinates',
            'is_system' => false,
            'status' => 'active',
            'sort_order' => 4
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'organization.update'],
            [
            'display_name' => '更新组织机构',
            'module' => 'organization',
            'action' => 'update',
            'description' => '更新组织机构信息',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3, 4, 5]),
            'scope_type' => 'all_subordinates',
            'is_system' => false,
            'status' => 'active',
            'sort_order' => 5
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'organization.delete'],
            [
            'display_name' => '删除组织机构',
            'module' => 'organization',
            'action' => 'delete',
            'description' => '删除组织机构',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3, 4]),
            'scope_type' => 'direct_subordinates',
            'is_system' => false,
            'status' => 'active',
            'sort_order' => 6
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'organization.index'],
            [
            'display_name' => '组织机构列表',
            'module' => 'organization',
            'action' => 'index',
            'description' => '查看组织机构列表',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3, 4, 5]),
            'scope_type' => 'all_subordinates',
            'is_system' => false,
            'status' => 'active',
            'sort_order' => 7
            ]
        );

        // 用户管理权限
        Permission::updateOrCreate(
            ['name' => 'user.view'],
            [
            'display_name' => '查看用户',
            'module' => 'user',
            'action' => 'view',
            'description' => '查看用户信息',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3, 4, 5]),
            'scope_type' => 'all_subordinates',
            'is_system' => false,
            'status' => 'active',
            'sort_order' => 5
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'user.create'],
            [
            'display_name' => '创建用户',
            'module' => 'user',
            'action' => 'create',
            'description' => '创建新用户',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3, 4, 5]),
            'scope_type' => 'all_subordinates',
            'is_system' => false,
            'status' => 'active',
            'sort_order' => 6
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'user.edit'],
            [
            'display_name' => '编辑用户',
            'module' => 'user',
            'action' => 'edit',
            'description' => '编辑用户信息',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3, 4, 5]),
            'scope_type' => 'all_subordinates',
            'is_system' => false,
            'status' => 'active',
            'sort_order' => 7
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'user.update'],
            [
            'display_name' => '更新用户',
            'module' => 'user',
            'action' => 'update',
            'description' => '更新用户信息',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3, 4, 5]),
            'scope_type' => 'all_subordinates',
            'is_system' => false,
            'status' => 'active',
            'sort_order' => 8
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'user.delete'],
            [
            'display_name' => '删除用户',
            'module' => 'user',
            'action' => 'delete',
            'description' => '删除用户',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3, 4]),
            'scope_type' => 'direct_subordinates',
            'is_system' => false,
            'status' => 'active',
            'sort_order' => 9
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'user.assign-role'],
            [
            'display_name' => '分配用户角色',
            'module' => 'user',
            'action' => 'assign-role',
            'description' => '为用户分配角色',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3, 4]),
            'scope_type' => 'all_subordinates',
            'is_system' => false,
            'status' => 'active',
            'sort_order' => 10
            ]
        );

        // 角色管理权限
        Permission::updateOrCreate(
            ['name' => 'role.view'],
            [
            'display_name' => '查看角色',
            'module' => 'role',
            'action' => 'view',
            'description' => '查看角色信息',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3, 4, 5]),
            'scope_type' => 'all_subordinates',
            'is_system' => false,
            'status' => 'active',
            'sort_order' => 8
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'role.assign'],
            [
            'display_name' => '分配角色',
            'module' => 'role',
            'action' => 'assign',
            'description' => '为用户分配角色',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3, 4, 5]),
            'scope_type' => 'all_subordinates',
            'is_system' => false,
            'status' => 'active',
            'sort_order' => 11
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'role.create'],
            [
            'display_name' => '创建角色',
            'module' => 'role',
            'action' => 'create',
            'description' => '创建新角色',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3]),
            'scope_type' => 'direct_subordinates',
            'is_system' => false,
            'status' => 'active',
            'sort_order' => 12
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'role.update'],
            [
            'display_name' => '更新角色',
            'module' => 'role',
            'action' => 'update',
            'description' => '更新角色信息',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3]),
            'scope_type' => 'all_subordinates',
            'is_system' => false,
            'status' => 'active',
            'sort_order' => 13
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'role.delete'],
            [
            'display_name' => '删除角色',
            'module' => 'role',
            'action' => 'delete',
            'description' => '删除角色',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3]),
            'scope_type' => 'direct_subordinates',
            'is_system' => false,
            'status' => 'active',
            'sort_order' => 14
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'role.assign-permissions'],
            [
            'display_name' => '分配角色权限',
            'module' => 'role',
            'action' => 'assign-permissions',
            'description' => '为角色分配权限',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3]),
            'scope_type' => 'all_subordinates',
            'is_system' => false,
            'status' => 'active',
            'sort_order' => 15
            ]
        );

        // 实验教学管理权限
        Permission::updateOrCreate(
            ['name' => 'experiment.view'],
            [
            'display_name' => '查看实验',
            'module' => 'experiment',
            'action' => 'view',
            'description' => '查看实验信息',
            'min_level' => 5,
            'applicable_levels' => json_encode([5]),
            'scope_type' => 'self',
            'is_system' => false,
            'status' => 'active',
            'sort_order' => 10
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'experiment.manage'],
            [
            'display_name' => '管理实验',
            'module' => 'experiment',
            'action' => 'manage',
            'description' => '管理实验教学',
            'min_level' => 5,
            'applicable_levels' => json_encode([5]),
            'scope_type' => 'self',
            'is_system' => false,
            'status' => 'active',
            'sort_order' => 11
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'experiment.report'],
            [
            'display_name' => '实验报告',
            'module' => 'experiment',
            'action' => 'report',
            'description' => '查看和管理实验报告',
            'min_level' => 4,
            'applicable_levels' => json_encode([4, 5]),
            'scope_type' => 'all_subordinates',
            'is_system' => false,
            'status' => 'active',
            'sort_order' => 12
            ]
        );

        // 权限设置管理权限
        Permission::updateOrCreate(
            ['name' => 'permission.view'],
            [
            'display_name' => '查看权限设置',
            'module' => 'permission',
            'action' => 'view',
            'description' => '查看权限设置界面',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3, 4]),
            'scope_type' => 'all_subordinates',
            'is_system' => true,
            'status' => 'active',
            'sort_order' => 20
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'permission.visualization'],
            [
            'display_name' => '权限可视化',
            'module' => 'permission',
            'action' => 'visualization',
            'description' => '查看权限继承关系和可视化界面',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3, 4]),
            'scope_type' => 'all_subordinates',
            'is_system' => true,
            'status' => 'active',
            'sort_order' => 21
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'permission.matrix'],
            [
            'display_name' => '权限矩阵管理',
            'module' => 'permission',
            'action' => 'matrix',
            'description' => '管理权限矩阵和批量权限操作',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3, 4]),
            'scope_type' => 'all_subordinates',
            'is_system' => true,
            'status' => 'active',
            'sort_order' => 22
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'permission.audit'],
            [
            'display_name' => '权限审计',
            'module' => 'permission',
            'action' => 'audit',
            'description' => '查看权限审计日志和历史记录',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3, 4]),
            'scope_type' => 'all_subordinates',
            'is_system' => true,
            'status' => 'active',
            'sort_order' => 23
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'permission.template.view'],
            [
            'display_name' => '查看权限模板',
            'module' => 'permission',
            'action' => 'template.view',
            'description' => '查看权限模板列表',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3, 4]),
            'scope_type' => 'all_subordinates',
            'is_system' => true,
            'status' => 'active',
            'sort_order' => 24
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'permission.template.create'],
            [
            'display_name' => '创建权限模板',
            'module' => 'permission',
            'action' => 'template.create',
            'description' => '创建新的权限模板',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3]),
            'scope_type' => 'all_subordinates',
            'is_system' => true,
            'status' => 'active',
            'sort_order' => 25
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'permission.template.update'],
            [
            'display_name' => '编辑权限模板',
            'module' => 'permission',
            'action' => 'template.update',
            'description' => '编辑权限模板',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3]),
            'scope_type' => 'all_subordinates',
            'is_system' => true,
            'status' => 'active',
            'sort_order' => 26
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'permission.template.delete'],
            [
            'display_name' => '删除权限模板',
            'module' => 'permission',
            'action' => 'template.delete',
            'description' => '删除权限模板',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3]),
            'scope_type' => 'all_subordinates',
            'is_system' => true,
            'status' => 'active',
            'sort_order' => 27
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'permission.template.apply'],
            [
            'display_name' => '应用权限模板',
            'module' => 'permission',
            'action' => 'template.apply',
            'description' => '将权限模板应用到角色或用户',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3, 4]),
            'scope_type' => 'all_subordinates',
            'is_system' => true,
            'status' => 'active',
            'sort_order' => 28
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'permission.conflict.resolve'],
            [
            'display_name' => '解决权限冲突',
            'module' => 'permission',
            'action' => 'conflict.resolve',
            'description' => '解决和处理权限冲突',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3]),
            'scope_type' => 'all_subordinates',
            'is_system' => true,
            'status' => 'active',
            'sort_order' => 29
            ]
        );

        Permission::updateOrCreate(
            ['name' => 'permission.audit.export'],
            [
            'display_name' => '导出审计日志',
            'module' => 'permission',
            'action' => 'audit.export',
            'description' => '导出权限审计日志',
            'min_level' => 1,
            'applicable_levels' => json_encode([1, 2, 3]),
            'scope_type' => 'all_subordinates',
            'is_system' => true,
            'status' => 'active',
            'sort_order' => 30
            ]
        );
    }
}
