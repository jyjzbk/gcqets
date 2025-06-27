<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 获取角色和权限
        $systemAdmin = Role::where('name', 'system_admin')->first();
        $provinceAdmin = Role::where('name', 'province_admin')->first();
        $cityAdmin = Role::where('name', 'city_admin')->first();
        $districtAdmin = Role::where('name', 'district_admin')->first();
        $educationZoneAdmin = Role::where('name', 'education_zone_admin')->first();
        $principal = Role::where('name', 'principal')->first();
        $vicePrincipal = Role::where('name', 'vice_principal')->first();
        $academicDirector = Role::where('name', 'academic_director')->first();
        $teacher = Role::where('name', 'teacher')->first();
        $labAdmin = Role::where('name', 'lab_admin')->first();

        $allPermissions = Permission::all();
        $systemPermissions = Permission::where('is_system', true)->get();
        $organizationPermissions = Permission::where('module', 'organization')->get();
        $userPermissions = Permission::where('module', 'user')->get();
        $rolePermissions = Permission::where('module', 'role')->get();
        $experimentPermissions = Permission::where('module', 'experiment')->get();

        // 系统管理员 - 拥有所有权限
        if ($systemAdmin) {
            $systemAdmin->permissions()->sync($allPermissions->pluck('id'));
        }

        // 省级管理员 - 拥有除系统管理外的所有权限
        if ($provinceAdmin) {
            $provincePermissions = $allPermissions->where('is_system', false);
            $provinceAdmin->permissions()->sync($provincePermissions->pluck('id'));
        }

        // 市级管理员 - 组织、用户、角色管理权限
        if ($cityAdmin) {
            $cityPermissionIds = collect();
            $cityPermissionIds = $cityPermissionIds->merge($organizationPermissions->pluck('id'));
            $cityPermissionIds = $cityPermissionIds->merge($userPermissions->pluck('id'));
            $cityPermissionIds = $cityPermissionIds->merge($rolePermissions->pluck('id'));
            $cityAdmin->permissions()->sync($cityPermissionIds->unique());
        }

        // 区县级管理员 - 组织、用户、角色管理权限
        if ($districtAdmin) {
            $districtPermissionIds = collect();
            $districtPermissionIds = $districtPermissionIds->merge($organizationPermissions->pluck('id'));
            $districtPermissionIds = $districtPermissionIds->merge($userPermissions->pluck('id'));
            $districtPermissionIds = $districtPermissionIds->merge($rolePermissions->pluck('id'));
            $districtAdmin->permissions()->sync($districtPermissionIds->unique());
        }

        // 学区管理员 - 组织、用户、角色、实验报告权限
        if ($educationZoneAdmin) {
            $zonePermissionIds = collect();
            $zonePermissionIds = $zonePermissionIds->merge($organizationPermissions->pluck('id'));
            $zonePermissionIds = $zonePermissionIds->merge($userPermissions->pluck('id'));
            $zonePermissionIds = $zonePermissionIds->merge($rolePermissions->pluck('id'));
            $zonePermissionIds = $zonePermissionIds->merge($experimentPermissions->where('action', 'report')->pluck('id'));
            $educationZoneAdmin->permissions()->sync($zonePermissionIds->unique());
        }

        // 校长 - 学校内所有权限
        if ($principal) {
            $principalPermissionIds = collect();
            $principalPermissionIds = $principalPermissionIds->merge($organizationPermissions->where('action', '!=', 'create')->pluck('id'));
            $principalPermissionIds = $principalPermissionIds->merge($userPermissions->pluck('id'));
            $principalPermissionIds = $principalPermissionIds->merge($rolePermissions->pluck('id'));
            $principalPermissionIds = $principalPermissionIds->merge($experimentPermissions->pluck('id'));
            $principal->permissions()->sync($principalPermissionIds->unique());
        }

        // 副校长 - 部分管理权限
        if ($vicePrincipal) {
            $vicePrincipalPermissionIds = collect();
            $vicePrincipalPermissionIds = $vicePrincipalPermissionIds->merge($organizationPermissions->where('action', 'view')->pluck('id'));
            $vicePrincipalPermissionIds = $vicePrincipalPermissionIds->merge($userPermissions->whereIn('action', ['view', 'edit'])->pluck('id'));
            $vicePrincipalPermissionIds = $vicePrincipalPermissionIds->merge($experimentPermissions->pluck('id'));
            $vicePrincipal->permissions()->sync($vicePrincipalPermissionIds->unique());
        }

        // 教务主任 - 教学相关权限
        if ($academicDirector) {
            $academicPermissionIds = collect();
            $academicPermissionIds = $academicPermissionIds->merge($userPermissions->where('action', 'view')->pluck('id'));
            $academicPermissionIds = $academicPermissionIds->merge($experimentPermissions->pluck('id'));
            $academicDirector->permissions()->sync($academicPermissionIds->unique());
        }

        // 教师 - 基本查看和实验权限
        if ($teacher) {
            $teacherPermissionIds = collect();
            $teacherPermissionIds = $teacherPermissionIds->merge($experimentPermissions->where('action', 'view')->pluck('id'));
            $teacher->permissions()->sync($teacherPermissionIds->unique());
        }

        // 实验管理员 - 实验管理权限
        if ($labAdmin) {
            $labPermissionIds = collect();
            $labPermissionIds = $labPermissionIds->merge($experimentPermissions->pluck('id'));
            $labAdmin->permissions()->sync($labPermissionIds->unique());
        }
    }
}
