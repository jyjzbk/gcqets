<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'level',
        'role_type',
        'status',
        'sort_order',
        'is_default',
        'applicable_org_types'
    ];

    protected $casts = [
        'level' => 'integer',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $attributes = [
        'status' => 'active',
        'role_type' => 'custom',
        'level' => 4,
        'sort_order' => 0,
        'is_default' => false
    ];

    /**
     * 权限级别常量定义
     */
    const LEVEL_SYSTEM_ADMIN = 1;      // 系统管理员
    const LEVEL_ORG_ADMIN = 2;         // 组织管理员
    const LEVEL_DEPT_ADMIN = 3;        // 部门管理员
    const LEVEL_NORMAL_USER = 4;       // 普通用户
    const LEVEL_RESTRICTED_USER = 5;   // 受限用户

    /**
     * 权限级别映射
     */
    public static $levelMap = [
        self::LEVEL_SYSTEM_ADMIN => '系统管理员',
        self::LEVEL_ORG_ADMIN => '组织管理员',
        self::LEVEL_DEPT_ADMIN => '部门管理员',
        self::LEVEL_NORMAL_USER => '普通用户',
        self::LEVEL_RESTRICTED_USER => '受限用户'
    ];

    /**
     * 用户关系
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')
            ->withPivot(['organization_id', 'scope_type', 'effective_date', 'expiry_date', 'status', 'remarks', 'created_by'])
            ->withTimestamps();
    }

    /**
     * 权限关系
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role')
            ->withPivot(['conditions', 'access_type'])
            ->withTimestamps();
    }

    /**
     * 获取角色的有效权限
     */
    public function getActivePermissions()
    {
        return $this->permissions()->wherePivot('status', true)->get();
    }

    /**
     * 检查角色是否有指定权限
     */
    public function hasPermission($permission): bool
    {
        $permissionName = is_string($permission) ? $permission : $permission->name;
        
        return $this->permissions()->where('name', $permissionName)->exists();
    }

    /**
     * 分配权限给角色
     */
    public function givePermissionTo($permission, $assignedBy = null)
    {
        $permissionId = is_object($permission) ? $permission->id : $permission;

        $pivotData = [
            'access_type' => 'allow',
            'created_at' => now(),
            'updated_at' => now()
        ];

        return $this->permissions()->attach($permissionId, $pivotData);
    }

    /**
     * 从角色中移除权限
     */
    public function revokePermissionTo($permission)
    {
        $permissionId = is_object($permission) ? $permission->id : $permission;
        
        return $this->permissions()->detach($permissionId);
    }

    /**
     * 同步角色权限
     */
    public function syncPermissions(array $permissions, $assignedBy = null)
    {
        $syncData = [];

        foreach ($permissions as $permissionId) {
            $syncData[$permissionId] = [
                'access_type' => 'allow'
            ];
        }

        return $this->permissions()->sync($syncData);
    }

    /**
     * 获取角色级别名称
     */
    public function getLevelNameAttribute(): string
    {
        return self::$levelMap[$this->level] ?? '未知级别';
    }

    /**
     * 获取角色显示名称
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->attributes['display_name'] ?: $this->name;
    }

    /**
     * 检查是否为系统角色
     */
    public function isSystemRole(): bool
    {
        return $this->role_type === 'system';
    }

    /**
     * 检查是否为管理员角色
     */
    public function isAdminRole(): bool
    {
        return in_array($this->level, [
            self::LEVEL_SYSTEM_ADMIN,
            self::LEVEL_ORG_ADMIN,
            self::LEVEL_DEPT_ADMIN
        ]);
    }

    /**
     * 检查是否高于指定级别
     */
    public function isHigherThan($level): bool
    {
        return $this->level < $level;
    }

    /**
     * 检查是否低于指定级别
     */
    public function isLowerThan($level): bool
    {
        return $this->level > $level;
    }

    /**
     * 获取角色的数据访问范围类型
     */
    public function getDataScopeType(): string
    {
        switch ($this->level) {
            case self::LEVEL_SYSTEM_ADMIN:
                return 'all'; // 全部数据
            case self::LEVEL_ORG_ADMIN:
                return 'organization'; // 组织及下级数据
            case self::LEVEL_DEPT_ADMIN:
                return 'department'; // 部门及下级数据
            case self::LEVEL_NORMAL_USER:
                return 'department'; // 本部门数据
            case self::LEVEL_RESTRICTED_USER:
                return 'self'; // 仅自己的数据
            default:
                return 'none';
        }
    }

    /**
     * 获取角色可管理的权限级别范围
     */
    public function getManageableLevels(): array
    {
        switch ($this->level) {
            case self::LEVEL_SYSTEM_ADMIN:
                return [2, 3, 4, 5]; // 可管理组织管理员及以下
            case self::LEVEL_ORG_ADMIN:
                return [3, 4, 5]; // 可管理部门管理员及以下
            case self::LEVEL_DEPT_ADMIN:
                return [4, 5]; // 可管理普通用户及以下
            default:
                return []; // 无管理权限
        }
    }

    /**
     * 检查是否可以管理指定级别的角色
     */
    public function canManageLevel($level): bool
    {
        return in_array($level, $this->getManageableLevels());
    }

    /**
     * 查询作用域：活跃角色
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * 查询作用域：指定级别
     */
    public function scopeLevel(Builder $query, int $level): Builder
    {
        return $query->where('level', $level);
    }

    /**
     * 查询作用域：系统角色
     */
    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('role_type', 'system');
    }

    /**
     * 查询作用域：非系统角色
     */
    public function scopeCustom(Builder $query): Builder
    {
        return $query->where('role_type', 'custom');
    }

    /**
     * 查询作用域：管理员角色
     */
    public function scopeAdmin(Builder $query): Builder
    {
        return $query->whereIn('level', [
            self::LEVEL_SYSTEM_ADMIN,
            self::LEVEL_ORG_ADMIN,
            self::LEVEL_DEPT_ADMIN
        ]);
    }

    /**
     * 查询作用域：指定级别及以下
     */
    public function scopeLevelOrBelow(Builder $query, int $level): Builder
    {
        return $query->where('level', '>=', $level);
    }

    /**
     * 查询作用域：指定级别及以上
     */
    public function scopeLevelOrAbove(Builder $query, int $level): Builder
    {
        return $query->where('level', '<=', $level);
    }

    /**
     * 查询作用域：有指定权限的角色
     */
    public function scopeWithPermission(Builder $query, string $permission): Builder
    {
        return $query->whereHas('permissions', function ($q) use ($permission) {
            $q->where('name', $permission);
        });
    }

    /**
     * 获取所有权限级别选项
     */
    public static function getLevelOptions(): array
    {
        return self::$levelMap;
    }

    /**
     * 根据级别获取默认角色
     */
    public static function getDefaultRolesByLevel(): array
    {
        return [
            self::LEVEL_SYSTEM_ADMIN => ['系统管理员', 'Super Admin'],
            self::LEVEL_ORG_ADMIN => ['组织管理员', 'Organization Admin'],
            self::LEVEL_DEPT_ADMIN => ['部门管理员', 'Department Admin'],
            self::LEVEL_NORMAL_USER => ['普通用户', 'Normal User'],
            self::LEVEL_RESTRICTED_USER => ['受限用户', 'Restricted User']
        ];
    }

    /**
     * 创建默认角色
     */
    public static function createDefaultRoles()
    {
        $defaultRoles = self::getDefaultRolesByLevel();
        
        foreach ($defaultRoles as $level => $roleNames) {
            foreach ($roleNames as $index => $roleName) {
                $name = strtolower(str_replace([' ', '管理员', '用户'], ['_', '_admin', '_user'], $roleName));
                
                self::firstOrCreate([
                    'name' => $name,
                ], [
                    'display_name' => $roleName,
                    'level' => $level,
                    'role_type' => 'system',
                    'status' => 'active',
                    'sort_order' => $level * 10 + $index,
                    'description' => "系统默认{$roleName}角色"
                ]);
            }
        }
    }

    /**
     * 验证权限级别
     */
    public static function validateLevel(int $level): bool
    {
        return array_key_exists($level, self::$levelMap);
    }

    /**
     * 模型启动方法
     */
    protected static function boot()
    {
        parent::boot();

        // 创建时验证级别
        static::creating(function ($role) {
            if (!self::validateLevel($role->level)) {
                throw new \InvalidArgumentException("Invalid role level: {$role->level}");
            }
        });

        // 更新时验证级别
        static::updating(function ($role) {
            if ($role->isDirty('level') && !self::validateLevel($role->level)) {
                throw new \InvalidArgumentException("Invalid role level: {$role->level}");
            }
        });

        // 删除时检查是否为系统角色
        static::deleting(function ($role) {
            if ($role->role_type === 'system') {
                throw new \Exception('Cannot delete system role');
            }
            
            // 检查是否有用户使用此角色
            if ($role->users()->count() > 0) {
                throw new \Exception('Cannot delete role that is assigned to users');
            }
        });
    }
}