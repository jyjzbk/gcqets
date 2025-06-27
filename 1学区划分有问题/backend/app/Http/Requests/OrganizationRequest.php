<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrganizationRequest extends FormRequest
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
        $organizationId = $this->route('organization');
        
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('organizations')->ignore($organizationId)
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('organizations')->ignore($organizationId)
            ],
            'parent_id' => [
                'nullable',
                'exists:organizations,id'
            ],
            'level' => [
                'required',
                'integer',
                'min:1',
                'max:5'
            ],
            'description' => [
                'nullable',
                'string',
                'max:500'
            ],
            'status' => [
                'boolean'
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0'
            ],
            'contact_person' => [
                'nullable',
                'string',
                'max:50'
            ],
            'contact_phone' => [
                'nullable',
                'string',
                'max:20'
            ],
            'contact_email' => [
                'nullable',
                'email',
                'max:100'
            ],
            'address' => [
                'nullable',
                'string',
                'max:200'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => '组织名称不能为空',
            'name.max' => '组织名称不能超过100个字符',
            'name.unique' => '组织名称已存在',
            'code.required' => '组织编码不能为空',
            'code.max' => '组织编码不能超过50个字符',
            'code.regex' => '组织编码只能包含大写字母、数字、下划线和连字符',
            'code.unique' => '组织编码已存在',
            'parent_id.exists' => '父级组织不存在',
            'level.required' => '组织级别不能为空',
            'level.integer' => '组织级别必须是整数',
            'level.min' => '组织级别不能小于1',
            'level.max' => '组织级别不能大于5',
            'description.max' => '描述不能超过500个字符',
            'sort_order.integer' => '排序值必须是整数',
            'sort_order.min' => '排序值不能小于0',
            'contact_person.max' => '联系人姓名不能超过50个字符',
            'contact_phone.max' => '联系电话不能超过20个字符',
            'contact_email.email' => '联系邮箱格式不正确',
            'contact_email.max' => '联系邮箱不能超过100个字符',
            'address.max' => '地址不能超过200个字符'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $parentId = $this->input('parent_id');
            $level = $this->input('level');
            
            // 验证父级组织与级别的关系
            if ($parentId) {
                $parent = \App\Models\Organization::find($parentId);
                if ($parent && $parent->level >= $level) {
                    $validator->errors()->add('level', '子组织的级别必须高于父组织');
                }
            } else {
                // 根组织的级别应该是1
                if ($level != 1) {
                    $validator->errors()->add('level', '根组织的级别必须是1');
                }
            }
        });
    }
} 