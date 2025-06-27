<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 系统管理员角色
        Role::updateOrCreate(
            ['name' => 'system_admin'],
            [
                'display_name' => '系统管理员',
                    'description' => '系统最高管理员，拥有所有权限',
                    'role_type' => 'system',
                    'level' => 1,
                    'applicable_org_types' => json_encode(['province', 'city', 'district', 'education_zone', 'school']),
                    'status' => 'active',
                    'sort_order' => 1,
                    'is_default' => false
            ]
        );

        // 省级管理员
        Role::updateOrCreate(
            ['name' => 'province_admin'],
            [
                'display_name' => '省级管理员',
                'description' => '省级教育管理员，管理省内所有教育机构',
                'role_type' => 'system',
                'level' => 1,
                'applicable_org_types' => json_encode(['province']),
                'status' => 'active',
                'sort_order' => 2,
                'is_default' => false
            ]
        );

        // 市级管理员
        Role::updateOrCreate(
            ['name' => 'city_admin'],
            [
                'display_name' => '市级管理员',
                'description' => '市级教育管理员，管理市内所有教育机构',
                'role_type' => 'system',
                'level' => 2,
                'applicable_org_types' => json_encode(['city']),
                'status' => 'active',
                'sort_order' => 3,
                'is_default' => false
            ]
        );

        // 区县级管理员
        Role::updateOrCreate(
            ['name' => 'district_admin'],
            [
                'display_name' => '区县级管理员',
                'description' => '区县级教育管理员，管理区县内所有教育机构',
                'role_type' => 'system',
                'level' => 3,
                'applicable_org_types' => json_encode(['district']),
                'status' => 'active',
                'sort_order' => 4,
                'is_default' => false
            ]
        );

        // 学区管理员
        Role::updateOrCreate(
            ['name' => 'education_zone_admin'],
            [
                'display_name' => '学区管理员',
                'description' => '学区管理员，管理学区内所有学校',
                'role_type' => 'system',
                'level' => 4,
                'applicable_org_types' => json_encode(['education_zone']),
                'status' => 'active',
                'sort_order' => 5,
                'is_default' => false
            ]
        );

        // 校长
        Role::updateOrCreate(
            ['name' => 'principal'],
            [
                'display_name' => '校长',
                'description' => '学校校长，管理学校所有事务',
                'role_type' => 'system',
                'level' => 5,
                'applicable_org_types' => json_encode(['school']),
                'status' => 'active',
                'sort_order' => 6,
                'is_default' => false
            ]
        );

        // 副校长
        Role::updateOrCreate(
            ['name' => 'vice_principal'],
            [
                'display_name' => '副校长',
                'description' => '学校副校长，协助校长管理学校事务',
                'role_type' => 'system',
                'level' => 5,
                'applicable_org_types' => json_encode(['school']),
                'status' => 'active',
                'sort_order' => 7,
                'is_default' => false
            ]
        );

        // 教务主任
        Role::updateOrCreate(
            ['name' => 'academic_director'],
            [
                'display_name' => '教务主任',
                'description' => '教务主任，负责学校教学管理',
                'role_type' => 'custom',
                'level' => 5,
                'applicable_org_types' => json_encode(['school']),
                'status' => 'active',
                'sort_order' => 8,
                'is_default' => false
            ]
        );

        // 教师
        Role::updateOrCreate(
            ['name' => 'teacher'],
            [
                'display_name' => '教师',
                'description' => '普通教师，负责教学工作',
                'role_type' => 'custom',
                'level' => 5,
                'applicable_org_types' => json_encode(['school']),
                'status' => 'active',
                'sort_order' => 9,
                'is_default' => true
            ]
        );

        // 实验管理员
        Role::updateOrCreate(
            ['name' => 'lab_admin'],
            [
                'display_name' => '实验管理员',
                'description' => '实验室管理员，负责实验教学管理',
                'role_type' => 'custom',
                'level' => 5,
                'applicable_org_types' => json_encode(['school']),
                'status' => 'active',
                'sort_order' => 10,
                'is_default' => false
            ]
        );
    }
}
