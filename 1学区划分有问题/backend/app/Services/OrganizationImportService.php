<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\OrganizationImportLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class OrganizationImportService
{
    /**
     * 导入组织数据
     */
    public function import($file, $parentId = null, $overwrite = false, $validateOnly = false, User $user = null)
    {
        $importLog = null;

        try {
            // 读取文件内容
            $fileContent = file_get_contents($file->getPathname());
            $lines = explode("\n", $fileContent);

            if (empty($lines)) {
                throw new Exception('文件内容为空或格式不正确');
            }

            // 解析CSV数据
            $rows = [];
            foreach ($lines as $line) {
                if (trim($line)) {
                    $rows[] = str_getcsv(trim($line));
                }
            }

            if (empty($rows)) {
                throw new Exception('文件内容为空或格式不正确');
            }

            $header = array_shift($rows); // 移除表头

            // 验证表头格式
            $this->validateHeader($header);

            // 创建导入日志记录（仅在非验证模式下）
            if (!$validateOnly && $user) {
                $importLog = OrganizationImportLog::create([
                    'filename' => $file->getClientOriginalName(),
                    'user_id' => $user->id,
                    'parent_id' => $parentId,
                    'total_rows' => count($rows),
                    'status' => 'processing'
                ]);
            }

            // 处理数据
            $results = [
                'total' => count($rows),
                'success' => 0,
                'failed' => 0,
                'errors' => [],
                'warnings' => []
            ];

            if (!$validateOnly) {
                DB::beginTransaction();
            }

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // Excel行号（从2开始，因为第1行是表头）
                
                try {
                    $organizationData = $this->parseRowData($row, $header, $parentId);
                    
                    // 验证数据
                    $validator = $this->validateRowData($organizationData);
                    
                    if ($validator->fails()) {
                        $results['failed']++;
                        $results['errors'][] = [
                            'row' => $rowNumber,
                            'errors' => $validator->errors()->all()
                        ];
                        continue;
                    }

                    // 检查权限
                    if ($user && !$user->canAccessOrganization($organizationData['parent_id'] ?? $parentId)) {
                        $results['failed']++;
                        $results['errors'][] = [
                            'row' => $rowNumber,
                            'errors' => ['无权在指定父级组织下创建子组织']
                        ];
                        continue;
                    }

                    if (!$validateOnly) {
                        // 检查是否已存在
                        $existing = Organization::where('code', $organizationData['code'])->first();
                        
                        if ($existing) {
                            if ($overwrite) {
                                $existing->update($organizationData);
                                $results['warnings'][] = [
                                    'row' => $rowNumber,
                                    'message' => "组织 {$organizationData['name']} 已存在，已更新"
                                ];
                            } else {
                                $results['warnings'][] = [
                                    'row' => $rowNumber,
                                    'message' => "组织 {$organizationData['name']} 已存在，已跳过"
                                ];
                            }
                        } else {
                            // 创建新组织
                            $organization = Organization::create($organizationData);
                            $organization->updatePath();
                            $organization->updateChildrenLevel();
                        }
                    }

                    $results['success']++;

                } catch (Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $rowNumber,
                        'errors' => [$e->getMessage()]
                    ];
                    
                    Log::error('Organization import error', [
                        'row' => $rowNumber,
                        'error' => $e->getMessage(),
                        'data' => $row
                    ]);
                }
            }

            if (!$validateOnly) {
                DB::commit();

                // 更新导入日志
                if ($importLog) {
                    $importLog->update([
                        'success_count' => $results['success'],
                        'failed_count' => $results['failed'],
                        'errors' => $results['errors'],
                        'warnings' => $results['warnings'],
                        'status' => 'completed'
                    ]);
                }
            }

            return $results;

        } catch (Exception $e) {
            if (!$validateOnly) {
                DB::rollBack();

                // 更新导入日志为失败状态
                if ($importLog) {
                    $importLog->update([
                        'status' => 'failed',
                        'remarks' => $e->getMessage()
                    ]);
                }
            }

            Log::error('Organization import failed', [
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * 验证表头格式
     */
    private function validateHeader($header)
    {
        $requiredColumns = ['名称', '代码', '类型', '描述', '联系人', '联系电话', '地址'];
        $missingColumns = [];

        foreach ($requiredColumns as $column) {
            if (!in_array($column, $header)) {
                $missingColumns[] = $column;
            }
        }

        if (!empty($missingColumns)) {
            throw new Exception('缺少必需的列: ' . implode(', ', $missingColumns));
        }
    }

    /**
     * 解析行数据
     */
    private function parseRowData($row, $header, $parentId = null)
    {
        $data = array_combine($header, $row);
        
        return [
            'name' => trim($data['名称'] ?? ''),
            'code' => trim($data['代码'] ?? ''),
            'type' => $this->parseType(trim($data['类型'] ?? '')),
            'description' => trim($data['描述'] ?? ''),
            'contact_person' => trim($data['联系人'] ?? ''),
            'contact_phone' => trim($data['联系电话'] ?? ''),
            'address' => trim($data['地址'] ?? ''),
            'parent_id' => $parentId,
            'status' => true,
            'sort_order' => 0
        ];
    }

    /**
     * 解析组织类型
     */
    private function parseType($typeStr)
    {
        $typeMap = [
            '省' => 'province',
            '市' => 'city', 
            '区县' => 'district',
            '学区' => 'education_district',
            '学校' => 'school'
        ];

        return $typeMap[$typeStr] ?? 'school';
    }

    /**
     * 验证行数据
     */
    private function validateRowData($data)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:organizations,code',
            'type' => 'required|in:province,city,district,education_district,school',
            'description' => 'nullable|string|max:500',
            'contact_person' => 'nullable|string|max:50',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:200'
        ];

        return Validator::make($data, $rules);
    }

    /**
     * 生成导入模板
     */
    public function generateTemplate()
    {
        $headers = [
            '名称',
            '代码',
            '类型',
            '描述',
            '联系人',
            '联系电话',
            '地址'
        ];

        $sampleData = [
            [
                '东城小学',
                'DC001',
                '学校',
                '藁城区廉州学区东城小学',
                '张校长',
                '0311-88123456',
                '河北省石家庄市藁城区廉州镇东城村'
            ],
            [
                '西城小学',
                'XC001',
                '学校',
                '藁城区廉州学区西城小学',
                '李校长',
                '0311-88234567',
                '河北省石家庄市藁城区廉州镇西城村'
            ]
        ];

        return [
            'headers' => $headers,
            'sample_data' => $sampleData
        ];
    }

    /**
     * 生成CSV模板内容
     */
    public function generateCsvTemplate()
    {
        $template = $this->generateTemplate();
        $csv = implode(',', $template['headers']) . "\n";

        foreach ($template['sample_data'] as $row) {
            $csv .= implode(',', $row) . "\n";
        }

        return $csv;
    }
}
