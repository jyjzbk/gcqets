<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\PermissionInheritance;

class PermissionInheritanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 获取所有组织和权限
        $organizations = Organization::all();
        $permissions = Permission::all();

        // 为每个组织创建权限继承关系
        foreach ($organizations as $organization) {
            if ($organization->parent_id) {
                $parent = $organization->parent;
                
                // 为每个权限创建继承关系
                foreach ($permissions as $permission) {
                    // 检查权限是否适用于当前组织级别
                    $applicableLevels = json_decode($permission->applicable_levels, true) ?? [];
                    
                    if (in_array($organization->level, $applicableLevels)) {
                        // 创建权限继承关系
                        PermissionInheritance::updateOrCreate([
                            'parent_organization_id' => $parent->id,
                            'child_organization_id' => $organization->id,
                            'permission_id' => $permission->id,
                        ], [
                            'inheritance_type' => 'direct',
                            'inheritance_path' => $this->buildInheritancePath($organization),
                            'is_overridden' => false,
                            'status' => 'active'
                        ]);
                    }
                }
            }
        }

        // 创建一些特殊的权限继承关系示例
        $this->createSpecialInheritanceExamples();
    }

    /**
     * 构建权限继承路径
     */
    private function buildInheritancePath(Organization $organization): array
    {
        $path = [];
        $current = $organization;
        
        while ($current) {
            array_unshift($path, $current->name);
            $current = $current->parent;
        }
        
        return $path;
    }

    /**
     * 创建特殊的权限继承关系示例
     */
    private function createSpecialInheritanceExamples(): void
    {
        // 获取一些示例组织
        $province = Organization::where('level', 1)->first();
        $city = Organization::where('level', 2)->first();
        $district = Organization::where('level', 3)->first();
        $schoolDistrict = Organization::where('level', 4)->first();
        $school = Organization::where('level', 5)->first();

        // 获取一些示例权限
        $systemPermission = Permission::where('name', 'system.manage')->first();
        $orgPermission = Permission::where('name', 'organization.view')->first();
        $userPermission = Permission::where('name', 'user.view')->first();

        if ($province && $city && $systemPermission) {
            // 创建省级到市级的系统管理权限继承
            PermissionInheritance::updateOrCreate([
                'parent_organization_id' => $province->id,
                'child_organization_id' => $city->id,
                'permission_id' => $systemPermission->id,
            ], [
                'inheritance_type' => 'direct',
                'inheritance_path' => [$province->name, $city->name],
                'is_overridden' => false,
                'status' => 'active'
            ]);
        }

        if ($city && $district && $orgPermission) {
            // 创建市级到区县级的组织管理权限继承
            PermissionInheritance::updateOrCreate([
                'parent_organization_id' => $city->id,
                'child_organization_id' => $district->id,
                'permission_id' => $orgPermission->id,
            ], [
                'inheritance_type' => 'direct',
                'inheritance_path' => [$province->name ?? '省级', $city->name, $district->name],
                'is_overridden' => false,
                'status' => 'active'
            ]);
        }

        if ($district && $schoolDistrict && $userPermission) {
            // 创建区县级到学区级的用户管理权限继承
            PermissionInheritance::updateOrCreate([
                'parent_organization_id' => $district->id,
                'child_organization_id' => $schoolDistrict->id,
                'permission_id' => $userPermission->id,
            ], [
                'inheritance_type' => 'direct',
                'inheritance_path' => [$province->name ?? '省级', $city->name ?? '市级', $district->name, $schoolDistrict->name],
                'is_overridden' => false,
                'status' => 'active'
            ]);
        }

        // 创建一个被覆盖的权限继承示例
        if ($schoolDistrict && $school && $userPermission) {
            PermissionInheritance::updateOrCreate([
                'parent_organization_id' => $schoolDistrict->id,
                'child_organization_id' => $school->id,
                'permission_id' => $userPermission->id,
            ], [
                'inheritance_type' => 'direct',
                'inheritance_path' => [$province->name ?? '省级', $city->name ?? '市级', $district->name ?? '区县级', $schoolDistrict->name, $school->name],
                'is_overridden' => true,
                'overridden_by' => 1, // 假设是管理员覆盖的
                'overridden_at' => now(),
                'status' => 'active'
            ]);
        }
    }
}
