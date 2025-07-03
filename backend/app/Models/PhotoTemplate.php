<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotoTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'subject',
        'grade',
        'textbook_version',
        'experiment_type',
        'description',
        'required_photos',
        'optional_photos',
        'photo_specifications',
        'status',
        'organization_id',
        'created_by',
        'updated_by',
        'extra_data'
    ];

    protected $casts = [
        'required_photos' => 'array',
        'optional_photos' => 'array',
        'photo_specifications' => 'array',
        'extra_data' => 'array',
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

    // 实验类型选项
    public static function getExperimentTypeOptions()
    {
        return [
            'demonstration' => '演示实验',
            'group' => '分组实验',
            'individual' => '个人实验',
            'inquiry' => '探究实验'
        ];
    }

    // 状态选项
    public static function getStatusOptions()
    {
        return [
            'active' => '启用',
            'inactive' => '禁用',
            'draft' => '草稿'
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
     * 获取实验类型中文名称
     */
    public function getExperimentTypeNameAttribute()
    {
        return self::getExperimentTypeOptions()[$this->experiment_type] ?? $this->experiment_type;
    }

    /**
     * 获取状态中文名称
     */
    public function getStatusNameAttribute()
    {
        return self::getStatusOptions()[$this->status] ?? $this->status;
    }

    /**
     * 获取必需照片数量
     */
    public function getRequiredPhotosCountAttribute()
    {
        return is_array($this->required_photos) ? count($this->required_photos) : 0;
    }

    /**
     * 获取可选照片数量
     */
    public function getOptionalPhotosCountAttribute()
    {
        return is_array($this->optional_photos) ? count($this->optional_photos) : 0;
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
     * 作用域：按实验类型过滤
     */
    public function scopeByExperimentType($query, $experimentType)
    {
        return $query->where('experiment_type', $experimentType);
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
}
