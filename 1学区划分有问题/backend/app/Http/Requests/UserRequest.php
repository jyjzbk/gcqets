<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
        $userId = $this->route('user');
        
        $rules = [
            'username' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('users')->ignore($userId)
            ],
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('users')->ignore($userId)
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users')->ignore($userId)
            ],
            'real_name' => [
                'required',
                'string',
                'max:50'
            ],
            'employee_id' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('users')->ignore($userId)
            ],
            'position' => [
                'nullable',
                'string',
                'max:100'
            ],
            'department' => [
                'nullable',
                'string',
                'max:100'
            ],
            'status' => [
                'boolean'
            ],
            'gender' => [
                'nullable',
                'in:male,female,unknown'
            ],
            'birthday' => [
                'nullable',
                'date'
            ],
            'join_date' => [
                'nullable',
                'date'
            ],
            'remark' => [
                'nullable',
                'string',
                'max:500'
            ],
            'primary_organization_id' => [
                'required',
                'exists:organizations,id'
            ],
            'organization_ids' => [
                'nullable',
                'array'
            ],
            'organization_ids.*' => [
                'exists:organizations,id'
            ],
            'role_ids' => [
                'nullable',
                'array'
            ],
            'role_ids.*' => [
                'exists:roles,id'
            ]
        ];

        // 创建用户时需要密码
        if (!$userId) {
            $rules['password'] = [
                'required',
                'string',
                'min:6',
                'confirmed'
            ];
            $rules['password_confirmation'] = [
                'required',
                'string'
            ];
        } else {
            // 更新用户时密码可选
            $rules['password'] = [
                'nullable',
                'string',
                'min:6',
                'confirmed'
            ];
            $rules['password_confirmation'] = [
                'nullable',
                'string'
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'username.required' => '用户名不能为空',
            'username.max' => '用户名不能超过50个字符',
            'username.regex' => '用户名只能包含字母、数字、下划线和连字符',
            'username.unique' => '用户名已存在',
            'email.required' => '邮箱不能为空',
            'email.email' => '邮箱格式不正确',
            'email.max' => '邮箱不能超过100个字符',
            'email.unique' => '邮箱已存在',
            'phone.max' => '手机号不能超过20个字符',
            'phone.unique' => '手机号已存在',
            'real_name.required' => '真实姓名不能为空',
            'real_name.max' => '真实姓名不能超过50个字符',
            'employee_id.max' => '工号不能超过50个字符',
            'employee_id.unique' => '工号已存在',
            'position.max' => '职位不能超过100个字符',
            'department.max' => '部门不能超过100个字符',
            'gender.in' => '性别值不正确',
            'birthday.date' => '生日格式不正确',
            'join_date.date' => '入职日期格式不正确',
            'remark.max' => '备注不能超过500个字符',
            'primary_organization_id.required' => '主要组织不能为空',
            'primary_organization_id.exists' => '主要组织不存在',
            'organization_ids.array' => '组织列表格式不正确',
            'organization_ids.*.exists' => '组织不存在',
            'role_ids.array' => '角色列表格式不正确',
            'role_ids.*.exists' => '角色不存在',
            'password.required' => '密码不能为空',
            'password.min' => '密码不能少于6个字符',
            'password.confirmed' => '密码确认不匹配',
            'password_confirmation.required' => '密码确认不能为空'
        ];
    }
} 