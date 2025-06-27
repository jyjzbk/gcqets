<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'parent_id',
        'level',
        'path',
        'description',
        'status',
        'sort_order',
        'contact_person',
        'contact_phone',
        'contact_email',
        'address'
    ];

    protected $casts = [
        'level' => 'integer',
        'sort_order' => 'integer',
        'extra_data' => 'json',
        'longitude' => 'decimal:7',
        'latitude' => 'decimal:7',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $attributes = [
        'status' => 'active',
        'level' => 1,
        'sort_order' => 0
    ];

    /**
     * 父级组织关系
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'parent_id');
    }

    /**
     * 子级组织关系
     */
    public function children(): HasMany
    {
        return $this->hasMany(Organization::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * 递归获取所有子级组织
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * 用户关系
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_organizations')
            ->withPivot(['is_primary', 'status'])
            ->withTimestamps();
    }

    /**
     * 主要用户关系（主要组织机构的用户）
     */
    public function primaryUsers(): BelongsToMany
    {
        return $this->users()->wherePivot('is_primary', true);
    }

    /**
     * 获取组织机构的所有祖先
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
     * 获取组织机构的所有后代
     */
    public function getDescendants()
    {
        $descendants = collect();
        $this->collectDescendants($this, $descendants);
        return $descendants;
    }

    /**
     * 递归收集后代组织
     */
    private function collectDescendants($organization, &$descendants)
    {
        $children = $organization->children;
        
        foreach ($children as $child) {
            $descendants->push($child);
            $this->collectDescendants($child, $descendants);
        }
    }

    /**
     * 获取根组织
     */
    public function getRoot()
    {
        $current = $this;
        while ($current->parent_id) {
            $parent = static::find($current->parent_id);
            if ($parent) {
                $current = $parent;
            } else {
                break;
            }
        }
        return $current;
    }

    /**
     * 检查是否为根组织
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
     * 检查是否为指定组织的祖先
     */
    public function isAncestorOf(Organization $organization): bool
    {
        return $organization->getAncestors()->contains('id', $this->id);
    }

    /**
     * 检查是否为指定组织的后代
     */
    public function isDescendantOf(Organization $organization): bool
    {
        return $this->getAncestors()->contains('id', $organization->id);
    }

    /**
     * 获取组织层级路径
     */
    public function getPathAttribute(): string
    {
        if (isset($this->attributes['full_path']) && $this->attributes['full_path']) {
            return $this->attributes['full_path'];
        }

        $path = collect([$this->id]);
        $ancestors = $this->getAncestors();

        foreach ($ancestors as $ancestor) {
            $path->prepend($ancestor->id);
        }

        return $path->implode('/');
    }

    /**
     * 获取完整组织名称路径
     */
    public function getFullNameAttribute(): string
    {
        $names = collect([$this->name]);
        $ancestors = $this->getAncestors();
        
        foreach ($ancestors as $ancestor) {
            $names->prepend($ancestor->name);
        }

        return $names->implode(' > ');
    }

    /**
     * 查询作用域：活跃状态
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * 查询作用域：根组织
     */
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * 查询作用域：指定层级
     */
    public function scopeLevel(Builder $query, int $level): Builder
    {
        return $query->where('level', $level);
    }

    /**
     * 查询作用域：指定父级
     */
    public function scopeChildrenOf(Builder $query, int $parentId): Builder
    {
        return $query->where('parent_id', $parentId);
    }

    /**
     * 模型启动方法
     */
    protected static function boot()
    {
        parent::boot();

        // 创建时自动设置层级和路径
        static::creating(function ($organization) {
            if ($organization->parent_id) {
                $parent = static::find($organization->parent_id);
                if ($parent) {
                    $organization->level = $parent->level + 1;
                }
            }
        });

        // 更新时重新计算路径
        static::saved(function ($organization) {
            $organization->updatePath();
            $organization->updateChildrenLevel();
        });

        // 删除时处理子组织
        static::deleting(function ($organization) {
            if ($organization->children()->count() > 0) {
                throw new \Exception('Cannot delete organization with children');
            }
        });
    }

    /**
     * 更新路径
     */
    public function updatePath()
    {
        $path = $this->getPathAttribute();
        if ($this->full_path !== $path) {
            $this->withoutEvents(function () use ($path) {
                $this->update(['full_path' => $path]);
            });
        }
    }

    /**
     * 更新子组织层级
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
     * 获取组织树结构
     */
    public static function getTree($parentId = null)
    {
        // 一次性加载所有组织数据，减少数据库查询
        $allOrganizations = static::active()
            ->orderBy('sort_order')
            ->get()
            ->keyBy('id');

        // 构建树形结构
        $tree = [];
        foreach ($allOrganizations as $org) {
            if ($org->parent_id === $parentId) {
                $org->children_tree = static::buildTree($allOrganizations, $org->id);
                $tree[] = $org;
            }
        }

        return $tree;
    }

    /**
     * 递归构建树形结构
     */
    private static function buildTree($organizations, $parentId)
    {
        $children = [];
        
        foreach ($organizations as $org) {
            if ($org->parent_id === $parentId) {
                $org->children_tree = static::buildTree($organizations, $org->id);
                $children[] = $org;
            }
        }
        
        return $children;
    }

    /**
     * 获取指定组织的访问权限范围
     */
    public function getAccessScope()
    {
        $scope = collect([$this->id]);
        
        // 添加所有后代组织
        $descendants = $this->getDescendants();
        $scope = $scope->merge($descendants->pluck('id'));
        
        return $scope->unique()->values();
    }

    /**
     * 学校地理位置信息（仅学校类型组织有效）
     */
    public function schoolLocation(): HasOne
    {
        return $this->hasOne(SchoolLocation::class);
    }

    /**
     * 学区边界信息（仅学区类型组织有效）
     */
    public function districtBoundary(): HasOne
    {
        return $this->hasOne(DistrictBoundary::class, 'education_district_id');
    }

    /**
     * 学区划分历史（作为新学区）
     */
    public function assignmentHistoryAsNew(): HasMany
    {
        return $this->hasMany(DistrictAssignmentHistory::class, 'new_district_id');
    }

    /**
     * 学区划分历史（作为原学区）
     */
    public function assignmentHistoryAsOld(): HasMany
    {
        return $this->hasMany(DistrictAssignmentHistory::class, 'old_district_id');
    }

    /**
     * 学校的划分历史
     */
    public function schoolAssignmentHistory(): HasMany
    {
        return $this->hasMany(DistrictAssignmentHistory::class, 'school_id');
    }
}