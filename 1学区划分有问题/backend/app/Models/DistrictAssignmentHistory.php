<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistrictAssignmentHistory extends Model
{
    use HasFactory;

    protected $table = 'district_assignment_history';

    protected $fillable = [
        'school_id',
        'old_district_id',
        'new_district_id',
        'assignment_type',
        'reason',
        'assignment_data',
        'operated_by',
        'effective_date'
    ];

    protected $casts = [
        'assignment_data' => 'json',
        'effective_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * 关联学校
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'school_id');
    }

    /**
     * 关联原学区
     */
    public function oldDistrict(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'old_district_id');
    }

    /**
     * 关联新学区
     */
    public function newDistrict(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'new_district_id');
    }

    /**
     * 关联操作人
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operated_by');
    }

    /**
     * 获取划分类型文本
     */
    public function getAssignmentTypeTextAttribute(): string
    {
        $typeMap = [
            'auto' => '自动划分',
            'manual' => '手动调整',
            'import' => '批量导入'
        ];

        return $typeMap[$this->assignment_type] ?? '未知';
    }

    /**
     * 获取距离信息
     */
    public function getDistanceInfoAttribute(): ?array
    {
        if (!isset($this->assignment_data['distance'])) {
            return null;
        }

        return [
            'distance' => $this->assignment_data['distance'],
            'unit' => $this->assignment_data['distance_unit'] ?? 'km'
        ];
    }

    /**
     * 获取规模信息
     */
    public function getScaleInfoAttribute(): ?array
    {
        $data = $this->assignment_data;
        
        if (!isset($data['student_count'])) {
            return null;
        }

        return [
            'student_count' => $data['student_count'],
            'teacher_count' => $data['teacher_count'] ?? 0,
            'class_count' => $data['class_count'] ?? 0,
            'scale_level' => $data['scale_level'] ?? 'unknown'
        ];
    }

    /**
     * 检查是否为有效的调整
     */
    public function isValidAssignment(): bool
    {
        return $this->school_id && $this->new_district_id && 
               $this->school_id !== $this->new_district_id;
    }

    /**
     * 检查是否为跨区调整
     */
    public function isCrossDistrictAssignment(): bool
    {
        return $this->old_district_id && 
               $this->old_district_id !== $this->new_district_id;
    }

    /**
     * 范围查询：按学校
     */
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * 范围查询：按学区
     */
    public function scopeForDistrict($query, $districtId)
    {
        return $query->where('new_district_id', $districtId);
    }

    /**
     * 范围查询：按划分类型
     */
    public function scopeByType($query, $type)
    {
        return $query->where('assignment_type', $type);
    }

    /**
     * 范围查询：按操作人
     */
    public function scopeByOperator($query, $userId)
    {
        return $query->where('operated_by', $userId);
    }

    /**
     * 范围查询：按时间范围
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * 范围查询：最近的记录
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * 创建划分记录
     */
    public static function createAssignment(
        $schoolId,
        $newDistrictId,
        $assignmentType,
        $operatedBy,
        $oldDistrictId = null,
        $reason = null,
        $assignmentData = null,
        $effectiveDate = null
    ): self {
        return static::create([
            'school_id' => $schoolId,
            'old_district_id' => $oldDistrictId,
            'new_district_id' => $newDistrictId,
            'assignment_type' => $assignmentType,
            'reason' => $reason,
            'assignment_data' => $assignmentData,
            'operated_by' => $operatedBy,
            'effective_date' => $effectiveDate ?? now()
        ]);
    }

    /**
     * 获取学校的最新划分记录
     */
    public static function getLatestForSchool($schoolId): ?self
    {
        return static::where('school_id', $schoolId)
                    ->orderBy('created_at', 'desc')
                    ->first();
    }

    /**
     * 获取学区的划分统计
     */
    public static function getDistrictStatistics($districtId, $days = 30): array
    {
        $query = static::where('new_district_id', $districtId)
                      ->where('created_at', '>=', now()->subDays($days));

        return [
            'total_assignments' => $query->count(),
            'auto_assignments' => $query->clone()->where('assignment_type', 'auto')->count(),
            'manual_assignments' => $query->clone()->where('assignment_type', 'manual')->count(),
            'import_assignments' => $query->clone()->where('assignment_type', 'import')->count(),
            'cross_district_assignments' => $query->clone()->whereNotNull('old_district_id')->count()
        ];
    }
}
