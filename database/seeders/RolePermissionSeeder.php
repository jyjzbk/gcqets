<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // 创建权限
        $permissions = [
            // 组织机构权限
            ['name' => 'organization.view', 'description' => '查看组织机构'],
            ['name' => 'organization.create', 'description' => '创建组织机构'],
            ['name' => 'organization.edit', 'description' => '编辑组织机构'],
            ['name' => 'organization.delete', 'description' => '删除组织机构'],
            // 用户管理权限
            ['name' => 'user.view', 'description' => '查看用户'],
            ['name' => 'user.create', 'description' => '创建用户'],
            ['name' => 'user.edit', 'description' => '编辑用户'],
            ['name' => 'user.delete', 'description' => '删除用户'],
            // 角色管理权限
            ['name' => 'role.view', 'description' => '查看角色'],
            ['name' => 'role.create', 'description' => '创建角色'],
            ['name' => 'role.edit', 'description' => '编辑角色'],
            ['name' => 'role.delete', 'description' => '删除角色']
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // 创建角色并分配权限
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => '超级管理员',
                'permissions' => ['*'] // 所有权限
            ],
            [
                'name' => 'province_admin',
                'display_name' => '省级管理员',
                'permissions' => [
                    'organization.view', 'organization.create', 'organization.edit',
                    'user.view', 'user.create', 'user.edit',
                    'role.view'
                ]
            ],
            [
                'name' => 'city_admin',
                'display_name' => '市级管理员',
                'permissions' => [
                    'organization.view', 'organization.edit',
                    'user.view', 'user.create',
                    'role.view'
                ]
            ],
            [
                'name' => 'school_admin',
                'display_name' => '学校管理员',
                'permissions' => [
                    'organization.view',
                    'user.view',
                    'role.view'
                ]
            ]
        ];

        foreach ($roles as $roleData) {
            $role = Role::create([
                'name' => $roleData['name'],
                'display_name' => $roleData['display_name']
            ]);

            if ($roleData['permissions'][0] === '*') {
                $role->permissions()->attach(Permission::all());
            } else {
                $role->permissions()->attach(
                    Permission::whereIn('name', $roleData['permissions'])->get()
                );
            }
        }
    }
}