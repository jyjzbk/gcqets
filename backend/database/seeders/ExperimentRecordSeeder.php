<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExperimentRecord;
use App\Models\ExperimentPhoto;
use App\Models\ExperimentPlan;
use App\Models\User;

class ExperimentRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 获取测试数据
        $plans = ExperimentPlan::take(3)->get();
        $teachers = User::where('user_type', 'teacher')->take(2)->get();

        if ($plans->isEmpty() || $teachers->isEmpty()) {
            $this->command->warn('缺少必要的测试数据，请先运行其他 Seeder');
            return;
        }

        $records = [
            [
                'experiment_plan_id' => $plans->first()->id,
                'execution_date' => '2025-07-08',
                'start_time' => '09:00',
                'end_time' => '09:45',
                'actual_duration' => 45,
                'actual_student_count' => 34,
                'completion_status' => 'completed',
                'execution_notes' => '实验顺利进行，学生积极参与观察植物根茎叶的结构。通过放大镜观察，学生能够清楚地看到植物各部分的特征。',
                'problems_encountered' => '部分学生在使用放大镜时需要指导，有2个放大镜出现轻微划痕。',
                'solutions_applied' => '安排学习小组互助，更换了有划痕的放大镜。',
                'teaching_reflection' => '学生对植物结构的理解比预期要好，下次可以增加更多的观察内容。实验时间安排合理。',
                'student_feedback' => '学生反映实验很有趣，希望能观察更多不同种类的植物。',
                'equipment_used' => [
                    ['name' => '放大镜', 'quantity' => 10, 'condition' => '良好', 'notes' => '2个有轻微划痕已更换'],
                    ['name' => '镊子', 'quantity' => 10, 'condition' => '良好', 'notes' => '使用正常'],
                    ['name' => '培养皿', 'quantity' => 10, 'condition' => '良好', 'notes' => '清洁完好']
                ],
                'materials_consumed' => [
                    ['name' => '新鲜植物样本', 'quantity' => 10, 'unit' => '份', 'notes' => '包含根茎叶完整结构'],
                    ['name' => '蒸馏水', 'quantity' => 200, 'unit' => 'ml', 'notes' => '用于清洁和保湿']
                ],
                'data_records' => [
                    ['parameter' => '根长度', 'value' => '8-12', 'unit' => 'cm', 'notes' => '平均值'],
                    ['parameter' => '叶片数量', 'value' => '6-8', 'unit' => '片', 'notes' => '每株平均'],
                    ['parameter' => '茎直径', 'value' => '2-3', 'unit' => 'mm', 'notes' => '测量值']
                ],
                'status' => 'approved',
                'equipment_confirmed' => true,
                'equipment_confirmed_at' => now()->subDays(1),
                'organization_id' => $plans->first()->organization_id,
                'created_by' => $teachers->first()->id,
                'reviewed_by' => $teachers->first()->id,
                'reviewed_at' => now(),
                'review_notes' => '记录详细完整，实验执行效果良好，同意通过。'
            ],
            [
                'experiment_plan_id' => $plans->count() > 1 ? $plans->get(1)->id : $plans->first()->id,
                'execution_date' => '2025-07-12',
                'start_time' => '14:00',
                'end_time' => '14:35',
                'actual_duration' => 35,
                'actual_student_count' => 30,
                'completion_status' => 'partial',
                'execution_notes' => '酸碱指示剂实验进行了一半，由于时间不够，部分实验内容延后进行。',
                'problems_encountered' => '实验准备时间过长，导致实际实验时间不足。部分试剂浓度需要调整。',
                'solutions_applied' => '下次课继续完成剩余实验内容，提前准备试剂。',
                'teaching_reflection' => '需要更好地控制实验节奏，提前做好充分准备。',
                'student_feedback' => '学生对变色现象很感兴趣，希望能看到更多的变色实验。',
                'equipment_used' => [
                    ['name' => '试管', 'quantity' => 15, 'condition' => '良好', 'notes' => '使用正常'],
                    ['name' => '试管架', 'quantity' => 5, 'condition' => '良好', 'notes' => '稳定性好'],
                    ['name' => '滴管', 'quantity' => 8, 'condition' => '良好', 'notes' => '2个需要清洁']
                ],
                'materials_consumed' => [
                    ['name' => '石蕊试液', 'quantity' => 30, 'unit' => 'ml', 'notes' => '0.1%浓度'],
                    ['name' => '稀盐酸', 'quantity' => 50, 'unit' => 'ml', 'notes' => '0.1mol/L'],
                    ['name' => '氢氧化钠溶液', 'quantity' => 50, 'unit' => 'ml', 'notes' => '0.1mol/L']
                ],
                'data_records' => [
                    ['parameter' => '酸性溶液颜色', 'value' => '红色', 'unit' => '', 'notes' => '石蕊试液变色'],
                    ['parameter' => '碱性溶液颜色', 'value' => '蓝色', 'unit' => '', 'notes' => '石蕊试液变色'],
                    ['parameter' => '变色时间', 'value' => '2-3', 'unit' => '秒', 'notes' => '反应速度']
                ],
                'status' => 'submitted',
                'equipment_confirmed' => true,
                'equipment_confirmed_at' => now()->subHours(2),
                'organization_id' => $plans->count() > 1 ? $plans->get(1)->organization_id : $plans->first()->organization_id,
                'created_by' => $teachers->count() > 1 ? $teachers->get(1)->id : $teachers->first()->id
            ],
            [
                'experiment_plan_id' => $plans->count() > 2 ? $plans->get(2)->id : $plans->first()->id,
                'execution_date' => '2025-07-15',
                'start_time' => '10:30',
                'end_time' => null,
                'actual_duration' => null,
                'actual_student_count' => 28,
                'completion_status' => 'in_progress',
                'execution_notes' => '简单电路制作实验正在进行中，学生正在组装电路。',
                'problems_encountered' => '部分电池电量不足，需要更换。',
                'solutions_applied' => '已更换新电池，继续实验。',
                'teaching_reflection' => '',
                'student_feedback' => '',
                'equipment_used' => [
                    ['name' => '电池盒', 'quantity' => 8, 'condition' => '良好', 'notes' => '2个电池盒电池需更换'],
                    ['name' => '小灯泡', 'quantity' => 16, 'condition' => '良好', 'notes' => '4个备用'],
                    ['name' => '导线', 'quantity' => 24, 'condition' => '良好', 'notes' => '鳄鱼夹完好']
                ],
                'materials_consumed' => [
                    ['name' => '5号电池', 'quantity' => 16, 'unit' => '节', 'notes' => '1.5V干电池'],
                    ['name' => '开关', 'quantity' => 8, 'unit' => '个', 'notes' => '按钮式开关']
                ],
                'data_records' => [
                    ['parameter' => '电路通路数', 'value' => '6', 'unit' => '个', 'notes' => '成功点亮灯泡'],
                    ['parameter' => '电池电压', 'value' => '3.0', 'unit' => 'V', 'notes' => '两节电池串联']
                ],
                'status' => 'draft',
                'equipment_confirmed' => false,
                'organization_id' => $plans->count() > 2 ? $plans->get(2)->organization_id : $plans->first()->organization_id,
                'created_by' => $teachers->first()->id
            ]
        ];

        foreach ($records as $recordData) {
            $record = ExperimentRecord::create($recordData);
            
            // 为每个记录创建一些示例照片记录（不包含实际文件）
            $photoTypes = ['preparation', 'process', 'result'];
            foreach ($photoTypes as $index => $type) {
                if ($record->status !== 'draft' || $index < 2) { // 草稿状态只创建前两种类型的照片
                    ExperimentPhoto::create([
                        'experiment_record_id' => $record->id,
                        'photo_type' => $type,
                        'file_path' => "experiment_photos/{$record->id}/{$type}/sample_{$type}.jpg",
                        'file_name' => "sample_{$type}.jpg",
                        'original_name' => "实验{$type}照片.jpg",
                        'file_size' => rand(500000, 2000000), // 0.5-2MB
                        'mime_type' => 'image/jpeg',
                        'width' => 1920,
                        'height' => 1080,
                        'upload_method' => $index % 2 === 0 ? 'web' : 'mobile',
                        'description' => $this->getPhotoDescription($type),
                        'compliance_status' => $record->status === 'approved' ? 'compliant' : 'pending',
                        'watermark_applied' => true,
                        'hash' => hash('sha256', "sample_{$type}_{$record->id}"),
                        'organization_id' => $record->organization_id,
                        'created_by' => $record->created_by
                    ]);
                }
            }
            
            // 更新记录的照片数量
            $record->updatePhotoCount();
        }

        $this->command->info('实验记录测试数据创建完成！');
        $this->command->info('- 创建了 3 个测试记录');
        $this->command->info('- 包含不同状态：已通过、已提交、草稿');
        $this->command->info('- 包含不同完成状态：已完成、部分完成、进行中');
        $this->command->info('- 为每个记录创建了示例照片记录');
    }

    /**
     * 获取照片描述
     */
    private function getPhotoDescription($type): string
    {
        $descriptions = [
            'preparation' => '实验前的器材准备情况，所有设备摆放整齐',
            'process' => '学生正在进行实验操作的过程记录',
            'result' => '实验结果展示，可以清楚看到实验现象',
            'equipment' => '实验器材的使用状况记录',
            'safety' => '实验安全措施的执行情况'
        ];

        return $descriptions[$type] ?? '实验照片';
    }
}
