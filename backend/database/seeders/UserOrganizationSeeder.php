<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;

class UserOrganizationSeeder extends Seeder
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

        // 获取组织机构
        $gaocheng = Organization::where('name', '藁城区')->first();
        $lianzhou = Organization::where('name', '廉州学区')->first();
        $dongcheng = Organization::where('name', '东城小学')->first();

        // 创建用户组织关联
        $userOrganizations = [];

        if ($sysAdmin && $gaocheng) {
            $userOrganizations[] = [
                'user_id' => $sysAdmin->id,
                'organization_id' => $gaocheng->id,
                'is_primary' => true,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        if ($lianzhouAdmin && $lianzhou) {
            $userOrganizations[] = [
                'user_id' => $lianzhouAdmin->id,
                'organization_id' => $lianzhou->id,
                'is_primary' => true,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        if ($dongchengPrincipal && $dongcheng) {
            $userOrganizations[] = [
                'user_id' => $dongchengPrincipal->id,
                'organization_id' => $dongcheng->id,
                'is_primary' => true,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        if (!empty($userOrganizations)) {
            DB::table('user_organizations')->insert($userOrganizations);
        }
    }
}
