<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EquipmentCategory;
use App\Models\Equipment;
use App\Models\MaterialCategory;
use App\Models\Material;
use App\Models\User;
use App\Models\Organization;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 获取测试用户和组织
        $sysAdmin = User::where('username', 'sysadmin')->first();
        $dongchengSchool = Organization::where('name', '东城小学')->first();
        
        if (!$sysAdmin || !$dongchengSchool) {
            $this->command->error('请先运行用户和组织数据填充');
            return;
        }

        // 创建设备分类
        $this->createEquipmentCategories($sysAdmin, $dongchengSchool);
        
        // 创建设备
        $this->createEquipment($sysAdmin, $dongchengSchool);
        
        // 创建材料分类
        $this->createMaterialCategories($sysAdmin, $dongchengSchool);
        
        // 创建材料
        $this->createMaterials($sysAdmin, $dongchengSchool);

        $this->command->info('设备物料数据填充完成');
    }

    private function createEquipmentCategories($user, $organization)
    {
        $categories = [
            [
                'name' => '物理实验设备',
                'code' => 'PHYS_EQ',
                'description' => '物理学科实验设备',
                'subject' => 'physics',
                'grade_range' => 'grade7-12',
                'parent_id' => null,
            ],
            [
                'name' => '化学实验设备',
                'code' => 'CHEM_EQ',
                'description' => '化学学科实验设备',
                'subject' => 'chemistry',
                'grade_range' => 'grade7-12',
                'parent_id' => null,
            ],
            [
                'name' => '生物实验设备',
                'code' => 'BIO_EQ',
                'description' => '生物学科实验设备',
                'subject' => 'biology',
                'grade_range' => 'grade7-12',
                'parent_id' => null,
            ],
            [
                'name' => '通用实验设备',
                'code' => 'GEN_EQ',
                'description' => '通用实验设备',
                'subject' => 'general',
                'grade_range' => 'all',
                'parent_id' => null,
            ]
        ];

        foreach ($categories as $index => $categoryData) {
            $category = EquipmentCategory::create([
                'name' => $categoryData['name'],
                'code' => $categoryData['code'],
                'description' => $categoryData['description'],
                'subject' => $categoryData['subject'],
                'grade_range' => $categoryData['grade_range'],
                'parent_id' => $categoryData['parent_id'],
                'sort_order' => $index + 1,
                'status' => 'active',
                'organization_id' => $organization->id,
                'created_by' => $user->id,
            ]);

            // 创建子分类
            if ($categoryData['code'] === 'PHYS_EQ') {
                EquipmentCategory::create([
                    'name' => '光学设备',
                    'code' => 'PHYS_OPTICS',
                    'description' => '光学实验设备',
                    'subject' => 'physics',
                    'grade_range' => 'grade7-12',
                    'parent_id' => $category->id,
                    'sort_order' => 1,
                    'status' => 'active',
                    'organization_id' => $organization->id,
                    'created_by' => $user->id,
                ]);

                EquipmentCategory::create([
                    'name' => '力学设备',
                    'code' => 'PHYS_MECHANICS',
                    'description' => '力学实验设备',
                    'subject' => 'physics',
                    'grade_range' => 'grade7-12',
                    'parent_id' => $category->id,
                    'sort_order' => 2,
                    'status' => 'active',
                    'organization_id' => $organization->id,
                    'created_by' => $user->id,
                ]);
            }
        }
    }

    private function createEquipment($user, $organization)
    {
        $physicsCategory = EquipmentCategory::where('code', 'PHYS_EQ')->first();
        $opticsCategory = EquipmentCategory::where('code', 'PHYS_OPTICS')->first();
        $mechanicsCategory = EquipmentCategory::where('code', 'PHYS_MECHANICS')->first();
        $chemistryCategory = EquipmentCategory::where('code', 'CHEM_EQ')->first();

        $equipment = [
            [
                'name' => '数字显微镜',
                'code' => 'EQ_MICROSCOPE_001',
                'model' => 'DM-2000X',
                'brand' => '奥林巴斯',
                'description' => '高倍数字显微镜，适用于生物观察',
                'category_id' => $opticsCategory->id,
                'serial_number' => 'OLY2024001',
                'purchase_price' => 15000.00,
                'purchase_date' => '2024-01-15',
                'supplier' => '科学仪器有限公司',
                'warranty_date' => '2027-01-15',
                'status' => 'available',
                'location' => '物理实验室A',
                'usage_notes' => '使用前请检查镜头清洁度',
            ],
            [
                'name' => '电子天平',
                'code' => 'EQ_BALANCE_001',
                'model' => 'FA2004N',
                'brand' => '上海精科',
                'description' => '精密电子天平，精度0.1mg',
                'category_id' => $chemistryCategory->id,
                'serial_number' => 'SJ2024001',
                'purchase_price' => 8000.00,
                'purchase_date' => '2024-02-20',
                'supplier' => '实验设备公司',
                'warranty_date' => '2027-02-20',
                'status' => 'available',
                'location' => '化学实验室B',
                'usage_notes' => '称量前需预热30分钟',
            ],
            [
                'name' => '力学实验台',
                'code' => 'EQ_MECHANICS_001',
                'model' => 'LX-2024',
                'brand' => '教学设备厂',
                'description' => '多功能力学实验台',
                'category_id' => $mechanicsCategory->id,
                'serial_number' => 'JX2024001',
                'purchase_price' => 5000.00,
                'purchase_date' => '2024-03-10',
                'supplier' => '教育装备公司',
                'warranty_date' => '2026-03-10',
                'status' => 'available',
                'location' => '物理实验室C',
                'usage_notes' => '实验前检查各部件连接',
            ],
            [
                'name' => '激光器',
                'code' => 'EQ_LASER_001',
                'model' => 'LD-650',
                'brand' => '激光科技',
                'description' => '红光激光器，功率5mW',
                'category_id' => $opticsCategory->id,
                'serial_number' => 'LS2024001',
                'purchase_price' => 3000.00,
                'purchase_date' => '2024-04-05',
                'supplier' => '光电设备公司',
                'warranty_date' => '2026-04-05',
                'status' => 'maintenance',
                'location' => '物理实验室A',
                'usage_notes' => '使用时必须佩戴防护眼镜',
                'maintenance_notes' => '激光功率不稳定，需要校准',
                'last_maintenance_date' => '2024-06-01',
                'next_maintenance_date' => '2024-12-01',
            ]
        ];

        foreach ($equipment as $equipmentData) {
            Equipment::create(array_merge($equipmentData, [
                'organization_id' => $organization->id,
                'created_by' => $user->id,
            ]));
        }
    }

    private function createMaterialCategories($user, $organization)
    {
        $categories = [
            [
                'name' => '化学试剂',
                'code' => 'CHEM_REAGENT',
                'description' => '各类化学试剂',
                'subject' => 'chemistry',
                'grade_range' => 'grade7-12',
                'material_type' => 'chemical',
                'parent_id' => null,
            ],
            [
                'name' => '生物材料',
                'code' => 'BIO_MATERIAL',
                'description' => '生物实验材料',
                'subject' => 'biology',
                'grade_range' => 'grade7-12',
                'material_type' => 'biological',
                'parent_id' => null,
            ],
            [
                'name' => '消耗品',
                'code' => 'CONSUMABLE',
                'description' => '实验消耗品',
                'subject' => 'general',
                'grade_range' => 'all',
                'material_type' => 'consumable',
                'parent_id' => null,
            ],
            [
                'name' => '可重复使用材料',
                'code' => 'REUSABLE',
                'description' => '可重复使用的实验材料',
                'subject' => 'general',
                'grade_range' => 'all',
                'material_type' => 'reusable',
                'parent_id' => null,
            ]
        ];

        foreach ($categories as $index => $categoryData) {
            MaterialCategory::create([
                'name' => $categoryData['name'],
                'code' => $categoryData['code'],
                'description' => $categoryData['description'],
                'subject' => $categoryData['subject'],
                'grade_range' => $categoryData['grade_range'],
                'material_type' => $categoryData['material_type'],
                'parent_id' => $categoryData['parent_id'],
                'sort_order' => $index + 1,
                'status' => 'active',
                'organization_id' => $organization->id,
                'created_by' => $user->id,
            ]);
        }
    }

    private function createMaterials($user, $organization)
    {
        $chemicalCategory = MaterialCategory::where('code', 'CHEM_REAGENT')->first();
        $biologicalCategory = MaterialCategory::where('code', 'BIO_MATERIAL')->first();
        $consumableCategory = MaterialCategory::where('code', 'CONSUMABLE')->first();
        $reusableCategory = MaterialCategory::where('code', 'REUSABLE')->first();

        $materials = [
            [
                'name' => '氢氧化钠',
                'code' => 'MAT_NAOH_001',
                'specification' => 'AR级，500g/瓶',
                'brand' => '国药集团',
                'description' => '分析纯氢氧化钠',
                'category_id' => $chemicalCategory->id,
                'unit' => 'g',
                'unit_price' => 0.05,
                'current_stock' => 2000,
                'min_stock' => 500,
                'max_stock' => 5000,
                'storage_location' => '化学试剂柜A-1',
                'storage_conditions' => '密封保存，防潮',
                'expiry_date' => '2025-12-31',
                'safety_notes' => '强碱性，使用时佩戴防护用品',
                'supplier' => '化学试剂公司',
            ],
            [
                'name' => '盐酸',
                'code' => 'MAT_HCL_001',
                'specification' => 'AR级，37%，500ml/瓶',
                'brand' => '国药集团',
                'description' => '分析纯盐酸',
                'category_id' => $chemicalCategory->id,
                'unit' => 'ml',
                'unit_price' => 0.02,
                'current_stock' => 3000,
                'min_stock' => 1000,
                'max_stock' => 8000,
                'storage_location' => '化学试剂柜A-2',
                'storage_conditions' => '通风保存，远离碱性物质',
                'expiry_date' => '2025-06-30',
                'safety_notes' => '强酸性，腐蚀性强，小心使用',
                'supplier' => '化学试剂公司',
            ],
            [
                'name' => '洋葱表皮',
                'code' => 'MAT_ONION_001',
                'specification' => '新鲜洋葱表皮',
                'brand' => '生物材料供应商',
                'description' => '用于细胞观察实验',
                'category_id' => $biologicalCategory->id,
                'unit' => '片',
                'unit_price' => 1.00,
                'current_stock' => 100,
                'min_stock' => 20,
                'max_stock' => 200,
                'storage_location' => '生物实验室冷藏柜',
                'storage_conditions' => '4°C冷藏保存',
                'expiry_date' => '2024-08-15',
                'safety_notes' => '使用前检查新鲜度',
                'supplier' => '生物材料公司',
            ],
            [
                'name' => '滤纸',
                'code' => 'MAT_FILTER_001',
                'specification' => '定性滤纸，直径9cm',
                'brand' => '实验用品厂',
                'description' => '实验用定性滤纸',
                'category_id' => $consumableCategory->id,
                'unit' => '张',
                'unit_price' => 0.10,
                'current_stock' => 500,
                'min_stock' => 100,
                'max_stock' => 1000,
                'storage_location' => '实验用品柜B-1',
                'storage_conditions' => '干燥保存',
                'safety_notes' => '无特殊安全要求',
                'supplier' => '实验用品公司',
            ],
            [
                'name' => '玻璃棒',
                'code' => 'MAT_GLASS_ROD_001',
                'specification' => '长度20cm，直径5mm',
                'brand' => '玻璃器皿厂',
                'description' => '实验用玻璃搅拌棒',
                'category_id' => $reusableCategory->id,
                'unit' => '根',
                'unit_price' => 2.00,
                'current_stock' => 50,
                'min_stock' => 10,
                'max_stock' => 100,
                'storage_location' => '玻璃器皿柜C-1',
                'storage_conditions' => '小心轻放，防止破损',
                'safety_notes' => '使用时小心玻璃碎片',
                'supplier' => '玻璃器皿公司',
            ]
        ];

        foreach ($materials as $materialData) {
            Material::create(array_merge($materialData, [
                'total_purchased' => $materialData['current_stock'],
                'last_purchase_date' => now()->subDays(rand(30, 90)),
                'status' => 'active',
                'organization_id' => $organization->id,
                'created_by' => $user->id,
            ]));
        }
    }
}
