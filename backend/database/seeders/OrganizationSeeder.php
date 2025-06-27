<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. 河北省
        $hebei = Organization::updateOrCreate(
            ['code' => 'HB001'],
            [
            'name' => '河北省',
            'code' => 'HB001',
            'type' => 'province',
            'level' => 1,
            'parent_id' => null,
            'full_path' => '1',
            'description' => '河北省教育厅',
            'contact_person' => '张省长',
            'contact_phone' => '0311-12345678',
            'address' => '河北省石家庄市长安区',
            'status' => 'active',
            'sort_order' => 1
            ]
        );

        // 2. 石家庄市
        $shijiazhuang = Organization::updateOrCreate(
            ['code' => 'SJZ001'],
            [
            'name' => '石家庄市',
            'code' => 'SJZ001',
            'type' => 'city',
            'level' => 2,
            'parent_id' => $hebei->id,
            'full_path' => $hebei->id . '/' . 2,
            'description' => '石家庄市教育局',
            'contact_person' => '李市长',
            'contact_phone' => '0311-87654321',
            'address' => '河北省石家庄市长安区中山路',
            'status' => 'active',
            'sort_order' => 1
            ]
        );

        // 3. 藁城区
        $gaocheng = Organization::updateOrCreate(
            ['code' => 'GC001'],
            [
            'name' => '藁城区',
            'code' => 'GC001',
            'type' => 'district',
            'level' => 3,
            'parent_id' => $shijiazhuang->id,
            'full_path' => $hebei->id . '/' . $shijiazhuang->id . '/' . 3,
            'description' => '藁城区教育局',
            'contact_person' => '王区长',
            'contact_phone' => '0311-88123456',
            'address' => '河北省石家庄市藁城区府前街',
            'status' => 'active',
            'sort_order' => 1
            ]
        );

        // 4. 廉州学区
        $lianzhou = Organization::updateOrCreate(
            ['code' => 'LZ001'],
            [
            'name' => '廉州学区',
            'code' => 'LZ001',
            'type' => 'education_zone',
            'level' => 4,
            'parent_id' => $gaocheng->id,
            'full_path' => $hebei->id . '/' . $shijiazhuang->id . '/' . $gaocheng->id . '/' . 4,
            'description' => '廉州学区管理委员会',
            'contact_person' => '赵主任',
            'contact_phone' => '0311-88234567',
            'address' => '河北省石家庄市藁城区廉州镇',
            'status' => 'active',
            'sort_order' => 1
            ]
        );

        // 5. 东城小学
        $dongcheng = Organization::updateOrCreate(
            ['code' => 'DC001'],
            [
            'name' => '东城小学',
            'code' => 'DC001',
            'type' => 'school',
            'level' => 5,
            'parent_id' => $lianzhou->id,
            'full_path' => $hebei->id . '/' . $shijiazhuang->id . '/' . $gaocheng->id . '/' . $lianzhou->id . '/' . 5,
            'description' => '藁城区廉州学区东城小学',
            'contact_person' => '刘校长',
            'contact_phone' => '0311-88345678',
            'address' => '河北省石家庄市藁城区廉州镇东城村',
            'status' => 'active',
            'sort_order' => 1
            ]
        );

        // 添加更多学校示例
        Organization::updateOrCreate(
            ['code' => 'XC001'],
            [
            'name' => '西城小学',
            'code' => 'XC001',
            'type' => 'school',
            'level' => 5,
            'parent_id' => $lianzhou->id,
            'full_path' => $hebei->id . '/' . $shijiazhuang->id . '/' . $gaocheng->id . '/' . $lianzhou->id . '/' . 6,
            'description' => '藁城区廉州学区西城小学',
            'contact_person' => '陈校长',
            'contact_phone' => '0311-88345679',
            'address' => '河北省石家庄市藁城区廉州镇西城村',
            'status' => 'active',
            'sort_order' => 2
            ]
        );

        Organization::updateOrCreate(
            ['code' => 'NC001'],
            [
            'name' => '南城小学',
            'code' => 'NC001',
            'type' => 'school',
            'level' => 5,
            'parent_id' => $lianzhou->id,
            'full_path' => $hebei->id . '/' . $shijiazhuang->id . '/' . $gaocheng->id . '/' . $lianzhou->id . '/' . 7,
            'description' => '藁城区廉州学区南城小学',
            'contact_person' => '孙校长',
            'contact_phone' => '0311-88345680',
            'address' => '河北省石家庄市藁城区廉州镇南城村',
            'status' => 'active',
            'sort_order' => 3
            ]
        );

        // 更新full_path
        $this->updateFullPaths();
    }

    private function updateFullPaths()
    {
        $organizations = Organization::orderBy('level')->get();
        
        foreach ($organizations as $org) {
            if ($org->parent_id) {
                $parent = Organization::find($org->parent_id);
                $org->full_path = $parent->full_path . '/' . $org->id;
                $org->save();
            } else {
                $org->full_path = (string)$org->id;
                $org->save();
            }
        }
    }
}
