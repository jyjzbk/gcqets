<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExperimentPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'experiment_catalog_id',
        'curriculum_standard_id',
        'teacher_id',
        'class_name',
        'student_count',
        'planned_date',
        'planned_duration',
        'status',
        'description',
        'objectives',
        'key_points',
        'safety_requirements',
        'equipment_requirements',
        'material_requirements',
        'approval_notes',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'revision_count',
        'priority',
        'is_public',
        'schedule_info',
        'organization_id',
        'created_by',
        'updated_by',
        'extra_data'
    ];

    protected $casts = [
        'equipment_requirements' => 'array',
        'material_requirements' => 'array',
        'schedule_info' => 'array',
        'extra_data' => 'array',
        'planned_date' => 'date',
        'approved_at' => 'datetime',
        'student_count' => 'integer',
        'planned_duration' => 'integer',
        'revision_count' => 'integer',
        'is_public' => 'boolean',
    ];

    protected $attributes = [
        'status' => 'draft',
        'priority' => 'medium',
        'revision_count' => 0,
        'is_public' => false,
    ];

    // 状态选项
    public static function getStatusOptions()
    {
        return [
            'draft' => '草稿',
            'pending' => '待审批',
            'approved' => '已批准',
            'rejected' => '已拒绝',
            'executing' => '执行中',
            'completed' => '已完成',
            'cancelled' => '已取消'
        ];
    }

    // 优先级选项
    public static function getPriorityOptions()
    {
        return [
            'low' => '低',
            'medium' => '中',
            'high' => '高'
        ];
    }

    // 关联实验目录
    public function experimentCatalog(): BelongsTo
    {
        return $this->belongsTo(ExperimentCatalog::class);
    }

    // 关联课程标准
    public function curriculumStandard(): BelongsTo
    {
        return $this->belongsTo(CurriculumStandard::class);
    }

    // 关联教师
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // 关联审批人
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // 关联组织机构
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    // 关联创建人
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // 关联更新人
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // 关联实验记录
    public function experimentRecords(): HasMany
    {
        return $this->hasMany(ExperimentRecord::class);
    }

    // 作用域：按状态筛选
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // 作用域：按教师筛选
    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    // 作用域：按组织筛选
    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    // 作用域：按日期范围筛选
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('planned_date', [$startDate, $endDate]);
    }

    // 作用域：待审批的计划
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // 作用域：已批准的计划
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // 作用域：草稿状态
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    // 检查是否可以编辑
    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'rejected']);
    }

    // 检查是否可以提交审批
    public function canSubmit(): bool
    {
        return $this->status === 'draft';
    }

    // 检查是否可以审批
    public function canApprove(): bool
    {
        return $this->status === 'pending';
    }

    // 检查是否可以执行
    public function canExecute(): bool
    {
        return $this->status === 'approved';
    }

    // 获取状态标签
    public function getStatusLabelAttribute(): string
    {
        return self::getStatusOptions()[$this->status] ?? $this->status;
    }

    // 获取优先级标签
    public function getPriorityLabelAttribute(): string
    {
        return self::getPriorityOptions()[$this->priority] ?? $this->priority;
    }

    // 生成计划编码
    public static function generateCode($organizationId): string
    {
        $prefix = 'EP' . date('Ymd');
        $count = self::where('organization_id', $organizationId)
                    ->whereDate('created_at', today())
                    ->count() + 1;
        return $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    // 提交审批
    public function submitForApproval(): bool
    {
        if (!$this->canSubmit()) {
            return false;
        }

        $this->status = 'pending';
        $this->revision_count++;
        return $this->save();
    }

    // 审批通过
    public function approve($approverId, $notes = null): bool
    {
        if (!$this->canApprove()) {
            return false;
        }

        $this->status = 'approved';
        $this->approved_by = $approverId;
        $this->approved_at = now();
        $this->approval_notes = $notes;
        return $this->save();
    }

    // 审批拒绝
    public function reject($approverId, $reason): bool
    {
        if (!$this->canApprove()) {
            return false;
        }

        $this->status = 'rejected';
        $this->approved_by = $approverId;
        $this->approved_at = now();
        $this->rejection_reason = $reason;
        return $this->save();
    }
}
