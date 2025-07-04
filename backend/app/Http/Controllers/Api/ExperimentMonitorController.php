<?php

namespace App\Http\Controllers\Api;

use App\Models\ExperimentPlan;
use App\Models\ExperimentRecord;
use App\Models\ExperimentReviewLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExperimentMonitorController extends BaseApiController
{
    /**
     * 获取监控看板数据
     */
    public function getDashboard(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->error('用户未认证', 401);
            }

            $timeRange = $request->input('time_range', 'month'); // week, month, quarter, year

            // 获取时间范围
            $dateRange = $this->getDateRange($timeRange);

            // 基础统计
            $basicStats = $this->getBasicStats($user, $dateRange);

            return $this->success([
                'basicStats' => $basicStats,
                'progressStats' => [],
                'trendData' => [],
                'anomalyData' => [],
                'rankings' => [],
                'timeRange' => $timeRange,
                'dateRange' => [
                    'start' => $dateRange[0]->toDateString(),
                    'end' => $dateRange[1]->toDateString()
                ],
                'lastUpdated' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            return $this->error('获取监控数据失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取进度统计
     */
    public function getProgressStats(Request $request)
    {
        try {
            $user = Auth::user();
            $timeRange = $request->input('time_range', 'month');
            $dateRange = $this->getDateRange($timeRange);

            $stats = $this->getProgressStatsData($user, $dateRange);

            return $this->success($stats);

        } catch (\Exception $e) {
            return $this->error('获取进度统计失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取异常分析
     */
    public function getAnomalyAnalysis(Request $request)
    {
        try {
            $user = Auth::user();
            $timeRange = $request->input('time_range', 'month');
            $dateRange = $this->getDateRange($timeRange);
            
            // 暂时返回简化的异常数据
            $anomalies = [
                'overdueExperiments' => [],
                'pendingRecords' => [],
                'lowCompletionExperiments' => []
            ];

            return $this->success($anomalies);
            
        } catch (\Exception $e) {
            return $this->error('获取异常分析失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取实时统计
     */
    public function getRealTimeStats(Request $request)
    {
        try {
            $user = Auth::user();
            
            // 今日统计
            $todayStats = $this->getTodayStats($user);
            
            // 本周统计
            $weekStats = $this->getWeekStats($user);
            
            // 活跃度统计
            $activityStats = $this->getActivityStats($user);
            
            return $this->success([
                'today' => $todayStats,
                'week' => $weekStats,
                'activity' => $activityStats,
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            return $this->error('获取实时统计失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取基础统计数据
     */
    private function getBasicStats($user, $dateRange)
    {
        $baseQuery = ExperimentPlan::query();

        // 权限过滤
        if ($user->user_type !== 'admin') {
            $baseQuery->where('organization_id', $user->organization_id);
        }

        $baseQuery->whereBetween('created_at', $dateRange);

        $totalPlans = (clone $baseQuery)->count();
        $approvedPlans = (clone $baseQuery)->where('status', 'approved')->count();
        $completedPlans = (clone $baseQuery)->where('status', 'completed')->count();
        $overduePlans = (clone $baseQuery)->where('status', 'approved')
                             ->where('planned_date', '<', now()->toDateString())
                             ->count();
        
        // 实验记录统计
        $recordBaseQuery = ExperimentRecord::query();
        if ($user->user_type !== 'admin') {
            $recordBaseQuery->whereHas('experimentPlan', function($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            });
        }
        $recordBaseQuery->whereBetween('created_at', $dateRange);

        $totalRecords = (clone $recordBaseQuery)->count();
        $completedRecords = (clone $recordBaseQuery)->where('completion_status', 'completed')->count();
        
        return [
            'totalPlans' => $totalPlans,
            'approvedPlans' => $approvedPlans,
            'completedPlans' => $completedPlans,
            'overduePlans' => $overduePlans,
            'totalRecords' => $totalRecords,
            'completedRecords' => $completedRecords,
            'completionRate' => $totalPlans > 0 ? round(($completedPlans / $totalPlans) * 100, 2) : 0,
            'recordCompletionRate' => $totalRecords > 0 ? round(($completedRecords / $totalRecords) * 100, 2) : 0
        ];
    }
    
    /**
     * 获取进度统计数据
     */
    private function getProgressStatsData($user, $dateRange)
    {
        $query = ExperimentPlan::query();
        
        if ($user->user_type !== 'admin') {
            $query->where('organization_id', $user->organization_id);
        }
        
        $query->whereBetween('created_at', $dateRange);
        
        // 按状态统计
        $statusStats = $query->select('status', DB::raw('count(*) as count'))
                            ->groupBy('status')
                            ->get()
                            ->pluck('count', 'status')
                            ->toArray();
        
        // 按月份统计
        $monthlyStats = $query->select(
                                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                                DB::raw('count(*) as count')
                            )
                            ->groupBy('month')
                            ->orderBy('month')
                            ->get();
        
        // 按组织统计（仅系统管理员）
        $orgStats = [];
        if ($user->user_type === 'admin') {
            $orgStats = ExperimentPlan::with('organization')
                                    ->whereBetween('created_at', $dateRange)
                                    ->select('organization_id', DB::raw('count(*) as count'))
                                    ->groupBy('organization_id')
                                    ->get()
                                    ->map(function($item) {
                                        return [
                                            'organization' => $item->organization->name ?? '未知',
                                            'count' => $item->count
                                        ];
                                    });
        }
        
        return [
            'statusStats' => $statusStats,
            'monthlyStats' => $monthlyStats,
            'organizationStats' => $orgStats
        ];
    }
    
    /**
     * 获取趋势数据
     */
    private function getTrendData($user, $dateRange, $timeRange)
    {
        $query = ExperimentPlan::query();
        
        if ($user->user_type !== 'admin') {
            $query->where('organization_id', $user->organization_id);
        }
        
        $query->whereBetween('created_at', $dateRange);
        
        // 根据时间范围确定分组格式
        $dateFormat = match($timeRange) {
            'week' => '%Y-%m-%d',
            'month' => '%Y-%m-%d',
            'quarter' => '%Y-%m',
            'year' => '%Y-%m',
            default => '%Y-%m-%d'
        };
        
        $trendData = $query->select(
                            DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as date"),
                            DB::raw('count(*) as total'),
                            DB::raw('sum(case when status = "approved" then 1 else 0 end) as approved'),
                            DB::raw('sum(case when status = "completed" then 1 else 0 end) as completed')
                        )
                        ->groupBy('date')
                        ->orderBy('date')
                        ->get();
        
        return $trendData;
    }
    
    /**
     * 获取异常数据
     */
    private function getAnomalyData($user, $dateRange)
    {
        $anomalies = [];
        
        // 逾期实验
        $overdueQuery = ExperimentPlan::query();
        if ($user->user_type !== 'admin') {
            $overdueQuery->where('organization_id', $user->organization_id);
        }
        
        $overdueExperiments = $overdueQuery->where('status', 'approved')
                                          ->where('planned_date', '<', now()->toDateString())
                                          ->with(['teacher', 'experimentCatalog'])
                                          ->get();
        
        // 长时间未审核的记录
        $pendingRecords = ExperimentRecord::with(['experimentPlan.teacher', 'experimentPlan.experimentCatalog'])
                                         ->where('review_status', 'pending')
                                         ->where('created_at', '<', now()->subDays(3))
                                         ->when($user->user_type !== 'admin', function($q) use ($user) {
                                             $q->whereHas('experimentPlan', function($subQ) use ($user) {
                                                 $subQ->where('organization_id', $user->organization_id);
                                             });
                                         })
                                         ->get();
        
        // 异常完成率的实验
        $lowCompletionExperiments = ExperimentPlan::with(['teacher', 'experimentCatalog', 'experimentRecords'])
                                                 ->where('status', 'approved')
                                                 ->when($user->user_type !== 'admin', function($q) use ($user) {
                                                     $q->where('organization_id', $user->organization_id);
                                                 })
                                                 ->get()
                                                 ->filter(function($plan) {
                                                     $totalRecords = $plan->experimentRecords->count();
                                                     $completedRecords = $plan->experimentRecords->where('completion_status', 'completed')->count();
                                                     $completionRate = $totalRecords > 0 ? ($completedRecords / $totalRecords) : 0;
                                                     return $totalRecords > 0 && $completionRate < 0.5; // 完成率低于50%
                                                 });
        
        return [
            'overdueExperiments' => $overdueExperiments->map(function($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'teacher' => $plan->teacher->real_name ?? $plan->teacher->username,
                    'catalog' => $plan->experimentCatalog->name ?? '',
                    'plannedDate' => $plan->planned_date,
                    'overdueDays' => now()->diffInDays($plan->planned_date)
                ];
            }),
            'pendingRecords' => $pendingRecords->map(function($record) {
                return [
                    'id' => $record->id,
                    'planName' => $record->experimentPlan->name,
                    'teacher' => $record->experimentPlan->teacher->real_name ?? $record->experimentPlan->teacher->username,
                    'createdAt' => $record->created_at,
                    'pendingDays' => now()->diffInDays($record->created_at)
                ];
            }),
            'lowCompletionExperiments' => $lowCompletionExperiments->map(function($plan) {
                $totalRecords = $plan->experimentRecords->count();
                $completedRecords = $plan->experimentRecords->where('completion_status', 'completed')->count();
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'teacher' => $plan->teacher->real_name ?? $plan->teacher->username,
                    'totalRecords' => $totalRecords,
                    'completedRecords' => $completedRecords,
                    'completionRate' => $totalRecords > 0 ? round(($completedRecords / $totalRecords) * 100, 2) : 0
                ];
            })
        ];
    }
    
    /**
     * 获取排行榜数据
     */
    private function getRankings($user, $dateRange)
    {
        // 教师活跃度排行
        $teacherRanking = ExperimentPlan::with('teacher')
                                       ->when($user->user_type !== 'admin', function($q) use ($user) {
                                           $q->where('organization_id', $user->organization_id);
                                       })
                                       ->whereBetween('created_at', $dateRange)
                                       ->select('teacher_id', DB::raw('count(*) as plan_count'))
                                       ->groupBy('teacher_id')
                                       ->orderBy('plan_count', 'desc')
                                       ->limit(10)
                                       ->get()
                                       ->map(function($item) {
                                           return [
                                               'teacher' => $item->teacher->real_name ?? $item->teacher->username,
                                               'planCount' => $item->plan_count
                                           ];
                                       });
        
        // 实验目录使用排行
        $catalogRanking = ExperimentPlan::with('experimentCatalog')
                                       ->when($user->user_type !== 'admin', function($q) use ($user) {
                                           $q->where('organization_id', $user->organization_id);
                                       })
                                       ->whereBetween('created_at', $dateRange)
                                       ->whereNotNull('experiment_catalog_id')
                                       ->select('experiment_catalog_id', DB::raw('count(*) as usage_count'))
                                       ->groupBy('experiment_catalog_id')
                                       ->orderBy('usage_count', 'desc')
                                       ->limit(10)
                                       ->get()
                                       ->map(function($item) {
                                           return [
                                               'catalog' => $item->experimentCatalog->name ?? '未知',
                                               'usageCount' => $item->usage_count
                                           ];
                                       });
        
        return [
            'teacherRanking' => $teacherRanking,
            'catalogRanking' => $catalogRanking
        ];
    }
    
    /**
     * 获取今日统计
     */
    private function getTodayStats($user)
    {
        $today = now()->toDateString();
        
        $query = ExperimentPlan::query();
        if ($user->user_type !== 'admin') {
            $query->where('organization_id', $user->organization_id);
        }
        
        return [
            'todayPlans' => $query->whereDate('planned_date', $today)->count(),
            'todayCreated' => $query->whereDate('created_at', $today)->count(),
            'todayApproved' => $query->whereDate('updated_at', $today)->where('status', 'approved')->count()
        ];
    }
    
    /**
     * 获取本周统计
     */
    private function getWeekStats($user)
    {
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        
        $query = ExperimentPlan::query();
        if ($user->user_type !== 'admin') {
            $query->where('organization_id', $user->organization_id);
        }
        
        return [
            'weekPlans' => $query->whereBetween('planned_date', [$weekStart, $weekEnd])->count(),
            'weekCreated' => $query->whereBetween('created_at', [$weekStart, $weekEnd])->count(),
            'weekCompleted' => $query->whereBetween('updated_at', [$weekStart, $weekEnd])->where('status', 'completed')->count()
        ];
    }
    
    /**
     * 获取活跃度统计
     */
    private function getActivityStats($user)
    {
        $last24Hours = now()->subHours(24);
        
        return [
            'recentPlans' => ExperimentPlan::when($user->user_type !== 'admin', function($q) use ($user) {
                                $q->where('organization_id', $user->organization_id);
                            })
                            ->where('created_at', '>=', $last24Hours)
                            ->count(),
            'recentRecords' => ExperimentRecord::whereHas('experimentPlan', function($q) use ($user) {
                                  if ($user->user_type !== 'admin') {
                                      $q->where('organization_id', $user->organization_id);
                                  }
                              })
                              ->where('created_at', '>=', $last24Hours)
                              ->count(),
            'recentReviews' => ExperimentReviewLog::when($user->user_type !== 'admin', function($q) use ($user) {
                                  $q->where('organization_id', $user->organization_id);
                              })
                              ->where('created_at', '>=', $last24Hours)
                              ->count()
        ];
    }
    
    /**
     * 获取日期范围
     */
    private function getDateRange($timeRange)
    {
        return match($timeRange) {
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'quarter' => [now()->startOfQuarter(), now()->endOfQuarter()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()]
        };
    }
}
