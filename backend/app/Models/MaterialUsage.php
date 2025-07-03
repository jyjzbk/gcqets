<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialUsage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'usage_code',
        'material_id',
        'user_id',
        'experiment_catalog_id',
        'quantity_used',
        'purpose',
        'used_at',
        'notes',
        'usage_type',
        'class_name',
        'student_count',
        'organization_id',
        'created_by',
        'updated_by',
        'extra_data'
    ];

    protected $casts = [
        'extra_data' => 'array',
        'quantity_used' => 'integer',
        'student_count' => 'integer',
        'used_at' => 'datetime',
    ];

    // 使用类型选项
    public static function getUsageTypeOptions()
    {
        return [
            'experiment' => '实验教学',
            'maintenance' => '设备维护',
            'teaching' => '课堂教学',
            'other' => '其他用途'
        ];
    }

    // 关联关系
    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function experimentCatalog()
    {
        return $this->belongsTo(ExperimentCatalog::class, 'experiment_catalog_id');
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

    // 作用域
    public function scopeByMaterial($query, $materialId)
    {
        return $query->where('material_id', $materialId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByUsageType($query, $type)
    {
        return $query->where('usage_type', $type);
    }

    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('used_at', [$startDate, $endDate]);
    }

    public function scopeByExperiment($query, $experimentId)
    {
        return $query->where('experiment_catalog_id', $experimentId);
    }

    // 获取使用类型标签
    public function getUsageTypeLabelAttribute()
    {
        $options = self::getUsageTypeOptions();
        return $options[$this->usage_type] ?? $this->usage_type;
    }

    // 计算总成本
    public function getTotalCostAttribute()
    {
        if ($this->material && $this->material->unit_price) {
            return $this->quantity_used * $this->material->unit_price;
        }
        return 0;
    }

    // 创建使用记录并更新库存
    public static function createUsage($data)
    {
        $material = Material::findOrFail($data['material_id']);
        
        // 检查库存是否足够
        if ($material->current_stock < $data['quantity_used']) {
            throw new \Exception('库存不足，当前库存：' . $material->current_stock . ' ' . $material->unit);
        }

        // 创建使用记录
        $usage = self::create($data);

        // 更新材料库存
        $material->updateStock(
            -$data['quantity_used'], 
            'usage', 
            '实验使用：' . $data['purpose'],
            $data['created_by'],
            'material_usage',
            $usage->id
        );

        // 更新材料的总消耗量
        $material->increment('total_consumed', $data['quantity_used']);

        return $usage;
    }
}
