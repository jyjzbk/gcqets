<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\Material;
use App\Models\EquipmentBorrowing;
use App\Models\MaterialUsage;
use App\Models\User;
use App\Models\Organization;
use App\Models\EquipmentCategory;
use App\Models\MaterialCategory;
use App\Models\ExperimentCatalog;

class EquipmentMaterialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 获取测试用户和组织
        $sysAdmin = User::where('username', 'sysadmin')->first();
        $teacher = User::where('email', 'hh78@163.com')->first();
        $dongchengSchool = Organization::where('name', '东城小学')->first();
        
        if (!$sysAdmin || !$dongchengSchool) {
            $this->command->error('请先运行用户和组织数据填充');
            return;
        }

        // 获取分类
        $physicsCategory = EquipmentCategory::where('code', 'PHYS_EQ')->first();
        $chemistryCategory = EquipmentCategory::where('code', 'CHEM_EQ')->first();
        $biologyCategory = EquipmentCategory::where('code', 'BIO_EQ')->first();
        $generalCategory = EquipmentCategory::where('code', 'GEN_EQ')->first();

        $chemicalCategory = MaterialCategory::where('code', 'CHEM_REAGENT')->first();
        $biologicalCategory = MaterialCategory::where('code', 'BIO_MATERIAL')->first();
        $consumableCategory = MaterialCategory::where('code', 'CONSUMABLE')->first();
        $reusableCategory = MaterialCategory::where('code', 'REUSABLE')->first();

        // 添加更多设备
        $this->addMoreEquipment($sysAdmin, $dongchengSchool, $physicsCategory, $chemistryCategory, $biologyCategory, $generalCategory);
        
        // 添加更多材料
        $this->addMoreMaterials($sysAdmin, $dongchengSchool, $chemicalCategory, $biologicalCategory, $consumableCategory, $reusableCategory);
        
        // 添加设备借用记录
        $this->addBorrowingRecords($sysAdmin, $teacher, $dongchengSchool);
        
        // 添加材料使用记录
        $this->addUsageRecords($sysAdmin, $teacher, $dongchengSchool);

        $this->command->info('设备物料示例数据填充完成');
    }

    private function addMoreEquipment($user, $organization, $physicsCategory, $chemistryCategory, $biologyCategory, $generalCategory)
    {
        $equipment = [
            // 物理设备
            [
                'name' => '示波器',
                'code' => 'EQ_OSCILLOSCOPE_001',
                'model' => 'DS1054Z',
                'brand' => '普源精电',
                'description' => '数字示波器，4通道50MHz',
                'category_id' => $physicsCategory->id,
                'serial_number' => 'DS2024001',
                'purchase_price' => 3500.00,
                'purchase_date' => '2024-03-15',
                'supplier' => '仪器设备公司',
                'warranty_date' => '2027-03-15',
                'status' => 'available',
                'location' => '物理实验室B',
                'usage_notes' => '使用前需预热5分钟',
            ],
            [
                'name' => '万用表',
                'code' => 'EQ_MULTIMETER_001',
                'model' => 'UT61E',
                'brand' => '优利德',
                'description' => '数字万用表，真有效值',
                'category_id' => $physicsCategory->id,
                'serial_number' => 'UT2024001',
                'purchase_price' => 280.00,
                'purchase_date' => '2024-04-10',
                'supplier' => '电子仪器商城',
                'warranty_date' => '2026-04-10',
                'status' => 'available',
                'location' => '物理实验室A',
                'usage_notes' => '测量前检查档位设置',
            ],
            [
                'name' => '光谱仪',
                'code' => 'EQ_SPECTROMETER_001',
                'model' => 'SP-2000',
                'brand' => '海光仪器',
                'description' => '可见光分光光度计',
                'category_id' => $physicsCategory->id,
                'serial_number' => 'HG2024001',
                'purchase_price' => 12000.00,
                'purchase_date' => '2024-02-20',
                'supplier' => '光学仪器公司',
                'warranty_date' => '2027-02-20',
                'status' => 'maintenance',
                'location' => '物理实验室C',
                'usage_notes' => '需要专业人员操作',
                'maintenance_notes' => '光源需要更换',
                'last_maintenance_date' => '2024-06-15',
                'next_maintenance_date' => '2024-12-15',
            ],
            // 化学设备
            [
                'name' => '酸度计',
                'code' => 'EQ_PH_METER_001',
                'model' => 'PHS-3C',
                'brand' => '雷磁',
                'description' => '台式酸度计，精度0.01pH',
                'category_id' => $chemistryCategory->id,
                'serial_number' => 'LC2024001',
                'purchase_price' => 1800.00,
                'purchase_date' => '2024-03-25',
                'supplier' => '化学仪器公司',
                'warranty_date' => '2026-03-25',
                'status' => 'available',
                'location' => '化学实验室A',
                'usage_notes' => '使用前需校准',
            ],
            [
                'name' => '离心机',
                'code' => 'EQ_CENTRIFUGE_001',
                'model' => 'TDL-5-A',
                'brand' => '安亭科学',
                'description' => '台式低速离心机',
                'category_id' => $chemistryCategory->id,
                'serial_number' => 'AT2024001',
                'purchase_price' => 4500.00,
                'purchase_date' => '2024-01-30',
                'supplier' => '实验设备公司',
                'warranty_date' => '2026-01-30',
                'status' => 'borrowed',
                'location' => '化学实验室B',
                'usage_notes' => '使用时注意平衡',
            ],
            // 生物设备
            [
                'name' => '培养箱',
                'code' => 'EQ_INCUBATOR_001',
                'model' => 'DHP-9272',
                'brand' => '一恒',
                'description' => '电热恒温培养箱',
                'category_id' => $biologyCategory->id,
                'serial_number' => 'YH2024001',
                'purchase_price' => 3200.00,
                'purchase_date' => '2024-02-15',
                'supplier' => '生物仪器公司',
                'warranty_date' => '2026-02-15',
                'status' => 'available',
                'location' => '生物实验室A',
                'usage_notes' => '温度设置不超过60℃',
            ],
            [
                'name' => '超净工作台',
                'code' => 'EQ_CLEAN_BENCH_001',
                'model' => 'SW-CJ-1F',
                'brand' => '苏净',
                'description' => '单人单面净化工作台',
                'category_id' => $biologyCategory->id,
                'serial_number' => 'SJ2024001',
                'purchase_price' => 5800.00,
                'purchase_date' => '2024-03-05',
                'supplier' => '净化设备公司',
                'warranty_date' => '2026-03-05',
                'status' => 'available',
                'location' => '生物实验室B',
                'usage_notes' => '使用前紫外消毒30分钟',
            ],
            // 通用设备
            [
                'name' => '投影仪',
                'code' => 'EQ_PROJECTOR_001',
                'model' => 'EB-X41',
                'brand' => '爱普生',
                'description' => '教学投影仪，3600流明',
                'category_id' => $generalCategory->id,
                'serial_number' => 'EP2024001',
                'purchase_price' => 2800.00,
                'purchase_date' => '2024-04-20',
                'supplier' => '教学设备公司',
                'warranty_date' => '2026-04-20',
                'status' => 'available',
                'location' => '多媒体教室',
                'usage_notes' => '使用后及时关闭',
            ],
            [
                'name' => '打印机',
                'code' => 'EQ_PRINTER_001',
                'model' => 'LaserJet Pro M404n',
                'brand' => '惠普',
                'description' => '黑白激光打印机',
                'category_id' => $generalCategory->id,
                'serial_number' => 'HP2024001',
                'purchase_price' => 1200.00,
                'purchase_date' => '2024-05-10',
                'supplier' => '办公设备公司',
                'warranty_date' => '2025-05-10',
                'status' => 'damaged',
                'location' => '办公室',
                'usage_notes' => '定期更换硒鼓',
                'maintenance_notes' => '进纸器故障，需要维修',
            ]
        ];

        foreach ($equipment as $equipmentData) {
            Equipment::create(array_merge($equipmentData, [
                'organization_id' => $organization->id,
                'created_by' => $user->id,
            ]));
        }
    }

    private function addMoreMaterials($user, $organization, $chemicalCategory, $biologicalCategory, $consumableCategory, $reusableCategory)
    {
        $materials = [
            // 化学试剂
            [
                'name' => '硫酸',
                'code' => 'MAT_H2SO4_001',
                'specification' => 'AR级，98%，500ml/瓶',
                'brand' => '国药集团',
                'description' => '分析纯浓硫酸',
                'category_id' => $chemicalCategory->id,
                'unit' => 'ml',
                'unit_price' => 0.08,
                'current_stock' => 1500,
                'min_stock' => 300,
                'max_stock' => 3000,
                'storage_location' => '化学试剂柜A-3',
                'storage_conditions' => '密封保存，防潮，通风',
                'expiry_date' => '2025-08-31',
                'safety_notes' => '强酸性，腐蚀性极强，使用时必须佩戴防护用品',
                'supplier' => '化学试剂公司',
            ],
            [
                'name' => '碳酸钠',
                'code' => 'MAT_NA2CO3_001',
                'specification' => 'AR级，500g/瓶',
                'brand' => '国药集团',
                'description' => '分析纯碳酸钠',
                'category_id' => $chemicalCategory->id,
                'unit' => 'g',
                'unit_price' => 0.03,
                'current_stock' => 800,
                'min_stock' => 200,
                'max_stock' => 2000,
                'storage_location' => '化学试剂柜B-1',
                'storage_conditions' => '干燥保存，防潮',
                'expiry_date' => '2026-03-31',
                'safety_notes' => '避免吸入粉尘',
                'supplier' => '化学试剂公司',
            ],
            [
                'name' => '酚酞指示剂',
                'code' => 'MAT_PHENOL_001',
                'specification' => '1%乙醇溶液，100ml/瓶',
                'brand' => '天津化学',
                'description' => '酸碱指示剂',
                'category_id' => $chemicalCategory->id,
                'unit' => 'ml',
                'unit_price' => 0.15,
                'current_stock' => 500,
                'min_stock' => 100,
                'max_stock' => 1000,
                'storage_location' => '化学试剂柜C-2',
                'storage_conditions' => '避光保存，室温',
                'expiry_date' => '2025-12-31',
                'safety_notes' => '避免接触皮肤和眼睛',
                'supplier' => '化学试剂公司',
            ],
            // 生物材料
            [
                'name' => '琼脂粉',
                'code' => 'MAT_AGAR_001',
                'specification' => '微生物级，250g/瓶',
                'brand' => 'Oxoid',
                'description' => '微生物培养基原料',
                'category_id' => $biologicalCategory->id,
                'unit' => 'g',
                'unit_price' => 0.80,
                'current_stock' => 200,
                'min_stock' => 50,
                'max_stock' => 500,
                'storage_location' => '生物实验室冷藏柜',
                'storage_conditions' => '4°C冷藏保存',
                'expiry_date' => '2025-09-30',
                'safety_notes' => '使用前检查是否变质',
                'supplier' => '生物材料公司',
            ],
            [
                'name' => '酵母菌',
                'code' => 'MAT_YEAST_001',
                'specification' => '活性干酵母，10g/包',
                'brand' => '安琪',
                'description' => '实验用酵母菌',
                'category_id' => $biologicalCategory->id,
                'unit' => 'g',
                'unit_price' => 2.00,
                'current_stock' => 80,
                'min_stock' => 20,
                'max_stock' => 200,
                'storage_location' => '生物实验室冷藏柜',
                'storage_conditions' => '4°C冷藏保存',
                'expiry_date' => '2024-12-31',
                'safety_notes' => '使用前检查活性',
                'supplier' => '生物材料公司',
            ],
            // 消耗品
            [
                'name' => '试管',
                'code' => 'MAT_TEST_TUBE_001',
                'specification' => '硼硅玻璃，15ml，φ16×150mm',
                'brand' => '舒博',
                'description' => '实验用试管',
                'category_id' => $consumableCategory->id,
                'unit' => '支',
                'unit_price' => 1.50,
                'current_stock' => 200,
                'min_stock' => 50,
                'max_stock' => 500,
                'storage_location' => '玻璃器皿柜A-1',
                'storage_conditions' => '小心轻放，防止破损',
                'safety_notes' => '使用时小心玻璃碎片',
                'supplier' => '实验用品公司',
            ],
            [
                'name' => '橡胶手套',
                'code' => 'MAT_GLOVES_001',
                'specification' => '一次性乳胶手套，M号',
                'brand' => '蓝帆',
                'description' => '实验防护手套',
                'category_id' => $consumableCategory->id,
                'unit' => '双',
                'unit_price' => 0.50,
                'current_stock' => 500,
                'min_stock' => 100,
                'max_stock' => 1000,
                'storage_location' => '防护用品柜',
                'storage_conditions' => '干燥保存，避免阳光直射',
                'expiry_date' => '2026-06-30',
                'safety_notes' => '使用前检查是否破损',
                'supplier' => '防护用品公司',
            ],
            [
                'name' => '酒精灯',
                'code' => 'MAT_ALCOHOL_LAMP_001',
                'specification' => '玻璃材质，150ml容量',
                'brand' => '教学仪器厂',
                'description' => '实验加热用酒精灯',
                'category_id' => $reusableCategory->id,
                'unit' => '个',
                'unit_price' => 8.00,
                'current_stock' => 30,
                'min_stock' => 10,
                'max_stock' => 50,
                'storage_location' => '实验器材柜B-2',
                'storage_conditions' => '小心轻放，防止破损',
                'safety_notes' => '使用时注意防火安全',
                'supplier' => '教学仪器公司',
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

    private function addBorrowingRecords($sysAdmin, $teacher, $organization)
    {
        // 获取一些设备
        $equipment = Equipment::where('organization_id', $organization->id)->take(6)->get();

        if ($equipment->count() < 6) {
            $this->command->warn('设备数量不足，跳过借用记录创建');
            return;
        }

        $borrowings = [
            [
                'equipment_id' => $equipment[0]->id,
                'borrower_id' => $teacher ? $teacher->id : $sysAdmin->id,
                'purpose' => '物理光学实验教学',
                'planned_start_time' => now()->addDays(1),
                'planned_end_time' => now()->addDays(3),
                'status' => 'pending',
            ],
            [
                'equipment_id' => $equipment[1]->id,
                'borrower_id' => $teacher ? $teacher->id : $sysAdmin->id,
                'purpose' => '化学分析实验',
                'planned_start_time' => now()->subDays(2),
                'planned_end_time' => now()->addDays(1),
                'status' => 'approved',
                'approved_at' => now()->subDays(1),
                'approver_id' => $sysAdmin->id,
                'approval_notes' => '实验需要，批准借用',
            ],
            [
                'equipment_id' => $equipment[2]->id,
                'borrower_id' => $teacher ? $teacher->id : $sysAdmin->id,
                'purpose' => '学生实验课程',
                'planned_start_time' => now()->subDays(5),
                'planned_end_time' => now()->subDays(3),
                'status' => 'borrowed',
                'approved_at' => now()->subDays(4),
                'approver_id' => $sysAdmin->id,
                'approval_notes' => '同意借用',
                'borrowed_at' => now()->subDays(4),
                'borrowing_notes' => '设备状态良好',
                'equipment_condition_before' => 'good',
            ],
            [
                'equipment_id' => $equipment[3]->id,
                'borrower_id' => $teacher ? $teacher->id : $sysAdmin->id,
                'purpose' => '教师演示实验',
                'planned_start_time' => now()->subDays(10),
                'planned_end_time' => now()->subDays(8),
                'status' => 'returned',
                'approved_at' => now()->subDays(9),
                'approver_id' => $sysAdmin->id,
                'approval_notes' => '批准借用',
                'borrowed_at' => now()->subDays(9),
                'borrowing_notes' => '设备正常',
                'equipment_condition_before' => 'good',
                'returned_at' => now()->subDays(7),
                'return_notes' => '使用正常，已归还',
                'equipment_condition_after' => 'good',
                'actual_start_time' => now()->subDays(9),
                'actual_end_time' => now()->subDays(7),
            ]
        ];

        foreach ($borrowings as $index => $borrowingData) {
            $borrowingCode = 'BOR_' . date('YmdHis') . '_' . ($index + 1);

            EquipmentBorrowing::create(array_merge($borrowingData, [
                'borrowing_code' => $borrowingCode,
                'organization_id' => $organization->id,
                'created_by' => $borrowingData['borrower_id'],
            ]));
        }
    }

    private function addUsageRecords($sysAdmin, $teacher, $organization)
    {
        // 获取一些材料
        $materials = Material::where('organization_id', $organization->id)->take(8)->get();

        if ($materials->count() < 8) {
            $this->command->warn('材料数量不足，跳过使用记录创建');
            return;
        }

        // 获取实验目录（如果存在）
        $experimentCatalog = ExperimentCatalog::where('organization_id', $organization->id)->first();

        $usages = [
            [
                'material_id' => $materials[0]->id,
                'user_id' => $teacher ? $teacher->id : $sysAdmin->id,
                'quantity_used' => 50,
                'purpose' => '酸碱中和滴定实验',
                'used_at' => now()->subDays(1),
                'usage_type' => 'experiment',
                'class_name' => '九年级(1)班',
                'student_count' => 35,
                'notes' => '学生分组实验，每组使用约1.5ml',
            ],
            [
                'material_id' => $materials[1]->id,
                'user_id' => $teacher ? $teacher->id : $sysAdmin->id,
                'quantity_used' => 20,
                'purpose' => '金属活动性实验',
                'used_at' => now()->subDays(3),
                'usage_type' => 'experiment',
                'class_name' => '九年级(2)班',
                'student_count' => 32,
                'notes' => '演示实验用量',
            ],
            [
                'material_id' => $materials[2]->id,
                'user_id' => $teacher ? $teacher->id : $sysAdmin->id,
                'quantity_used' => 10,
                'purpose' => '酸碱指示剂实验',
                'used_at' => now()->subDays(5),
                'usage_type' => 'teaching',
                'class_name' => '八年级(3)班',
                'student_count' => 30,
                'notes' => '课堂演示实验',
            ],
            [
                'material_id' => $materials[3]->id,
                'user_id' => $sysAdmin->id,
                'quantity_used' => 5,
                'purpose' => '微生物培养基制备',
                'used_at' => now()->subDays(7),
                'usage_type' => 'experiment',
                'notes' => '生物实验室培养基准备',
            ]
        ];

        foreach ($usages as $index => $usageData) {
            $usageCode = 'USE_' . date('YmdHis') . '_' . ($index + 1);

            $usage = MaterialUsage::create(array_merge($usageData, [
                'usage_code' => $usageCode,
                'experiment_catalog_id' => $experimentCatalog ? $experimentCatalog->id : null,
                'organization_id' => $organization->id,
                'created_by' => $usageData['user_id'],
            ]));

            // 更新材料库存
            $material = $materials[$index];
            $material->updateStock(
                -$usageData['quantity_used'],
                'usage',
                $usageData['purpose'],
                $usageData['user_id'],
                'material_usage',
                $usage->id
            );
        }
    }
}
