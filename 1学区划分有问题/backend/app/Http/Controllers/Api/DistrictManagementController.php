<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\SchoolLocation;
use App\Models\DistrictBoundary;
use App\Models\DistrictAssignmentHistory;
use App\Services\DistrictDivisionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DistrictManagementController extends Controller
{
    protected $districtService;

    public function __construct(DistrictDivisionService $districtService)
    {
        $this->districtService = $districtService;
    }

    /**
     * 获取学区划分概览
     */
    public function overview(Request $request): JsonResponse
    {
        $user = Auth::user();
        $regionId = $request->input('region_id');

        try {
            // 获取用户可访问的区域
            $accessScope = $user->getDataAccessScope();

            if ($regionId && !in_array($regionId, $accessScope['organization_ids'])) {
                return response()->json([
                    'message' => '无权访问该区域',
                    'code' => 403
                ], 403);
            }

            // 获取统计数据
            $stats = $this->getDistrictStatistics($regionId, $accessScope);

            return response()->json([
                'message' => '获取学区划分概览成功',
                'data' => $stats,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取概览失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取学校地理信息
     */
    public function getSchoolLocations(Request $request): JsonResponse
    {
        $user = Auth::user();
        $regionId = $request->input('region_id');

        try {
            $query = SchoolLocation::with(['organization' => function ($q) {
                $q->where('type', 'school')->where('status', true);
            }]);

            // 根据用户权限过滤
            $accessScope = $user->getDataAccessScope();
            if ($accessScope['type'] === 'specific') {
                $query->whereHas('organization', function ($q) use ($accessScope) {
                    $q->whereIn('id', $accessScope['organization_ids']);
                });
            }

            // 按区域过滤
            if ($regionId) {
                $query->whereHas('organization', function ($q) use ($regionId) {
                    $q->where('parent_id', $regionId)
                      ->orWhere('id', $regionId)
                      ->orWhereRaw("FIND_IN_SET(?, full_path)", [$regionId]);
                });
            }

            $locations = $query->get();

            return response()->json([
                'message' => '获取学校地理信息成功',
                'data' => $locations,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取学校地理信息失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取学区边界
     */
    public function getDistrictBoundaries(Request $request): JsonResponse
    {
        $user = Auth::user();
        $regionId = $request->input('region_id');

        try {
            $query = DistrictBoundary::with(['educationDistrict', 'creator'])
                                   ->where('status', 'active');

            // 根据用户权限过滤
            $accessScope = $user->getDataAccessScope();
            if ($accessScope['type'] === 'specific') {
                $query->whereIn('education_district_id', $accessScope['organization_ids']);
            }

            // 按区域过滤
            if ($regionId) {
                $query->whereHas('educationDistrict', function ($q) use ($regionId) {
                    $q->where('parent_id', $regionId)
                      ->orWhere('id', $regionId)
                      ->orWhereRaw("FIND_IN_SET(?, full_path)", [$regionId]);
                });
            }

            $boundaries = $query->get();

            return response()->json([
                'message' => '获取学区边界成功',
                'data' => $boundaries,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取学区边界失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 自动划分学区
     */
    public function autoAssign(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'region_id' => 'required|exists:organizations,id',
            'criteria' => 'array',
            'criteria.distance_weight' => 'integer|min:0|max:100',
            'criteria.scale_weight' => 'integer|min:0|max:100',
            'criteria.balance_weight' => 'integer|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '参数验证失败',
                'errors' => $validator->errors(),
                'code' => 422
            ], 422);
        }

        $regionId = $request->input('region_id');
        $criteria = $request->input('criteria', []);

        // 检查权限
        if (!$user->canAccessOrganization($regionId)) {
            return response()->json([
                'message' => '无权限操作该区域',
                'code' => 403
            ], 403);
        }

        try {
            $results = $this->districtService->autoAssignDistricts($regionId, $criteria, $user);

            return response()->json([
                'message' => '自动划分完成',
                'data' => $results,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '自动划分失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 手动调整学校学区归属
     */
    public function manualAssign(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'school_id' => 'required|exists:organizations,id',
            'district_id' => 'required|exists:organizations,id',
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '参数验证失败',
                'errors' => $validator->errors(),
                'code' => 422
            ], 422);
        }

        $schoolId = $request->input('school_id');
        $districtId = $request->input('district_id');
        $reason = $request->input('reason');

        try {
            $result = $this->districtService->manualAssignSchool($schoolId, $districtId, $reason, $user);

            return response()->json([
                'message' => '手动调整成功',
                'data' => ['success' => $result],
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '手动调整失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 批量调整学校学区归属
     */
    public function batchAssign(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'assignments' => 'required|array|min:1',
            'assignments.*.school_id' => 'required|exists:organizations,id',
            'assignments.*.district_id' => 'required|exists:organizations,id',
            'assignments.*.reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '参数验证失败',
                'errors' => $validator->errors(),
                'code' => 422
            ], 422);
        }

        $assignments = $request->input('assignments');

        try {
            $results = $this->districtService->batchAssignSchools($assignments, $user);

            return response()->json([
                'message' => '批量调整完成',
                'data' => $results,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '批量调整失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取学区划分历史
     */
    public function getAssignmentHistory(Request $request): JsonResponse
    {
        $user = Auth::user();

        try {
            $query = DistrictAssignmentHistory::with([
                'school', 'oldDistrict', 'newDistrict', 'operator'
            ]);

            // 根据用户权限过滤
            $accessScope = $user->getDataAccessScope();
            if ($accessScope['type'] === 'specific') {
                $query->where(function ($q) use ($accessScope) {
                    $q->whereIn('school_id', $accessScope['organization_ids'])
                      ->orWhereIn('new_district_id', $accessScope['organization_ids'])
                      ->orWhereIn('old_district_id', $accessScope['organization_ids']);
                });
            }

            // 搜索条件
            if ($request->filled('school_id')) {
                $query->where('school_id', $request->input('school_id'));
            }

            if ($request->filled('district_id')) {
                $query->where('new_district_id', $request->input('district_id'));
            }

            if ($request->filled('assignment_type')) {
                $query->where('assignment_type', $request->input('assignment_type'));
            }

            if ($request->filled('date_from')) {
                $query->where('created_at', '>=', $request->input('date_from'));
            }

            if ($request->filled('date_to')) {
                $query->where('created_at', '<=', $request->input('date_to'));
            }

            // 排序和分页
            $query->orderBy('created_at', 'desc');
            $perPage = $request->input('per_page', 15);
            $history = $query->paginate($perPage);

            return response()->json([
                'message' => '获取划分历史成功',
                'data' => $history,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取划分历史失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 预览自动划分结果
     */
    public function previewAutoAssign(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'region_id' => 'required|exists:organizations,id',
            'criteria' => 'array',
            'criteria.distance_weight' => 'integer|min:0|max:100',
            'criteria.scale_weight' => 'integer|min:0|max:100',
            'criteria.balance_weight' => 'integer|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '参数验证失败',
                'errors' => $validator->errors(),
                'code' => 422
            ], 422);
        }

        $regionId = $request->input('region_id');
        $criteria = $request->input('criteria', []);

        // 检查权限
        if (!$user->canAccessOrganization($regionId)) {
            return response()->json([
                'message' => '无权限操作该区域',
                'code' => 403
            ], 403);
        }

        try {
            // 这里应该调用服务类的预览方法，暂时返回模拟数据
            $previewResults = [
                'assignments' => [
                    [
                        'school_name' => '示例学校1',
                        'district_name' => '示例学区1',
                        'reason' => '距离最近，负载均衡'
                    ],
                    [
                        'school_name' => '示例学校2',
                        'district_name' => '示例学区2',
                        'reason' => '规模匹配，地理位置合适'
                    ]
                ]
            ];

            return response()->json([
                'message' => '预览自动划分结果成功',
                'data' => $previewResults,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '预览自动划分失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 撤销划分操作
     */
    public function revertAssignment(Request $request, $historyId): JsonResponse
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '参数验证失败',
                'errors' => $validator->errors(),
                'code' => 422
            ], 422);
        }

        try {
            $history = DistrictAssignmentHistory::findOrFail($historyId);
            $reason = $request->input('reason');

            // 检查权限
            if (!$user->canAccessOrganization($history->school_id) ||
                !$user->canAccessOrganization($history->new_district_id)) {
                return response()->json([
                    'message' => '无权限撤销该操作',
                    'code' => 403
                ], 403);
            }

            // 执行撤销操作
            $result = $this->districtService->revertAssignment($history, $reason, $user);

            return response()->json([
                'message' => '撤销操作成功',
                'data' => ['success' => $result],
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '撤销操作失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取可分配的学校列表
     */
    public function getAvailableSchools(Request $request): JsonResponse
    {
        $user = Auth::user();
        $regionId = $request->input('region_id');
        $withoutDistrict = $request->boolean('without_district', false);

        try {
            $query = Organization::where('type', 'school')->where('status', true);

            // 根据用户权限过滤
            $accessScope = $user->getDataAccessScope();
            if ($accessScope['type'] === 'specific') {
                $query->whereIn('id', $accessScope['organization_ids']);
            }

            // 按区域过滤
            if ($regionId) {
                $query->where(function ($q) use ($regionId) {
                    $q->where('parent_id', $regionId)
                      ->orWhere('id', $regionId)
                      ->orWhereRaw("FIND_IN_SET(?, full_path)", [$regionId]);
                });
            }

            // 只获取未分配学区的学校
            if ($withoutDistrict) {
                $query->whereNull('parent_id');
            }

            $schools = $query->with(['schoolLocation'])->get();

            return response()->json([
                'message' => '获取可分配学校成功',
                'data' => $schools,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取可分配学校失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取学区内的学校列表
     */
    public function getDistrictSchools(Request $request, $districtId): JsonResponse
    {
        $user = Auth::user();

        try {
            // 检查权限
            if (!$user->canAccessOrganization($districtId)) {
                return response()->json([
                    'message' => '无权限访问该学区',
                    'code' => 403
                ], 403);
            }

            $schools = Organization::where('type', 'school')
                                 ->where('status', true)
                                 ->where('parent_id', $districtId)
                                 ->with(['schoolLocation'])
                                 ->get();

            return response()->json([
                'message' => '获取学区学校成功',
                'data' => $schools,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取学区学校失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取统计信息
     */
    public function getStatistics(Request $request): JsonResponse
    {
        $user = Auth::user();
        $regionId = $request->input('region_id');

        try {
            $accessScope = $user->getDataAccessScope();
            $stats = $this->getDistrictStatistics($regionId, $accessScope);

            return response()->json([
                'message' => '获取统计信息成功',
                'data' => $stats,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取统计信息失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 导出报告
     */
    public function exportReport(Request $request): JsonResponse
    {
        // 暂时返回模拟响应
        return response()->json([
            'message' => '导出功能开发中',
            'code' => 501
        ], 501);
    }

    /**
     * 获取负载均衡分析
     */
    public function getLoadBalanceAnalysis(Request $request): JsonResponse
    {
        // 暂时返回模拟响应
        return response()->json([
            'message' => '负载均衡分析功能开发中',
            'data' => [],
            'code' => 200
        ]);
    }

    /**
     * 获取距离分析
     */
    public function getDistanceAnalysis(Request $request): JsonResponse
    {
        // 暂时返回模拟响应
        return response()->json([
            'message' => '距离分析功能开发中',
            'data' => [],
            'code' => 200
        ]);
    }

    /**
     * 创建学区边界
     */
    public function createBoundary(Request $request): JsonResponse
    {
        // 暂时返回模拟响应
        return response()->json([
            'message' => '创建边界功能开发中',
            'code' => 501
        ], 501);
    }

    /**
     * 更新学区边界
     */
    public function updateBoundary(Request $request, $boundaryId): JsonResponse
    {
        // 暂时返回模拟响应
        return response()->json([
            'message' => '更新边界功能开发中',
            'code' => 501
        ], 501);
    }

    /**
     * 删除学区边界
     */
    public function deleteBoundary($boundaryId): JsonResponse
    {
        // 暂时返回模拟响应
        return response()->json([
            'message' => '删除边界功能开发中',
            'code' => 501
        ], 501);
    }

    /**
     * 获取学区统计信息
     */
    private function getDistrictStatistics($regionId, $accessScope): array
    {
        $schoolQuery = Organization::where('type', 'school')->where('status', true);
        $districtQuery = Organization::where('type', 'education_zone')->where('status', true);

        // 应用权限过滤
        if ($accessScope['type'] === 'specific' && !empty($accessScope['organization_ids'])) {
            // 对于学校，检查学校本身或其父级学区是否在权限范围内
            $schoolQuery->where(function ($q) use ($accessScope) {
                $q->whereIn('id', $accessScope['organization_ids'])
                  ->orWhereIn('parent_id', $accessScope['organization_ids']);
            });

            // 对于学区，直接检查是否在权限范围内
            $districtQuery->whereIn('id', $accessScope['organization_ids']);
        }

        // 应用区域过滤
        if ($regionId) {
            $schoolQuery->where(function ($q) use ($regionId) {
                $q->where('parent_id', $regionId)
                  ->orWhere('id', $regionId)
                  ->orWhereRaw("FIND_IN_SET(?, full_path)", [$regionId]);
            });

            $districtQuery->where(function ($q) use ($regionId) {
                $q->where('parent_id', $regionId)
                  ->orWhere('id', $regionId)
                  ->orWhereRaw("FIND_IN_SET(?, full_path)", [$regionId]);
            });
        }

        $totalSchools = $schoolQuery->count();
        $totalDistricts = $districtQuery->count();

        // 调试日志
        \Log::info('学区统计查询结果', [
            'total_schools' => $totalSchools,
            'total_districts' => $totalDistricts,
            'region_id' => $regionId,
            'access_scope' => $accessScope
        ]);

        // 已分配学区的学校数量（parent_id指向学区的学校）
        $assignedSchools = $schoolQuery->clone()->whereHas('parent', function ($q) {
            $q->where('type', 'education_zone');
        })->count();
        $unassignedSchools = $totalSchools - $assignedSchools;

        // 有地理信息的学校数量
        $schoolsWithLocation = $schoolQuery->clone()->whereHas('schoolLocation', function ($q) {
            $q->whereNotNull('latitude')->whereNotNull('longitude');
        })->count();

        // 计算负载均衡度（简化计算）
        $balanceScore = $totalDistricts > 0 ? min(100, ($assignedSchools / max($totalDistricts, 1)) * 10) : 0;

        $result = [
            'total_districts' => $totalDistricts,
            'total_schools' => $totalSchools,
            'assigned_schools' => $assignedSchools,
            'unassigned_schools' => $unassignedSchools,
            'schools_with_location' => $schoolsWithLocation,
            'location_coverage_rate' => $totalSchools > 0 ? round(($schoolsWithLocation / $totalSchools) * 100, 2) : 0,
            'balance_score' => round($balanceScore, 1)
        ];

        // 调试日志
        \Log::info('学区统计结果', $result);

        return $result;
    }
}
