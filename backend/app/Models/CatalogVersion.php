<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogVersion extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'catalog_id',
        'version',
        'version_description',
        'changes',
        'catalog_data',
        'change_type',
        'change_reason',
        'status',
        'created_by',
        'created_at'
    ];

    protected $casts = [
        'changes' => 'array',
        'catalog_data' => 'array',
        'created_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    // 变更类型选项
    public static function getChangeTypeOptions()
    {
        return [
            'create' => '创建',
            'update' => '更新',
            'delete' => '删除',
            'restore' => '恢复'
        ];
    }

    // 状态选项
    public static function getStatusOptions()
    {
        return [
            'active' => '活跃',
            'archived' => '已归档',
            'rollback' => '已回滚'
        ];
    }

    /**
     * 关联实验目录
     */
    public function catalog(): BelongsTo
    {
        return $this->belongsTo(ExperimentCatalog::class, 'catalog_id');
    }

    /**
     * 关联创建人
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 获取变更类型中文名称
     */
    public function getChangeTypeNameAttribute()
    {
        return self::getChangeTypeOptions()[$this->change_type] ?? $this->change_type;
    }

    /**
     * 获取状态中文名称
     */
    public function getStatusNameAttribute()
    {
        return self::getStatusOptions()[$this->status] ?? $this->status;
    }

    /**
     * 获取变更摘要
     */
    public function getChangesSummaryAttribute()
    {
        if (!is_array($this->changes) || empty($this->changes)) {
            return '无变更记录';
        }

        $summary = [];
        foreach ($this->changes as $field => $change) {
            if (isset($change['old']) && isset($change['new'])) {
                $summary[] = "{$field}: {$change['old']} → {$change['new']}";
            } elseif (isset($change['action'])) {
                $summary[] = "{$field}: {$change['action']}";
            }
        }

        return implode('; ', $summary);
    }

    /**
     * 作用域：按目录过滤
     */
    public function scopeByCatalog($query, $catalogId)
    {
        return $query->where('catalog_id', $catalogId);
    }

    /**
     * 作用域：按变更类型过滤
     */
    public function scopeByChangeType($query, $changeType)
    {
        return $query->where('change_type', $changeType);
    }

    /**
     * 作用域：按状态过滤
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * 作用域：活跃状态
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * 生成版本号
     */
    public static function generateVersion($catalogId)
    {
        $latestVersion = self::where('catalog_id', $catalogId)
                            ->orderBy('created_at', 'desc')
                            ->first();

        if (!$latestVersion) {
            return '1.0.0';
        }

        // 简单的版本号递增逻辑
        $versionParts = explode('.', $latestVersion->version);
        if (count($versionParts) === 3) {
            $versionParts[2] = (int)$versionParts[2] + 1;
            return implode('.', $versionParts);
        }

        return '1.0.0';
    }

    /**
     * 创建版本记录
     */
    public static function createVersion($catalogId, $changeType, $changes = [], $reason = null, $createdBy = null)
    {
        $catalog = ExperimentCatalog::find($catalogId);
        if (!$catalog) {
            return null;
        }

        $version = self::generateVersion($catalogId);

        return self::create([
            'catalog_id' => $catalogId,
            'version' => $version,
            'version_description' => "版本 {$version} - " . self::getChangeTypeOptions()[$changeType],
            'changes' => $changes,
            'catalog_data' => $catalog->toArray(),
            'change_type' => $changeType,
            'change_reason' => $reason,
            'created_by' => $createdBy ?? auth()->id(),
            'created_at' => now(),
        ]);
    }
}
