<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
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
        $permissionId = $this->route('permission');
        
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('permissions')->ignore($permissionId)
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
            'group' => [
                'required',
                'string',
                'max:50'
            ],
            'guard_name' => [
                'nullable',
                'string',
                'max:50'
            ],
            'module' => [
                'nullable',
                'string',
                'max:50'
            ],
            'action' => [
                'nullable',
                'string',
                'max:50'
            ],
            'resource' => [
                'nullable',
                'string',
                'max:50'
            ],
            'method' => [
                'nullable',
                'string',
                'max:10'
            ],
            'route' => [
                'nullable',
                'string',
                'max:200'
            ],
            'icon' => [
                'nullable',
                'string',
                'max:50'
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0'
            ],
            'parent_id' => [
                'nullable',
                'exists:permissions,id'
            ],
            'level' => [
                'required',
                'integer',
                'min:1'
            ],
            'status' => [
                'boolean'
            ],
            'is_menu' => [
                'boolean'
            ],
            'is_system' => [
                'boolean'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => '权限名称不能为空',
            'name.max' => '权限名称不能超过100个字符',
            'name.unique' => '权限名称已存在',
            'display_name.required' => '权限显示名称不能为空',
            'display_name.max' => '权限显示名称不能超过100个字符',
            'description.max' => '描述不能超过500个字符',
            'group.required' => '权限分组不能为空',
            'group.max' => '权限分组不能超过50个字符',
            'guard_name.max' => '守卫名称不能超过50个字符',
            'module.max' => '模块名称不能超过50个字符',
            'action.max' => '操作名称不能超过50个字符',
            'resource.max' => '资源名称不能超过50个字符',
            'method.max' => '请求方法不能超过10个字符',
            'route.max' => '路由不能超过200个字符',
            'icon.max' => '图标不能超过50个字符',
            'sort_order.integer' => '排序值必须是整数',
            'sort_order.min' => '排序值不能小于0',
            'parent_id.exists' => '父级权限不存在',
            'level.required' => '权限级别不能为空',
            'level.integer' => '权限级别必须是整数',
            'level.min' => '权限级别不能小于1'
        ];
    }
} 