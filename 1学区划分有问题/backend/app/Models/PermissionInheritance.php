<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermissionInheritance extends Model
{
    use HasFactory;

    protected $table = 'permission_inheritance';

    protected $fillable = [
        'parent_organization_id',
        'child_organization_id',
        'permission_id',
        'inheritance_type',
        'inheritance_path',
        'is_overridden',
        'overridden_by',
        'overridden_at',
        'status'
    ];

    protected $casts = [
        'inheritance_path' => 'array',
        'is_overridden' => 'boolean',
        'overridden_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'inheritance_type' => 'direct',
        'is_overridden' => false,
        'status' => 'active'
    ];

    /**
     * 父级组织关系
     */
    public function parentOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'parent_organization_id');
    }

    /**
     * 子级组织关系
     */
    public function childOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'child_organization_id');
    }

    /**
     * 权限关系
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * 覆盖者关系
     */
    public function overriddenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'overridden_by');
    }

    /**
     * 作用域：活跃状态
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * 作用域：直接继承
     */
    public function scopeDirect($query)
    {
        return $query->where('inheritance_type', 'direct');
    }

    /**
     * 作用域：间接继承
     */
    public function scopeIndirect($query)
    {
        return $query->where('inheritance_type', 'indirect');
    }

    /**
     * 作用域：未被覆盖
     */
    public function scopeNotOverridden($query)
    {
        return $query->where('is_overridden', false);
    }

    /**
     * 作用域：已被覆盖
     */
    public function scopeOverridden($query)
    {
        return $query->where('is_overridden', true);
    }

    /**
     * 获取继承路径字符串
     */
    public function getInheritancePathStringAttribute(): string
    {
        if (!$this->inheritance_path) {
            return '';
        }

        return implode(' → ', $this->inheritance_path);
    }

    /**
     * 计算继承深度
     */
    public function getInheritanceDepthAttribute(): int
    {
        return $this->inheritance_path ? count($this->inheritance_path) : 0;
    }

    /**
     * 检查是否为有效继承
     */
    public function isValidInheritance(): bool
    {
        return $this->status === 'active' && !$this->is_overridden;
    }

    /**
     * 标记为覆盖
     */
    public function markAsOverridden(int $userId, string $reason = null): bool
    {
        return $this->update([
            'is_overridden' => true,
            'overridden_by' => $userId,
            'overridden_at' => now()
        ]);
    }

    /**
     * 取消覆盖
     */
    public function unmarkOverridden(): bool
    {
        return $this->update([
            'is_overridden' => false,
            'overridden_by' => null,
            'overridden_at' => null
        ]);
    }

    /**
     * 获取指定组织的权限继承关系
     */
    public static function getInheritanceForOrganization(int $organizationId, array $permissionIds = null)
    {
        $query = static::where('child_organization_id', $organizationId)
            ->active()
            ->with(['parentOrganization', 'permission']);

        if ($permissionIds) {
            $query->whereIn('permission_id', $permissionIds);
        }

        return $query->get();
    }

    /**
     * 构建权限继承树
     */
    public static function buildInheritanceTree(int $organizationId): array
    {
        $inheritances = static::where('child_organization_id', $organizationId)
            ->active()
            ->with(['parentOrganization', 'permission'])
            ->get();

        $tree = [];
        foreach ($inheritances as $inheritance) {
            $permissionId = $inheritance->permission_id;
            if (!isset($tree[$permissionId])) {
                $tree[$permissionId] = [
                    'permission' => $inheritance->permission,
                    'sources' => []
                ];
            }

            $tree[$permissionId]['sources'][] = [
                'organization' => $inheritance->parentOrganization,
                'type' => $inheritance->inheritance_type,
                'path' => $inheritance->inheritance_path,
                'is_overridden' => $inheritance->is_overridden
            ];
        }

        return $tree;
    }
}
