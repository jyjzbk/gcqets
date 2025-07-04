<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExperimentPlan;
use App\Models\ExperimentCatalog;
use App\Models\CurriculumStandard;
use App\Models\User;
use App\Models\Organization;

class ExperimentPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 获取测试数据
        $catalogs = ExperimentCatalog::take(2)->get();
        $standards = CurriculumStandard::take(2)->get();
        $teachers = User::where('user_type', 'teacher')->take(2)->get();
        $organizations = Organization::where('type', 'school')->take(2)->get();

        if ($catalogs->isEmpty() || $teachers->isEmpty() || $organizations->isEmpty()) {
            $this->command->warn('缺少必要的测试数据，请先运行其他 Seeder');
            return;
        }

        $plans = [
            [
                'name' => '观察植物根茎叶实验计划',
                'code' => 'EP20250703001',
                'experiment_catalog_id' => $catalogs->first()->id,
                'curriculum_standard_id' => $standards->first()->id ?? null,
                'teacher_id' => $teachers->first()->id,
                'class_name' => '三年级一班',
                'student_count' => 35,
                'planned_date' => '2025-07-10',
                'planned_duration' => 45,
                'status' => 'approved',
                'description' => '通过观察植物的根、茎、叶，让学生了解植物的基本结构和功能。',
                'objectives' => '1. 认识植物的基本结构\n2. 了解根茎叶的功能\n3. 培养观察能力',
                'key_points' => '重点：植物各部分的特征\n难点：理解各部分的功能',
                'safety_requirements' => '1. 小心使用放大镜\n2. 不要随意采摘植物\n3. 注意保持实验台整洁',
                'equipment_requirements' => [
                    ['name' => '放大镜', 'quantity' => 10, 'specification' => '5倍放大'],
                    ['name' => '镊子', 'quantity' => 10, 'specification' => '不锈钢材质'],
                    ['name' => '培养皿', 'quantity' => 10, 'specification' => '直径10cm']
                ],
                'material_requirements' => [
                    ['name' => '新鲜植物样本', 'quantity' => 10, 'unit' => '份', 'specification' => '包含根茎叶完整结构'],
                    ['name' => '蒸馏水', 'quantity' => 500, 'unit' => 'ml', 'specification' => '实验用纯净水']
                ],
                'priority' => 'medium',
                'is_public' => true,
                'organization_id' => $organizations->first()->id,
                'created_by' => $teachers->first()->id,
                'approved_by' => $teachers->first()->id,
                'approved_at' => now(),
                'approval_notes' => '计划详细，符合教学要求，同意执行。'
            ],
            [
                'name' => '酸碱指示剂变色实验计划',
                'code' => 'EP20250703002',
                'experiment_catalog_id' => $catalogs->count() > 1 ? $catalogs->get(1)->id : $catalogs->first()->id,
                'curriculum_standard_id' => $standards->count() > 1 ? $standards->get(1)->id : null,
                'teacher_id' => $teachers->count() > 1 ? $teachers->get(1)->id : $teachers->first()->id,
                'class_name' => '五年级二班',
                'student_count' => 32,
                'planned_date' => '2025-07-15',
                'planned_duration' => 40,
                'status' => 'pending',
                'description' => '通过酸碱指示剂的变色反应，让学生了解酸碱性质。',
                'objectives' => '1. 认识酸碱指示剂\n2. 观察指示剂变色现象\n3. 理解酸碱的基本概念',
                'key_points' => '重点：指示剂的变色规律\n难点：酸碱概念的理解',
                'safety_requirements' => '1. 穿戴防护用品\n2. 小心使用化学试剂\n3. 避免试剂接触皮肤\n4. 实验后及时清洗',
                'equipment_requirements' => [
                    ['name' => '试管', 'quantity' => 20, 'specification' => '15ml容量'],
                    ['name' => '试管架', 'quantity' => 5, 'specification' => '木质或塑料'],
                    ['name' => '滴管', 'quantity' => 10, 'specification' => '塑料材质']
                ],
                'material_requirements' => [
                    ['name' => '石蕊试液', 'quantity' => 50, 'unit' => 'ml', 'specification' => '0.1%浓度'],
                    ['name' => '稀盐酸', 'quantity' => 100, 'unit' => 'ml', 'specification' => '0.1mol/L'],
                    ['name' => '氢氧化钠溶液', 'quantity' => 100, 'unit' => 'ml', 'specification' => '0.1mol/L']
                ],
                'priority' => 'high',
                'is_public' => false,
                'organization_id' => $organizations->count() > 1 ? $organizations->get(1)->id : $organizations->first()->id,
                'created_by' => $teachers->count() > 1 ? $teachers->get(1)->id : $teachers->first()->id
            ],
            [
                'name' => '简单电路制作实验计划',
                'code' => 'EP20250703003',
                'experiment_catalog_id' => $catalogs->first()->id,
                'teacher_id' => $teachers->first()->id,
                'class_name' => '四年级三班',
                'student_count' => 28,
                'planned_date' => '2025-07-20',
                'planned_duration' => 50,
                'status' => 'draft',
                'description' => '让学生动手制作简单电路，理解电路的基本原理。',
                'objectives' => '1. 了解电路的基本组成\n2. 学会制作简单电路\n3. 培养动手能力',
                'key_points' => '重点：电路连接方法\n难点：理解电流路径',
                'safety_requirements' => '1. 使用低压电源\n2. 注意导线连接\n3. 避免短路',
                'equipment_requirements' => [
                    ['name' => '电池盒', 'quantity' => 10, 'specification' => '2节5号电池'],
                    ['name' => '小灯泡', 'quantity' => 20, 'specification' => '2.5V'],
                    ['name' => '导线', 'quantity' => 30, 'specification' => '带鳄鱼夹']
                ],
                'material_requirements' => [
                    ['name' => '5号电池', 'quantity' => 20, 'unit' => '节', 'specification' => '1.5V干电池'],
                    ['name' => '开关', 'quantity' => 10, 'unit' => '个', 'specification' => '按钮式开关']
                ],
                'priority' => 'low',
                'is_public' => true,
                'organization_id' => $organizations->first()->id,
                'created_by' => $teachers->first()->id
            ],
            [
                'name' => '水的三态变化观察实验',
                'code' => 'EP20250703004',
                'experiment_catalog_id' => $catalogs->first()->id,
                'teacher_id' => $teachers->first()->id,
                'class_name' => '二年级一班',
                'student_count' => 30,
                'planned_date' => '2025-07-25',
                'planned_duration' => 35,
                'status' => 'rejected',
                'description' => '观察水在不同温度下的状态变化。',
                'objectives' => '1. 认识水的三种状态\n2. 观察状态变化过程\n3. 理解温度对物质状态的影响',
                'key_points' => '重点：三态变化现象\n难点：理解变化原因',
                'safety_requirements' => '1. 小心热水烫伤\n2. 注意用电安全\n3. 保持实验环境整洁',
                'equipment_requirements' => [
                    ['name' => '烧杯', 'quantity' => 5, 'specification' => '250ml'],
                    ['name' => '温度计', 'quantity' => 5, 'specification' => '0-100℃'],
                    ['name' => '电热器', 'quantity' => 2, 'specification' => '实验室专用']
                ],
                'material_requirements' => [
                    ['name' => '蒸馏水', 'quantity' => 1000, 'unit' => 'ml', 'specification' => '纯净水'],
                    ['name' => '冰块', 'quantity' => 500, 'unit' => 'g', 'specification' => '实验用冰']
                ],
                'priority' => 'medium',
                'is_public' => false,
                'organization_id' => $organizations->first()->id,
                'created_by' => $teachers->first()->id,
                'approved_by' => $teachers->first()->id,
                'approved_at' => now(),
                'rejection_reason' => '实验设计存在安全隐患，请修改后重新提交。'
            ]
        ];

        foreach ($plans as $planData) {
            ExperimentPlan::create($planData);
        }

        $this->command->info('实验计划测试数据创建完成！');
        $this->command->info('- 创建了 4 个测试计划');
        $this->command->info('- 包含不同状态：已批准、待审批、草稿、已拒绝');
        $this->command->info('- 包含不同优先级和公开设置');
    }
}
