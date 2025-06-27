<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class PermissionAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'target_user_id',
        'organization_id',
        'permission_id',
        'role_id',
        'action',
        'subject_type',
        'subject_id',
        'permission_name',
        'old_values',
        'new_values',
        'reason',
        'ip_address',
        'user_agent',
        'context',
        'result',
        'error_message'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'context' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'result' => 'success'
    ];

    /**
     * 操作用户关系
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 目标用户关系
     */
    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    /**
     * 组织关系
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * 权限关系
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * 角色关系
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * 作用域：按操作类型筛选
     */
    public function scopeByAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    /**
     * 作用域：按主体类型筛选
     */
    public function scopeBySubjectType(Builder $query, string $subjectType): Builder
    {
        return $query->where('subject_type', $subjectType);
    }

    /**
     * 作用域：按结果筛选
     */
    public function scopeByResult(Builder $query, string $result): Builder
    {
        return $query->where('result', $result);
    }

    /**
     * 作用域：按时间范围筛选
     */
    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * 作用域：成功的操作
     */
    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('result', 'success');
    }

    /**
     * 作用域：失败的操作
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('result', 'failed');
    }

    /**
     * 记录权限分配操作
     */
    public static function logGrant(array $data): self
    {
        return static::create([
            'user_id' => $data['user_id'] ?? auth()->id(),
            'target_user_id' => $data['target_user_id'] ?? null,
            'organization_id' => $data['organization_id'] ?? null,
            'permission_id' => $data['permission_id'] ?? null,
            'role_id' => $data['role_id'] ?? null,
            'action' => 'grant',
            'subject_type' => $data['subject_type'],
            'subject_id' => $data['subject_id'],
            'permission_name' => $data['permission_name'] ?? null,
            'new_values' => $data['new_values'] ?? null,
            'reason' => $data['reason'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'context' => $data['context'] ?? null,
            'result' => $data['result'] ?? 'success',
            'error_message' => $data['error_message'] ?? null
        ]);
    }

    /**
     * 记录权限撤销操作
     */
    public static function logRevoke(array $data): self
    {
        return static::create([
            'user_id' => $data['user_id'] ?? auth()->id(),
            'target_user_id' => $data['target_user_id'] ?? null,
            'organization_id' => $data['organization_id'] ?? null,
            'permission_id' => $data['permission_id'] ?? null,
            'role_id' => $data['role_id'] ?? null,
            'action' => 'revoke',
            'subject_type' => $data['subject_type'],
            'subject_id' => $data['subject_id'],
            'permission_name' => $data['permission_name'] ?? null,
            'old_values' => $data['old_values'] ?? null,
            'reason' => $data['reason'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'context' => $data['context'] ?? null,
            'result' => $data['result'] ?? 'success',
            'error_message' => $data['error_message'] ?? null
        ]);
    }

    /**
     * 记录权限更新操作
     */
    public static function logUpdate(array $data): self
    {
        return static::create([
            'user_id' => $data['user_id'] ?? auth()->id(),
            'target_user_id' => $data['target_user_id'] ?? null,
            'organization_id' => $data['organization_id'] ?? null,
            'permission_id' => $data['permission_id'] ?? null,
            'role_id' => $data['role_id'] ?? null,
            'action' => 'update',
            'subject_type' => $data['subject_type'],
            'subject_id' => $data['subject_id'],
            'permission_name' => $data['permission_name'] ?? null,
            'old_values' => $data['old_values'] ?? null,
            'new_values' => $data['new_values'] ?? null,
            'reason' => $data['reason'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'context' => $data['context'] ?? null,
            'result' => $data['result'] ?? 'success',
            'error_message' => $data['error_message'] ?? null
        ]);
    }

    /**
     * 记录模板应用操作
     */
    public static function logTemplateApply(array $data): self
    {
        return static::create([
            'user_id' => $data['user_id'] ?? auth()->id(),
            'target_user_id' => $data['target_user_id'] ?? null,
            'organization_id' => $data['organization_id'] ?? null,
            'action' => 'template_apply',
            'subject_type' => $data['subject_type'],
            'subject_id' => $data['subject_id'],
            'new_values' => $data['template_data'] ?? null,
            'reason' => $data['reason'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'context' => $data['context'] ?? null,
            'result' => $data['result'] ?? 'success',
            'error_message' => $data['error_message'] ?? null
        ]);
    }

    /**
     * 获取审计统计
     */
    public static function getAuditStats(array $filters = []): array
    {
        $query = static::query();

        // 应用筛选条件
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return [
            'total_logs' => $query->count(),
            'grant_count' => $query->where('action', 'grant')->count(),
            'revoke_count' => $query->where('action', 'revoke')->count(),
            'update_count' => $query->where('action', 'update')->count(),
            'template_apply_count' => $query->where('action', 'template_apply')->count(),
            'success_rate' => $query->where('result', 'success')->count() / max($query->count(), 1) * 100,
            'by_subject_type' => [
                'user' => $query->where('subject_type', 'user')->count(),
                'role' => $query->where('subject_type', 'role')->count(),
                'organization' => $query->where('subject_type', 'organization')->count(),
                'template' => $query->where('subject_type', 'template')->count()
            ]
        ];
    }

    /**
     * 获取用户操作历史
     */
    public static function getUserHistory(int $userId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('user_id', $userId)
            ->with(['targetUser', 'organization', 'permission', 'role'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * 获取权限热点（最常操作的权限）
     */
    public static function getPermissionHotspots(int $limit = 10): array
    {
        return static::selectRaw('permission_id, permission_name, COUNT(*) as operation_count')
            ->whereNotNull('permission_id')
            ->groupBy('permission_id', 'permission_name')
            ->orderBy('operation_count', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
