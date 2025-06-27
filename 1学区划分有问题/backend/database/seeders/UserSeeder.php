<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 获取组织机构
        $dongcheng = Organization::where('name', '东城小学')->first();
        $xicheng = Organization::where('name', '西城小学')->first();
        $nancheng = Organization::where('name', '南城小学')->first();
        $lianzhou = Organization::where('name', '廉州学区')->first();
        $gaocheng = Organization::where('name', '藁城区')->first();

        // 1. 系统管理员
        User::updateOrCreate(
            ['username' => 'sysadmin'],
            [
            'name' => '系统管理员',
            'real_name' => '系统管理员',
            'gender' => 'male',
            'birth_date' => '1980-01-01',
            'phone' => '13800000001',
            'id_card' => '130100198001010001',
            'address' => '河北省石家庄市长安区',
            'department' => '信息中心',
            'position' => '系统管理员',
            'title' => '高级工程师',
            'hire_date' => '2020-01-01',
            'email' => 'sysadmin@gcqets.edu.cn',
            'organization_id' => $gaocheng->id,
            'employee_id' => 'SYS001',
            'user_type' => 'admin',
            'status' => 'active',
            'password' => Hash::make('123456'),
            'email_verified_at' => now()
            ]
        );

        // 2. 学区管理员
        User::updateOrCreate(
            ['username' => 'lianzhou_admin'],
            [
            'name' => '赵学区主任',
            'real_name' => '赵学区主任',
            'gender' => 'male',
            'birth_date' => '1975-05-15',
            'phone' => '13800000002',
            'id_card' => '130100197505150002',
            'address' => '河北省石家庄市藁城区廉州镇',
            'department' => '廉州学区',
            'position' => '学区主任',
            'title' => '高级教师',
            'hire_date' => '2018-09-01',
            'email' => 'zhao.admin@gcqets.edu.cn',
            'organization_id' => $lianzhou->id,
            'employee_id' => 'LZ001',
            'user_type' => 'supervisor',
            'status' => 'active',
            'password' => Hash::make('123456'),
            'email_verified_at' => now()
            ]
        );

        // 3. 东城小学校长
        User::updateOrCreate(
            ['username' => 'dongcheng_principal'],
            [
            'name' => '刘校长',
            'real_name' => '刘校长',
            'gender' => 'male',
            'birth_date' => '1970-08-20',
            'phone' => '13800000003',
            'id_card' => '130100197008200003',
            'address' => '河北省石家庄市藁城区廉州镇东城村',
            'department' => '校长室',
            'position' => '校长',
            'title' => '特级教师',
            'hire_date' => '2015-09-01',
            'email' => 'liu.principal@dongcheng.edu.cn',
            'organization_id' => $dongcheng->id,
            'employee_id' => 'DC001',
            'user_type' => 'admin',
            'status' => 'active',
            'password' => Hash::make('123456'),
            'email_verified_at' => now()
            ]
        );


    }
}
