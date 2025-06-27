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
        'group',
        'guard_name',
        'module',
        'action',
        'resource',
        'method',
        'route',
        'icon',
        'sort_order',
        'parent_id',
        'level',
        'path',
        'status',
        'is_menu',
        'is_system',
        'created_by',
        'updated_by',
        'min_level',
        'applicable_levels',
        'scope_type'
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
            // 系统管理
            [
                'name' => 'system',
                'display_name' => '系统管理',
                'group' => self::GROUP_SYSTEM,
                'is_menu' => true,
                'icon' => 'settings',
                'sort_order' => 100,
                'children' => [
                    [
                        'name' => 'system.config',
                        'display_name' => '系统配置',
                        'is_menu' => true,
                        'route' => 'admin.system.config',
                        'sort_order' => 1
                    ],
                    [
                        'name' => 'system.cache',
                        'display_name' => '缓存管理',
                        'is_menu' => true,
                        'route' => 'admin.system.cache',
                        'sort_order' => 2
                    ]
                ]
            ],
            // 用户管理
            [
                'name' => 'user',
                'display_name' => '用户管理',
                'group' => self::GROUP_USER,
                'is_menu' => true,
                'icon' => 'users',
                'sort_order' => 200,
                'children' => [
                    [
                        'name' => 'user.index',
                        'display_name' => '用户列表',
                        'is_menu' => true,
                        'route' => 'admin.users.index',
                        'sort_order' => 1
                    ],
                    [
                        'name' => 'user.create',
                        'display_name' => '创建用户',
                        'action' => 'create',
                        'sort_order' => 2
                    ],
                    [
                        'name' => 'user.edit',
                        'display_name' => '编辑用户',
                        'action' => 'edit',
                        'sort_order' => 3
                    ],
                    [
                        'name' => 'user.delete',
                        'display_name' => '删除用户',
                        'action' => 'delete',
                        'sort_order' => 4
                    ]
                ]
            ],
            // 角色管理
            [
                'name' => 'role',
                'display_name' => '角色管理',
                'group' => self::GROUP_ROLE,
                'is_menu' => true,
                'icon' => 'shield',
                'sort_order' => 300,
                'children' => [
                    [
                        'name' => 'role.index',
                        'display_name' => '角色列表',
                        'is_menu' => true,
                        'route' => 'admin.roles.index',
                        'sort_order' => 1
                    ],
                    [
                        'name' => 'role.create',
                        'display_name' => '创建角色',
                        'action' => 'create',
                        'sort_order' => 2
                    ],
                    [
                        'name' => 'role.edit',
                        'display_name' => '编辑角色',
                        'action' => 'edit',
                        'sort_order' => 3
                    ],
                    [
                        'name' => 'role.delete',
                        'display_name' => '删除角色',
                        'action' => 'delete',
                        'sort_order' => 4
                    ]
                ]
            ],
            // 组织管理
            [
                'name' => 'organization',
                'display_name' => '组织管理',
                'group' => self::GROUP_ORG,
                'is_menu' => true,
                'icon' => 'building',
                'sort_order' => 400,
                'children' => [
                    [
                        'name' => 'organization.index',
                        'display_name' => '组织列表',
                        'is_menu' => true,
                        'route' => 'admin.organizations.index',
                        'sort_order' => 1
                    ],
                    [
                        'name' => 'organization.create',
                        'display_name' => '创建组织',
                        'action' => 'create',
                        'sort_order' => 2
                    ],
                    [
                        'name' => 'organization.edit',
                        'display_name' => '编辑组织',
                        'action' => 'edit',
                        'sort_order' => 3
                    ],
                    [
                        'name' => 'organization.delete',
                        'display_name' => '删除组织',
                        'action' => 'delete',
                        'sort_order' => 4
                    ]
                ]
            ]
        ];

        foreach ($defaultPermissions as $permissionData) {
            static::createPermissionTree($permissionData);
        }
    }

    /**
     * 递归创建权限树
     */
    private static function createPermissionTree($data, $parentId = null, $level = 1)
    {
        $children = $data['children'] ?? [];
        unset($data['children']);
        
        $data['parent_id'] = $parentId;
        $data['level'] = $level;
        $data['is_system'] = true;
        
        $permission = static::firstOrCreate(
            ['name' => $data['name']],
            $data
        );

        foreach ($children as $childData) {
            static::createPermissionTree($childData, $permission->id, $level + 1);
        }

        return $permission;
    }

    /**
     * 更新权限路径和级别
     */
    public function updatePath()
    {
        $path = $this->getPathAttribute();
        if ($this->path !== $path) {
            $this->withoutEvents(function () use ($path) {
                $this->update(['path' => $path]);
            });
        }
    }

    /**
     * 更新子权限级别
     */
    public function updateChildrenLevel()
    {
        $children = $this->children;
        foreach ($children as $child) {
            $newLevel = $this->level + 1;
            if ($child->level !== $newLevel) {
                $child->withoutEvents(function () use ($newLevel) {
                    $child->update(['level' => $newLevel]);
                });
                $child->updateChildrenLevel();
            }
        }
    }

    /**
     * 模型启动方法
     */
    protected static function boot()
    {
        parent::boot();

        // 创建时自动设置层级
        static::creating(function ($permission) {
            if ($permission->parent_id) {
                $parent = static::find($permission->parent_id);
                if ($parent) {
                    $permission->level = $parent->level + 1;
                    $permission->group = $permission->group ?: $parent->group;
                    $permission->module = $permission->module ?: $parent->module;
                }
            }
        });

        // 保存时更新路径
        static::saved(function ($permission) {
            $permission->updatePath();
            $permission->updateChildrenLevel();
        });

        // 删除时检查约束
        static::deleting(function ($permission) {
            // 检查是否为系统权限
            if ($permission->is_system) {
                throw new \Exception('Cannot delete system permission');
            }
            
            // 检查是否有子权限
            if ($permission->children()->count() > 0) {
                throw new \Exception('Cannot delete permission with children');
            }
            
            // 检查是否有角色使用
            if ($permission->roles()->count() > 0) {
                throw new \Exception('Cannot delete permission assigned to roles');
            }
        });
    }
}