<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\SchoolLocation;
use App\Models\DistrictBoundary;
use App\Models\DistrictAssignmentHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class DistrictDivisionService
{
    /**
     * 自动划分学区（改进版）
     */
    public function autoAssignDistricts($regionId, $criteria = [], User $user = null): array
    {
        try {
            DB::beginTransaction();

            // 获取区域内的学校
            $schools = $this->getSchoolsInRegion($regionId);
            
            // 获取可用的学区
            $districts = $this->getAvailableDistricts($regionId);

            if ($schools->isEmpty() || $districts->isEmpty()) {
                throw new Exception('没有找到可划分的学校或学区');
            }

            $results = [
                'total_schools' => $schools->count(),
                'assigned' => 0,
                'failed' => 0,
                'assignments' => [],
                'errors' => []
            ];

            // 预计算学区负载
            $districtLoads = [];
            foreach ($districts as $district) {
                $districtLoads[$district->id] = $this->getDistrictCurrentLoad($district);
            }

            foreach ($schools as $school) {
                try {
                    // 检查学校是否已有学区且符合条件
                    if ($school->parent_id && in_array($school->parent_id, $districts->pluck('id')->toArray())) {
                        $currentLoad = $districtLoads[$school->parent_id] ?? 0;
                        if ($currentLoad < 0.8) { // 当前学区负载合理则保持
                            $results['assigned']++;
                            $results['assignments'][] = [
                                'school_id' => $school->id,
                                'school_name' => $school->name,
                                'district_id' => $school->parent_id,
                                'district_name' => $districts->firstWhere('id', $school->parent_id)->name,
                                'reason' => '保持原有学区分配'
                            ];
                            continue;
                        }
                    }

                    $bestDistrict = $this->findBestDistrict($school, $districts, $criteria);
                    
                    if ($bestDistrict) {
                        $districtLoad = $districtLoads[$bestDistrict->id] ?? 0;
                        if ($districtLoad > 0.9) { // 避免分配到高负载学区
                            throw new Exception('目标学区负载过高');
                        }

                        $this->assignSchoolToDistrict(
                            $school,
                            $bestDistrict,
                            'auto',
                            $user,
                            '自动划分基于: ' . $this->getAssignmentReason($school, $bestDistrict, $criteria)
                        );
                        
                        // 更新统计和负载缓存
                        if ($boundary = DistrictBoundary::where('education_district_id', $bestDistrict->id)->first()) {
                            $boundary->updateStatistics();
                        }
                        $districtLoads[$bestDistrict->id] = $this->getDistrictCurrentLoad($bestDistrict);
                        
                        // 如果学校原来属于其他学区，更新原学区统计
                        if ($school->parent_id && $school->parent_id != $bestDistrict->id) {
                            if ($oldBoundary = DistrictBoundary::where('education_district_id', $school->parent_id)->first()) {
                                $oldBoundary->updateStatistics();
                            }
                        }
                        
                        $results['assigned']++;
                        $results['assignments'][] = [
                            'school_id' => $school->id,
                            'school_name' => $school->name,
                            'district_id' => $bestDistrict->id,
                            'district_name' => $bestDistrict->name,
                            'reason' => $this->getAssignmentReason($school, $bestDistrict, $criteria)
                        ];
                    } else {
                        throw new Exception('未找到合适的学区');
                    }
                } catch (Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'school_id' => $school->id,
                        'school_name' => $school->name,
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::commit();
            return $results;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Auto district assignment failed', [
                'region_id' => $regionId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * 手动调整学校学区归属
     */
    public function manualAssignSchool($schoolId, $districtId, $reason, User $user): bool
    {
        try {
            DB::beginTransaction();

            $school = Organization::findOrFail($schoolId);
            $district = Organization::findOrFail($districtId);

            // 验证权限
            if (!$user->canAccessOrganization($districtId)) {
                throw new Exception('无权限操作该学区');
            }

            $oldDistrictId = $school->parent_id;

            // 更新学校的父级组织
            $school->update(['parent_id' => $districtId]);
            $school->updatePath();

            // 记录划分历史
            DistrictAssignmentHistory::createAssignment(
                $schoolId,
                $districtId,
                'manual',
                $user->id,
                $oldDistrictId,
                $reason
            );

            // 更新相关统计信息
            $this->updateDistrictStatistics($districtId);
            if ($oldDistrictId) {
                $this->updateDistrictStatistics($oldDistrictId);
            }

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Manual school assignment failed', [
                'school_id' => $schoolId,
                'district_id' => $districtId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * 批量调整学校学区归属
     */
    public function batchAssignSchools($assignments, User $user): array
    {
        $results = [
            'total' => count($assignments),
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($assignments as $assignment) {
            try {
                $this->manualAssignSchool(
                    $assignment['school_id'],
                    $assignment['district_id'],
                    $assignment['reason'] ?? '批量调整',
                    $user
                );
                $results['success']++;
            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'school_id' => $assignment['school_id'],
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * 撤销划分操作
     */
    public function revertAssignment($history, $reason, User $user): bool
    {
        try {
            DB::beginTransaction();

            $school = Organization::findOrFail($history->school_id);

            // 恢复到原来的学区
            $school->update(['parent_id' => $history->old_district_id]);
            $school->updatePath();

            // 记录撤销操作
            DistrictAssignmentHistory::createAssignment(
                $history->school_id,
                $history->old_district_id,
                'revert',
                $user->id,
                $history->new_district_id,
                "撤销操作: {$reason}",
                ['reverted_history_id' => $history->id]
            );

            // 更新相关统计信息
            if ($history->old_district_id) {
                $this->updateDistrictStatistics($history->old_district_id);
            }
            if ($history->new_district_id) {
                $this->updateDistrictStatistics($history->new_district_id);
            }

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Revert assignment failed', [
                'history_id' => $history->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * 获取区域内的学校
     */
    private function getSchoolsInRegion($regionId)
    {
        return Organization::where('type', 'school')
                          ->where(function ($query) use ($regionId) {
                              $query->where('parent_id', $regionId)
                                    ->orWhereHas('ancestors', function ($q) use ($regionId) {
                                        $q->where('id', $regionId);
                                    });
                          })
                          ->with('schoolLocation')
                          ->get();
    }

    /**
     * 获取可用的学区
     */
    private function getAvailableDistricts($regionId)
    {
        return Organization::where('type', 'education_district')
                          ->where(function ($query) use ($regionId) {
                              $query->where('parent_id', $regionId)
                                    ->orWhereHas('ancestors', function ($q) use ($regionId) {
                                        $q->where('id', $regionId);
                                    });
                          })
                          ->where('status', true)
                          ->get();
    }

    /**
     * 为学校找到最佳学区
     */
    private function findBestDistrict($school, $districts, $criteria)
    {
        $schoolLocation = $school->schoolLocation;
        
        if (!$schoolLocation || !$schoolLocation->hasCompleteLocation()) {
            return null;
        }

        $bestDistrict = null;
        $bestScore = -1;

        foreach ($districts as $district) {
            $score = $this->calculateDistrictScore($school, $district, $criteria);
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestDistrict = $district;
            }
        }

        return $bestDistrict;
    }

    /**
     * 计算学区匹配分数
     */
    private function calculateDistrictScore($school, $district, $criteria): float
    {
        $score = 0;
        $schoolLocation = $school->schoolLocation;

        // 距离因素 (权重: 40%)
        if (isset($criteria['distance_weight'])) {
            $distanceScore = $this->calculateDistanceScore($schoolLocation, $district);
            $score += $distanceScore * ($criteria['distance_weight'] / 100) * 0.4;
        }

        // 规模因素 (权重: 30%)
        if (isset($criteria['scale_weight'])) {
            $scaleScore = $this->calculateScaleScore($schoolLocation, $district);
            $score += $scaleScore * ($criteria['scale_weight'] / 100) * 0.3;
        }

        // 负载均衡因素 (权重: 30%)
        if (isset($criteria['balance_weight'])) {
            $balanceScore = $this->calculateBalanceScore($district);
            $score += $balanceScore * ($criteria['balance_weight'] / 100) * 0.3;
        }

        return $score;
    }

    /**
     * 计算距离分数
     */
    private function calculateDistanceScore($schoolLocation, $district): float
    {
        // 简化实现：基于地理中心点距离
        $districtCenter = $this->getDistrictCenter($district);
        
        if (!$districtCenter) {
            return 0;
        }

        $distance = $schoolLocation->distanceTo(
            $districtCenter['lat'],
            $districtCenter['lng']
        );

        // 距离越近分数越高 (最大10公里为满分)
        return max(0, 100 - ($distance / 10) * 100);
    }

    /**
     * 计算规模分数
     */
    private function calculateScaleScore($schoolLocation, $district): float
    {
        // 基于学校规模和学区当前负载
        $schoolScale = $schoolLocation->scale_level;
        $districtLoad = $this->getDistrictCurrentLoad($district);

        // 简化的规模匹配逻辑
        $scaleScores = [
            'large' => 80,
            'medium' => 90,
            'small' => 95,
            'mini' => 100
        ];

        $baseScore = $scaleScores[$schoolScale] ?? 50;
        
        // 根据学区负载调整分数
        if ($districtLoad > 0.8) {
            $baseScore *= 0.7; // 负载过高降低分数
        } elseif ($districtLoad < 0.3) {
            $baseScore *= 1.1; // 负载较低提高分数
        }

        return min(100, $baseScore);
    }

    /**
     * 计算负载均衡分数
     */
    private function calculateBalanceScore($district): float
    {
        $currentLoad = $this->getDistrictCurrentLoad($district);
        
        // 负载在50%-70%之间为最佳
        if ($currentLoad >= 0.5 && $currentLoad <= 0.7) {
            return 100;
        } elseif ($currentLoad < 0.5) {
            return 80 + ($currentLoad * 40); // 负载过低
        } else {
            return 100 - (($currentLoad - 0.7) * 200); // 负载过高
        }
    }

    /**
     * 获取学区中心点
     */
    private function getDistrictCenter($district): ?array
    {
        $boundary = DistrictBoundary::where('education_district_id', $district->id)
                                   ->where('status', 'active')
                                   ->first();

        if ($boundary && $boundary->center_latitude && $boundary->center_longitude) {
            return [
                'lat' => $boundary->center_latitude,
                'lng' => $boundary->center_longitude
            ];
        }

        // 如果没有边界信息，使用学区内学校的平均位置
        $schools = $district->children()->where('type', 'school')->get();
        $locations = $schools->map(function ($school) {
            return $school->schoolLocation;
        })->filter(function ($location) {
            return $location && $location->hasCompleteLocation();
        });

        if ($locations->isEmpty()) {
            return null;
        }

        return [
            'lat' => $locations->avg('latitude'),
            'lng' => $locations->avg('longitude')
        ];
    }

    /**
     * 获取学区当前负载（改进版）
     */
    private function getDistrictCurrentLoad($district): float
    {
        // 获取学区边界信息
        $boundary = DistrictBoundary::where('education_district_id', $district->id)
                                 ->active()
                                 ->first();

        if (!$boundary) {
            return 0;
        }

        // 获取学区内的学校数量和学生总数
        $schoolCount = $boundary->school_count;
        $studentCount = $boundary->total_students;
        $areaSize = $boundary->area_size;

        // 计算基于学校数量的负载
        $schoolLoad = min(1.0, $schoolCount / ($district->max_schools ?? 20));
        
        // 计算基于学生密度的负载
        $densityLoad = $areaSize > 0 ? min(1.0, $studentCount / ($areaSize * ($district->max_density ?? 500))) : 0;
        
        // 综合负载 (学校数量权重60%，学生密度权重40%)
        return ($schoolLoad * 0.6) + ($densityLoad * 0.4);
    }

    /**
     * 分配学校到学区
     */
    private function assignSchoolToDistrict($school, $district, $type, $user, $reason)
    {
        $oldDistrictId = $school->parent_id;

        // 更新学校的父级组织
        $school->update(['parent_id' => $district->id]);
        $school->updatePath();

        // 记录划分历史
        $assignmentData = [
            'distance' => $this->calculateDistance($school, $district),
            'school_scale' => $school->schoolLocation?->scale_level,
            'district_load_before' => $this->getDistrictCurrentLoad($district)
        ];

        DistrictAssignmentHistory::createAssignment(
            $school->id,
            $district->id,
            $type,
            $user?->id,
            $oldDistrictId,
            $reason,
            $assignmentData
        );
    }

    /**
     * 计算学校到学区的距离
     */
    private function calculateDistance($school, $district): ?float
    {
        $schoolLocation = $school->schoolLocation;
        $districtCenter = $this->getDistrictCenter($district);

        if (!$schoolLocation || !$districtCenter || !$schoolLocation->hasCompleteLocation()) {
            return null;
        }

        return $schoolLocation->distanceTo(
            $districtCenter['lat'],
            $districtCenter['lng']
        );
    }

    /**
     * 获取分配原因
     */
    private function getAssignmentReason($school, $district, $criteria): string
    {
        $reasons = [];

        if (isset($criteria['distance_weight']) && $criteria['distance_weight'] > 0) {
            $distance = $this->calculateDistance($school, $district);
            if ($distance) {
                $reasons[] = "距离{$distance}公里";
            }
        }

        if (isset($criteria['scale_weight']) && $criteria['scale_weight'] > 0) {
            $scale = $school->schoolLocation?->scale_level;
            if ($scale) {
                $reasons[] = "学校规模{$scale}";
            }
        }

        if (isset($criteria['balance_weight']) && $criteria['balance_weight'] > 0) {
            $load = $this->getDistrictCurrentLoad($district);
            $reasons[] = "学区负载" . round($load * 100) . "%";
        }

        return implode(', ', $reasons) ?: '综合评估';
    }

    /**
     * 更新学区统计信息
     */
    private function updateDistrictStatistics($districtId): void
    {
        $boundary = DistrictBoundary::where('education_district_id', $districtId)->first();
        
        if ($boundary) {
            $boundary->updateStatistics();
        }
    }
}
