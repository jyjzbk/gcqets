<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExperimentReviewLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'experiment_record_id',
        'review_type',
        'previous_status',
        'new_status',
        'review_notes',
        'review_details',
        'attachment_files',
        'review_category',
        'review_score',
        'is_ai_review',
        'ai_analysis_result',
        'reviewer_id',
        'reviewer_role',
        'reviewer_name',
        'review_deadline',
        'review_duration',
        'is_urgent',
        'ip_address',
        'user_agent',
        'organization_id',
        'extra_data'
    ];

    protected $casts = [
        'review_details' => 'array',
        'attachment_files' => 'array',
        'ai_analysis_result' => 'array',
        'extra_data' => 'array',
        'review_deadline' => 'datetime',
        'review_score' => 'integer',
        'review_duration' => 'integer',
        'is_ai_review' => 'boolean',
        'is_urgent' => 'boolean',
    ];

    protected $attributes = [
        'is_ai_review' => false,
        'is_urgent' => false,
    ];

    // 审核类型选项
    public static function getReviewTypeOptions()
    {
        return [
            'submit' => '提交审核',
            'approve' => '审核通过',
            'reject' => '审核拒绝',
            'revision_request' => '要求修改',
            'force_complete' => '强制完成',
            'batch_approve' => '批量通过',
            'batch_reject' => '批量拒绝',
            'ai_check' => 'AI检查',
            'manual_check' => '人工检查'
        ];
    }

    // 审核分类选项
    public static function getReviewCategoryOptions()
    {
        return [
            'format' => '格式问题',
            'content' => '内容问题',
            'photo' => '照片问题',
            'data' => '数据问题',
            'safety' => '安全问题',
            'completeness' => '完整性问题',
            'other' => '其他问题'
        ];
    }

    // 关联实验记录
    public function experimentRecord(): BelongsTo
    {
        return $this->belongsTo(ExperimentRecord::class);
    }

    // 关联审核人
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    // 关联组织机构
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    // 作用域：按审核类型筛选
    public function scopeReviewType($query, $type)
    {
        return $query->where('review_type', $type);
    }

    // 作用域：按审核分类筛选
    public function scopeReviewCategory($query, $category)
    {
        return $query->where('review_category', $category);
    }

    // 作用域：按组织筛选
    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    // 作用域：按审核人筛选
    public function scopeByReviewer($query, $reviewerId)
    {
        return $query->where('reviewer_id', $reviewerId);
    }

    // 作用域：AI审核
    public function scopeAiReview($query)
    {
        return $query->where('is_ai_review', true);
    }

    // 作用域：人工审核
    public function scopeManualReview($query)
    {
        return $query->where('is_ai_review', false);
    }

    // 作用域：紧急审核
    public function scopeUrgent($query)
    {
        return $query->where('is_urgent', true);
    }

    // 作用域：按日期范围筛选
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // 获取审核类型标签
    public function getReviewTypeLabelAttribute(): string
    {
        return self::getReviewTypeOptions()[$this->review_type] ?? $this->review_type;
    }

    // 获取审核分类标签
    public function getReviewCategoryLabelAttribute(): string
    {
        return self::getReviewCategoryOptions()[$this->review_category] ?? $this->review_category;
    }

    // 获取审核评分等级
    public function getReviewGradeAttribute(): string
    {
        if (!$this->review_score) {
            return '未评分';
        }

        if ($this->review_score >= 9) return '优秀';
        if ($this->review_score >= 8) return '良好';
        if ($this->review_score >= 7) return '中等';
        if ($this->review_score >= 6) return '及格';
        return '不及格';
    }

    // 检查是否超时
    public function isOverdue(): bool
    {
        return $this->review_deadline && now()->gt($this->review_deadline);
    }

    // 计算审核耗时
    public function calculateDuration(): int
    {
        if ($this->review_duration) {
            return $this->review_duration;
        }

        // 如果没有记录耗时，可以根据创建时间和更新时间估算
        return $this->updated_at->diffInMinutes($this->created_at);
    }

    // 创建审核日志
    public static function createLog(
        $recordId,
        $reviewType,
        $previousStatus,
        $newStatus,
        $reviewerId,
        $reviewerRole,
        $reviewerName,
        $organizationId,
        $options = []
    ): self {
        $data = [
            'experiment_record_id' => $recordId,
            'review_type' => $reviewType,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'reviewer_id' => $reviewerId,
            'reviewer_role' => $reviewerRole,
            'reviewer_name' => $reviewerName,
            'organization_id' => $organizationId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        // 合并可选参数
        $data = array_merge($data, $options);

        return self::create($data);
    }

    // 批量创建审核日志
    public static function createBatchLogs(
        array $recordIds,
        $reviewType,
        $previousStatus,
        $newStatus,
        $reviewerId,
        $reviewerRole,
        $reviewerName,
        $organizationId,
        $options = []
    ): int {
        $logs = [];
        $baseData = [
            'review_type' => $reviewType,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'reviewer_id' => $reviewerId,
            'reviewer_role' => $reviewerRole,
            'reviewer_name' => $reviewerName,
            'organization_id' => $organizationId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // 合并可选参数
        $baseData = array_merge($baseData, $options);

        foreach ($recordIds as $recordId) {
            $logs[] = array_merge($baseData, ['experiment_record_id' => $recordId]);
        }

        self::insert($logs);
        return count($logs);
    }

    // 获取审核统计
    public static function getReviewStatistics($organizationId = null, $dateRange = null): array
    {
        $query = self::query();

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        if ($dateRange && count($dateRange) === 2) {
            $query->whereBetween('created_at', $dateRange);
        }

        $stats = [
            'total' => $query->count(),
            'by_type' => $query->clone()->groupBy('review_type')
                              ->selectRaw('review_type, count(*) as count')
                              ->pluck('count', 'review_type')
                              ->toArray(),
            'by_category' => $query->clone()->whereNotNull('review_category')
                                  ->groupBy('review_category')
                                  ->selectRaw('review_category, count(*) as count')
                                  ->pluck('count', 'review_category')
                                  ->toArray(),
            'ai_vs_manual' => [
                'ai' => $query->clone()->where('is_ai_review', true)->count(),
                'manual' => $query->clone()->where('is_ai_review', false)->count(),
            ],
            'avg_score' => $query->clone()->whereNotNull('review_score')->avg('review_score'),
            'avg_duration' => $query->clone()->whereNotNull('review_duration')->avg('review_duration'),
            'urgent_count' => $query->clone()->where('is_urgent', true)->count(),
        ];

        return $stats;
    }

    // 获取审核趋势
    public static function getReviewTrend($organizationId = null, $days = 30): array
    {
        $query = self::query();

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        $trend = $query->where('created_at', '>=', now()->subDays($days))
                      ->groupBy(\DB::raw('DATE(created_at)'))
                      ->selectRaw('DATE(created_at) as date, count(*) as count')
                      ->orderBy('date')
                      ->get()
                      ->pluck('count', 'date')
                      ->toArray();

        return $trend;
    }

    // 获取审核人排行
    public static function getReviewerRanking($organizationId = null, $limit = 10): array
    {
        $query = self::query();

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        $ranking = $query->groupBy(['reviewer_id', 'reviewer_name'])
                        ->selectRaw('reviewer_id, reviewer_name, count(*) as review_count, avg(review_score) as avg_score')
                        ->orderByDesc('review_count')
                        ->limit($limit)
                        ->get()
                        ->toArray();

        return $ranking;
    }
}
