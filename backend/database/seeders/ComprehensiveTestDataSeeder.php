<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExperimentPlan;
use App\Models\ExperimentRecord;
use App\Models\ExperimentPhoto;
use App\Models\ExperimentReviewLog;
use App\Models\ExperimentCatalog;
use App\Models\User;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ComprehensiveTestDataSeeder extends Seeder
{
    /**
     * 创建全面的测试数据
     */
    public function run()
    {
        $this->command->info('开始创建全面的测试数据...');

        // 获取基础数据
        $teachers = User::where('user_type', 'teacher')->get();
        $admins = User::whereIn('user_type', ['admin', 'school_admin', 'district_admin'])->get();
        $organizations = Organization::all();
        $catalogs = ExperimentCatalog::all();

        if ($teachers->isEmpty() || $organizations->isEmpty() || $catalogs->isEmpty()) {
            $this->command->warn('缺少基础数据，请先运行基础数据种子');
            return;
        }

        // 创建实验计划数据
        $this->createExperimentPlans($teachers, $organizations, $catalogs);
        
        // 创建实验记录数据
        $this->createExperimentRecords();
        
        // 创建审核日志数据
        $this->createReviewLogs($admins);
        
        // 创建照片数据
        $this->createExperimentPhotos();

        $this->command->info('全面测试数据创建完成！');
    }

    /**
     * 创建实验计划数据
     */
    private function createExperimentPlans($teachers, $organizations, $catalogs)
    {
        $this->command->info('创建实验计划数据...');

        $statuses = ['draft', 'pending', 'approved', 'rejected', 'executing', 'completed', 'cancelled'];
        $priorities = ['low', 'medium', 'high'];
        $classes = ['一年级A班', '一年级B班', '二年级A班', '二年级B班', '三年级A班', '三年级B班', 
                   '四年级A班', '四年级B班', '五年级A班', '五年级B班', '六年级A班', '六年级B班'];

        $planTemplates = [
            ['name' => '光的折射实验', 'description' => '通过实验观察光在不同介质中的折射现象，理解折射定律'],
            ['name' => '电路连接实验', 'description' => '学习串联和并联电路的连接方法，观察电流变化'],
            ['name' => '植物生长观察', 'description' => '观察植物在不同条件下的生长情况，记录生长数据'],
            ['name' => '化学反应实验', 'description' => '观察酸碱反应现象，了解化学反应的基本特征'],
            ['name' => '磁场实验', 'description' => '研究磁场的性质和磁力线分布，理解磁场概念'],
            ['name' => '声音传播实验', 'description' => '探索声音在不同介质中的传播速度和特性'],
            ['name' => '水的三态变化', 'description' => '观察水在不同温度下的状态变化过程'],
            ['name' => '空气压力实验', 'description' => '感受和测量大气压力，理解压力概念'],
            ['name' => '太阳能发电实验', 'description' => '了解太阳能发电原理，制作简单的太阳能装置'],
            ['name' => 'DNA提取实验', 'description' => '从水果中提取DNA，观察遗传物质的基本特征']
        ];

        for ($i = 0; $i < 100; $i++) {
            $teacher = $teachers->random();
            $organization = $organizations->random();
            $catalog = $catalogs->random();
            $template = $planTemplates[array_rand($planTemplates)];
            
            // 生成时间分布
            $createdAt = Carbon::now()->subDays(rand(1, 90));
            $plannedDate = $createdAt->copy()->addDays(rand(1, 60));
            
            $status = $statuses[array_rand($statuses)];
            
            // 根据状态调整时间逻辑
            if ($status === 'completed') {
                $plannedDate = Carbon::now()->subDays(rand(1, 30));
            } elseif ($status === 'executing') {
                $plannedDate = Carbon::now()->subDays(rand(0, 7));
            }

            ExperimentPlan::create([
                'name' => $template['name'] . ' - ' . $classes[array_rand($classes)],
                'code' => 'EXP' . date('Ymd', $createdAt->timestamp) . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'experiment_catalog_id' => $catalog->id,
                'teacher_id' => $teacher->id,
                'organization_id' => $organization->id,
                'class_name' => $classes[array_rand($classes)],
                'student_count' => rand(20, 40),
                'planned_date' => $plannedDate->toDateString(),
                'planned_duration' => [40, 45, 60, 90][array_rand([40, 45, 60, 90])],
                'status' => $status,
                'priority' => $priorities[array_rand($priorities)],
                'description' => $template['description'],
                'objectives' => '通过实验培养学生的观察能力、动手能力和科学思维',
                'key_points' => '实验操作规范、数据记录准确、安全意识强',
                'safety_requirements' => '注意实验安全，正确使用实验器材，遵守实验室规则',
                'equipment_requirements' => json_encode([
                    '实验器材' => rand(5, 15) . '套',
                    '试剂' => rand(2, 8) . '种',
                    '工具' => rand(3, 10) . '件'
                ]),
                'material_requirements' => json_encode([
                    '消耗材料' => rand(10, 50) . '份',
                    '记录表' => rand(20, 40) . '张'
                ]),
                'revision_count' => rand(0, 3),
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addDays(rand(0, 5))
            ]);
        }

        $this->command->info('创建了100个实验计划');
    }

    /**
     * 创建实验记录数据
     */
    private function createExperimentRecords()
    {
        $this->command->info('创建实验记录数据...');

        $approvedPlans = ExperimentPlan::where('status', 'approved')
                                      ->orWhere('status', 'executing')
                                      ->orWhere('status', 'completed')
                                      ->get();

        $completionStatuses = ['not_started', 'in_progress', 'partial', 'completed', 'cancelled'];
        $reviewStatuses = ['pending', 'approved', 'rejected', 'revision_required'];

        foreach ($approvedPlans as $plan) {
            // 70%的概率创建记录
            if (rand(1, 100) <= 70) {
                $executionDate = Carbon::parse($plan->planned_date)->addDays(rand(-2, 7));
                $completionStatus = $completionStatuses[array_rand($completionStatuses)];
                $reviewStatus = $reviewStatuses[array_rand($reviewStatuses)];

                ExperimentRecord::create([
                    'experiment_plan_id' => $plan->id,
                    'execution_date' => $executionDate->toDateString(),
                    'actual_duration' => $plan->planned_duration + rand(-15, 30),
                    'actual_student_count' => $plan->student_count + rand(-3, 3),
                    'completion_status' => $completionStatus,
                    'review_status' => $reviewStatus,
                    'experiment_process' => $this->generateExperimentProcess(),
                    'observations' => $this->generateObservations(),
                    'results' => $this->generateResults(),
                    'problems_encountered' => rand(1, 100) <= 30 ? $this->generateProblems() : null,
                    'improvements' => rand(1, 100) <= 40 ? $this->generateImprovements() : null,
                    'teacher_notes' => $this->generateTeacherNotes(),
                    'equipment_used' => json_encode($this->generateEquipmentUsed()),
                    'materials_consumed' => json_encode($this->generateMaterialsConsumed()),
                    'created_by' => $plan->teacher_id,
                    'created_at' => $executionDate->copy()->addHours(rand(1, 8)),
                    'updated_at' => $executionDate->copy()->addDays(rand(0, 3))
                ]);
            }
        }

        $this->command->info('创建了实验记录数据');
    }

    /**
     * 创建审核日志数据
     */
    private function createReviewLogs($admins)
    {
        $this->command->info('创建审核日志数据...');

        $records = ExperimentRecord::whereIn('review_status', ['approved', 'rejected', 'revision_required'])->get();
        $reviewTypes = ['manual', 'ai_check', 'batch'];
        $reviewResults = ['approved', 'rejected', 'revision_required'];

        foreach ($records as $record) {
            $reviewer = $admins->random();
            $reviewType = $reviewTypes[array_rand($reviewTypes)];
            $reviewResult = $record->review_status;

            ExperimentReviewLog::create([
                'experiment_record_id' => $record->id,
                'reviewer_id' => $reviewer->id,
                'organization_id' => $reviewer->organization_id,
                'review_type' => $reviewType,
                'review_result' => $reviewResult,
                'review_comments' => $this->generateReviewComments($reviewResult),
                'review_score' => $reviewResult === 'approved' ? rand(80, 100) : rand(40, 79),
                'review_criteria' => json_encode($this->generateReviewCriteria()),
                'ai_analysis' => $reviewType === 'ai_check' ? json_encode($this->generateAiAnalysis()) : null,
                'created_at' => $record->created_at->copy()->addDays(rand(1, 5))
            ]);
        }

        $this->command->info('创建了审核日志数据');
    }

    /**
     * 创建照片数据
     */
    private function createExperimentPhotos()
    {
        $this->command->info('创建实验照片数据...');

        $records = ExperimentRecord::all();
        $photoTypes = ['setup', 'process', 'result', 'cleanup'];

        foreach ($records as $record) {
            $photoCount = rand(2, 8);
            
            for ($i = 0; $i < $photoCount; $i++) {
                ExperimentPhoto::create([
                    'experiment_record_id' => $record->id,
                    'photo_type' => $photoTypes[array_rand($photoTypes)],
                    'file_name' => 'experiment_' . $record->id . '_' . ($i + 1) . '.jpg',
                    'file_path' => 'uploads/experiments/' . date('Y/m', strtotime($record->created_at)) . '/experiment_' . $record->id . '_' . ($i + 1) . '.jpg',
                    'file_size' => rand(500000, 3000000), // 0.5MB - 3MB
                    'description' => $this->generatePhotoDescription($photoTypes[array_rand($photoTypes)]),
                    'ai_analysis' => json_encode($this->generatePhotoAiAnalysis()),
                    'created_at' => $record->created_at->copy()->addMinutes(rand(10, 120))
                ]);
            }
        }

        $this->command->info('创建了实验照片数据');
    }

    // 辅助方法生成随机内容
    private function generateExperimentProcess()
    {
        $processes = [
            '1. 准备实验器材和材料\n2. 按照实验步骤进行操作\n3. 观察实验现象\n4. 记录实验数据\n5. 清理实验现场',
            '1. 检查实验设备\n2. 设置实验参数\n3. 开始实验操作\n4. 实时监测数据\n5. 完成实验记录',
            '1. 分组准备\n2. 讲解实验要点\n3. 学生动手操作\n4. 教师指导观察\n5. 总结实验结果'
        ];
        return $processes[array_rand($processes)];
    }

    private function generateObservations()
    {
        $observations = [
            '实验现象明显，学生观察认真，数据记录完整',
            '部分现象不够明显，需要重复实验验证',
            '学生参与度高，观察细致，提出了有价值的问题',
            '实验效果良好，达到了预期的教学目标'
        ];
        return $observations[array_rand($observations)];
    }

    private function generateResults()
    {
        $results = [
            '实验结果符合理论预期，学生掌握了相关知识点',
            '通过实验验证了理论知识，加深了学生理解',
            '实验数据准确，学生能够分析实验结果',
            '达到了实验教学目标，学生收获良好'
        ];
        return $results[array_rand($results)];
    }

    private function generateProblems()
    {
        $problems = [
            '部分实验器材老化，影响实验效果',
            '学生操作不够熟练，需要更多指导',
            '实验时间略显不足，建议延长',
            '个别学生注意力不集中，影响实验进度'
        ];
        return $problems[array_rand($problems)];
    }

    private function generateImprovements()
    {
        $improvements = [
            '建议更新实验器材，提高实验效果',
            '增加预习环节，让学生提前了解实验步骤',
            '优化实验流程，提高实验效率',
            '加强安全教育，确保实验安全'
        ];
        return $improvements[array_rand($improvements)];
    }

    private function generateTeacherNotes()
    {
        $notes = [
            '学生表现良好，实验效果达到预期',
            '需要加强实验前的理论讲解',
            '建议增加实验的趣味性，提高学生参与度',
            '实验组织有序，学生配合度高'
        ];
        return $notes[array_rand($notes)];
    }

    private function generateEquipmentUsed()
    {
        return [
            '显微镜' => rand(5, 15) . '台',
            '试管' => rand(20, 50) . '支',
            '烧杯' => rand(10, 30) . '个',
            '量筒' => rand(5, 20) . '个'
        ];
    }

    private function generateMaterialsConsumed()
    {
        return [
            '试剂A' => rand(10, 100) . 'ml',
            '试剂B' => rand(5, 50) . 'ml',
            '滤纸' => rand(10, 30) . '张',
            '记录表' => rand(20, 40) . '份'
        ];
    }

    private function generateReviewComments($result)
    {
        $comments = [
            'approved' => [
                '实验记录完整，数据准确，符合要求',
                '实验过程规范，结果分析到位',
                '照片清晰，记录详细，质量良好'
            ],
            'rejected' => [
                '实验记录不完整，需要补充',
                '数据记录有误，请重新核实',
                '照片不清晰，无法验证实验过程'
            ],
            'revision_required' => [
                '实验记录基本符合要求，但需要完善部分内容',
                '建议补充实验分析和总结',
                '照片需要重新上传，确保清晰度'
            ]
        ];
        
        $resultComments = $comments[$result] ?? $comments['approved'];
        return $resultComments[array_rand($resultComments)];
    }

    private function generateReviewCriteria()
    {
        return [
            '实验记录完整性' => rand(70, 100),
            '数据准确性' => rand(70, 100),
            '照片质量' => rand(70, 100),
            '安全规范' => rand(80, 100),
            '学生参与度' => rand(70, 100)
        ];
    }

    private function generateAiAnalysis()
    {
        return [
            'photo_quality' => rand(70, 95),
            'safety_compliance' => rand(80, 100),
            'equipment_usage' => rand(75, 95),
            'process_compliance' => rand(70, 90),
            'confidence_score' => rand(80, 95)
        ];
    }

    private function generatePhotoDescription($type)
    {
        $descriptions = [
            'setup' => '实验器材准备阶段',
            'process' => '实验进行过程',
            'result' => '实验结果展示',
            'cleanup' => '实验清理阶段'
        ];
        return $descriptions[$type] ?? '实验照片';
    }

    private function generatePhotoAiAnalysis()
    {
        return [
            'clarity' => rand(70, 95),
            'relevance' => rand(80, 100),
            'safety_visible' => rand(1, 100) <= 80,
            'equipment_visible' => rand(1, 100) <= 90,
            'students_visible' => rand(1, 100) <= 70
        ];
    }
}
