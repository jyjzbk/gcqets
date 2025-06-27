<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\SchoolImportLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Exception;

class SchoolImportService
{
    /**
     * 支持的文件类型
     */
    const SUPPORTED_EXTENSIONS = ['xlsx', 'xls', 'csv'];

    /**
     * 学校类型映射
     */
    const SCHOOL_TYPES = [
        '小学' => 'primary',
        '初中' => 'junior_high',
        '高中' => 'senior_high',
        '九年一贯制' => 'nine_year',
        '完全中学' => 'complete_middle',
        '职业学校' => 'vocational',
        '特殊教育学校' => 'special_education'
    ];

    /**
     * 教育层次映射
     */
    const EDUCATION_LEVELS = [
        '学前教育' => 'preschool',
        '小学教育' => 'primary',
        '初中教育' => 'junior_high',
        '高中教育' => 'senior_high',
        '中等职业教育' => 'vocational',
        '特殊教育' => 'special'
    ];

    /**
     * 导入学校数据
     */
    public function import($file, $parentId = null, $overwrite = false, $validateOnly = false, User $user = null)
    {
        $importLog = null;
        
        try {
            // 创建导入日志
            if (!$validateOnly) {
                $importLog = SchoolImportLog::create([
                    'filename' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'file_path' => $file->store('imports/schools', 'public'),
                    'parent_id' => $parentId,
                    'user_id' => $user ? $user->id : null,
                    'status' => 'processing',
                    'import_options' => [
                        'overwrite' => $overwrite,
                        'validate_only' => $validateOnly
                    ]
                ]);
            }

            // 读取文件数据
            $data = $this->readFile($file);
            
            if (empty($data)) {
                throw new Exception('文件为空或格式不正确');
            }

            // 验证表头
            $header = array_shift($data);
            $this->validateHeader($header);

            // 处理数据
            $results = [
                'total' => count($data),
                'success' => 0,
                'failed' => 0,
                'errors' => [],
                'warnings' => [],
                'import_log_id' => $importLog ? $importLog->id : null
            ];

            if (!$validateOnly) {
                DB::beginTransaction();
            }

            foreach ($data as $index => $row) {
                $rowNumber = $index + 2; // Excel行号（从2开始，因为第1行是表头）
                
                try {
                    $schoolData = $this->parseRowData($row, $header, $parentId);
                    
                    // 验证数据
                    $validator = $this->validateSchoolData($schoolData);
                    
                    if ($validator->fails()) {
                        $results['failed']++;
                        $results['errors'][] = [
                            'row' => $rowNumber,
                            'data' => $schoolData,
                            'errors' => $validator->errors()->all()
                        ];
                        continue;
                    }

                    // 检查权限
                    if ($user && !$this->checkUserPermission($user, $schoolData['parent_id'] ?? $parentId)) {
                        $results['failed']++;
                        $results['errors'][] = [
                            'row' => $rowNumber,
                            'data' => $schoolData,
                            'errors' => ['无权在指定父级组织下创建学校']
                        ];
                        continue;
                    }

                    // 检查重复
                    $existingSchool = $this->findExistingSchool($schoolData);
                    if ($existingSchool && !$overwrite) {
                        $results['warnings'][] = [
                            'row' => $rowNumber,
                            'data' => $schoolData,
                            'message' => "学校已存在：{$existingSchool->name} (代码: {$existingSchool->code})"
                        ];
                        continue;
                    }

                    if (!$validateOnly) {
                        // 创建或更新学校
                        if ($existingSchool && $overwrite) {
                            $existingSchool->update($schoolData);
                            $results['success']++;
                        } else {
                            Organization::create($schoolData);
                            $results['success']++;
                        }
                    } else {
                        $results['success']++;
                    }

                } catch (Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $rowNumber,
                        'data' => $schoolData ?? [],
                        'errors' => [$e->getMessage()]
                    ];
                    
                    Log::error('学校导入行处理失败', [
                        'row' => $rowNumber,
                        'error' => $e->getMessage(),
                        'data' => $schoolData ?? []
                    ]);
                }
            }

            if (!$validateOnly) {
                if ($results['failed'] > 0 && $results['success'] == 0) {
                    DB::rollBack();
                    throw new Exception('所有数据导入失败');
                } else {
                    DB::commit();
                }

                // 更新导入日志
                if ($importLog) {
                    $importLog->update([
                        'status' => $results['failed'] > 0 ? 'partial_success' : 'success',
                        'total_rows' => $results['total'],
                        'success_rows' => $results['success'],
                        'failed_rows' => $results['failed'],
                        'error_details' => $results['errors'],
                        'warning_details' => $results['warnings'],
                        'completed_at' => now()
                    ]);
                }
            }

            return $results;

        } catch (Exception $e) {
            if (!$validateOnly) {
                DB::rollBack();
            }

            if ($importLog) {
                $importLog->update([
                    'status' => 'failed',
                    'error_details' => [['error' => $e->getMessage()]],
                    'completed_at' => now()
                ]);
            }

            Log::error('学校导入失败', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'user_id' => $user ? $user->id : null
            ]);

            throw $e;
        }
    }

    /**
     * 读取文件数据
     */
    private function readFile($file)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, self::SUPPORTED_EXTENSIONS)) {
            throw new Exception('不支持的文件格式，请使用 Excel (.xlsx, .xls) 或 CSV (.csv) 文件');
        }

        try {
            if ($extension === 'csv') {
                return $this->readCsvFile($file);
            } else {
                return $this->readExcelFile($file);
            }
        } catch (Exception $e) {
            throw new Exception('文件读取失败：' . $e->getMessage());
        }
    }

    /**
     * 读取CSV文件
     */
    private function readCsvFile($file)
    {
        $data = [];
        $handle = fopen($file->getRealPath(), 'r');
        
        if ($handle !== false) {
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $data[] = array_map('trim', $row);
            }
            fclose($handle);
        }
        
        return $data;
    }

    /**
     * 读取Excel文件
     */
    private function readExcelFile($file)
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();
        $data = [];
        
        foreach ($worksheet->getRowIterator() as $row) {
            $rowData = [];
            foreach ($row->getCellIterator() as $cell) {
                $rowData[] = trim($cell->getCalculatedValue());
            }
            $data[] = $rowData;
        }
        
        return $data;
    }

    /**
     * 验证表头
     */
    private function validateHeader($header)
    {
        $requiredHeaders = [
            '学校名称', '学校代码', '学校类型', '教育层次', '校长姓名',
            '校长电话', '联系人', '联系电话', '学校地址'
        ];

        $missingHeaders = [];
        foreach ($requiredHeaders as $required) {
            if (!in_array($required, $header)) {
                $missingHeaders[] = $required;
            }
        }

        if (!empty($missingHeaders)) {
            throw new Exception('缺少必需的表头字段：' . implode(', ', $missingHeaders));
        }
    }

    /**
     * 解析行数据
     */
    private function parseRowData($row, $header, $parentId)
    {
        $data = array_combine($header, $row);

        // 获取父级组织信息
        $parent = null;
        if ($parentId) {
            $parent = Organization::find($parentId);
            if (!$parent) {
                throw new Exception('指定的父级组织不存在');
            }
        }

        // 解析学校类型
        $schoolType = $this->parseSchoolType($data['学校类型'] ?? '');
        $educationLevel = $this->parseEducationLevel($data['教育层次'] ?? '');

        return [
            'name' => $data['学校名称'] ?? '',
            'code' => $data['学校代码'] ?? '',
            'school_code' => $data['学校代码'] ?? '',
            'type' => 'school',
            'school_type' => $schoolType,
            'education_level' => $educationLevel,
            'parent_id' => $parentId,
            'level' => $parent ? $parent->level + 1 : 5, // 学校通常是最底层
            'description' => $data['学校描述'] ?? '',
            'status' => 'active',

            // 学校特有字段
            'principal_name' => $data['校长姓名'] ?? '',
            'principal_phone' => $data['校长电话'] ?? '',
            'principal_email' => $data['校长邮箱'] ?? '',
            'contact_person' => $data['联系人'] ?? '',
            'contact_phone' => $data['联系电话'] ?? '',
            'address' => $data['学校地址'] ?? '',
            'student_count' => $this->parseInteger($data['学生人数'] ?? ''),
            'campus_area' => $this->parseDecimal($data['校园面积'] ?? ''),
            'founded_year' => $this->parseInteger($data['建校年份'] ?? ''),
            'longitude' => $this->parseDecimal($data['经度'] ?? ''),
            'latitude' => $this->parseDecimal($data['纬度'] ?? ''),

            // 额外信息
            'extra_data' => [
                'class_count' => $this->parseInteger($data['班级数'] ?? ''),
                'teacher_count' => $this->parseInteger($data['教师人数'] ?? ''),
                'facilities' => $data['设施设备'] ?? '',
                'special_programs' => $data['特色项目'] ?? ''
            ]
        ];
    }

    /**
     * 解析学校类型
     */
    private function parseSchoolType($type)
    {
        return self::SCHOOL_TYPES[$type] ?? 'primary';
    }

    /**
     * 解析教育层次
     */
    private function parseEducationLevel($level)
    {
        return self::EDUCATION_LEVELS[$level] ?? 'primary';
    }

    /**
     * 解析整数
     */
    private function parseInteger($value)
    {
        if (empty($value) || !is_numeric($value)) {
            return null;
        }
        return (int) $value;
    }

    /**
     * 解析小数
     */
    private function parseDecimal($value)
    {
        if (empty($value) || !is_numeric($value)) {
            return null;
        }
        return (float) $value;
    }

    /**
     * 验证学校数据
     */
    private function validateSchoolData($data)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:organizations,code',
            'school_code' => 'required|string|max:50',
            'school_type' => 'required|string|in:' . implode(',', array_values(self::SCHOOL_TYPES)),
            'education_level' => 'required|string|in:' . implode(',', array_values(self::EDUCATION_LEVELS)),
            'principal_name' => 'required|string|max:100',
            'principal_phone' => 'required|string|max:20',
            'principal_email' => 'nullable|email|max:255',
            'contact_person' => 'required|string|max:100',
            'contact_phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'student_count' => 'nullable|integer|min:0',
            'campus_area' => 'nullable|numeric|min:0',
            'founded_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'longitude' => 'nullable|numeric|between:-180,180',
            'latitude' => 'nullable|numeric|between:-90,90'
        ];

        $messages = [
            'name.required' => '学校名称不能为空',
            'name.max' => '学校名称不能超过255个字符',
            'code.required' => '学校代码不能为空',
            'code.unique' => '学校代码已存在',
            'code.max' => '学校代码不能超过50个字符',
            'school_code.required' => '学校代码不能为空',
            'school_type.required' => '学校类型不能为空',
            'school_type.in' => '学校类型不正确',
            'education_level.required' => '教育层次不能为空',
            'education_level.in' => '教育层次不正确',
            'principal_name.required' => '校长姓名不能为空',
            'principal_phone.required' => '校长电话不能为空',
            'principal_email.email' => '校长邮箱格式不正确',
            'contact_person.required' => '联系人不能为空',
            'contact_phone.required' => '联系电话不能为空',
            'address.required' => '学校地址不能为空',
            'student_count.integer' => '学生人数必须是整数',
            'student_count.min' => '学生人数不能小于0',
            'campus_area.numeric' => '校园面积必须是数字',
            'campus_area.min' => '校园面积不能小于0',
            'founded_year.integer' => '建校年份必须是整数',
            'founded_year.min' => '建校年份不能早于1900年',
            'founded_year.max' => '建校年份不能晚于当前年份',
            'longitude.between' => '经度必须在-180到180之间',
            'latitude.between' => '纬度必须在-90到90之间'
        ];

        return Validator::make($data, $rules, $messages);
    }

    /**
     * 检查用户权限
     */
    private function checkUserPermission(User $user, $organizationId)
    {
        if (!$organizationId) {
            return false;
        }

        // 检查用户是否有权限在指定组织下创建学校
        $accessScope = $user->getDataAccessScope();

        if ($accessScope['type'] === 'all') {
            return true;
        }

        if ($accessScope['type'] === 'specific') {
            return in_array($organizationId, $accessScope['organizations']);
        }

        return false;
    }

    /**
     * 查找已存在的学校
     */
    private function findExistingSchool($data)
    {
        return Organization::where('type', 'school')
            ->where(function ($query) use ($data) {
                $query->where('code', $data['code'])
                      ->orWhere('school_code', $data['school_code']);
            })
            ->first();
    }

    /**
     * 生成学校导入模板
     */
    public function generateTemplate()
    {
        $headers = [
            '学校名称',
            '学校代码',
            '学校类型',
            '教育层次',
            '校长姓名',
            '校长电话',
            '校长邮箱',
            '联系人',
            '联系电话',
            '学校地址',
            '学生人数',
            '班级数',
            '教师人数',
            '校园面积',
            '建校年份',
            '经度',
            '纬度',
            '设施设备',
            '特色项目',
            '学校描述'
        ];

        $sampleData = [
            [
                '东城小学',
                'DC001',
                '小学',
                '小学教育',
                '张校长',
                '0311-88123456',
                'principal@dongcheng.edu.cn',
                '李老师',
                '0311-88123457',
                '河北省石家庄市藁城区廉州镇东城村',
                '500',
                '18',
                '35',
                '15000',
                '1985',
                '114.8493',
                '38.0428',
                '教学楼、实验室、图书馆、体育馆',
                '书法特色、科技创新',
                '藁城区廉州学区东城小学，办学历史悠久'
            ],
            [
                '西城小学',
                'XC001',
                '小学',
                '小学教育',
                '王校长',
                '0311-88234567',
                'principal@xicheng.edu.cn',
                '赵老师',
                '0311-88234568',
                '河北省石家庄市藁城区廉州镇西城村',
                '420',
                '15',
                '28',
                '12000',
                '1990',
                '114.8393',
                '38.0328',
                '教学楼、多媒体教室、操场',
                '音乐特色、体育强项',
                '藁城区廉州学区西城小学，注重全面发展'
            ]
        ];

        return [
            'headers' => $headers,
            'sample_data' => $sampleData,
            'school_types' => array_keys(self::SCHOOL_TYPES),
            'education_levels' => array_keys(self::EDUCATION_LEVELS)
        ];
    }

    /**
     * 生成Excel模板文件
     */
    public function generateExcelTemplate()
    {
        $template = $this->generateTemplate();

        // 这里可以使用PhpSpreadsheet生成更复杂的Excel模板
        // 包括数据验证、下拉列表等
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        // 设置表头
        $worksheet->fromArray($template['headers'], null, 'A1');

        // 设置样本数据
        $worksheet->fromArray($template['sample_data'], null, 'A2');

        // 设置列宽
        foreach (range('A', 'T') as $column) {
            $worksheet->getColumnDimension($column)->setAutoSize(true);
        }

        // 设置表头样式
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2EFDA']
            ]
        ];
        $worksheet->getStyle('A1:T1')->applyFromArray($headerStyle);

        return $spreadsheet;
    }

    /**
     * 生成CSV模板内容
     */
    public function generateCsvTemplate()
    {
        $template = $this->generateTemplate();

        // 添加BOM以支持中文
        $csv = "\xEF\xBB\xBF";
        $csv .= implode(',', $template['headers']) . "\n";

        foreach ($template['sample_data'] as $row) {
            $csv .= implode(',', array_map(function($field) {
                return '"' . str_replace('"', '""', $field) . '"';
            }, $row)) . "\n";
        }

        return $csv;
    }

    /**
     * 获取导入统计信息
     */
    public function getImportStats($userId = null, $days = 30)
    {
        $query = SchoolImportLog::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $query->where('created_at', '>=', now()->subDays($days));

        $logs = $query->get();

        $totalImports = $logs->count();
        $successfulImports = $logs->where('status', 'success')->count();
        $failedImports = $logs->where('status', 'failed')->count();
        $partialSuccessImports = $logs->where('status', 'partial_success')->count();
        $totalSchoolsImported = $logs->sum('success_rows');
        $totalErrors = $logs->sum('failed_rows');

        return [
            'basic_stats' => [
                'total_imports' => $totalImports,
                'successful_imports' => $successfulImports,
                'failed_imports' => $failedImports,
                'partial_success_imports' => $partialSuccessImports,
                'total_schools_imported' => $totalSchoolsImported,
                'total_errors' => $totalErrors,
                'success_rate' => $totalImports > 0 ? round(($successfulImports / $totalImports) * 100, 2) : 0,
                'average_schools_per_import' => $totalImports > 0 ? round($totalSchoolsImported / $totalImports, 1) : 0
            ],
            'trend_data' => $this->getImportTrendData($userId, $days),
            'error_analysis' => $this->getErrorAnalysis($userId, $days),
            'user_stats' => $this->getUserImportStats($userId, $days),
            'file_stats' => $this->getFileTypeStats($userId, $days),
            'time_analysis' => $this->getTimeAnalysis($userId, $days)
        ];
    }

    /**
     * 获取导入趋势数据
     */
    private function getImportTrendData($userId = null, $days = 30)
    {
        $query = SchoolImportLog::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $query->where('created_at', '>=', now()->subDays($days));

        $logs = $query->orderBy('created_at')->get();

        $trendData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayLogs = $logs->filter(function($log) use ($date) {
                return $log->created_at->format('Y-m-d') === $date;
            });

            $trendData[] = [
                'date' => $date,
                'total_imports' => $dayLogs->count(),
                'successful_imports' => $dayLogs->where('status', 'success')->count(),
                'failed_imports' => $dayLogs->where('status', 'failed')->count(),
                'schools_imported' => $dayLogs->sum('success_rows'),
                'errors' => $dayLogs->sum('failed_rows')
            ];
        }

        return $trendData;
    }

    /**
     * 获取错误分析
     */
    private function getErrorAnalysis($userId = null, $days = 30)
    {
        $query = SchoolImportLog::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $query->where('created_at', '>=', now()->subDays($days))
              ->whereNotNull('error_details');

        $logs = $query->get();

        $errorTypes = [];
        $errorFrequency = [];

        foreach ($logs as $log) {
            if (!empty($log->error_details)) {
                foreach ($log->error_details as $error) {
                    if (isset($error['errors'])) {
                        foreach ($error['errors'] as $errorMsg) {
                            // 分类错误类型
                            $errorType = $this->categorizeError($errorMsg);
                            $errorTypes[$errorType] = ($errorTypes[$errorType] ?? 0) + 1;

                            // 统计具体错误频率
                            $errorKey = substr($errorMsg, 0, 50);
                            $errorFrequency[$errorKey] = ($errorFrequency[$errorKey] ?? 0) + 1;
                        }
                    }
                }
            }
        }

        // 排序并取前10
        arsort($errorTypes);
        arsort($errorFrequency);

        return [
            'error_types' => array_slice($errorTypes, 0, 10, true),
            'error_frequency' => array_slice($errorFrequency, 0, 10, true),
            'total_error_logs' => $logs->count()
        ];
    }

    /**
     * 获取用户导入统计
     */
    private function getUserImportStats($userId = null, $days = 30)
    {
        if ($userId) {
            // 如果指定了用户，返回该用户的详细统计
            $user = User::find($userId);
            if (!$user) {
                return null;
            }

            $logs = SchoolImportLog::where('user_id', $userId)
                ->where('created_at', '>=', now()->subDays($days))
                ->get();

            return [
                'user_name' => $user->real_name,
                'total_imports' => $logs->count(),
                'success_rate' => $logs->count() > 0 ? round(($logs->where('status', 'success')->count() / $logs->count()) * 100, 2) : 0,
                'total_schools' => $logs->sum('success_rows'),
                'avg_schools_per_import' => $logs->count() > 0 ? round($logs->sum('success_rows') / $logs->count(), 1) : 0
            ];
        } else {
            // 返回所有用户的统计排名
            $userStats = SchoolImportLog::select('user_id')
                ->selectRaw('COUNT(*) as import_count')
                ->selectRaw('SUM(success_rows) as total_schools')
                ->selectRaw('AVG(success_rows) as avg_schools')
                ->selectRaw('COUNT(CASE WHEN status = "success" THEN 1 END) as success_count')
                ->where('created_at', '>=', now()->subDays($days))
                ->whereNotNull('user_id')
                ->groupBy('user_id')
                ->orderByDesc('import_count')
                ->limit(10)
                ->with('user:id,real_name')
                ->get();

            return $userStats->map(function($stat) {
                return [
                    'user_name' => $stat->user->real_name ?? '未知用户',
                    'import_count' => $stat->import_count,
                    'total_schools' => $stat->total_schools,
                    'avg_schools' => round($stat->avg_schools, 1),
                    'success_rate' => $stat->import_count > 0 ? round(($stat->success_count / $stat->import_count) * 100, 2) : 0
                ];
            });
        }
    }

    /**
     * 获取文件类型统计
     */
    private function getFileTypeStats($userId = null, $days = 30)
    {
        $query = SchoolImportLog::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $logs = $query->where('created_at', '>=', now()->subDays($days))->get();

        $fileTypes = [];
        foreach ($logs as $log) {
            $extension = pathinfo($log->filename, PATHINFO_EXTENSION);
            $fileTypes[$extension] = ($fileTypes[$extension] ?? 0) + 1;
        }

        return $fileTypes;
    }

    /**
     * 获取时间分析
     */
    private function getTimeAnalysis($userId = null, $days = 30)
    {
        $query = SchoolImportLog::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $logs = $query->where('created_at', '>=', now()->subDays($days))->get();

        $hourlyStats = array_fill(0, 24, 0);
        $weeklyStats = array_fill(0, 7, 0);

        foreach ($logs as $log) {
            $hour = $log->created_at->hour;
            $dayOfWeek = $log->created_at->dayOfWeek;

            $hourlyStats[$hour]++;
            $weeklyStats[$dayOfWeek]++;
        }

        return [
            'hourly_distribution' => $hourlyStats,
            'weekly_distribution' => $weeklyStats,
            'peak_hour' => array_search(max($hourlyStats), $hourlyStats),
            'peak_day' => array_search(max($weeklyStats), $weeklyStats)
        ];
    }

    /**
     * 分类错误类型
     */
    private function categorizeError($errorMsg)
    {
        if (strpos($errorMsg, '不能为空') !== false || strpos($errorMsg, 'required') !== false) {
            return '必填字段缺失';
        } elseif (strpos($errorMsg, '已存在') !== false || strpos($errorMsg, 'unique') !== false) {
            return '数据重复';
        } elseif (strpos($errorMsg, '格式') !== false || strpos($errorMsg, 'format') !== false) {
            return '格式错误';
        } elseif (strpos($errorMsg, '长度') !== false || strpos($errorMsg, 'max') !== false) {
            return '长度超限';
        } elseif (strpos($errorMsg, '权限') !== false || strpos($errorMsg, 'permission') !== false) {
            return '权限不足';
        } else {
            return '其他错误';
        }
    }

    /**
     * 预览导入数据
     */
    public function previewImport($file, $parentId = null, User $user = null, $limit = 10)
    {
        try {
            // 读取文件数据
            $data = $this->readFile($file);

            if (empty($data)) {
                throw new Exception('文件为空或格式不正确');
            }

            // 验证表头
            $header = array_shift($data);
            $this->validateHeader($header);

            // 限制预览行数
            $previewData = array_slice($data, 0, $limit);

            $results = [
                'total_rows' => count($data),
                'preview_rows' => count($previewData),
                'headers' => $header,
                'data' => [],
                'validation_summary' => [
                    'valid' => 0,
                    'invalid' => 0,
                    'warnings' => 0
                ],
                'errors' => [],
                'warnings' => []
            ];

            foreach ($previewData as $index => $row) {
                $rowNumber = $index + 2; // Excel行号（从2开始，因为第1行是表头）

                try {
                    $schoolData = $this->parseRowData($row, $header, $parentId);

                    // 验证数据
                    $validator = $this->validateSchoolData($schoolData);

                    $rowResult = [
                        'row_number' => $rowNumber,
                        'data' => $schoolData,
                        'original_data' => array_combine($header, $row),
                        'is_valid' => true,
                        'errors' => [],
                        'warnings' => []
                    ];

                    if ($validator->fails()) {
                        $rowResult['is_valid'] = false;
                        $rowResult['errors'] = $validator->errors()->all();
                        $results['validation_summary']['invalid']++;

                        $results['errors'][] = [
                            'row' => $rowNumber,
                            'errors' => $validator->errors()->all()
                        ];
                    } else {
                        $results['validation_summary']['valid']++;
                    }

                    // 检查权限
                    if ($user && !$this->checkUserPermission($user, $schoolData['parent_id'] ?? $parentId)) {
                        $rowResult['warnings'][] = '无权在指定父级组织下创建学校';
                        $results['validation_summary']['warnings']++;

                        $results['warnings'][] = [
                            'row' => $rowNumber,
                            'message' => '无权在指定父级组织下创建学校'
                        ];
                    }

                    // 检查重复
                    $existingSchool = $this->findExistingSchool($schoolData);
                    if ($existingSchool) {
                        $rowResult['warnings'][] = "学校已存在：{$existingSchool->name} (代码: {$existingSchool->code})";
                        $results['validation_summary']['warnings']++;

                        $results['warnings'][] = [
                            'row' => $rowNumber,
                            'message' => "学校已存在：{$existingSchool->name} (代码: {$existingSchool->code})"
                        ];
                    }

                    $results['data'][] = $rowResult;

                } catch (Exception $e) {
                    $results['validation_summary']['invalid']++;
                    $results['errors'][] = [
                        'row' => $rowNumber,
                        'errors' => [$e->getMessage()]
                    ];

                    $results['data'][] = [
                        'row_number' => $rowNumber,
                        'data' => [],
                        'original_data' => array_combine($header, $row),
                        'is_valid' => false,
                        'errors' => [$e->getMessage()],
                        'warnings' => []
                    ];
                }
            }

            // 添加文件信息
            $results['file_info'] = [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'type' => $file->getClientOriginalExtension(),
                'mime_type' => $file->getMimeType()
            ];

            return $results;

        } catch (Exception $e) {
            Log::error('学校导入预览失败', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'user_id' => $user ? $user->id : null
            ]);

            throw $e;
        }
    }

    /**
     * 分析导入文件
     */
    public function analyzeImportFile($file, $parentId = null, User $user = null)
    {
        try {
            // 读取文件数据
            $data = $this->readFile($file);

            if (empty($data)) {
                throw new Exception('文件为空或格式不正确');
            }

            // 验证表头
            $header = array_shift($data);
            $this->validateHeader($header);

            $analysis = [
                'file_info' => [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'formatted_size' => $this->formatFileSize($file->getSize()),
                    'type' => $file->getClientOriginalExtension(),
                    'mime_type' => $file->getMimeType(),
                    'encoding' => $this->detectFileEncoding($file),
                    'last_modified' => date('Y-m-d H:i:s', filemtime($file->getRealPath()))
                ],
                'data_summary' => [
                    'total_rows' => count($data),
                    'total_columns' => count($header),
                    'headers' => $header,
                    'empty_rows' => 0,
                    'duplicate_rows' => 0
                ],
                'validation_results' => [
                    'valid_rows' => 0,
                    'invalid_rows' => 0,
                    'warning_rows' => 0,
                    'duplicate_codes' => [],
                    'duplicate_names' => [],
                    'missing_required_fields' => [],
                    'format_errors' => [],
                    'data_inconsistencies' => []
                ],
                'data_quality' => [
                    'completeness' => [],
                    'uniqueness' => [],
                    'validity' => [],
                    'consistency' => [],
                    'accuracy' => []
                ],
                'recommendations' => []
            ];

            // 分析数据质量
            $schoolCodes = [];
            $schoolNames = [];
            $phoneNumbers = [];
            $requiredFields = ['学校名称', '学校代码', '学校类型', '教育层次', '校长姓名', '校长电话', '联系人', '联系电话', '学校地址'];
            $emptyRowCount = 0;
            $duplicateRowHashes = [];

            foreach ($data as $index => $row) {
                $rowNumber = $index + 2;
                $rowData = array_combine($header, $row);

                // 检查空行
                if ($this->isEmptyRow($row)) {
                    $emptyRowCount++;
                    continue;
                }

                // 检查重复行
                $rowHash = md5(serialize($row));
                if (in_array($rowHash, $duplicateRowHashes)) {
                    $analysis['data_summary']['duplicate_rows']++;
                } else {
                    $duplicateRowHashes[] = $rowHash;
                }

                try {
                    $schoolData = $this->parseRowData($row, $header, $parentId);
                    $validator = $this->validateSchoolData($schoolData);

                    if ($validator->fails()) {
                        $analysis['validation_results']['invalid_rows']++;
                        foreach ($validator->errors()->all() as $error) {
                            $analysis['validation_results']['format_errors'][] = [
                                'row' => $rowNumber,
                                'error' => $error
                            ];
                        }
                    } else {
                        $analysis['validation_results']['valid_rows']++;
                    }

                    // 检查重复学校代码
                    $code = $schoolData['code'];
                    if (in_array($code, $schoolCodes)) {
                        $existingIndex = array_search($code, $schoolCodes);
                        $analysis['validation_results']['duplicate_codes'][] = [
                            'code' => $code,
                            'rows' => [$existingIndex + 2, $rowNumber],
                            'severity' => 'error'
                        ];
                    } else {
                        $schoolCodes[] = $code;
                    }

                    // 检查重复学校名称
                    $name = $schoolData['name'];
                    if (in_array($name, $schoolNames)) {
                        $existingIndex = array_search($name, $schoolNames);
                        $analysis['validation_results']['duplicate_names'][] = [
                            'name' => $name,
                            'rows' => [$existingIndex + 2, $rowNumber],
                            'severity' => 'warning'
                        ];
                    } else {
                        $schoolNames[] = $name;
                    }

                    // 检查必填字段完整性
                    foreach ($requiredFields as $field) {
                        if (empty($rowData[$field])) {
                            $analysis['validation_results']['missing_required_fields'][] = [
                                'row' => $rowNumber,
                                'field' => $field,
                                'severity' => 'error'
                            ];
                        }
                    }

                    // 检查数据一致性
                    $this->checkDataConsistency($rowData, $rowNumber, $analysis);

                    // 检查与现有数据的冲突
                    $this->checkExistingDataConflicts($schoolData, $rowNumber, $analysis);

                } catch (Exception $e) {
                    $analysis['validation_results']['invalid_rows']++;
                    $analysis['validation_results']['format_errors'][] = [
                        'row' => $rowNumber,
                        'error' => $e->getMessage(),
                        'severity' => 'error'
                    ];
                }
            }

            $analysis['data_summary']['empty_rows'] = $emptyRowCount;

            // 计算数据质量指标
            $totalRows = count($data);
            if ($totalRows > 0) {
                $analysis['data_quality']['completeness'] = [
                    'percentage' => round(($analysis['validation_results']['valid_rows'] / $totalRows) * 100, 2),
                    'description' => '数据完整性'
                ];

                $uniqueCodesCount = count($schoolCodes);
                $duplicateCodesCount = count($analysis['validation_results']['duplicate_codes']);
                $analysis['data_quality']['uniqueness'] = [
                    'percentage' => $uniqueCodesCount > 0 ? round((($uniqueCodesCount - $duplicateCodesCount) / $uniqueCodesCount) * 100, 2) : 100,
                    'description' => '数据唯一性'
                ];

                $analysis['data_quality']['validity'] = [
                    'percentage' => round(($analysis['validation_results']['valid_rows'] / $totalRows) * 100, 2),
                    'description' => '数据有效性'
                ];

                $inconsistencyCount = count($analysis['validation_results']['data_inconsistencies']);
                $analysis['data_quality']['consistency'] = [
                    'percentage' => round((($totalRows - $inconsistencyCount) / $totalRows) * 100, 2),
                    'description' => '数据一致性'
                ];

                $missingFieldsCount = count($analysis['validation_results']['missing_required_fields']);
                $analysis['data_quality']['accuracy'] = [
                    'percentage' => round((($totalRows - $missingFieldsCount) / $totalRows) * 100, 2),
                    'description' => '数据准确性'
                ];
            }

            // 生成建议
            $analysis['recommendations'] = $this->generateRecommendations($analysis);

            return $analysis;

        } catch (Exception $e) {
            Log::error('学校导入文件分析失败', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'user_id' => $user ? $user->id : null
            ]);

            throw $e;
        }
    }

    /**
     * 格式化文件大小
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * 检查是否为空行
     */
    private function isEmptyRow($row)
    {
        return empty(array_filter($row, function($value) {
            return !empty(trim($value));
        }));
    }

    /**
     * 检查数据一致性
     */
    private function checkDataConsistency($rowData, $rowNumber, &$analysis)
    {
        // 检查学校类型与教育层次的一致性
        $schoolType = $rowData['学校类型'] ?? '';
        $educationLevel = $rowData['教育层次'] ?? '';

        $typeMapping = [
            '小学' => ['小学教育', '学前教育'],
            '初中' => ['初中教育'],
            '高中' => ['高中教育'],
            '九年一贯制' => ['小学教育', '初中教育'],
            '完全中学' => ['初中教育', '高中教育'],
            '职业学校' => ['中等职业教育'],
            '特殊教育学校' => ['特殊教育']
        ];

        if (isset($typeMapping[$schoolType]) && !in_array($educationLevel, $typeMapping[$schoolType])) {
            $analysis['validation_results']['data_inconsistencies'][] = [
                'row' => $rowNumber,
                'type' => 'type_level_mismatch',
                'message' => "学校类型'{$schoolType}'与教育层次'{$educationLevel}'不匹配",
                'severity' => 'warning'
            ];
        }

        // 检查联系电话格式
        $phones = [$rowData['校长电话'] ?? '', $rowData['联系电话'] ?? ''];
        foreach ($phones as $phone) {
            if (!empty($phone) && !$this->isValidPhoneNumber($phone)) {
                $analysis['validation_results']['data_inconsistencies'][] = [
                    'row' => $rowNumber,
                    'type' => 'invalid_phone',
                    'message' => "电话号码格式不正确: {$phone}",
                    'severity' => 'warning'
                ];
            }
        }

        // 检查学生人数合理性
        $studentCount = $rowData['学生人数'] ?? '';
        if (!empty($studentCount) && is_numeric($studentCount)) {
            if ($studentCount < 0 || $studentCount > 10000) {
                $analysis['validation_results']['data_inconsistencies'][] = [
                    'row' => $rowNumber,
                    'type' => 'unreasonable_student_count',
                    'message' => "学生人数可能不合理: {$studentCount}",
                    'severity' => 'warning'
                ];
            }
        }
    }

    /**
     * 检查与现有数据的冲突
     */
    private function checkExistingDataConflicts($schoolData, $rowNumber, &$analysis)
    {
        // 检查学校代码是否已存在
        $existingByCode = Organization::where('code', $schoolData['code'])->first();
        if ($existingByCode) {
            $analysis['validation_results']['data_inconsistencies'][] = [
                'row' => $rowNumber,
                'type' => 'existing_code_conflict',
                'message' => "学校代码'{$schoolData['code']}'已存在于系统中",
                'existing_school' => $existingByCode->name,
                'severity' => 'error'
            ];
        }

        // 检查学校名称是否已存在（同一父级组织下）
        if ($schoolData['parent_id']) {
            $existingByName = Organization::where('name', $schoolData['name'])
                ->where('parent_id', $schoolData['parent_id'])
                ->first();
            if ($existingByName) {
                $analysis['validation_results']['data_inconsistencies'][] = [
                    'row' => $rowNumber,
                    'type' => 'existing_name_conflict',
                    'message' => "学校名称'{$schoolData['name']}'在同一组织下已存在",
                    'existing_school' => $existingByName->name,
                    'severity' => 'warning'
                ];
            }
        }
    }

    /**
     * 验证电话号码格式
     */
    private function isValidPhoneNumber($phone)
    {
        // 简单的电话号码验证
        return preg_match('/^[\d\-\+\(\)\s]{7,20}$/', $phone);
    }

    /**
     * 检测文件编码
     */
    private function detectFileEncoding($file)
    {
        $content = file_get_contents($file->getRealPath());
        $encoding = mb_detect_encoding($content, ['UTF-8', 'GBK', 'GB2312', 'ASCII'], true);
        return $encoding ?: 'Unknown';
    }

    /**
     * 生成数据质量建议
     */
    private function generateRecommendations($analysis)
    {
        $recommendations = [];

        // 基于错误数量的建议
        $totalRows = $analysis['data_summary']['total_rows'];
        $errorRate = $totalRows > 0 ? $analysis['validation_results']['invalid_rows'] / $totalRows : 0;

        if ($errorRate > 0.1) {
            $recommendations[] = [
                'type' => 'high_error_rate',
                'priority' => 'high',
                'title' => '数据错误率较高',
                'message' => sprintf('数据错误率为 %.1f%%，建议检查数据格式和模板是否正确', $errorRate * 100),
                'action' => '重新下载模板并检查数据格式',
                'icon' => 'warning'
            ];
        }

        // 基于重复数据的建议
        if (!empty($analysis['validation_results']['duplicate_codes'])) {
            $duplicateCount = count($analysis['validation_results']['duplicate_codes']);
            $recommendations[] = [
                'type' => 'duplicate_codes',
                'priority' => 'high',
                'title' => '发现重复学校代码',
                'message' => sprintf('发现 %d 个重复的学校代码，这将导致导入失败', $duplicateCount),
                'action' => '检查并修正重复的学校代码',
                'icon' => 'error'
            ];
        }

        // 基于缺失字段的建议
        if (!empty($analysis['validation_results']['missing_required_fields'])) {
            $missingCount = count($analysis['validation_results']['missing_required_fields']);
            $recommendations[] = [
                'type' => 'missing_fields',
                'priority' => 'medium',
                'title' => '必填字段缺失',
                'message' => sprintf('发现 %d 个必填字段为空，建议补充完整信息', $missingCount),
                'action' => '填写所有必填字段',
                'icon' => 'warning'
            ];
        }

        // 基于数据一致性的建议
        if (!empty($analysis['validation_results']['data_inconsistencies'])) {
            $inconsistencyCount = count($analysis['validation_results']['data_inconsistencies']);
            $recommendations[] = [
                'type' => 'data_inconsistency',
                'priority' => 'medium',
                'title' => '数据一致性问题',
                'message' => sprintf('发现 %d 个数据不一致问题，建议检查数据逻辑', $inconsistencyCount),
                'action' => '检查学校类型与教育层次的匹配关系',
                'icon' => 'info'
            ];
        }

        // 基于重复名称的建议
        if (!empty($analysis['validation_results']['duplicate_names'])) {
            $duplicateNameCount = count($analysis['validation_results']['duplicate_names']);
            $recommendations[] = [
                'type' => 'duplicate_names',
                'priority' => 'low',
                'title' => '发现重复学校名称',
                'message' => sprintf('发现 %d 个重复的学校名称，建议确认是否为同一学校', $duplicateNameCount),
                'action' => '检查重复名称的学校是否为同一学校',
                'icon' => 'info'
            ];
        }

        // 基于空行的建议
        if ($analysis['data_summary']['empty_rows'] > 0) {
            $recommendations[] = [
                'type' => 'empty_rows',
                'priority' => 'low',
                'title' => '发现空行',
                'message' => sprintf('文件中有 %d 个空行，建议清理后重新导入', $analysis['data_summary']['empty_rows']),
                'action' => '删除文件中的空行',
                'icon' => 'info'
            ];
        }

        // 基于数据质量的总体建议
        $qualityScore = $this->calculateOverallQualityScore($analysis);
        if ($qualityScore < 80) {
            $recommendations[] = [
                'type' => 'overall_quality',
                'priority' => 'high',
                'title' => '数据质量需要改善',
                'message' => sprintf('数据质量评分为 %.1f 分，建议优化数据后再导入', $qualityScore),
                'action' => '根据上述建议优化数据质量',
                'icon' => 'warning'
            ];
        } elseif ($qualityScore >= 95) {
            $recommendations[] = [
                'type' => 'high_quality',
                'priority' => 'info',
                'title' => '数据质量优秀',
                'message' => sprintf('数据质量评分为 %.1f 分，可以直接导入', $qualityScore),
                'action' => '可以放心进行数据导入',
                'icon' => 'success'
            ];
        }

        return $recommendations;
    }

    /**
     * 计算总体数据质量评分
     */
    private function calculateOverallQualityScore($analysis)
    {
        $weights = [
            'completeness' => 0.3,
            'uniqueness' => 0.25,
            'validity' => 0.25,
            'consistency' => 0.1,
            'accuracy' => 0.1
        ];

        $score = 0;
        foreach ($weights as $dimension => $weight) {
            if (isset($analysis['data_quality'][$dimension]['percentage'])) {
                $score += $analysis['data_quality'][$dimension]['percentage'] * $weight;
            }
        }

        return round($score, 1);
    }
}
