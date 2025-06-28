<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $roleId = $this->route('role');
        
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('roles')->ignore($roleId)
            ],
            'display_name' => [
                'required',
                'string',
                'max:100'
            ],
            'description' => [
                'nullable',
                'string',
                'max:500'
            ],
            'level' => [
                'required',
                'integer',
                'min:1',
                'max:5'
            ],
            'guard_name' => [
                'nullable',
                'string',
                'max:50'
            ],
            'status' => [
                'nullable',
                'in:active,inactive'
            ],
            'is_system' => [
                'boolean'
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0'
            ],
            'permission_ids' => [
                'nullable',
                'array'
            ],
            'permission_ids.*' => [
                'exists:permissions,id'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => '角色名称不能为空',
            'name.max' => '角色名称不能超过50个字符',
            'name.regex' => '角色名称只能包含字母、数字、下划线和连字符',
            'name.unique' => '角色名称已存在',
            'display_name.required' => '角色显示名称不能为空',
            'display_name.max' => '角色显示名称不能超过100个字符',
            'description.max' => '描述不能超过500个字符',
            'level.required' => '角色级别不能为空',
            'level.integer' => '角色级别必须是整数',
            'level.min' => '角色级别不能小于1',
            'level.max' => '角色级别不能大于5',
            'guard_name.max' => '守卫名称不能超过50个字符',
            'status.in' => '状态值不正确',
            'sort_order.integer' => '排序值必须是整数',
            'sort_order.min' => '排序值不能小于0',
            'permission_ids.array' => '权限列表格式不正确',
            'permission_ids.*.exists' => '权限不存在'
        ];
    }
} 