<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'latitude',
        'longitude',
        'address',
        'province',
        'city',
        'district',
        'street',
        'postal_code',
        'student_count',
        'teacher_count',
        'class_count',
        'area_size',
        'school_type',
        'facilities',
        'remarks'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'student_count' => 'integer',
        'teacher_count' => 'integer',
        'class_count' => 'integer',
        'area_size' => 'decimal:2',
        'facilities' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * 关联学校组织
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * 计算与指定坐标的距离（公里）
     */
    public function distanceTo($latitude, $longitude): float
    {
        if (!$this->latitude || !$this->longitude) {
            return 0;
        }

        $earthRadius = 6371; // 地球半径（公里）
        
        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * 获取学校规模等级
     */
    public function getScaleLevelAttribute(): string
    {
        $totalStudents = $this->student_count;
        
        if ($totalStudents >= 1000) {
            return 'large';
        } elseif ($totalStudents >= 500) {
            return 'medium';
        } elseif ($totalStudents >= 200) {
            return 'small';
        } else {
            return 'mini';
        }
    }

    /**
     * 获取师生比
     */
    public function getTeacherStudentRatioAttribute(): float
    {
        if ($this->student_count == 0) {
            return 0;
        }
        
        return round($this->student_count / max($this->teacher_count, 1), 2);
    }

    /**
     * 获取班级平均人数
     */
    public function getAverageClassSizeAttribute(): float
    {
        if ($this->class_count == 0) {
            return 0;
        }
        
        return round($this->student_count / $this->class_count, 1);
    }

    /**
     * 检查是否有完整的地理信息
     */
    public function hasCompleteLocation(): bool
    {
        return !empty($this->latitude) && !empty($this->longitude);
    }

    /**
     * 获取地址全称
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->province,
            $this->city,
            $this->district,
            $this->street,
            $this->address
        ]);
        
        return implode('', $parts);
    }

    /**
     * 范围查询：在指定范围内的学校
     */
    public function scopeWithinRadius($query, $latitude, $longitude, $radiusKm)
    {
        // 简化的矩形范围查询（实际应用中可以使用更精确的地理查询）
        $latRange = $radiusKm / 111; // 1度纬度约等于111公里
        $lonRange = $radiusKm / (111 * cos(deg2rad($latitude)));

        return $query->whereBetween('latitude', [$latitude - $latRange, $latitude + $latRange])
                    ->whereBetween('longitude', [$longitude - $lonRange, $longitude + $lonRange]);
    }

    /**
     * 范围查询：按学校类型
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('school_type', $type);
    }

    /**
     * 范围查询：按规模
     */
    public function scopeByScale($query, $scale)
    {
        switch ($scale) {
            case 'large':
                return $query->where('student_count', '>=', 1000);
            case 'medium':
                return $query->whereBetween('student_count', [500, 999]);
            case 'small':
                return $query->whereBetween('student_count', [200, 499]);
            case 'mini':
                return $query->where('student_count', '<', 200);
            default:
                return $query;
        }
    }
}
