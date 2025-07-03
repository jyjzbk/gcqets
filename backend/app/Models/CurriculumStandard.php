<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CurriculumStandard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'version',
        'subject',
        'grade',
        'content',
        'learning_objectives',
        'key_concepts',
        'skills_requirements',
        'assessment_criteria',
        'effective_date',
        'expiry_date',
        'status',
        'organization_id',
        'created_by',
        'updated_by',
        'extra_data'
    ];

    protected $casts = [
        'extra_data' => 'array',
        'effective_date' => 'date',
        'expiry_date' => 'date',
    ];

    protected $attributes = [
        'status' => 'draft',
    ];

    // 学科选项
    public static function getSubjectOptions()
    {
        return [
            'physics' => '物理',
            'chemistry' => '化学',
            'biology' => '生物',
            'science' => '科学'
        ];
    }

    // 年级选项
    public static function getGradeOptions()
    {
        return [
            'grade1' => '一年级',
            'grade2' => '二年级',
            'grade3' => '三年级',
            'grade4' => '四年级',
            'grade5' => '五年级',
            'grade6' => '六年级',
            'grade7' => '七年级',
            'grade8' => '八年级',
            'grade9' => '九年级'
        ];
    }

    // 状态选项
    public static function getStatusOptions()
    {
        return [
            'active' => '启用',
            'inactive' => '禁用',
            'draft' => '草稿',
            'archived' => '已归档'
        ];
    }

    /**
     * 关联组织机构
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * 关联创建人
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 关联更新人
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 关联实验目录
     */
    public function experimentCatalogs(): HasMany
    {
        return $this->hasMany(ExperimentCatalog::class);
    }

    /**
     * 获取学科中文名称
     */
    public function getSubjectNameAttribute()
    {
        return self::getSubjectOptions()[$this->subject] ?? $this->subject;
    }

    /**
     * 获取年级中文名称
     */
    public function getGradeNameAttribute()
    {
        return self::getGradeOptions()[$this->grade] ?? $this->grade;
    }

    /**
     * 获取状态中文名称
     */
    public function getStatusNameAttribute()
    {
        return self::getStatusOptions()[$this->status] ?? $this->status;
    }

    /**
     * 检查是否有效
     */
    public function getIsValidAttribute()
    {
        $now = now()->toDateString();
        return $this->status === 'active' &&
               ($this->effective_date === null || $this->effective_date <= $now) &&
               ($this->expiry_date === null || $this->expiry_date >= $now);
    }

    /**
     * 作用域：按组织过滤
     */
    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * 作用域：按学科过滤
     */
    public function scopeBySubject($query, $subject)
    {
        return $query->where('subject', $subject);
    }

    /**
     * 作用域：按年级过滤
     */
    public function scopeByGrade($query, $grade)
    {
        return $query->where('grade', $grade);
    }

    /**
     * 作用域：按状态过滤
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * 作用域：启用状态
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * 作用域：有效的标准
     */
    public function scopeValid($query)
    {
        $now = now()->toDateString();
        return $query->where('status', 'active')
                    ->where(function ($q) use ($now) {
                        $q->whereNull('effective_date')
                          ->orWhere('effective_date', '<=', $now);
                    })
                    ->where(function ($q) use ($now) {
                        $q->whereNull('expiry_date')
                          ->orWhere('expiry_date', '>=', $now);
                    });
    }
}
