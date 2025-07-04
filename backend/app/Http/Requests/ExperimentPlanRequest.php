<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExperimentPlanRequest extends FormRequest
{
    /**
     * 确定用户是否有权限进行此请求
     */
    public function authorize()
    {
        return true;
    }

    /**
     * 获取应用于请求的验证规则
     */
    public function rules()
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2'
            ],
            'experiment_catalog_id' => [
                'required',
                'integer',
                'exists:experiment_catalogs,id'
            ],
            'class_name' => [
                'required',
                'string',
                'max:100'
            ],
            'student_count' => [
                'required',
                'integer',
                'min:1',
                'max:100'
            ],
            'planned_date' => [
                'required',
                'date',
                'after_or_equal:today'
            ],
            'planned_duration' => [
                'required',
                'integer',
                'min:10',
                'max:300'
            ],
            'priority' => [
                'required',
                Rule::in(['low', 'medium', 'high'])
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000'
            ],
            'objectives' => [
                'nullable',
                'string',
                'max:2000'
            ],
            'key_points' => [
                'nullable',
                'string',
                'max:2000'
            ],
            'safety_requirements' => [
                'nullable',
                'string',
                'max:2000'
            ],
            'equipment_requirements' => [
                'nullable',
                'array'
            ],
            'equipment_requirements.*' => [
                'string',
                'max:255'
            ],
            'material_requirements' => [
                'nullable',
                'array'
            ],
            'material_requirements.*' => [
                'string',
                'max:255'
            ]
        ];

        // 更新时的特殊规则
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $planId = $this->route('id');
            
            // 如果计划已经被批准，某些字段不能修改
            $plan = \App\Models\ExperimentPlan::find($planId);
            if ($plan && in_array($plan->status, ['approved', 'executing', 'completed'])) {
                // 已批准的计划只能修改部分字段
                $rules = array_intersect_key($rules, array_flip([
                    'description',
                    'objectives', 
                    'key_points',
                    'safety_requirements'
                ]));
            }
            
            // 编码唯一性验证（排除当前记录）
            $rules['code'] = [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('experiment_plans')->ignore($planId)
            ];
        } else {
            // 创建时的编码验证
            $rules['code'] = [
                'sometimes',
                'string',
                'max:50',
                'unique:experiment_plans'
            ];
        }

        return $rules;
    }

    /**
     * 获取验证错误的自定义消息
     */
    public function messages()
    {
        return [
            'name.required' => '实验计划名称不能为空',
            'name.min' => '实验计划名称至少需要2个字符',
            'name.max' => '实验计划名称不能超过255个字符',
            
            'experiment_catalog_id.required' => '请选择实验目录',
            'experiment_catalog_id.exists' => '选择的实验目录不存在',
            
            'class_name.required' => '班级名称不能为空',
            'class_name.max' => '班级名称不能超过100个字符',
            
            'student_count.required' => '学生人数不能为空',
            'student_count.integer' => '学生人数必须是整数',
            'student_count.min' => '学生人数至少为1人',
            'student_count.max' => '学生人数不能超过100人',
            
            'planned_date.required' => '计划日期不能为空',
            'planned_date.date' => '计划日期格式不正确',
            'planned_date.after_or_equal' => '计划日期不能早于今天',
            
            'planned_duration.required' => '计划时长不能为空',
            'planned_duration.integer' => '计划时长必须是整数',
            'planned_duration.min' => '计划时长至少10分钟',
            'planned_duration.max' => '计划时长不能超过300分钟',
            
            'priority.required' => '请选择优先级',
            'priority.in' => '优先级只能是低、中、高',
            
            'description.max' => '实验描述不能超过2000个字符',
            'objectives.max' => '教学目标不能超过2000个字符',
            'key_points.max' => '重点难点不能超过2000个字符',
            'safety_requirements.max' => '安全要求不能超过2000个字符',
            
            'equipment_requirements.array' => '设备要求必须是数组格式',
            'equipment_requirements.*.string' => '设备要求项必须是字符串',
            'equipment_requirements.*.max' => '设备要求项不能超过255个字符',
            
            'material_requirements.array' => '材料要求必须是数组格式',
            'material_requirements.*.string' => '材料要求项必须是字符串',
            'material_requirements.*.max' => '材料要求项不能超过255个字符',
            
            'code.unique' => '实验计划编码已存在',
            'code.max' => '实验计划编码不能超过50个字符'
        ];
    }

    /**
     * 获取验证字段的自定义属性名称
     */
    public function attributes()
    {
        return [
            'name' => '实验计划名称',
            'experiment_catalog_id' => '实验目录',
            'class_name' => '班级名称',
            'student_count' => '学生人数',
            'planned_date' => '计划日期',
            'planned_duration' => '计划时长',
            'priority' => '优先级',
            'description' => '实验描述',
            'objectives' => '教学目标',
            'key_points' => '重点难点',
            'safety_requirements' => '安全要求',
            'equipment_requirements' => '设备要求',
            'material_requirements' => '材料要求',
            'code' => '实验计划编码'
        ];
    }

    /**
     * 配置验证器实例
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // 自定义验证逻辑
            $this->validateBusinessRules($validator);
        });
    }

    /**
     * 业务规则验证
     */
    protected function validateBusinessRules($validator)
    {
        // 检查日期冲突
        if ($this->filled('planned_date') && $this->filled('teacher_id')) {
            $this->validateDateConflict($validator);
        }

        // 检查学生人数合理性
        if ($this->filled('student_count') && $this->filled('class_name')) {
            $this->validateStudentCount($validator);
        }

        // 检查时长合理性
        if ($this->filled('planned_duration')) {
            $this->validateDuration($validator);
        }
    }

    /**
     * 验证日期冲突
     */
    protected function validateDateConflict($validator)
    {
        $teacherId = $this->input('teacher_id') ?? auth()->id();
        $plannedDate = $this->input('planned_date');
        $planId = $this->route('id');

        $conflictQuery = \App\Models\ExperimentPlan::where('teacher_id', $teacherId)
            ->where('planned_date', $plannedDate)
            ->whereIn('status', ['approved', 'executing']);

        if ($planId) {
            $conflictQuery->where('id', '!=', $planId);
        }

        if ($conflictQuery->exists()) {
            $validator->errors()->add('planned_date', '该日期已有其他实验计划安排');
        }
    }

    /**
     * 验证学生人数合理性
     */
    protected function validateStudentCount($validator)
    {
        $studentCount = $this->input('student_count');
        
        // 检查人数是否过多（可能需要分组）
        if ($studentCount > 40) {
            $validator->errors()->add('student_count', '学生人数较多，建议分组进行实验');
        }
    }

    /**
     * 验证时长合理性
     */
    protected function validateDuration($validator)
    {
        $duration = $this->input('planned_duration');
        
        // 检查时长是否合理
        if ($duration < 20) {
            $validator->errors()->add('planned_duration', '实验时长可能过短，建议至少20分钟');
        } elseif ($duration > 180) {
            $validator->errors()->add('planned_duration', '实验时长可能过长，建议不超过180分钟');
        }
    }

    /**
     * 准备验证数据
     */
    protected function prepareForValidation()
    {
        // 自动生成编码（如果未提供）
        if (!$this->filled('code')) {
            $this->merge([
                'code' => $this->generatePlanCode()
            ]);
        }

        // 设置默认教师ID
        if (!$this->filled('teacher_id')) {
            $this->merge([
                'teacher_id' => auth()->id()
            ]);
        }

        // 设置默认组织ID
        if (!$this->filled('organization_id')) {
            $this->merge([
                'organization_id' => auth()->user()->organization_id
            ]);
        }

        // 处理JSON字段
        if ($this->filled('equipment_requirements') && is_string($this->equipment_requirements)) {
            $this->merge([
                'equipment_requirements' => json_decode($this->equipment_requirements, true)
            ]);
        }

        if ($this->filled('material_requirements') && is_string($this->material_requirements)) {
            $this->merge([
                'material_requirements' => json_decode($this->material_requirements, true)
            ]);
        }
    }

    /**
     * 生成计划编码
     */
    protected function generatePlanCode()
    {
        $prefix = 'EXP';
        $date = date('Ymd');
        $sequence = str_pad(\App\Models\ExperimentPlan::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        
        return $prefix . $date . $sequence;
    }
}
