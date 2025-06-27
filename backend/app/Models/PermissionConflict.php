<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class PermissionConflict extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization_id',
        'permission_id',
        'conflict_type',
        'conflict_sources',
        'severity',
        'description',
        'resolution_suggestions',
        'status',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
        'detected_at'
    ];

    protected $casts = [
        'conflict_sources' => 'array',
        'resolution_suggestions' => 'array',
        'resolved_at' => 'datetime',
        'detected_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'severity' => 'medium',
        'status' => 'active'
    ];

    /**
     * 用户关系
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
     * 解决者关系
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * 作用域：活跃的冲突
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * 作用域：已解决的冲突
     */
    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', 'resolved');
    }

    /**
     * 作用域：按严重程度筛选
     */
    public function scopeBySeverity(Builder $query, string $severity): Builder
    {
        return $query->where('severity', $severity);
    }

    /**
     * 作用域：按冲突类型筛选
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('conflict_type', $type);
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
        $rolePermissions = collect();
        $directPermissions = $user->permissions;

        // 收集所有角色权限
        foreach ($user->roles as $role) {
            foreach ($role->permissions as $permission) {
                $rolePermissions->push([
                    'permission_id' => $permission->id,
                    'permission_name' => $permission->name,
                    'role_id' => $role->id,
                    'role_name' => $role->name,
                    'source' => 'role'
                ]);
            }
        }

        // 检测直接权限与角色权限的冲突
        foreach ($directPermissions as $directPermission) {
            $conflictingRoles = $rolePermissions->where('permission_id', $directPermission->id);
            
            if ($conflictingRoles->isNotEmpty()) {
                $conflicts[] = [
                    'user_id' => $userId,
                    'permission_id' => $directPermission->id,
                    'conflict_type' => 'direct_permission',
                    'severity' => 'medium',
                    'description' => "权限 '{$directPermission->name}' 同时通过直接分配和角色获得",
                    'conflict_sources' => [
                        'direct' => [
                            'type' => 'direct',
                            'permission_name' => $directPermission->name
                        ],
                        'roles' => $conflictingRoles->toArray()
                    ],
                    'resolution_suggestions' => [
                        '移除直接分配的权限，保留角色权限',
                        '移除角色中的权限，保留直接分配'
                    ]
                ];
            }
        }

        // 检测角色间权限冲突（相同权限来自多个角色）
        $permissionRoleMap = $rolePermissions->groupBy('permission_id');
        foreach ($permissionRoleMap as $permissionId => $sources) {
            if ($sources->count() > 1) {
                $permission = Permission::find($permissionId);
                $conflicts[] = [
                    'user_id' => $userId,
                    'permission_id' => $permissionId,
                    'conflict_type' => 'role_permission',
                    'severity' => 'low',
                    'description' => "权限 '{$permission->name}' 来自多个角色",
                    'conflict_sources' => $sources->toArray(),
                    'resolution_suggestions' => [
                        '保留一个主要角色的权限',
                        '创建新的角色合并权限'
                    ]
                ];
            }
        }

        return $conflicts;
    }

    /**
     * 标记冲突为已解决
     */
    public function markAsResolved(int $resolvedBy, string $notes = null): bool
    {
        return $this->update([
            'status' => 'resolved',
            'resolved_by' => $resolvedBy,
            'resolved_at' => now(),
            'resolution_notes' => $notes
        ]);
    }

    /**
     * 忽略冲突
     */
    public function ignore(int $userId, string $reason = null): bool
    {
        return $this->update([
            'status' => 'ignored',
            'resolved_by' => $userId,
            'resolved_at' => now(),
            'resolution_notes' => $reason ?: '用户选择忽略此冲突'
        ]);
    }

    /**
     * 获取冲突统计
     */
    public static function getConflictStats(array $filters = []): array
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
            $query->where('detected_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('detected_at', '<=', $filters['date_to']);
        }

        return [
            'total' => $query->count(),
            'active' => $query->where('status', 'active')->count(),
            'resolved' => $query->where('status', 'resolved')->count(),
            'ignored' => $query->where('status', 'ignored')->count(),
            'by_severity' => [
                'low' => $query->where('severity', 'low')->count(),
                'medium' => $query->where('severity', 'medium')->count(),
                'high' => $query->where('severity', 'high')->count(),
                'critical' => $query->where('severity', 'critical')->count()
            ],
            'by_type' => [
                'role_permission' => $query->where('conflict_type', 'role_permission')->count(),
                'direct_permission' => $query->where('conflict_type', 'direct_permission')->count(),
                'inheritance' => $query->where('conflict_type', 'inheritance')->count(),
                'template' => $query->where('conflict_type', 'template')->count()
            ]
        ];
    }
}
