<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;

class OrganizationSeeder extends Seeder
{
    public function run()
    {
        // 创建省级组织
        $province = Organization::create([
            'name' => '浙江省教育厅',
            'code' => 'ZJ-EDU',
            'type' => 'province',
            'level' => 1,
            'status' => 'active'
        ]);

        // 创建市级组织
        $city = Organization::create([
            'name' => '杭州市教育局',
            'code' => 'HZ-EDU',
            'type' => 'city',
            'level' => 2,
            'parent_id' => $province->id,
            'status' => 'active'
        ]);

        // 创建区县级组织
        $district = Organization::create([
            'name' => '西湖区教育局',
            'code' => 'XH-EDU',
            'type' => 'district',
            'level' => 3,
            'parent_id' => $city->id,
            'status' => 'active'
        ]);

        // 创建学区
        $schoolDistrict = Organization::create([
            'name' => '西湖第一教育学区',
            'code' => 'XH1-EDU',
            'type' => 'school_district',
            'level' => 4,
            'parent_id' => $district->id,
            'status' => 'active'
        ]);

        // 创建学校
        Organization::create([
            'name' => '杭州第一中学',
            'code' => 'HZ1-SCH',
            'type' => 'school',
            'level' => 5,
            'parent_id' => $schoolDistrict->id,
            'status' => 'active'
        ]);
    }
}