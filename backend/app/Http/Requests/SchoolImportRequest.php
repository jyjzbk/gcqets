<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SchoolImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // 检查用户是否有学校导入权限
        return $this->user()->hasPermission('school.import');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:20480' // 20MB
            ],
            'parent_id' => [
                'required',
                'exists:organizations,id',
                function ($attribute, $value, $fail) {
                    // 检查父级组织是否可以包含学校
                    $organization = \App\Models\Organization::find($value);
                    if ($organization && !$this->canContainSchools($organization)) {
                        $fail('指定的父级组织不能包含学校');
                    }
                }
            ],
            'overwrite' => [
                'boolean'
            ],
            'validate_only' => [
                'boolean'
            ],
            'import_type' => [
                'string',
                Rule::in(['direct', 'zone_assignment'])
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => '请选择要导入的学校信息文件',
            'file.file' => '上传的必须是文件',
            'file.mimes' => '文件格式必须是 Excel (.xlsx, .xls) 或 CSV (.csv)',
            'file.max' => '文件大小不能超过 20MB',
            'parent_id.required' => '请选择父级组织',
            'parent_id.exists' => '指定的父级组织不存在',
            'overwrite.boolean' => '覆盖选项必须是布尔值',
            'validate_only.boolean' => '仅验证选项必须是布尔值',
            'import_type.in' => '导入类型不正确'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'file' => '导入文件',
            'parent_id' => '父级组织',
            'overwrite' => '覆盖已存在数据',
            'validate_only' => '仅验证数据',
            'import_type' => '导入类型'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 检查用户是否有权限在指定父级组织下创建学校
            if ($this->parent_id) {
                $user = $this->user();
                if (!$user->canAccessOrganization($this->parent_id)) {
                    $validator->errors()->add('parent_id', '无权在指定父级组织下创建学校');
                }
            }

            // 检查文件内容（如果需要）
            if ($this->hasFile('file') && !$validator->errors()->has('file')) {
                $this->validateFileContent($validator);
            }
        });
    }

    /**
     * 验证文件内容
     */
    private function validateFileContent($validator)
    {
        try {
            $file = $this->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            
            // 检查文件是否可读
            if (!is_readable($file->getRealPath())) {
                $validator->errors()->add('file', '文件无法读取，请检查文件是否损坏');
                return;
            }

            // 简单检查文件内容
            if ($extension === 'csv') {
                $this->validateCsvFile($file, $validator);
            } else {
                $this->validateExcelFile($file, $validator);
            }

        } catch (\Exception $e) {
            $validator->errors()->add('file', '文件格式验证失败：' . $e->getMessage());
        }
    }

    /**
     * 验证CSV文件
     */
    private function validateCsvFile($file, $validator)
    {
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            $validator->errors()->add('file', 'CSV文件无法打开');
            return;
        }

        // 读取第一行检查表头
        $header = fgetcsv($handle);
        fclose($handle);

        if (empty($header)) {
            $validator->errors()->add('file', 'CSV文件为空或格式不正确');
            return;
        }

        // 检查必需的表头字段
        $requiredHeaders = ['学校名称', '学校代码', '学校类型', '教育层次'];
        $missingHeaders = array_diff($requiredHeaders, $header);
        
        if (!empty($missingHeaders)) {
            $validator->errors()->add('file', '缺少必需的表头字段：' . implode(', ', $missingHeaders));
        }
    }

    /**
     * 验证Excel文件
     */
    private function validateExcelFile($file, $validator)
    {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            
            // 检查是否有数据
            if ($worksheet->getHighestRow() < 2) {
                $validator->errors()->add('file', 'Excel文件没有数据行');
                return;
            }

            // 读取表头
            $header = [];
            $headerRow = $worksheet->getRowIterator(1, 1)->current();
            foreach ($headerRow->getCellIterator() as $cell) {
                $header[] = trim($cell->getCalculatedValue());
            }

            // 检查必需的表头字段
            $requiredHeaders = ['学校名称', '学校代码', '学校类型', '教育层次'];
            $missingHeaders = array_diff($requiredHeaders, $header);
            
            if (!empty($missingHeaders)) {
                $validator->errors()->add('file', '缺少必需的表头字段：' . implode(', ', $missingHeaders));
            }

        } catch (\Exception $e) {
            $validator->errors()->add('file', 'Excel文件读取失败：' . $e->getMessage());
        }
    }

    /**
     * 检查组织是否可以包含学校
     */
    private function canContainSchools($organization): bool
    {
        // 根据组织类型和级别判断是否可以包含学校
        $allowedTypes = ['district', 'zone', 'education_bureau'];
        $allowedLevels = [3, 4]; // 区县级和学区级
        
        return in_array($organization->type, $allowedTypes) || 
               in_array($organization->level, $allowedLevels);
    }

    /**
     * 获取验证后的数据
     */
    public function getValidatedData(): array
    {
        $data = $this->validated();
        
        // 设置默认值
        $data['overwrite'] = $data['overwrite'] ?? false;
        $data['validate_only'] = $data['validate_only'] ?? false;
        $data['import_type'] = $data['import_type'] ?? 'direct';
        
        return $data;
    }

    /**
     * 获取导入选项
     */
    public function getImportOptions(): array
    {
        return [
            'overwrite' => $this->boolean('overwrite', false),
            'validate_only' => $this->boolean('validate_only', false),
            'import_type' => $this->input('import_type', 'direct'),
            'parent_id' => $this->input('parent_id'),
            'user_id' => $this->user()->id,
            'ip_address' => $this->ip(),
            'user_agent' => $this->userAgent()
        ];
    }
}
