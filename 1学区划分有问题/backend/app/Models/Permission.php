<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Permission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'module',
        'action',
        'min_level',
        'applicable_levels',
        'scope_type',
        'is_system',
        'status',
        'sort_order'
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_menu' => 'boolean',
        'is_system' => 'boolean',
        'level' => 'integer',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $attributes = [
        'status' => 'active',
        'is_system' => false,
        'sort_order' => 0
    ];

    /**
     * 权限分组常量
     */
    const GROUP_SYSTEM = 'system';      // 系统管理
    const GROUP_USER = 'user';          // 用户管理
    const GROUP_ROLE = 'role';          // 角色管理
    const GROUP_PERMISSION = 'permission'; // 权限管理
    const GROUP_ORG = 'organization';   // 组织管理
    const GROUP_CONTENT = 'content';    // 内容管理
    const GROUP_REPORT = 'report';      // 报表管理
    const GROUP_LOG = 'log';            // 日志管理

    /**
     * 权限分组映射
     */
    public static $groupMap = [
        self::GROUP_SYSTEM => '系统管理',
        self::GROUP_USER => '用户管理',
        self::GROUP_ROLE => '角色管理',
        self::GROUP_PERMISSION => '权限管理',
        self::GROUP_ORG => '组织管理',
        self::GROUP_CONTENT => '内容管理',
        self::GROUP_REPORT => '报表管理',
        self::GROUP_LOG => '日志管理'
    ];

    /**
     * 角色关系
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role')
            ->withPivot(['conditions', 'access_type'])
            ->withTimestamps();
    }

    /**
     * 用户关系（直接分配的权限）
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_permissions')
            ->withPivot(['organization_id', 'granted_by', 'granted_at', 'status'])
            ->withTimestamps();
    }

    /**
     * 父级权限关系
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'parent_id');
    }

    /**
     * 子级权限关系
     */
    public function children(): HasMany
    {
        return $this->hasMany(Permission::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * 递归获取所有子级权限
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * 获取权限的所有祖先
     */
    public function getAncestors()
    {
        $ancestors = collect();
        $current = $this;

        while ($current->parent_id) {
            $parent = static::find($current->parent_id);
            if ($parent) {
                $ancestors->prepend($parent);
                $current = $parent;
            } else {
                break;
            }
        }

        return $ancestors;
    }

    /**
     * 获取权限的所有后代
     */
    public function getDescendants()
    {
        $descendants = collect();
        $this->collectDescendants($this, $descendants);
        return $descendants;
    }

    /**
     * 递归收集后代权限
     */
    private function collectDescendants($permission, &$descendants)
    {
        $children = $permission->children;
        
        foreach ($children as $child) {
            $descendants->push($child);
            $this->collectDescendants($child, $descendants);
        }
    }

    /**
     * 检查是否为根权限
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * 检查是否为叶子节点
     */
    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }

    /**
     * 检查是否为菜单权限
     */
    public function isMenu(): bool
    {
        return $this->is_menu;
    }

    /**
     * 检查是否为系统权限
     */
    public function isSystem(): bool
    {
        return $this->is_system;
    }

    /**
     * 获取权限完整名称（包含层级路径）
     */
    public function getFullNameAttribute(): string
    {
        $names = collect([$this->display_name ?: $this->name]);
        $ancestors = $this->getAncestors();
        
        foreach ($ancestors as $ancestor) {
            $names->prepend($ancestor->display_name ?: $ancestor->name);
        }

        return $names->implode(' > ');
    }

    /**
     * 获取权限路径
     */
    public function getPathAttribute(): string
    {
        if (isset($this->attributes['path']) && $this->attributes['path']) {
            return $this->attributes['path'];
        }

        $path = collect([$this->id]);
        $ancestors = $this->getAncestors();

        foreach ($ancestors as $ancestor) {
            $path->prepend($ancestor->id);
        }

        return $path->implode('/');
    }

    /**
     * 获取权限分组名称
     */
    public function getGroupNameAttribute(): string
    {
        return self::$groupMap[$this->group] ?? $this->group;
    }

    /**
     * 获取权限显示名称
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->attributes['display_name'] ?: $this->name;
    }

    /**
     * 查询作用域：活跃权限
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * 查询作用域：菜单权限
     */
    public function scopeMenu(Builder $query): Builder
    {
        return $query->where('is_menu', true);
    }

    /**
     * 查询作用域：非菜单权限
     */
    public function scopeAction(Builder $query): Builder
    {
        return $query->where('is_menu', false);
    }

    /**
     * 查询作用域：系统权限
     */
    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('is_system', true);
    }

    /**
     * 查询作用域：自定义权限
     */
    public function scopeCustom(Builder $query): Builder
    {
        return $query->where('is_system', false);
    }

    /**
     * 查询作用域：根权限
     */
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * 查询作用域：指定分组
     */
    public function scopeGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    /**
     * 查询作用域：指定模块
     */
    public function scopeModule(Builder $query, string $module): Builder
    {
        return $query->where('module', $module);
    }

    /**
     * 查询作用域：指定父级
     */
    public function scopeChildrenOf(Builder $query, int $parentId): Builder
    {
        return $query->where('parent_id', $parentId);
    }

    /**
     * 查询作用域：指定级别
     */
    public function scopeLevel(Builder $query, int $level): Builder
    {
        return $query->where('level', $level);
    }

    /**
     * 获取权限树结构
     */
    public static function getTree($parentId = null, $activeOnly = true)
    {
        $query = static::where('parent_id', $parentId)->orderBy('sort_order');
        
        if ($activeOnly) {
            $query->active();
        }
        
        $permissions = $query->get();

        foreach ($permissions as $permission) {
            $permission->children_tree = static::getTree($permission->id, $activeOnly);
        }

        return $permissions;
    }

    /**
     * 获取菜单树结构
     */
    public static function getMenuTree($parentId = null)
    {
        $permissions = static::where('parent_id', $parentId)
            ->menu()
            ->active()
            ->orderBy('sort_order')
            ->get();

        foreach ($permissions as $permission) {
            $permission->children_tree = static::getMenuTree($permission->id);
        }

        return $permissions;
    }

    /**
     * 根据角色获取权限列表
     */
    public static function getByRoles($roles)
    {
        $roleIds = is_array($roles) ? $roles : $roles->pluck('id');
        
        return static::whereHas('roles', function ($query) use ($roleIds) {
            $query->whereIn('role_id', $roleIds)->where('status', true);
        })->active()->get();
    }

    /**
     * 创建默认权限
     */
    public static function createDefaultPermissions()
    {
        $defaultPermissions = [
            // 组织管理权限
            [
                'name' => 'organization.index',
                'display_name' => '查看组织列表',
                'module' => 'organization',
                'action' => 'index',
                'min_level' => 1,
                'applicable_levels' => json_encode([1, 2, 3, 4, 5]),
                'scope_type' => 'all_subordinates',
                'is_system' => true,
                'status' => 'active',
                'sort_order' => 1
            ],
            [
                'name' => 'organization.create',
                'display_name' => '创建组织',
                'module' => 'organization',
                'action' => 'create',
                'min_level' => 1,
                'applicable_levels' => json_encode([1, 2, 3, 4]),
                'scope_type' => 'all_subordinates',
                'is_system' => true,
                'status' => 'active',
                'sort_order' => 2
            ],
            [
                'name' => 'organization.update',
                'display_name' => '编辑组织',
                'module' => 'organization',
                'action' => 'update',
                'min_level' => 1,
                'applicable_levels' => json_encode([1, 2, 3, 4]),
                'scope_type' => 'all_subordinates',
                'is_system' => true,
                'status' => 'active',
                'sort_order' => 3
            ],
            [
                'name' => 'organization.delete',
                'display_name' => '删除组织',
                'module' => 'organization',
                'action' => 'delete',
                'min_level' => 1,
                'applicable_levels' => json_encode([1, 2, 3, 4]),
                'scope_type' => 'all_subordinates',
                'is_system' => true,
                'status' => 'active',
                'sort_order' => 4
            ],
            [
                'name' => 'organization.import',
                'display_name' => '批量导入组织',
                'module' => 'organization',
                'action' => 'import',
                'min_level' => 1,
                'applicable_levels' => json_encode([1, 2, 3, 4]),
                'scope_type' => 'all_subordinates',
                'is_system' => true,
                'status' => 'active',
                'sort_order' => 5
            ],
            // 用户管理权限
            [
                'name' => 'user.index',
                'display_name' => '查看用户列表',
                'module' => 'user',
                'action' => 'index',
                'min_level' => 1,
                'applicable_levels' => json_encode([1, 2, 3, 4, 5]),
                'scope_type' => 'all_subordinates',
                'is_system' => true,
                'status' => 'active',
                'sort_order' => 10
            ],
            [
                'name' => 'user.create',
                'display_name' => '创建用户',
                'module' => 'user',
                'action' => 'create',
                'min_level' => 1,
                'applicable_levels' => json_encode([1, 2, 3, 4, 5]),
                'scope_type' => 'all_subordinates',
                'is_system' => true,
                'status' => 'active',
                'sort_order' => 11
            ],
            [
                'name' => 'user.update',
                'display_name' => '编辑用户',
                'module' => 'user',
                'action' => 'update',
                'min_level' => 1,
                'applicable_levels' => json_encode([1, 2, 3, 4, 5]),
                'scope_type' => 'all_subordinates',
                'is_system' => true,
                'status' => 'active',
                'sort_order' => 12
            ],
            [
                'name' => 'user.delete',
                'display_name' => '删除用户',
                'module' => 'user',
                'action' => 'delete',
                'min_level' => 1,
                'applicable_levels' => json_encode([1, 2, 3, 4, 5]),
                'scope_type' => 'all_subordinates',
                'is_system' => true,
                'status' => 'active',
                'sort_order' => 13
            ],
            // 角色管理权限
            [
                'name' => 'role.index',
                'display_name' => '查看角色列表',
                'module' => 'role',
                'action' => 'index',
                'min_level' => 1,
                'applicable_levels' => json_encode([1, 2, 3, 4]),
                'scope_type' => 'all_subordinates',
                'is_system' => true,
                'status' => 'active',
                'sort_order' => 20
            ],
            [
                'name' => 'role.create',
                'display_name' => '创建角色',
                'module' => 'role',
                'action' => 'create',
                'min_level' => 1,
                'applicable_levels' => json_encode([1, 2, 3, 4]),
                'scope_type' => 'all_subordinates',
                'is_system' => true,
                'status' => 'active',
                'sort_order' => 21
            ],
            // 学区划分管理权限
            [
                'name' => 'district.view',
                'display_name' => '查看学区划分',
                'module' => 'district',
                'action' => 'view',
                'min_level' => 1,
                'applicable_levels' => json_encode([1, 2, 3, 4]),
                'scope_type' => 'all_subordinates',
                'is_system' => true,
                'status' => 'active',
                'sort_order' => 30
            ],
            [
                'name' => 'district.manage',
                'display_name' => '管理学区划分',
                'module' => 'district',
                'action' => 'manage',
                'min_level' => 1,
                'applicable_levels' => json_encode([1, 2, 3, 4]),
                'scope_type' => 'all_subordinates',
                'is_system' => true,
                'status' => 'active',
                'sort_order' => 31
            ]
        ];

        foreach ($defaultPermissions as $permissionData) {
            static::updateOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
        }
    }




}