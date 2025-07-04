<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExperimentRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'experiment_plan_id',
        'execution_date',
        'start_time',
        'end_time',
        'actual_duration',
        'actual_student_count',
        'completion_status',
        'execution_notes',
        'problems_encountered',
        'solutions_applied',
        'teaching_reflection',
        'student_feedback',
        'equipment_used',
        'materials_consumed',
        'data_records',
        'safety_incidents',
        'status',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
        'photo_count',
        'equipment_confirmed',
        'equipment_confirmed_at',
        'validation_results',
        'completion_percentage',
        'organization_id',
        'created_by',
        'updated_by',
        'extra_data'
    ];

    protected $casts = [
        'equipment_used' => 'array',
        'materials_consumed' => 'array',
        'data_records' => 'array',
        'validation_results' => 'array',
        'extra_data' => 'array',
        'execution_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'reviewed_at' => 'datetime',
        'equipment_confirmed_at' => 'datetime',
        'actual_duration' => 'integer',
        'actual_student_count' => 'integer',
        'photo_count' => 'integer',
        'completion_percentage' => 'decimal:2',
        'equipment_confirmed' => 'boolean',
    ];

    protected $attributes = [
        'completion_status' => 'not_started',
        'status' => 'draft',
        'photo_count' => 0,
        'equipment_confirmed' => false,
        'completion_percentage' => 0,
    ];

    // 完成状态选项
    public static function getCompletionStatusOptions()
    {
        return [
            'not_started' => '未开始',
            'in_progress' => '进行中',
            'partial' => '部分完成',
            'completed' => '已完成',
            'cancelled' => '已取消'
        ];
    }

    // 审核状态选项
    public static function getStatusOptions()
    {
        return [
            'draft' => '草稿',
            'submitted' => '已提交',
            'under_review' => '审核中',
            'approved' => '已通过',
            'rejected' => '已拒绝',
            'revision_required' => '需要修改'
        ];
    }

    // 关联实验计划
    public function experimentPlan(): BelongsTo
    {
        return $this->belongsTo(ExperimentPlan::class);
    }

    // 关联实验照片
    public function photos(): HasMany
    {
        return $this->hasMany(ExperimentPhoto::class);
    }

    // 关联审核人
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
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

    // 按类型获取照片
    public function photosByType($type): HasMany
    {
        return $this->photos()->where('photo_type', $type);
    }

    // 作用域：按状态筛选
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // 作用域：按完成状态筛选
    public function scopeCompletionStatus($query, $status)
    {
        return $query->where('completion_status', $status);
    }

    // 作用域：按组织筛选
    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    // 作用域：按日期范围筛选
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('execution_date', [$startDate, $endDate]);
    }

    // 作用域：待审核的记录
    public function scopePendingReview($query)
    {
        return $query->where('status', 'submitted');
    }

    // 作用域：已完成的记录
    public function scopeCompleted($query)
    {
        return $query->where('completion_status', 'completed');
    }

    // 检查是否可以编辑
    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'revision_required']);
    }

    // 检查是否可以提交
    public function canSubmit(): bool
    {
        return $this->status === 'draft' && $this->completion_status !== 'not_started';
    }

    // 检查是否可以审核
    public function canReview(): bool
    {
        return $this->status === 'submitted';
    }

    // 获取完成状态标签
    public function getCompletionStatusLabelAttribute(): string
    {
        return self::getCompletionStatusOptions()[$this->completion_status] ?? $this->completion_status;
    }

    // 获取审核状态标签
    public function getStatusLabelAttribute(): string
    {
        return self::getStatusOptions()[$this->status] ?? $this->status;
    }

    // 计算实际时长
    public function calculateActualDuration(): ?int
    {
        if ($this->start_time && $this->end_time) {
            $start = \Carbon\Carbon::parse($this->start_time);
            $end = \Carbon\Carbon::parse($this->end_time);
            return $end->diffInMinutes($start);
        }
        return null;
    }

    // 更新照片数量
    public function updatePhotoCount(): void
    {
        $this->photo_count = $this->photos()->count();
        $this->save();
    }

    // 确认器材准备
    public function confirmEquipment(): bool
    {
        $this->equipment_confirmed = true;
        $this->equipment_confirmed_at = now();
        return $this->save();
    }

    // 提交审核
    public function submitForReview(): bool
    {
        if (!$this->canSubmit()) {
            return false;
        }

        $this->status = 'submitted';
        return $this->save();
    }

    // 审核通过
    public function approve($reviewerId, $notes = null): bool
    {
        if (!$this->canReview()) {
            return false;
        }

        $this->status = 'approved';
        $this->reviewed_by = $reviewerId;
        $this->reviewed_at = now();
        $this->review_notes = $notes;
        return $this->save();
    }

    // 审核拒绝
    public function reject($reviewerId, $notes): bool
    {
        if (!$this->canReview()) {
            return false;
        }

        $this->status = 'rejected';
        $this->reviewed_by = $reviewerId;
        $this->reviewed_at = now();
        $this->review_notes = $notes;
        return $this->save();
    }

    // 要求修改
    public function requireRevision($reviewerId, $notes): bool
    {
        if (!$this->canReview()) {
            return false;
        }

        $this->status = 'revision_required';
        $this->reviewed_by = $reviewerId;
        $this->reviewed_at = now();
        $this->review_notes = $notes;
        return $this->save();
    }

    // 验证记录完整性
    public function validateRecord(): array
    {
        $errors = [];
        $warnings = [];

        // 检查基本信息
        if (!$this->execution_date) {
            $errors[] = '缺少执行日期';
        }

        if (!$this->actual_student_count) {
            $warnings[] = '建议填写实际参与学生数';
        }

        // 检查照片
        $photoCount = $this->photos()->count();
        if ($photoCount === 0) {
            $errors[] = '至少需要上传一张照片';
        }

        // 检查必需照片类型
        $requiredTypes = ['preparation', 'process', 'result'];
        foreach ($requiredTypes as $type) {
            if ($this->photos()->where('photo_type', $type)->count() === 0) {
                $warnings[] = "建议上传{$type}类型的照片";
            }
        }

        // 检查器材确认
        if (!$this->equipment_confirmed) {
            $warnings[] = '建议确认器材准备情况';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'completion_percentage' => $this->calculateCompletionPercentage()
        ];
    }

    // 计算完成百分比
    public function calculateCompletionPercentage(): float
    {
        $total = 0;
        $completed = 0;

        // 基本信息 (30%)
        $total += 30;
        if ($this->execution_date && $this->actual_student_count && $this->execution_notes) {
            $completed += 30;
        } elseif ($this->execution_date) {
            $completed += 15;
        }

        // 照片上传 (40%)
        $total += 40;
        $photoCount = $this->photos()->count();
        if ($photoCount >= 3) {
            $completed += 40;
        } elseif ($photoCount > 0) {
            $completed += 20;
        }

        // 器材确认 (15%)
        $total += 15;
        if ($this->equipment_confirmed) {
            $completed += 15;
        }

        // 教学反思 (15%)
        $total += 15;
        if ($this->teaching_reflection) {
            $completed += 15;
        }

        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }

    // 自动更新完成百分比
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($record) {
            $record->completion_percentage = $record->calculateCompletionPercentage();
        });
    }
}
