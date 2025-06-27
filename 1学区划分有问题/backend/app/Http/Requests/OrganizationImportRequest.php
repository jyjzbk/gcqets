<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrganizationImportRequest extends FormRequest
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
        return [
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:10240' // 10MB
            ],
            'parent_id' => [
                'nullable',
                'exists:organizations,id'
            ],
            'overwrite' => [
                'boolean'
            ],
            'validate_only' => [
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
            'file.required' => '请选择要导入的文件',
            'file.file' => '上传的必须是文件',
            'file.mimes' => '文件格式必须是 Excel (.xlsx, .xls) 或 CSV (.csv)',
            'file.max' => '文件大小不能超过 10MB',
            'parent_id.exists' => '指定的父级组织不存在'
        ];
    }
}
