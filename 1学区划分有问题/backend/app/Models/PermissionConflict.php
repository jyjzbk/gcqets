<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermissionConflict extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role_id',
        'organization_id',
        'permission_id',
        'conflict_type',
        'conflict_sources',
        'resolution_strategy',
        'priority',
        'status',
        'resolved_by',
        'resolved_at',
        'resolution_notes'
    ];

    protected $casts = [
        'conflict_sources' => 'array',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'resolution_strategy' => 'manual',
        'priority' => 'medium',
        'status' => 'unresolved'
    ];

    /**
     * 用户关系
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 角色关系
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * 组织机构关系
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
     * 解决者关系
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * 作用域：未解决
     */
    public function scopeUnresolved($query)
    {
        return $query->where('status', 'unresolved');
    }

    /**
     * 作用域：已解决
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * 作用域：已忽略
     */
    public function scopeIgnored($query)
    {
        return $query->where('status', 'ignored');
    }

    /**
     * 作用域：高优先级
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    /**
     * 作用域：按冲突类型
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('conflict_type', $type);
    }

    /**
     * 获取冲突描述
     */
    public function getConflictDescriptionAttribute(): string
    {
        $typeMap = [
            'role_user' => '角色与用户权限冲突',
            'role_role' => '角色间权限冲突',
            'inheritance' => '权限继承冲突',
            'explicit_deny' => '显式拒绝冲突'
        ];

        return $typeMap[$this->conflict_type] ?? '未知冲突类型';
    }

    /**
     * 获取优先级标签
     */
    public function getPriorityLabelAttribute(): string
    {
        $labelMap = [
            'high' => '高',
            'medium' => '中',
            'low' => '低'
        ];

        return $labelMap[$this->priority] ?? '未知';
    }

    /**
     * 获取状态标签
     */
    public function getStatusLabelAttribute(): string
    {
        $labelMap = [
            'unresolved' => '未解决',
            'resolved' => '已解决',
            'ignored' => '已忽略'
        ];

        return $labelMap[$this->status] ?? '未知';
    }

    /**
     * 解决冲突
     */
    public function resolve(int $userId, string $strategy = null, string $notes = null): bool
    {
        $strategy = $strategy ?: $this->resolution_strategy;
        
        try {
            $this->update([
                'status' => 'resolved',
                'resolution_strategy' => $strategy,
                'resolved_by' => $userId,
                'resolved_at' => now(),
                'resolution_notes' => $notes
            ]);

            // 记录审计日志
            PermissionAuditLog::logPermissionAction([
                'user_id' => $userId,
                'target_user_id' => $this->user_id,
                'role_id' => $this->role_id,
                'permission_id' => $this->permission_id,
                'organization_id' => $this->organization_id,
                'action' => 'modify',
                'target_type' => 'conflict',
                'target_name' => "冲突#{$this->id}",
                'new_values' => [
                    'status' => 'resolved',
                    'strategy' => $strategy
                ],
                'reason' => $notes ?: '解决权限冲突'
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 忽略冲突
     */
    public function ignore(int $userId, string $notes = null): bool
    {
        try {
            $this->update([
                'status' => 'ignored',
                'resolved_by' => $userId,
                'resolved_at' => now(),
                'resolution_notes' => $notes
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 检测用户权限冲突
     */
    public static function detectUserConflicts(int $userId): array
    {
        $user = User::with(['roles.permissions', 'permissions'])->find($userId);
        if (!$user) {
            return [];
        }

        $conflicts = [];
        
        // 获取用户通过角色获得的权限
        $rolePermissions = collect();
        foreach ($user->roles as $role) {
            foreach ($role->permissions as $permission) {
                $rolePermissions->push([
                    'permission_id' => $permission->id,
                    'source_type' => 'role',
                    'source_id' => $role->id,
                    'source_name' => $role->display_name
                ]);
            }
        }

        // 获取用户直接分配的权限
        $directPermissions = $user->permissions->map(function ($permission) {
            return [
                'permission_id' => $permission->id,
                'source_type' => 'direct',
                'source_id' => null,
                'source_name' => '直接分配'
            ];
        });

        // 检测冲突
        $allPermissions = $rolePermissions->merge($directPermissions);
        $permissionGroups = $allPermissions->groupBy('permission_id');

        foreach ($permissionGroups as $permissionId => $sources) {
            if ($sources->count() > 1) {
                $conflicts[] = [
                    'user_id' => $userId,
                    'permission_id' => $permissionId,
                    'conflict_type' => 'role_user',
                    'conflict_sources' => $sources->toArray(),
                    'priority' => 'medium'
                ];
            }
        }

        return $conflicts;
    }

    /**
     * 批量创建冲突记录
     */
    public static function createConflicts(array $conflicts): int
    {
        $created = 0;
        
        foreach ($conflicts as $conflict) {
            try {
                static::updateOrCreate([
                    'user_id' => $conflict['user_id'] ?? null,
                    'role_id' => $conflict['role_id'] ?? null,
                    'organization_id' => $conflict['organization_id'] ?? null,
                    'permission_id' => $conflict['permission_id'],
                    'conflict_type' => $conflict['conflict_type']
                ], $conflict);
                
                $created++;
            } catch (\Exception $e) {
                // 记录错误但继续处理其他冲突
                \Log::error('创建权限冲突记录失败', [
                    'conflict' => $conflict,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $created;
    }

    /**
     * 获取冲突统计
     */
    public static function getConflictStats(): array
    {
        return [
            'total' => static::count(),
            'unresolved' => static::unresolved()->count(),
            'high_priority' => static::unresolved()->highPriority()->count(),
            'by_type' => static::selectRaw('conflict_type, COUNT(*) as count')
                ->groupBy('conflict_type')
                ->pluck('count', 'conflict_type')
                ->toArray(),
            'by_priority' => static::unresolved()
                ->selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->pluck('count', 'priority')
                ->toArray()
        ];
    }
}
