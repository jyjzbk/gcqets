<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermissionAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'target_user_id',
        'role_id',
        'permission_id',
        'organization_id',
        'action',
        'target_type',
        'target_name',
        'old_values',
        'new_values',
        'reason',
        'ip_address',
        'user_agent',
        'status',
        'error_message'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'status' => 'success'
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
     * 角色关系
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * 权限关系
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * 组织机构关系
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * 作用域：成功操作
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * 作用域：失败操作
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * 作用域：待处理操作
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * 作用域：按操作类型
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * 作用域：按目标类型
     */
    public function scopeByTargetType($query, string $targetType)
    {
        return $query->where('target_type', $targetType);
    }

    /**
     * 作用域：按时间范围
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * 获取操作描述
     */
    public function getActionDescriptionAttribute(): string
    {
        $actionMap = [
            'grant' => '授予',
            'revoke' => '撤销',
            'modify' => '修改',
            'inherit' => '继承',
            'override' => '覆盖'
        ];

        $targetTypeMap = [
            'user' => '用户',
            'role' => '角色',
            'organization' => '组织'
        ];

        $action = $actionMap[$this->action] ?? $this->action;
        $targetType = $targetTypeMap[$this->target_type] ?? $this->target_type;

        return "{$action}{$targetType}权限";
    }

    /**
     * 获取变更摘要
     */
    public function getChangesSummaryAttribute(): array
    {
        $summary = [];

        if ($this->old_values && $this->new_values) {
            foreach ($this->new_values as $key => $newValue) {
                $oldValue = $this->old_values[$key] ?? null;
                if ($oldValue !== $newValue) {
                    $summary[] = [
                        'field' => $key,
                        'old_value' => $oldValue,
                        'new_value' => $newValue
                    ];
                }
            }
        }

        return $summary;
    }

    /**
     * 记录权限操作日志
     */
    public static function logPermissionAction(array $data): self
    {
        // 自动获取请求信息
        if (request()) {
            $data['ip_address'] = $data['ip_address'] ?? request()->ip();
            $data['user_agent'] = $data['user_agent'] ?? request()->userAgent();
        }

        // 自动获取当前用户
        if (!isset($data['user_id']) && auth()->check()) {
            $data['user_id'] = auth()->id();
        }

        return static::create($data);
    }

    /**
     * 获取用户操作统计
     */
    public static function getUserActionStats(int $userId, int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        return static::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();
    }

    /**
     * 获取组织权限变更统计
     */
    public static function getOrganizationChangeStats(int $organizationId, int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        return static::where('organization_id', $organizationId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, action, COUNT(*) as count')
            ->groupBy(['date', 'action'])
            ->orderBy('date')
            ->get()
            ->groupBy('date')
            ->map(function ($items) {
                return $items->pluck('count', 'action')->toArray();
            })
            ->toArray();
    }

    /**
     * 获取权限热点分析
     */
    public static function getPermissionHotspots(int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        return static::where('created_at', '>=', $startDate)
            ->whereNotNull('permission_id')
            ->with('permission')
            ->selectRaw('permission_id, COUNT(*) as operation_count')
            ->groupBy('permission_id')
            ->orderByDesc('operation_count')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'permission' => $item->permission,
                    'operation_count' => $item->operation_count
                ];
            })
            ->toArray();
    }
}
