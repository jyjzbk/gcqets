<?php

require_once 'backend/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;
use App\Models\Role;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;

try {
    // 创建Laravel应用实例
    $app = require_once 'backend/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    echo "=== 检查和修复角色管理数据 ===\n\n";

    // 1. 检查组织数据
    $orgCount = Organization::count();
    echo "组织机构数量: {$orgCount}\n";

    // 2. 检查角色数据
    $roleCount = Role::count();
    echo "角色数量: {$roleCount}\n";

    // 3. 检查用户数据
    $userCount = User::count();
    echo "用户数量: {$userCount}\n";

    // 4. 检查系统管理员用户
    $sysAdmin = User::where('username', 'sysadmin')->first();
    if (!$sysAdmin) {
        echo "\n创建系统管理员用户...\n";
        
        // 获取或创建默认组织
        $defaultOrg = Organization::first();
        if (!$defaultOrg) {
            $defaultOrg = Organization::create([
                'name' => '系统管理',
                'code' => 'SYS',
                'type' => 'system',
                'level' => 1,
                'status' => true
            ]);
        }

        $sysAdmin = User::create([
            'username' => 'sysadmin',
            'real_name' => '系统管理员',
            'email' => 'admin@system.com',
            'password' => Hash::make('123456'),
            'status' => true,
            'organization_id' => $defaultOrg->id,
            'employee_id' => 'SYS001'
        ]);
        echo "系统管理员用户创建成功\n";
    } else {
        echo "系统管理员用户已存在: {$sysAdmin->username}\n";
    }

    // 5. 检查系统管理员角色
    $adminRole = Role::where('name', 'system_admin')->first();
    if (!$adminRole) {
        echo "\n创建系统管理员角色...\n";
        $adminRole = Role::create([
            'name' => 'system_admin',
            'display_name' => '系统管理员',
            'description' => '系统管理员角色',
            'level' => 1,
            'status' => 'active',
            'role_type' => 'system',
            'sort_order' => 1
        ]);
        echo "系统管理员角色创建成功\n";
    } else {
        echo "系统管理员角色已存在: {$adminRole->display_name}\n";
    }

    // 6. 为系统管理员分配角色
    if (!$sysAdmin->roles()->where('role_id', $adminRole->id)->exists()) {
        echo "\n为系统管理员分配角色...\n";
        $sysAdmin->roles()->attach($adminRole->id, [
            'organization_id' => $sysAdmin->organization_id,
            'status' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "角色分配成功\n";
    } else {
        echo "系统管理员已有角色\n";
    }

    // 7. 创建测试角色（如果不存在）
    $testRoles = [
        ['name' => 'org_admin', 'display_name' => '组织管理员', 'level' => 2],
        ['name' => 'dept_admin', 'display_name' => '部门管理员', 'level' => 3],
        ['name' => 'normal_user', 'display_name' => '普通用户', 'level' => 4],
    ];

    foreach ($testRoles as $roleData) {
        $role = Role::where('name', $roleData['name'])->first();
        if (!$role) {
            Role::create([
                'name' => $roleData['name'],
                'display_name' => $roleData['display_name'],
                'description' => $roleData['display_name'] . '角色',
                'level' => $roleData['level'],
                'status' => 'active',
                'role_type' => 'custom',
                'sort_order' => $roleData['level']
            ]);
            echo "创建角色: {$roleData['display_name']}\n";
        }
    }

    echo "\n=== 数据检查完成 ===\n";
    echo "现在可以使用以下账户登录:\n";
    echo "用户名: sysadmin\n";
    echo "密码: 123456\n";
    echo "\n登录后即可正常使用角色管理功能。\n";

} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . "\n";
    echo "行号: " . $e->getLine() . "\n";
}
