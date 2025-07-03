<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialStockLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'log_code',
        'material_id',
        'operation_type',
        'quantity_before',
        'quantity_change',
        'quantity_after',
        'unit_price',
        'total_amount',
        'reason',
        'reference_type',
        'reference_id',
        'notes',
        'operator_id',
        'operated_at',
        'organization_id',
        'extra_data'
    ];

    protected $casts = [
        'extra_data' => 'array',
        'quantity_before' => 'integer',
        'quantity_change' => 'integer',
        'quantity_after' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'operated_at' => 'datetime',
    ];

    // 操作类型选项
    public static function getOperationTypeOptions()
    {
        return [
            'purchase' => '采购入库',
            'usage' => '使用消耗',
            'adjustment' => '库存调整',
            'expired' => '过期处理',
            'damaged' => '损坏处理'
        ];
    }

    // 关联关系
    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // 作用域
    public function scopeByMaterial($query, $materialId)
    {
        return $query->where('material_id', $materialId);
    }

    public function scopeByOperationType($query, $type)
    {
        return $query->where('operation_type', $type);
    }

    public function scopeByOperator($query, $operatorId)
    {
        return $query->where('operator_id', $operatorId);
    }

    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('operated_at', [$startDate, $endDate]);
    }

    public function scopePurchases($query)
    {
        return $query->where('operation_type', 'purchase');
    }

    public function scopeUsages($query)
    {
        return $query->where('operation_type', 'usage');
    }

    // 获取操作类型标签
    public function getOperationTypeLabelAttribute()
    {
        $options = self::getOperationTypeOptions();
        return $options[$this->operation_type] ?? $this->operation_type;
    }

    // 获取变更方向
    public function getChangeDirectionAttribute()
    {
        return $this->quantity_change > 0 ? 'increase' : 'decrease';
    }

    // 获取变更方向标签
    public function getChangeDirectionLabelAttribute()
    {
        return $this->quantity_change > 0 ? '增加' : '减少';
    }
}
