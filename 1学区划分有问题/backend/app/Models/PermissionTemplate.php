<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermissionTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'template_type',
        'target_level',
        'permission_ids',
        'conditions',
        'is_system',
        'is_default',
        'status',
        'created_by',
        'updated_by',
        'sort_order'
    ];

    protected $casts = [
        'permission_ids' => 'array',
        'conditions' => 'array',
        'is_system' => 'boolean',
        'is_default' => 'boolean',
        'target_level' => 'integer',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $attributes = [
        'is_system' => false,
        'is_default' => false,
        'status' => 'active',
        'sort_order' => 0
    ];

    /**
     * 创建者关系
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 更新者关系
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 权限关系（通过permission_ids数组）
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_template_permissions', 'template_id', 'permission_id');
    }

    /**
     * 作用域：活跃状态
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * 作用域：系统模板
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * 作用域：自定义模板
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * 作用域：默认模板
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * 作用域：按模板类型
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('template_type', $type);
    }

    /**
     * 作用域：按目标级别
     */
    public function scopeByLevel($query, int $level)
    {
        return $query->where('target_level', $level);
    }

    /**
     * 获取权限对象集合
     */
    public function getPermissionObjectsAttribute()
    {
        if (!$this->permission_ids) {
            return collect();
        }

        return Permission::whereIn('id', $this->permission_ids)->get();
    }

    /**
     * 获取权限数量
     */
    public function getPermissionCountAttribute(): int
    {
        return $this->permission_ids ? count($this->permission_ids) : 0;
    }

    /**
     * 检查是否可以应用到指定目标
     */
    public function canApplyTo(string $targetType, int $targetLevel = null): bool
    {
        // 检查模板类型匹配
        if ($this->template_type !== $targetType) {
            return false;
        }

        // 检查目标级别匹配
        if ($this->target_level && $targetLevel && $this->target_level !== $targetLevel) {
            return false;
        }

        // 检查状态
        if ($this->status !== 'active') {
            return false;
        }

        return true;
    }

    /**
     * 应用模板到角色
     */
    public function applyToRole(Role $role, int $userId = null): bool
    {
        if (!$this->canApplyTo('role', $role->level)) {
            return false;
        }

        try {
            $role->permissions()->sync($this->permission_ids);
            
            // 记录审计日志
            PermissionAuditLog::logPermissionAction([
                'user_id' => $userId,
                'role_id' => $role->id,
                'action' => 'modify',
                'target_type' => 'role',
                'target_name' => $role->display_name,
                'new_values' => [
                    'template_applied' => $this->name,
                    'permission_ids' => $this->permission_ids
                ],
                'reason' => "应用权限模板: {$this->display_name}"
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 应用模板到用户
     */
    public function applyToUser(User $user, int $organizationId = null, int $userId = null): bool
    {
        if (!$this->canApplyTo('user')) {
            return false;
        }

        try {
            $syncData = [];
            foreach ($this->permission_ids as $permissionId) {
                $syncData[$permissionId] = [
                    'organization_id' => $organizationId,
                    'granted_by' => $userId,
                    'granted_at' => now(),
                    'status' => 'active'
                ];
            }

            $user->permissions()->sync($syncData);
            
            // 记录审计日志
            PermissionAuditLog::logPermissionAction([
                'user_id' => $userId,
                'target_user_id' => $user->id,
                'organization_id' => $organizationId,
                'action' => 'modify',
                'target_type' => 'user',
                'target_name' => $user->name,
                'new_values' => [
                    'template_applied' => $this->name,
                    'permission_ids' => $this->permission_ids
                ],
                'reason' => "应用权限模板: {$this->display_name}"
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 创建默认权限模板
     */
    public static function createDefaultTemplates(): void
    {
        $templates = [
            [
                'name' => 'system_admin',
                'display_name' => '系统管理员模板',
                'description' => '系统管理员的完整权限模板',
                'template_type' => 'role',
                'target_level' => 1,
                'permission_ids' => Permission::where('is_system', true)->pluck('id')->toArray(),
                'is_system' => true,
                'is_default' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'org_admin',
                'display_name' => '组织管理员模板',
                'description' => '组织管理员的基础权限模板',
                'template_type' => 'role',
                'target_level' => 2,
                'permission_ids' => Permission::where('module', 'organization')->pluck('id')->toArray(),
                'is_system' => true,
                'is_default' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'teacher',
                'display_name' => '教师模板',
                'description' => '教师的基础权限模板',
                'template_type' => 'role',
                'target_level' => 5,
                'permission_ids' => Permission::whereIn('module', ['teaching', 'student'])->pluck('id')->toArray(),
                'is_system' => true,
                'is_default' => true,
                'sort_order' => 3
            ]
        ];

        foreach ($templates as $template) {
            static::updateOrCreate(
                ['name' => $template['name'], 'template_type' => $template['template_type']],
                $template
            );
        }
    }

    /**
     * 获取推荐模板
     */
    public static function getRecommendedTemplates(string $targetType, int $targetLevel = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = static::active()
            ->byType($targetType)
            ->orderBy('sort_order');

        if ($targetLevel) {
            $query->where(function ($q) use ($targetLevel) {
                $q->whereNull('target_level')
                  ->orWhere('target_level', $targetLevel);
            });
        }

        return $query->get();
    }
}
