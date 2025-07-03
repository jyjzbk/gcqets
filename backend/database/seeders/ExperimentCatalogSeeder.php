<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExperimentCatalog;
use App\Models\CurriculumStandard;
use App\Models\PhotoTemplate;
use App\Models\CatalogVersion;

class ExperimentCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 创建课程标准示例
        $curriculumStandards = [
            [
                'name' => '小学科学课程标准（2022版）',
                'code' => 'CS_SCI_2022',
                'version' => '2022.1',
                'subject' => 'science',
                'grade' => 'grade3',
                'content' => '小学三年级科学课程标准，注重培养学生的科学思维和实践能力。',
                'learning_objectives' => '1. 培养学生观察能力\n2. 培养学生实验操作能力\n3. 培养学生科学思维',
                'key_concepts' => '物质的性质、生命现象、地球与宇宙',
                'skills_requirements' => '观察、记录、分析、总结',
                'assessment_criteria' => '实验操作规范性、观察记录完整性、分析总结准确性',
                'effective_date' => '2022-09-01',
                'status' => 'active',
                'organization_id' => 1, // 河北省
                'created_by' => 1,
            ],
            [
                'name' => '初中化学课程标准（2022版）',
                'code' => 'CS_CHEM_2022',
                'version' => '2022.1',
                'subject' => 'chemistry',
                'grade' => 'grade9',
                'content' => '初中九年级化学课程标准，重点培养学生化学实验技能和安全意识。',
                'learning_objectives' => '1. 掌握基本化学实验操作\n2. 理解化学反应原理\n3. 培养安全实验意识',
                'key_concepts' => '化学反应、物质组成、化学方程式',
                'skills_requirements' => '实验操作、数据分析、安全防护',
                'assessment_criteria' => '实验安全性、操作准确性、结果分析能力',
                'effective_date' => '2022-09-01',
                'status' => 'active',
                'organization_id' => 1, // 河北省
                'created_by' => 1,
            ]
        ];

        foreach ($curriculumStandards as $standard) {
            CurriculumStandard::create($standard);
        }

        // 创建实验目录示例
        $experiments = [
            [
                'name' => '观察植物的根、茎、叶',
                'code' => 'EXP_SCI_001',
                'subject' => 'science',
                'grade' => 'grade3',
                'textbook_version' => '人教版2022',
                'experiment_type' => 'group',
                'description' => '通过观察不同植物的根、茎、叶，了解植物的基本结构特征。',
                'objectives' => '1. 认识植物的基本结构\n2. 学会使用放大镜观察\n3. 培养观察记录能力',
                'materials' => '放大镜、各种植物标本、记录表、彩色铅笔',
                'procedures' => '1. 准备观察材料\n2. 使用放大镜仔细观察植物的根、茎、叶\n3. 记录观察结果\n4. 对比不同植物的特征\n5. 总结植物结构的共同点和不同点',
                'safety_notes' => '1. 小心使用放大镜，避免阳光直射眼睛\n2. 轻拿轻放植物标本\n3. 保持实验台面整洁',
                'duration_minutes' => 40,
                'student_count' => 4,
                'difficulty_level' => 'easy',
                'status' => 'active',
                'curriculum_standard_id' => 1,
                'organization_id' => 5, // 东城小学
                'created_by' => 3, // 刘校长
                'extra_data' => [
                    'season' => 'spring',
                    'location' => 'classroom',
                    'equipment_needed' => ['magnifier', 'specimens', 'worksheets']
                ]
            ],
            [
                'name' => '酸碱指示剂的变色实验',
                'code' => 'EXP_CHEM_001',
                'subject' => 'chemistry',
                'grade' => 'grade9',
                'textbook_version' => '人教版2022',
                'experiment_type' => 'demonstration',
                'description' => '通过酸碱指示剂在不同溶液中的变色现象，认识酸碱性质。',
                'objectives' => '1. 认识常见的酸碱指示剂\n2. 观察指示剂的变色现象\n3. 理解酸碱的概念',
                'materials' => '石蕊试液、酚酞试液、稀盐酸、稀氢氧化钠溶液、试管、滴管',
                'procedures' => '1. 准备实验器材和试剂\n2. 在试管中分别加入稀盐酸和稀氢氧化钠溶液\n3. 分别滴入石蕊试液，观察颜色变化\n4. 重复步骤，使用酚酞试液\n5. 记录实验现象\n6. 分析实验结果',
                'safety_notes' => '1. 穿戴实验服和护目镜\n2. 小心使用酸碱溶液，避免接触皮肤\n3. 实验后及时清洗器材\n4. 保持实验室通风',
                'duration_minutes' => 45,
                'student_count' => 30,
                'difficulty_level' => 'medium',
                'status' => 'active',
                'curriculum_standard_id' => 2,
                'organization_id' => 5, // 东城小学
                'created_by' => 3, // 刘校长
                'extra_data' => [
                    'safety_level' => 'medium',
                    'location' => 'chemistry_lab',
                    'equipment_needed' => ['test_tubes', 'droppers', 'indicators', 'solutions']
                ]
            ]
        ];

        foreach ($experiments as $experiment) {
            $catalog = ExperimentCatalog::create($experiment);
            
            // 为每个实验目录创建初始版本记录
            CatalogVersion::createVersion(
                $catalog->id,
                'create',
                ['action' => '创建实验目录'],
                '初始创建',
                $experiment['created_by']
            );
        }

        // 创建照片模板示例
        $photoTemplates = [
            [
                'name' => '小学科学观察实验照片模板',
                'code' => 'PT_SCI_OBS',
                'subject' => 'science',
                'grade' => 'grade3',
                'textbook_version' => '人教版2022',
                'experiment_type' => 'group',
                'description' => '适用于小学科学观察类实验的照片拍摄模板',
                'required_photos' => [
                    [
                        'name' => '实验材料准备',
                        'description' => '展示实验所需的所有材料',
                        'timing' => 'before',
                        'angle' => 'overview'
                    ],
                    [
                        'name' => '学生观察过程',
                        'description' => '学生使用放大镜观察的过程',
                        'timing' => 'during',
                        'angle' => 'close-up'
                    ],
                    [
                        'name' => '观察记录结果',
                        'description' => '学生记录的观察结果',
                        'timing' => 'after',
                        'angle' => 'document'
                    ]
                ],
                'optional_photos' => [
                    [
                        'name' => '小组讨论',
                        'description' => '学生小组讨论实验结果',
                        'timing' => 'after',
                        'angle' => 'group'
                    ]
                ],
                'photo_specifications' => [
                    'resolution' => '1920x1080',
                    'format' => 'JPG',
                    'quality' => 'high',
                    'lighting' => 'natural_light_preferred'
                ],
                'status' => 'active',
                'organization_id' => 5, // 东城小学
                'created_by' => 3, // 刘校长
            ],
            [
                'name' => '初中化学实验照片模板',
                'code' => 'PT_CHEM_EXP',
                'subject' => 'chemistry',
                'grade' => 'grade9',
                'textbook_version' => '人教版2022',
                'experiment_type' => 'demonstration',
                'description' => '适用于初中化学演示实验的照片拍摄模板',
                'required_photos' => [
                    [
                        'name' => '实验器材准备',
                        'description' => '实验前器材和试剂的准备情况',
                        'timing' => 'before',
                        'angle' => 'overview'
                    ],
                    [
                        'name' => '实验操作过程',
                        'description' => '教师演示实验操作的关键步骤',
                        'timing' => 'during',
                        'angle' => 'close-up'
                    ],
                    [
                        'name' => '实验现象记录',
                        'description' => '实验中出现的颜色变化等现象',
                        'timing' => 'during',
                        'angle' => 'close-up'
                    ],
                    [
                        'name' => '安全防护措施',
                        'description' => '实验中的安全防护措施展示',
                        'timing' => 'during',
                        'angle' => 'safety'
                    ]
                ],
                'optional_photos' => [
                    [
                        'name' => '学生观察反应',
                        'description' => '学生观察实验现象的反应',
                        'timing' => 'during',
                        'angle' => 'audience'
                    ],
                    [
                        'name' => '实验总结讨论',
                        'description' => '实验后的总结和讨论',
                        'timing' => 'after',
                        'angle' => 'classroom'
                    ]
                ],
                'photo_specifications' => [
                    'resolution' => '1920x1080',
                    'format' => 'JPG',
                    'quality' => 'high',
                    'lighting' => 'lab_lighting',
                    'safety_note' => 'ensure_no_hazardous_materials_visible'
                ],
                'status' => 'active',
                'organization_id' => 5, // 东城小学
                'created_by' => 3, // 刘校长
            ]
        ];

        foreach ($photoTemplates as $template) {
            PhotoTemplate::create($template);
        }

        $this->command->info('实验目录示例数据创建完成！');
        $this->command->info('- 创建了 2 个课程标准');
        $this->command->info('- 创建了 2 个实验目录');
        $this->command->info('- 创建了 2 个照片模板');
        $this->command->info('- 创建了对应的版本记录');
    }
}
