<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExperimentPlan;
use App\Models\ExperimentCatalog;
use App\Models\User;
use App\Models\Organization;
use Carbon\Carbon;

class ExperimentCalendarTestSeeder extends Seeder
{
    /**
     * 为日历功能创建测试数据
     */
    public function run()
    {
        // 获取测试用户和组织
        $teacher = User::where('username', 'hh78@163.com')->first();
        $organization = Organization::where('name', '东城小学')->first();
        $catalog = ExperimentCatalog::first();

        if (!$teacher || !$organization || !$catalog) {
            $this->command->warn('缺少必要的测试数据，请先运行基础数据种子');
            return;
        }

        $this->command->info('开始创建实验日历测试数据...');

        // 创建不同状态和时间的实验计划
        $plans = [
            // 本月的计划
            [
                'name' => '光的折射实验',
                'planned_date' => Carbon::now()->addDays(3),
                'status' => 'approved',
                'description' => '观察光在不同介质中的折射现象'
            ],
            [
                'name' => '电路连接实验',
                'planned_date' => Carbon::now()->addDays(7),
                'status' => 'approved',
                'description' => '学习基本电路的连接方法'
            ],
            [
                'name' => '植物观察实验',
                'planned_date' => Carbon::now()->addDays(10),
                'status' => 'pending',
                'description' => '观察植物的生长过程'
            ],
            [
                'name' => '化学反应实验',
                'planned_date' => Carbon::now()->addDays(15),
                'status' => 'draft',
                'description' => '观察酸碱反应现象'
            ],
            
            // 逾期的计划
            [
                'name' => '磁场实验（逾期）',
                'planned_date' => Carbon::now()->subDays(2),
                'status' => 'approved',
                'description' => '研究磁场的性质和作用'
            ],
            [
                'name' => '声音传播实验（逾期）',
                'planned_date' => Carbon::now()->subDays(5),
                'status' => 'approved',
                'description' => '探索声音在不同介质中的传播'
            ],
            
            // 今天的计划
            [
                'name' => '今日实验：水的三态变化',
                'planned_date' => Carbon::now(),
                'status' => 'approved',
                'description' => '观察水的固态、液态、气态变化'
            ],
            
            // 明天的计划
            [
                'name' => '明日实验：空气压力',
                'planned_date' => Carbon::now()->addDay(),
                'status' => 'approved',
                'description' => '感受和测量空气压力'
            ],
            
            // 下个月的计划
            [
                'name' => '下月实验：太阳能发电',
                'planned_date' => Carbon::now()->addMonth()->addDays(5),
                'status' => 'approved',
                'description' => '了解太阳能发电原理'
            ],
            [
                'name' => '下月实验：DNA提取',
                'planned_date' => Carbon::now()->addMonth()->addDays(12),
                'status' => 'pending',
                'description' => '从水果中提取DNA'
            ]
        ];

        foreach ($plans as $index => $planData) {
            $plan = ExperimentPlan::create([
                'name' => $planData['name'],
                'code' => 'TEST' . date('Ymd') . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'experiment_catalog_id' => $catalog->id,
                'teacher_id' => $teacher->id,
                'organization_id' => $organization->id,
                'class_name' => '五年级' . chr(65 + ($index % 3)) . '班',
                'student_count' => rand(25, 35),
                'planned_date' => $planData['planned_date']->toDateString(),
                'planned_duration' => rand(40, 90),
                'status' => $planData['status'],
                'description' => $planData['description'],
                'objectives' => '通过实验培养学生的观察能力和科学思维',
                'key_points' => '实验操作规范，数据记录准确',
                'safety_requirements' => '注意实验安全，正确使用实验器材',
                'priority' => ['low', 'medium', 'high'][rand(0, 2)],
                'revision_count' => 0,
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 5))
            ]);

            $this->command->info("创建实验计划: {$plan->name} (状态: {$plan->status}, 日期: {$plan->planned_date})");
        }

        // 创建一些同一天的多个实验（测试冲突检测）
        $conflictDate = Carbon::now()->addDays(20);
        for ($i = 1; $i <= 3; $i++) {
            ExperimentPlan::create([
                'name' => "冲突测试实验 {$i}",
                'code' => 'CONFLICT' . date('Ymd') . str_pad($i, 2, '0', STR_PAD_LEFT),
                'experiment_catalog_id' => $catalog->id,
                'teacher_id' => $teacher->id,
                'organization_id' => $organization->id,
                'class_name' => '六年级' . chr(65 + $i - 1) . '班',
                'student_count' => 30,
                'planned_date' => $conflictDate->toDateString(),
                'planned_duration' => 45,
                'status' => 'approved',
                'description' => "这是第{$i}个在同一天的实验，用于测试冲突检测",
                'objectives' => '测试日程冲突功能',
                'priority' => 'medium',
                'revision_count' => 0
            ]);
        }

        $this->command->info("创建了3个冲突测试实验 (日期: {$conflictDate->toDateString()})");

        // 创建跨月份的实验计划
        $nextMonth = Carbon::now()->addMonth();
        $prevMonth = Carbon::now()->subMonth();
        
        ExperimentPlan::create([
            'name' => '上月补充实验',
            'code' => 'PREV' . $prevMonth->format('Ymd') . '001',
            'experiment_catalog_id' => $catalog->id,
            'teacher_id' => $teacher->id,
            'organization_id' => $organization->id,
            'class_name' => '四年级A班',
            'student_count' => 28,
            'planned_date' => $prevMonth->addDays(15)->toDateString(),
            'planned_duration' => 60,
            'status' => 'completed',
            'description' => '上个月的补充实验',
            'objectives' => '巩固上月学习内容',
            'priority' => 'low',
            'revision_count' => 0
        ]);

        $this->command->info('实验日历测试数据创建完成！');
        $this->command->info('数据包括：');
        $this->command->info('- 本月实验计划：7个（不同状态）');
        $this->command->info('- 逾期实验：2个');
        $this->command->info('- 今日实验：1个');
        $this->command->info('- 明日实验：1个');
        $this->command->info('- 下月实验：2个');
        $this->command->info('- 冲突测试：3个（同一天）');
        $this->command->info('- 跨月实验：1个');
        $this->command->info('');
        $this->command->info('现在可以访问 /experiment-calendar 测试日历功能');
    }
}
