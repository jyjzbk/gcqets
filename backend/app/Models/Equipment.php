<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'model',
        'brand',
        'description',
        'category_id',
        'serial_number',
        'purchase_price',
        'purchase_date',
        'supplier',
        'warranty_date',
        'status',
        'location',
        'usage_notes',
        'maintenance_notes',
        'total_usage_count',
        'total_usage_hours',
        'last_maintenance_date',
        'next_maintenance_date',
        'organization_id',
        'created_by',
        'updated_by',
        'extra_data'
    ];

    protected $casts = [
        'extra_data' => 'array',
        'purchase_price' => 'decimal:2',
        'purchase_date' => 'date',
        'warranty_date' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'total_usage_count' => 'integer',
        'total_usage_hours' => 'integer',
    ];

    protected $attributes = [
        'status' => 'available',
        'total_usage_count' => 0,
        'total_usage_hours' => 0,
    ];

    // 设备状态选项
    public static function getStatusOptions()
    {
        return [
            'available' => '可用',
            'borrowed' => '已借出',
            'maintenance' => '维护中',
            'damaged' => '损坏',
            'scrapped' => '报废'
        ];
    }

    // 关联关系
    public function category()
    {
        return $this->belongsTo(EquipmentCategory::class, 'category_id');
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

    public function borrowings()
    {
        return $this->hasMany(EquipmentBorrowing::class);
    }

    public function currentBorrowing()
    {
        return $this->hasOne(EquipmentBorrowing::class)
            ->whereIn('status', ['approved', 'borrowed'])
            ->latest();
    }

    // 作用域
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeBorrowed($query)
    {
        return $query->where('status', 'borrowed');
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

    public function scopeNeedsMaintenance($query)
    {
        return $query->where('next_maintenance_date', '<=', now());
    }

    public function scopeWarrantyExpiring($query, $days = 30)
    {
        return $query->where('warranty_date', '<=', now()->addDays($days))
            ->where('warranty_date', '>=', now());
    }

    // 获取设备状态标签
    public function getStatusLabelAttribute()
    {
        $options = self::getStatusOptions();
        return $options[$this->status] ?? $this->status;
    }

    // 检查是否可借用
    public function isAvailableForBorrowing()
    {
        return $this->status === 'available';
    }

    // 检查是否需要维护
    public function needsMaintenance()
    {
        return $this->next_maintenance_date && $this->next_maintenance_date <= now();
    }

    // 检查保修是否即将到期
    public function warrantyExpiringSoon($days = 30)
    {
        return $this->warranty_date && 
               $this->warranty_date <= now()->addDays($days) && 
               $this->warranty_date >= now();
    }

    // 更新使用统计
    public function updateUsageStats($hours = 0)
    {
        $this->increment('total_usage_count');
        if ($hours > 0) {
            $this->increment('total_usage_hours', $hours);
        }
    }
}
