<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExperimentReviewLog;
use App\Models\ExperimentRecord;
use App\Models\User;

class ExperimentReviewLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 获取测试数据
        $records = ExperimentRecord::take(3)->get();
        $admins = User::whereIn('user_type', ['admin', 'school_admin'])->take(2)->get();

        if ($records->isEmpty() || $admins->isEmpty()) {
            $this->command->warn('缺少必要的测试数据，请先运行其他 Seeder');
            return;
        }

        $logs = [];

        // 为第一个记录创建完整的审核流程日志
        if ($records->count() > 0) {
            $record = $records->first();
            $admin = $admins->first();

            // 1. 提交审核
            $logs[] = [
                'experiment_record_id' => $record->id,
                'review_type' => 'submit',
                'previous_status' => 'draft',
                'new_status' => 'submitted',
                'review_notes' => '实验记录已完成，提交审核。',
                'reviewer_id' => $record->created_by,
                'reviewer_role' => 'teacher',
                'reviewer_name' => '张老师',
                'review_duration' => 5,
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'organization_id' => $record->organization_id,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2)
            ];

            // 2. AI检查
            $logs[] = [
                'experiment_record_id' => $record->id,
                'review_type' => 'ai_check',
                'previous_status' => 'submitted',
                'new_status' => 'submitted',
                'review_notes' => 'AI自动检查完成，共检查 3 张照片',
                'is_ai_review' => true,
                'ai_analysis_result' => [
                    'total_photos' => 3,
                    'compliant_photos' => 3,
                    'non_compliant_photos' => 0,
                    'compliance_rate' => 100,
                    'check_results' => [
                        ['photo_id' => 1, 'photo_type' => 'preparation', 'compliance_status' => 'compliant'],
                        ['photo_id' => 2, 'photo_type' => 'process', 'compliance_status' => 'compliant'],
                        ['photo_id' => 3, 'photo_type' => 'result', 'compliance_status' => 'compliant']
                    ]
                ],
                'reviewer_id' => $admin->id,
                'reviewer_role' => 'admin',
                'reviewer_name' => 'AI系统',
                'review_duration' => 1,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'AI-System/1.0',
                'organization_id' => $record->organization_id,
                'created_at' => now()->subDays(1)->subHours(2),
                'updated_at' => now()->subDays(1)->subHours(2)
            ];

            // 3. 审核通过
            $logs[] = [
                'experiment_record_id' => $record->id,
                'review_type' => 'approve',
                'previous_status' => 'submitted',
                'new_status' => 'approved',
                'review_notes' => '记录详细完整，实验执行效果良好，同意通过。',
                'review_category' => 'completeness',
                'review_score' => 9,
                'reviewer_id' => $admin->id,
                'reviewer_role' => $admin->user_type,
                'reviewer_name' => $admin->real_name ?? $admin->username,
                'review_duration' => 15,
                'ip_address' => '192.168.1.200',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'organization_id' => $record->organization_id,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1)
            ];
        }

        // 为第二个记录创建要求修改的审核流程
        if ($records->count() > 1) {
            $record = $records->get(1);
            $admin = $admins->count() > 1 ? $admins->get(1) : $admins->first();

            // 1. 提交审核
            $logs[] = [
                'experiment_record_id' => $record->id,
                'review_type' => 'submit',
                'previous_status' => 'draft',
                'new_status' => 'submitted',
                'review_notes' => '实验记录提交审核。',
                'reviewer_id' => $record->created_by,
                'reviewer_role' => 'teacher',
                'reviewer_name' => '李老师',
                'review_duration' => 3,
                'ip_address' => '192.168.1.101',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'organization_id' => $record->organization_id,
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(6)
            ];

            // 2. 要求修改
            $logs[] = [
                'experiment_record_id' => $record->id,
                'review_type' => 'revision_request',
                'previous_status' => 'submitted',
                'new_status' => 'revision_required',
                'review_notes' => '实验记录需要补充完善，请按要求修改后重新提交。',
                'review_category' => 'content',
                'review_details' => [
                    'revision_requirements' => [
                        '补充教学反思',
                        '上传更多照片',
                        '完善实验数据'
                    ],
                    'revision_deadline' => now()->addDays(3)->toISOString()
                ],
                'review_deadline' => now()->addDays(3),
                'reviewer_id' => $admin->id,
                'reviewer_role' => $admin->user_type,
                'reviewer_name' => $admin->real_name ?? $admin->username,
                'review_duration' => 20,
                'ip_address' => '192.168.1.200',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'organization_id' => $record->organization_id,
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2)
            ];
        }

        // 为第三个记录创建拒绝的审核流程
        if ($records->count() > 2) {
            $record = $records->get(2);
            $admin = $admins->first();

            // 1. 提交审核
            $logs[] = [
                'experiment_record_id' => $record->id,
                'review_type' => 'submit',
                'previous_status' => 'draft',
                'new_status' => 'submitted',
                'review_notes' => '实验记录提交审核。',
                'reviewer_id' => $record->created_by,
                'reviewer_role' => 'teacher',
                'reviewer_name' => '王老师',
                'review_duration' => 2,
                'ip_address' => '192.168.1.102',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'organization_id' => $record->organization_id,
                'created_at' => now()->subMinutes(30),
                'updated_at' => now()->subMinutes(30)
            ];
        }

        // 创建一些批量审核的日志
        $batchRecordIds = $records->take(2)->pluck('id')->toArray();
        if (count($batchRecordIds) >= 2) {
            $admin = $admins->first();
            
            foreach ($batchRecordIds as $recordId) {
                $logs[] = [
                    'experiment_record_id' => $recordId,
                    'review_type' => 'batch_approve',
                    'previous_status' => 'submitted',
                    'new_status' => 'approved',
                    'review_notes' => '批量审核通过，记录质量良好。',
                    'review_category' => 'completeness',
                    'review_details' => [
                        'batch_operation' => true,
                        'batch_size' => count($batchRecordIds)
                    ],
                    'reviewer_id' => $admin->id,
                    'reviewer_role' => $admin->user_type,
                    'reviewer_name' => $admin->real_name ?? $admin->username,
                    'review_duration' => 8,
                    'ip_address' => '192.168.1.200',
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'organization_id' => $records->where('id', $recordId)->first()->organization_id,
                    'created_at' => now()->subDays(3),
                    'updated_at' => now()->subDays(3)
                ];
            }
        }

        // 插入所有日志
        foreach ($logs as $logData) {
            ExperimentReviewLog::create($logData);
        }

        $this->command->info('实验审核日志测试数据创建完成！');
        $this->command->info('- 创建了 ' . count($logs) . ' 条审核日志');
        $this->command->info('- 包含不同审核类型：提交、AI检查、通过、拒绝、要求修改、批量操作');
        $this->command->info('- 包含完整的审核流程示例');
    }
}
