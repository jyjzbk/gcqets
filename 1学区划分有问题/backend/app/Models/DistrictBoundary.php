<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistrictBoundary extends Model
{
    use HasFactory;

    protected $fillable = [
        'education_district_id',
        'name',
        'boundary_points',
        'center_latitude',
        'center_longitude',
        'area_size',
        'school_count',
        'total_students',
        'description',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'boundary_points' => 'json',
        'center_latitude' => 'decimal:8',
        'center_longitude' => 'decimal:8',
        'area_size' => 'decimal:2',
        'school_count' => 'integer',
        'total_students' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * 关联学区组织
     */
    public function educationDistrict(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'education_district_id');
    }

    /**
     * 关联创建者
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 关联更新者
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 关联学区划分历史
     */
    public function assignmentHistory(): HasMany
    {
        return $this->hasMany(DistrictAssignmentHistory::class, 'new_district_id', 'education_district_id');
    }

    /**
     * 检查点是否在边界内（改进的多边形包含算法）
     */
    public function containsPoint($latitude, $longitude): bool
    {
        if (empty($this->boundary_points)) {
            return false;
        }

        $points = is_string($this->boundary_points) ?
                  json_decode($this->boundary_points, true) :
                  $this->boundary_points;

        if (!is_array($points) || count($points) < 3) {
            return false;
        }

        // 转换为GeoJSON格式点
        $point = ['type' => 'Point', 'coordinates' => [$longitude, $latitude]];
        $polygon = [
            'type' => 'Polygon',
            'coordinates' => [array_map(function($p) {
                return [$p['lng'], $p['lat']];
            }, $points)]
        ];

        try {
            // 使用更精确的地理计算库
            return \Turf\booleanPointInPolygon($point, $polygon);
        } catch (\Exception $e) {
            Log::error('Point in polygon calculation failed', [
                'error' => $e->getMessage(),
                'boundary_id' => $this->id
            ]);
            return false;
        }
    }

    /**
     * 计算边界中心点
     */
    public function calculateCenter(): array
    {
        if (empty($this->boundary_points)) {
            return ['lat' => 0, 'lng' => 0];
        }

        $points = is_string($this->boundary_points) ?
                  json_decode($this->boundary_points, true) :
                  $this->boundary_points;

        if (!is_array($points)) {
            return ['lat' => 0, 'lng' => 0];
        }
        $latSum = 0;
        $lngSum = 0;
        $count = count($points);

        foreach ($points as $point) {
            $latSum += $point['lat'];
            $lngSum += $point['lng'];
        }

        return [
            'lat' => $latSum / $count,
            'lng' => $lngSum / $count
        ];
    }

    /**
     * 更新中心点坐标
     */
    public function updateCenter(): void
    {
        $center = $this->calculateCenter();
        $this->update([
            'center_latitude' => $center['lat'],
            'center_longitude' => $center['lng']
        ]);
    }

    /**
     * 计算边界面积（使用地理计算库）
     */
    public function calculateArea(): float
    {
        $points = is_string($this->boundary_points) ?
                  json_decode($this->boundary_points, true) :
                  $this->boundary_points;

        if (empty($points) || !is_array($points) || count($points) < 3) {
            return 0;
        }

        try {
            $polygon = [
                'type' => 'Polygon',
                'coordinates' => [array_map(function($p) {
                    return [$p['lng'], $p['lat']];
                }, $points)]
            ];
            
            $area = \Turf\area($polygon); // 返回平方米
            return $area / 1000000; // 转换为平方公里
        } catch (\Exception $e) {
            Log::error('Area calculation failed', [
                'error' => $e->getMessage(),
                'boundary_id' => $this->id
            ]);
            return 0;
        }
    }

    /**
     * 获取边界内的学校（改进版）
     */
    public function getSchoolsInBoundary()
    {
        return SchoolLocation::whereHas('organization', function ($query) {
                $query->where('type', 'school');
            })
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('student_count', '>', 0)
            ->get()
            ->filter(function ($school) {
                return $this->containsPoint($school->latitude, $school->longitude);
            });
    }

    /**
     * 更新统计信息（改进版）
     */
    public function updateStatistics(): void
    {
        try {
            DB::beginTransaction();
            
            // 获取边界内的有效学校（有位置和学生数据）
            $schools = $this->getSchoolsInBoundary()->filter(function($school) {
                return $school->hasCompleteLocation() && $school->student_count > 0;
            });
            
            // 更新统计信息
            $this->update([
                'school_count' => $schools->count(),
                'total_students' => $schools->sum('student_count'),
                'area_size' => $this->calculateArea()
            ]);
            
            // 更新中心点坐标
            $this->updateCenter();
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('更新学区统计失败', [
                'boundary_id' => $this->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 范围查询：活跃状态
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * 范围查询：按学区
     */
    public function scopeForDistrict($query, $districtId)
    {
        return $query->where('education_district_id', $districtId);
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttribute(): string
    {
        $statusMap = [
            'active' => '活跃',
            'inactive' => '停用',
            'draft' => '草稿'
        ];

        return $statusMap[$this->status] ?? '未知';
    }

    /**
     * 获取边界点数量
     */
    public function getBoundaryPointCountAttribute(): int
    {
        return is_array($this->boundary_points) ? count($this->boundary_points) : 0;
    }

    /**
     * 验证边界数据
     */
    public function validateBoundary(): array
    {
        $errors = [];

        $points = is_string($this->boundary_points) ?
                  json_decode($this->boundary_points, true) :
                  $this->boundary_points;

        if (empty($points)) {
            $errors[] = '边界点不能为空';
        } elseif (!is_array($points) || count($points) < 3) {
            $errors[] = '边界至少需要3个点';
        }

        if (is_array($points)) {
            foreach ($points as $index => $point) {
                if (!isset($point['lat']) || !isset($point['lng'])) {
                    $errors[] = "第" . ($index + 1) . "个边界点缺少坐标信息";
                } elseif (!is_numeric($point['lat']) || !is_numeric($point['lng'])) {
                    $errors[] = "第" . ($index + 1) . "个边界点坐标格式错误";
                }
            }
        }

        return $errors;
    }
}
