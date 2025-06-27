<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'phone',
        'avatar',
        'gender',
        'birth_date',
        'id_card',
        'address',
        'employee_id',
        'user_type',
        'status',
        'department',
        'position',
        'title',
        'hire_date',
        'preferences',
        'remarks',
        'primary_organization_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birth_date' => 'date',
        'hire_date' => 'date',
        'last_login_at' => 'datetime',
        'preferences' => 'json',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * 默认属性值
     */
    protected $attributes = [
        'status' => true,
        'user_type' => 'teacher',
        'gender' => 'other'
    ];

    /**
     * 组织机构关系（多对多）
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'user_organizations')
            ->withPivot(['is_primary', 'status'])
            ->withTimestamps();
    }

    /**
     * 主要组织机构关系
     */
    public function primaryOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'primary_organization_id');
    }

    /**
     * 角色关系（多对多）
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withPivot(['organization_id', 'scope_type', 'effective_date', 'expiry_date', 'status', 'remarks', 'created_by'])
            ->withTimestamps();
    }

    /**
     * 权限关系（多对多，通过角色）
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->withPivot(['granted_by', 'granted_at', 'expires_at', 'status'])
            ->withTimestamps();
    }

    /**
     * 获取用户在指定组织中的角色
     */
    public function rolesInOrganization($organizationId): BelongsToMany
    {
        return $this->roles()->wherePivot('organization_id', $organizationId);
    }

    /**
     * 检查用户是否有指定权限
     */
    public function hasPermission($permission, $organizationId = null): bool
    {
        // 通过角色获取权限
        return $this->roles()->whereHas('permissions', function ($query) use ($permission) {
            $query->where('name', $permission)
                  ->where('access_type', 'allow');
        })->exists();
    }

    /**
     * 检查用户是否有指定角色
     */
    public function hasRole($role): bool
    {
        if (is_string($role)) {
            return $this->roles()->where('name', $role)->exists();
        }
        
        if (is_array($role)) {
            return $this->roles()->whereIn('name', $role)->exists();
        }
        
        return false;
    }

    /**
     * 获取用户的所有权限
     */
    public function getAllPermissions()
    {
        $rolePermissions = Permission::whereHas('roles', function ($query) {
            $query->whereIn('roles.id', $this->roles()->pluck('roles.id'));
        })->get();

        $directPermissions = $this->permissions()->get();

        return $rolePermissions->merge($directPermissions)->unique('id');
    }

    /**
     * 更新最后登录信息
     */
    public function updateLastLogin($ip = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip
        ]);
    }

    /**
     * 获取用户数据访问范围
     */
    public function getDataAccessScope(): array
    {
        $maxLevel = 5; // 默认最低级别
        $organizationIds = [];

        // 获取用户所有角色中的最高级别
        foreach ($this->organizations as $organization) {
            $roles = $this->rolesInOrganization($organization->id)->get();
            foreach ($roles as $role) {
                if ($role->level < $maxLevel) {
                    $maxLevel = $role->level;
                }
            }
            $organizationIds[] = $organization->id;
        }

        // 根据用户的最高角色级别确定数据访问范围
        switch ($maxLevel) {
            case 1: // 系统管理员
                return ['type' => 'all', 'organizations' => []];
            case 2: // 组织管理员
            case 3: // 部门管理员
            case 4: // 普通用户
                return ['type' => 'specific', 'organizations' => $organizationIds];
            default:
                return ['type' => 'none', 'organizations' => []];
        }
    }

    /**
     * 获取用户可管理的角色级别
     */
    public function getManageableLevels(): array
    {
        $maxLevel = 5; // 默认最低级别

        // 获取用户所有角色中的最高级别
        foreach ($this->organizations as $organization) {
            $roles = $this->rolesInOrganization($organization->id)->get();
            foreach ($roles as $role) {
                if ($role->level < $maxLevel) {
                    $maxLevel = $role->level;
                }
            }
        }

        // 根据用户的最高角色级别确定可管理的级别
        switch ($maxLevel) {
            case 1: // 系统管理员
                return [2, 3, 4, 5]; // 可管理组织管理员及以下
            case 2: // 组织管理员
                return [3, 4, 5]; // 可管理部门管理员及以下
            case 3: // 部门管理员
                return [4, 5]; // 可管理普通用户及以下
            default:
                return []; // 无管理权限
        }
    }

    /**
     * 检查用户是否可以管理指定级别的角色
     */
    public function canManageLevel($level): bool
    {
        return in_array($level, $this->getManageableLevels());
    }

    /**
     * 检查用户是否可以访问指定组织
     */
    public function canAccessOrganization($organizationId): bool
    {
        // 获取用户的数据访问范围
        $accessScope = $this->getDataAccessScope();

        // 如果用户有全局权限，可以访问所有组织
        if ($accessScope['type'] === 'all') {
            return true;
        }

        // 如果用户没有任何权限，不能访问任何组织
        if ($accessScope['type'] === 'none') {
            return false;
        }

        // 如果是特定组织权限，检查是否在允许的组织列表中
        if ($accessScope['type'] === 'specific') {
            return in_array($organizationId, $accessScope['organizations']);
        }

        return false;
    }

    /**
     * 查询作用域：活跃用户
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * 查询作用域：指定用户类型
     */
    public function scopeUserType(Builder $query, string $type): Builder
    {
        return $query->where('user_type', $type);
    }

    /**
     * 查询作用域：指定组织的用户
     */
    public function scopeInOrganization(Builder $query, $organizationId): Builder
    {
        return $query->whereHas('organizations', function ($q) use ($organizationId) {
            $q->where('organizations.id', $organizationId);
        });
    }

    /**
     * 查询作用域：有指定角色的用户
     */
    public function scopeWithRole(Builder $query, string $role): Builder
    {
        return $query->whereHas('roles', function ($q) use ($role) {
            $q->where('name', $role);
        });
    }

    /**
     * 查询作用域：有指定权限的用户
     */
    public function scopeWithPermission(Builder $query, string $permission): Builder
    {
        return $query->whereHas('roles.permissions', function ($q) use ($permission) {
            $q->where('name', $permission);
        });
    }

    /**
     * 获取用户显示名称
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: $this->username;
    }

    /**
     * 获取用户头像URL
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        
        // 返回默认头像
        return asset('images/default-avatar.png');
    }

    /**
     * 模型启动方法
     */
    protected static function boot()
    {
        parent::boot();

        // 创建用户时的处理
        static::creating(function ($user) {
            // 如果没有设置用户名，使用邮箱前缀
            if (!$user->username && $user->email) {
                $user->username = explode('@', $user->email)[0];
            }
        });
    }
}
