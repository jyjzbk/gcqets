<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\PermissionTemplate;

class PermissionTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createSystemTemplates();
        $this->createRoleTemplates();
        $this->createUserTemplates();
    }

    /**
     * 创建系统权限模板
     */
    private function createSystemTemplates(): void
    {
        // 系统管理员模板
        $systemAdminPermissions = Permission::whereIn('name', [
            'system.manage',
            'organization.view',
            'organization.create',
            'organization.update',
            'organization.delete',
            'user.view',
            'user.create',
            'user.update',
            'user.delete',
            'role.view',
            'role.create',
            'role.update',
            'role.delete',
            'permission.view',
            'permission.visualization',
            'permission.matrix',
            'permission.audit',
            'permission.template.view',
            'permission.template.create',
            'permission.template.update',
            'permission.template.delete',
            'permission.template.apply'
        ])->pluck('id')->toArray();

        PermissionTemplate::create([
            'name' => 'system_admin',
            'display_name' => '系统管理员模板',
            'description' => '系统管理员拥有所有系统管理权限，包括用户管理、组织管理、权限管理等',
            'template_type' => 'role',
            'target_level' => 1,
            'permission_ids' => $systemAdminPermissions,
            'is_system' => true,
            'is_default' => true,
            'status' => 'active'
        ]);

        // 省级管理员模板
        $provinceAdminPermissions = Permission::whereIn('name', [
            'organization.view',
            'organization.create',
            'organization.update',
            'user.view',
            'user.create',
            'user.update',
            'role.view',
            'role.assign',
            'permission.view',
            'permission.visualization'
        ])->pluck('id')->toArray();

        PermissionTemplate::create([
            'name' => 'province_admin',
            'display_name' => '省级管理员模板',
            'description' => '省级管理员权限模板，可管理省内的组织机构和用户',
            'template_type' => 'role',
            'target_level' => 1,
            'permission_ids' => $provinceAdminPermissions,
            'is_system' => true,
            'is_default' => false,
            'status' => 'active'
        ]);
    }

    /**
     * 创建角色权限模板
     */
    private function createRoleTemplates(): void
    {
        // 市级管理员模板
        $cityAdminPermissions = Permission::whereIn('name', [
            'organization.view',
            'organization.create',
            'organization.update',
            'user.view',
            'user.create',
            'user.update',
            'role.view',
            'role.assign'
        ])->pluck('id')->toArray();

        PermissionTemplate::create([
            'name' => 'city_admin',
            'display_name' => '市级管理员模板',
            'description' => '市级管理员权限模板，可管理市内的组织机构和用户',
            'template_type' => 'role',
            'target_level' => 2,
            'permission_ids' => $cityAdminPermissions,
            'is_system' => false,
            'is_default' => true,
            'status' => 'active'
        ]);

        // 区县级管理员模板
        $districtAdminPermissions = Permission::whereIn('name', [
            'organization.view',
            'organization.update',
            'user.view',
            'user.create',
            'user.update',
            'role.view'
        ])->pluck('id')->toArray();

        PermissionTemplate::create([
            'name' => 'district_admin',
            'display_name' => '区县级管理员模板',
            'description' => '区县级管理员权限模板，可管理区县内的组织机构和用户',
            'template_type' => 'role',
            'target_level' => 3,
            'permission_ids' => $districtAdminPermissions,
            'is_system' => false,
            'is_default' => true,
            'status' => 'active'
        ]);

        // 学区管理员模板
        $schoolDistrictAdminPermissions = Permission::whereIn('name', [
            'organization.view',
            'user.view',
            'user.update',
            'district.manage'
        ])->pluck('id')->toArray();

        PermissionTemplate::create([
            'name' => 'school_district_admin',
            'display_name' => '学区管理员模板',
            'description' => '学区管理员权限模板，可管理学区内的学校和教师',
            'template_type' => 'role',
            'target_level' => 4,
            'permission_ids' => $schoolDistrictAdminPermissions,
            'is_system' => false,
            'is_default' => true,
            'status' => 'active'
        ]);

        // 学校管理员模板
        $schoolAdminPermissions = Permission::whereIn('name', [
            'organization.view',
            'user.view',
            'experiment.view',
            'experiment.manage',
            'experiment.report'
        ])->pluck('id')->toArray();

        PermissionTemplate::create([
            'name' => 'school_admin',
            'display_name' => '学校管理员模板',
            'description' => '学校管理员权限模板，可管理学校内的实验教学',
            'template_type' => 'role',
            'target_level' => 5,
            'permission_ids' => $schoolAdminPermissions,
            'is_system' => false,
            'is_default' => true,
            'status' => 'active'
        ]);
    }

    /**
     * 创建用户权限模板
     */
    private function createUserTemplates(): void
    {
        // 教师模板
        $teacherPermissions = Permission::whereIn('name', [
            'organization.view',
            'user.view',
            'experiment.view',
            'experiment.manage'
        ])->pluck('id')->toArray();

        PermissionTemplate::create([
            'name' => 'teacher',
            'display_name' => '教师权限模板',
            'description' => '教师权限模板，可查看和管理实验教学',
            'template_type' => 'user',
            'target_level' => 5,
            'permission_ids' => $teacherPermissions,
            'is_system' => false,
            'is_default' => false,
            'status' => 'active'
        ]);

        // 观察员模板
        $observerPermissions = Permission::whereIn('name', [
            'organization.view',
            'user.view',
            'experiment.view'
        ])->pluck('id')->toArray();

        PermissionTemplate::create([
            'name' => 'observer',
            'display_name' => '观察员权限模板',
            'description' => '观察员权限模板，只能查看相关信息',
            'template_type' => 'user',
            'target_level' => null,
            'permission_ids' => $observerPermissions,
            'is_system' => false,
            'is_default' => false,
            'status' => 'active'
        ]);

        // 审计员模板
        $auditorPermissions = Permission::whereIn('name', [
            'organization.view',
            'user.view',
            'permission.view',
            'permission.audit',
            'permission.audit.export'
        ])->pluck('id')->toArray();

        PermissionTemplate::create([
            'name' => 'auditor',
            'display_name' => '审计员权限模板',
            'description' => '审计员权限模板，可查看权限审计信息',
            'template_type' => 'user',
            'target_level' => null,
            'permission_ids' => $auditorPermissions,
            'is_system' => false,
            'is_default' => false,
            'status' => 'active'
        ]);
    }
}
