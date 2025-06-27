<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 获取用户
        $sysAdmin = User::where('username', 'sysadmin')->first();
        $lianzhouAdmin = User::where('username', 'lianzhou_admin')->first();
        $dongchengPrincipal = User::where('username', 'dongcheng_principal')->first();

        // 获取角色
        $systemAdminRole = Role::where('name', 'system_admin')->first();
        $educationZoneAdminRole = Role::where('name', 'education_zone_admin')->first();
        $principalRole = Role::where('name', 'principal')->first();

        // 获取组织机构
        $gaocheng = Organization::where('name', '藁城区')->first();
        $lianzhou = Organization::where('name', '廉州学区')->first();
        $dongcheng = Organization::where('name', '东城小学')->first();

        // 分配角色
        if ($sysAdmin && $systemAdminRole && $gaocheng) {
            DB::table('role_user')->updateOrInsert(
                [
                    'user_id' => $sysAdmin->id,
                    'role_id' => $systemAdminRole->id,
                    'organization_id' => $gaocheng->id
                ],
                [
                    'scope_type' => 'all_subordinates',
                    'effective_date' => '2020-01-01',
                    'status' => 'active',
                    'created_by' => $sysAdmin->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        if ($lianzhouAdmin && $educationZoneAdminRole && $lianzhou) {
            DB::table('role_user')->updateOrInsert(
                [
                    'user_id' => $lianzhouAdmin->id,
                    'role_id' => $educationZoneAdminRole->id,
                    'organization_id' => $lianzhou->id
                ],
                [
                    'scope_type' => 'all_subordinates',
                    'effective_date' => '2018-09-01',
                    'status' => 'active',
                    'created_by' => $sysAdmin->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        if ($dongchengPrincipal && $principalRole && $dongcheng) {
            DB::table('role_user')->updateOrInsert(
                [
                    'user_id' => $dongchengPrincipal->id,
                    'role_id' => $principalRole->id,
                    'organization_id' => $dongcheng->id
                ],
                [
                    'scope_type' => 'current_org',
                    'effective_date' => '2015-09-01',
                    'status' => 'active',
                    'created_by' => $lianzhouAdmin->id ?? $sysAdmin->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }


    }
}
