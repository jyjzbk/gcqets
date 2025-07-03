<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExperimentCatalog extends Model
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
        'objectives',
        'materials',
        'procedures',
        'safety_notes',
        'duration_minutes',
        'student_count',
        'difficulty_level',
        'status',
        'curriculum_standard_id',
        'organization_id',
        'created_by',
        'updated_by',
        'extra_data'
    ];

    protected $casts = [
        'extra_data' => 'array',
        'duration_minutes' => 'integer',
        'student_count' => 'integer',
    ];

    protected $attributes = [
        'status' => 'draft',
        'difficulty_level' => 'medium',
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

    // 难度等级选项
    public static function getDifficultyLevelOptions()
    {
        return [
            'easy' => '简单',
            'medium' => '中等',
            'hard' => '困难'
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
     * 关联课程标准
     */
    public function curriculumStandard(): BelongsTo
    {
        return $this->belongsTo(CurriculumStandard::class);
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
     * 关联版本记录
     */
    public function versions(): HasMany
    {
        return $this->hasMany(CatalogVersion::class, 'catalog_id');
    }

    /**
     * 获取最新版本
     */
    public function latestVersion()
    {
        return $this->versions()->latest()->first();
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
     * 获取难度等级中文名称
     */
    public function getDifficultyLevelNameAttribute()
    {
        return self::getDifficultyLevelOptions()[$this->difficulty_level] ?? $this->difficulty_level;
    }

    /**
     * 获取状态中文名称
     */
    public function getStatusNameAttribute()
    {
        return self::getStatusOptions()[$this->status] ?? $this->status;
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
}
