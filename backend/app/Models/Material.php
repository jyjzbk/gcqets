<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'specification',
        'brand',
        'description',
        'category_id',
        'unit',
        'unit_price',
        'current_stock',
        'min_stock',
        'max_stock',
        'total_purchased',
        'total_consumed',
        'storage_location',
        'storage_conditions',
        'expiry_date',
        'safety_notes',
        'status',
        'supplier',
        'last_purchase_date',
        'organization_id',
        'created_by',
        'updated_by',
        'extra_data'
    ];

    protected $casts = [
        'extra_data' => 'array',
        'unit_price' => 'decimal:2',
        'current_stock' => 'integer',
        'min_stock' => 'integer',
        'max_stock' => 'integer',
        'total_purchased' => 'integer',
        'total_consumed' => 'integer',
        'expiry_date' => 'date',
        'last_purchase_date' => 'date',
    ];

    protected $attributes = [
        'status' => 'active',
        'current_stock' => 0,
        'min_stock' => 0,
        'total_purchased' => 0,
        'total_consumed' => 0,
    ];

    // 材料状态选项
    public static function getStatusOptions()
    {
        return [
            'active' => '正常',
            'inactive' => '停用',
            'expired' => '过期',
            'out_of_stock' => '缺货'
        ];
    }

    // 常用计量单位
    public static function getUnitOptions()
    {
        return [
            'piece' => '个',
            'set' => '套',
            'kg' => '千克',
            'g' => '克',
            'l' => '升',
            'ml' => '毫升',
            'box' => '盒',
            'bottle' => '瓶',
            'pack' => '包',
            'meter' => '米',
            'cm' => '厘米',
            'mm' => '毫米'
        ];
    }

    // 关联关系
    public function category()
    {
        return $this->belongsTo(MaterialCategory::class, 'category_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function usages()
    {
        return $this->hasMany(MaterialUsage::class);
    }

    public function stockLogs()
    {
        return $this->hasMany(MaterialStockLog::class);
    }

    // 作用域
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('current_stock', '<=', 'min_stock');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('current_stock', '<=', 0);
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    // 获取状态标签
    public function getStatusLabelAttribute()
    {
        $options = self::getStatusOptions();
        return $options[$this->status] ?? $this->status;
    }

    // 检查是否库存不足
    public function isLowStock()
    {
        return $this->current_stock <= $this->min_stock;
    }

    // 检查是否缺货
    public function isOutOfStock()
    {
        return $this->current_stock <= 0;
    }

    // 检查是否即将过期
    public function isExpiringSoon($days = 30)
    {
        return $this->expiry_date && 
               $this->expiry_date <= now()->addDays($days) && 
               $this->expiry_date >= now();
    }

    // 检查是否已过期
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date < now();
    }

    // 更新库存
    public function updateStock($quantity, $operationType, $reason, $operatorId, $referenceType = null, $referenceId = null)
    {
        $oldStock = $this->current_stock;
        $newStock = $oldStock + $quantity;
        
        // 更新库存
        $this->update(['current_stock' => $newStock]);
        
        // 记录库存日志
        MaterialStockLog::create([
            'log_code' => 'LOG_' . date('YmdHis') . '_' . $this->id,
            'material_id' => $this->id,
            'operation_type' => $operationType,
            'quantity_before' => $oldStock,
            'quantity_change' => $quantity,
            'quantity_after' => $newStock,
            'reason' => $reason,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'operator_id' => $operatorId,
            'operated_at' => now(),
            'organization_id' => $this->organization_id,
        ]);
        
        // 更新状态
        if ($newStock <= 0) {
            $this->update(['status' => 'out_of_stock']);
        } elseif ($this->status === 'out_of_stock' && $newStock > 0) {
            $this->update(['status' => 'active']);
        }
        
        return $this;
    }
}
