<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'parent_id',
        'subject',
        'grade_range',
        'sort_order',
        'status',
        'organization_id',
        'created_by',
        'updated_by',
        'extra_data'
    ];

    protected $casts = [
        'extra_data' => 'array',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'status' => 'active',
        'sort_order' => 0,
    ];

    // 学科选项
    public static function getSubjectOptions()
    {
        return [
            'physics' => '物理',
            'chemistry' => '化学',
            'biology' => '生物',
            'science' => '科学',
            'mathematics' => '数学',
            'geography' => '地理',
            'general' => '通用'
        ];
    }

    // 年级范围选项
    public static function getGradeRangeOptions()
    {
        return [
            'grade1-3' => '1-3年级',
            'grade4-6' => '4-6年级',
            'grade7-9' => '7-9年级',
            'grade10-12' => '10-12年级',
            'all' => '全年级'
        ];
    }

    // 状态选项
    public static function getStatusOptions()
    {
        return [
            'active' => '启用',
            'inactive' => '禁用'
        ];
    }

    // 关联关系
    public function parent()
    {
        return $this->belongsTo(EquipmentCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(EquipmentCategory::class, 'parent_id');
    }

    public function equipment()
    {
        return $this->hasMany(Equipment::class, 'category_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // 作用域
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeBySubject($query, $subject)
    {
        return $query->where('subject', $subject);
    }

    public function scopeRootCategories($query)
    {
        return $query->whereNull('parent_id');
    }

    // 获取完整路径
    public function getFullPathAttribute()
    {
        $path = [$this->name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }
        
        return implode(' > ', $path);
    }

    // 获取所有子分类ID（包括自己）
    public function getAllChildrenIds()
    {
        $ids = [$this->id];
        
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getAllChildrenIds());
        }
        
        return $ids;
    }
}
