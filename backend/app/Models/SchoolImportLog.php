<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class SchoolImportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'file_size',
        'file_path',
        'parent_id',
        'user_id',
        'status',
        'total_rows',
        'success_rows',
        'failed_rows',
        'error_details',
        'warning_details',
        'import_options',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'total_rows' => 'integer',
        'success_rows' => 'integer',
        'failed_rows' => 'integer',
        'error_details' => 'array',
        'warning_details' => 'array',
        'import_options' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'status' => 'pending',
        'total_rows' => 0,
        'success_rows' => 0,
        'failed_rows' => 0
    ];

    /**
     * 状态常量
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SUCCESS = 'success';
    const STATUS_PARTIAL_SUCCESS = 'partial_success';
    const STATUS_FAILED = 'failed';

    /**
     * 状态映射
     */
    public static $statusMap = [
        self::STATUS_PENDING => '等待处理',
        self::STATUS_PROCESSING => '处理中',
        self::STATUS_SUCCESS => '成功',
        self::STATUS_PARTIAL_SUCCESS => '部分成功',
        self::STATUS_FAILED => '失败'
    ];

    /**
     * 父级组织关系
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'parent_id');
    }

    /**
     * 导入用户关系
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttribute(): string
    {
        return self::$statusMap[$this->status] ?? '未知';
    }

    /**
     * 获取成功率
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_rows == 0) {
            return 0;
        }
        
        return round(($this->success_rows / $this->total_rows) * 100, 2);
    }

    /**
     * 获取失败率
     */
    public function getFailureRateAttribute(): float
    {
        if ($this->total_rows == 0) {
            return 0;
        }
        
        return round(($this->failed_rows / $this->total_rows) * 100, 2);
    }

    /**
     * 获取处理时长（秒）
     */
    public function getDurationAttribute(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }
        
        return $this->completed_at->diffInSeconds($this->started_at);
    }

    /**
     * 获取格式化的文件大小
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * 检查是否完成
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, [
            self::STATUS_SUCCESS,
            self::STATUS_PARTIAL_SUCCESS,
            self::STATUS_FAILED
        ]);
    }

    /**
     * 检查是否成功
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * 检查是否失败
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * 检查是否部分成功
     */
    public function isPartialSuccess(): bool
    {
        return $this->status === self::STATUS_PARTIAL_SUCCESS;
    }

    /**
     * 获取错误摘要
     */
    public function getErrorSummary(): array
    {
        if (empty($this->error_details)) {
            return [];
        }

        $errorTypes = [];
        foreach ($this->error_details as $error) {
            if (isset($error['errors'])) {
                foreach ($error['errors'] as $errorMsg) {
                    $errorTypes[$errorMsg] = ($errorTypes[$errorMsg] ?? 0) + 1;
                }
            }
        }

        return $errorTypes;
    }

    /**
     * 获取警告摘要
     */
    public function getWarningSummary(): array
    {
        if (empty($this->warning_details)) {
            return [];
        }

        $warningTypes = [];
        foreach ($this->warning_details as $warning) {
            if (isset($warning['message'])) {
                $key = substr($warning['message'], 0, 50) . '...';
                $warningTypes[$key] = ($warningTypes[$key] ?? 0) + 1;
            }
        }

        return $warningTypes;
    }

    /**
     * 作用域：按状态筛选
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * 作用域：按用户筛选
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * 作用域：按时间范围筛选
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * 作用域：最近的记录
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * 获取导入统计摘要
     */
    public static function getStatsSummary($userId = null, $days = 30)
    {
        $query = static::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $query->where('created_at', '>=', now()->subDays($days));

        return $query->selectRaw('
            COUNT(*) as total_imports,
            SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as successful_imports,
            SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_imports,
            SUM(CASE WHEN status = "partial_success" THEN 1 ELSE 0 END) as partial_success_imports,
            SUM(success_rows) as total_schools_imported,
            SUM(failed_rows) as total_errors,
            AVG(success_rows) as avg_schools_per_import
        ')->first();
    }

    /**
     * 获取导入的学校列表
     */
    public function getImportedSchools()
    {
        if (!$this->isSuccessful() && !$this->isPartialSuccess()) {
            return collect();
        }

        // 根据导入时间和父级组织查找可能的学校
        return Organization::where('type', 'school')
            ->where('parent_id', $this->parent_id)
            ->where('created_at', '>=', $this->started_at)
            ->where('created_at', '<=', $this->completed_at)
            ->get();
    }

    /**
     * 生成导入审计报告
     */
    public function generateAuditReport()
    {
        $report = [
            'import_info' => [
                'id' => $this->id,
                'filename' => $this->filename,
                'user' => $this->user->real_name ?? '未知用户',
                'parent_organization' => $this->parent->name ?? '未知组织',
                'started_at' => $this->started_at,
                'completed_at' => $this->completed_at,
                'duration' => $this->duration,
                'status' => $this->status_text
            ],
            'statistics' => [
                'total_rows' => $this->total_rows,
                'success_rows' => $this->success_rows,
                'failed_rows' => $this->failed_rows,
                'success_rate' => $this->success_rate,
                'failure_rate' => $this->failure_rate
            ],
            'imported_schools' => $this->getImportedSchools()->map(function($school) {
                return [
                    'id' => $school->id,
                    'name' => $school->name,
                    'code' => $school->code,
                    'created_at' => $school->created_at
                ];
            }),
            'errors' => $this->error_details ?? [],
            'warnings' => $this->warning_details ?? [],
            'options' => $this->import_options ?? []
        ];

        return $report;
    }

    /**
     * 检查是否可以回滚
     */
    public function canRollback()
    {
        // 只有成功或部分成功的导入才能回滚
        if (!$this->isSuccessful() && !$this->isPartialSuccess()) {
            return false;
        }

        // 检查导入时间是否在允许回滚的时间范围内（比如24小时内）
        $rollbackTimeLimit = now()->subHours(24);
        if ($this->completed_at < $rollbackTimeLimit) {
            return false;
        }

        // 检查导入的学校是否还存在且未被修改
        $importedSchools = $this->getImportedSchools();
        foreach ($importedSchools as $school) {
            // 如果学校的更新时间晚于导入完成时间，说明已被修改
            if ($school->updated_at > $this->completed_at) {
                return false;
            }
        }

        return true;
    }

    /**
     * 执行回滚操作
     */
    public function rollback()
    {
        if (!$this->canRollback()) {
            throw new \Exception('此导入记录不能回滚');
        }

        DB::beginTransaction();
        try {
            $importedSchools = $this->getImportedSchools();
            $deletedCount = 0;

            foreach ($importedSchools as $school) {
                // 检查学校是否有关联数据（如用户、实验等）
                if ($this->hasRelatedData($school)) {
                    continue; // 跳过有关联数据的学校
                }

                $school->delete();
                $deletedCount++;
            }

            // 更新导入日志状态
            $this->update([
                'status' => 'rolled_back',
                'rollback_at' => now(),
                'rollback_details' => [
                    'deleted_schools' => $deletedCount,
                    'skipped_schools' => $importedSchools->count() - $deletedCount,
                    'rollback_reason' => '用户手动回滚'
                ]
            ]);

            DB::commit();
            return $deletedCount;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 检查学校是否有关联数据
     */
    private function hasRelatedData($school)
    {
        // 检查是否有用户关联
        if ($school->users()->count() > 0) {
            return true;
        }

        // 检查是否有子组织
        if ($school->children()->count() > 0) {
            return true;
        }

        // 可以添加更多关联检查
        return false;
    }

    /**
     * 比较两次导入的差异
     */
    public static function compareImports($importId1, $importId2)
    {
        $import1 = static::find($importId1);
        $import2 = static::find($importId2);

        if (!$import1 || !$import2) {
            throw new \Exception('导入记录不存在');
        }

        $schools1 = $import1->getImportedSchools();
        $schools2 = $import2->getImportedSchools();

        return [
            'import1' => [
                'id' => $import1->id,
                'filename' => $import1->filename,
                'date' => $import1->created_at,
                'schools_count' => $schools1->count()
            ],
            'import2' => [
                'id' => $import2->id,
                'filename' => $import2->filename,
                'date' => $import2->created_at,
                'schools_count' => $schools2->count()
            ],
            'comparison' => [
                'common_schools' => $schools1->intersect($schools2)->count(),
                'unique_to_import1' => $schools1->diff($schools2)->count(),
                'unique_to_import2' => $schools2->diff($schools1)->count(),
                'total_difference' => abs($schools1->count() - $schools2->count())
            ]
        ];
    }
}
