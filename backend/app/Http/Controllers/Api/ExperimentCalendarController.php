<?php

namespace App\Http\Controllers\Api;

use App\Models\ExperimentPlan;
use App\Models\ExperimentRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExperimentCalendarController extends BaseApiController
{
    /**
     * 获取日历数据
     */
    public function getCalendarData(Request $request)
    {
        try {
            $user = Auth::user();
            $start = $request->input('start', now()->startOfMonth()->toDateString());
            $end = $request->input('end', now()->endOfMonth()->toDateString());
            
            // 构建查询
            $query = ExperimentPlan::with(['experimentCatalog', 'teacher', 'organization'])
                ->whereBetween('planned_date', [$start, $end]);
            
            // 权限过滤
            if ($user->user_type !== 'admin') {
                $query->where('organization_id', $user->organization_id);
            }
            
            $plans = $query->get();
            
            // 转换为日历事件格式
            $events = $plans->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'title' => $plan->name,
                    'start' => $plan->planned_date,
                    'end' => $plan->planned_date,
                    'allDay' => true,
                    'backgroundColor' => $this->getStatusColor($plan->status),
                    'borderColor' => $this->getStatusColor($plan->status),
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'type' => 'experiment_plan',
                        'status' => $plan->status,
                        'statusLabel' => $plan->status_label,
                        'teacher' => $plan->teacher->real_name ?? $plan->teacher->username,
                        'catalog' => $plan->experimentCatalog->name ?? '',
                        'studentCount' => $plan->student_count,
                        'duration' => $plan->planned_duration,
                        'className' => $plan->class_name,
                        'description' => $plan->description,
                        'isOverdue' => $this->isOverdue($plan),
                        'daysUntilDue' => $this->getDaysUntilDue($plan)
                    ]
                ];
            });
            
            return $this->success([
                'events' => $events,
                'summary' => $this->getCalendarSummary($plans, $start, $end)
            ]);
            
        } catch (\Exception $e) {
            return $this->error('获取日历数据失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取逾期预警
     */
    public function getOverdueAlerts(Request $request)
    {
        try {
            $user = Auth::user();
            $days = (int) $request->input('days', 7); // 默认查看7天内的预警
            
            $query = ExperimentPlan::with(['experimentCatalog', 'teacher', 'organization'])
                ->where('status', 'approved')
                ->where('planned_date', '<=', now()->addDays($days))
                ->where('planned_date', '>=', now());
            
            // 权限过滤
            if ($user->user_type !== 'admin') {
                $query->where('organization_id', $user->organization_id);
            }
            
            $plans = $query->orderBy('planned_date')->get();
            
            // 分类预警
            $alerts = [
                'overdue' => [], // 已逾期
                'today' => [],   // 今天到期
                'tomorrow' => [], // 明天到期
                'thisWeek' => []  // 本周到期
            ];
            
            foreach ($plans as $plan) {
                $daysUntil = now()->diffInDays($plan->planned_date, false);
                
                if ($daysUntil < 0) {
                    $alerts['overdue'][] = $this->formatAlertItem($plan, $daysUntil);
                } elseif ($daysUntil == 0) {
                    $alerts['today'][] = $this->formatAlertItem($plan, $daysUntil);
                } elseif ($daysUntil == 1) {
                    $alerts['tomorrow'][] = $this->formatAlertItem($plan, $daysUntil);
                } elseif ($daysUntil <= 7) {
                    $alerts['thisWeek'][] = $this->formatAlertItem($plan, $daysUntil);
                }
            }
            
            return $this->success([
                'alerts' => $alerts,
                'counts' => [
                    'overdue' => count($alerts['overdue']),
                    'today' => count($alerts['today']),
                    'tomorrow' => count($alerts['tomorrow']),
                    'thisWeek' => count($alerts['thisWeek']),
                    'total' => $plans->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->error('获取预警信息失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取实验详情
     */
    public function getExperimentDetails($id)
    {
        try {
            $user = Auth::user();
            
            $plan = ExperimentPlan::with([
                'experimentCatalog', 
                'teacher', 
                'organization',
                'experimentRecords'
            ])->find($id);
            
            if (!$plan) {
                return $this->error('实验计划不存在');
            }
            
            // 权限检查
            if ($user->user_type !== 'admin' && $plan->organization_id !== $user->organization_id) {
                return $this->error('无权限查看此实验计划');
            }
            
            // 获取相关记录
            $records = $plan->experimentRecords()->with(['creator', 'photos'])->get();
            
            return $this->success([
                'plan' => [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'code' => $plan->code,
                    'status' => $plan->status,
                    'statusLabel' => $plan->status_label,
                    'plannedDate' => $plan->planned_date,
                    'plannedDuration' => $plan->planned_duration,
                    'studentCount' => $plan->student_count,
                    'className' => $plan->class_name,
                    'description' => $plan->description,
                    'objectives' => $plan->objectives,
                    'keyPoints' => $plan->key_points,
                    'safetyRequirements' => $plan->safety_requirements,
                    'teacher' => [
                        'id' => $plan->teacher->id,
                        'name' => $plan->teacher->real_name ?? $plan->teacher->username,
                        'username' => $plan->teacher->username
                    ],
                    'catalog' => $plan->experimentCatalog ? [
                        'id' => $plan->experimentCatalog->id,
                        'name' => $plan->experimentCatalog->name,
                        'code' => $plan->experimentCatalog->code
                    ] : null,
                    'organization' => [
                        'id' => $plan->organization->id,
                        'name' => $plan->organization->name
                    ],
                    'isOverdue' => $this->isOverdue($plan),
                    'daysUntilDue' => $this->getDaysUntilDue($plan)
                ],
                'records' => $records->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'executionDate' => $record->execution_date,
                        'completionStatus' => $record->completion_status,
                        'actualDuration' => $record->actual_duration,
                        'actualStudentCount' => $record->actual_student_count,
                        'creator' => $record->creator->real_name ?? $record->creator->username,
                        'photoCount' => $record->photos->count(),
                        'createdAt' => $record->created_at
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            return $this->error('获取实验详情失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 检查日程冲突
     */
    public function checkConflicts(Request $request)
    {
        try {
            $user = Auth::user();
            $date = $request->input('date');
            $teacherId = $request->input('teacher_id');
            $excludeId = $request->input('exclude_id'); // 编辑时排除自己
            
            if (!$date || !$teacherId) {
                return $this->error('日期和教师ID不能为空');
            }
            
            $query = ExperimentPlan::where('planned_date', $date)
                ->where('teacher_id', $teacherId)
                ->whereIn('status', ['approved', 'executing']);
            
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            
            // 权限过滤
            if ($user->user_type !== 'admin') {
                $query->where('organization_id', $user->organization_id);
            }
            
            $conflicts = $query->with(['experimentCatalog'])->get();
            
            return $this->success([
                'hasConflicts' => $conflicts->count() > 0,
                'conflicts' => $conflicts->map(function ($plan) {
                    return [
                        'id' => $plan->id,
                        'name' => $plan->name,
                        'code' => $plan->code,
                        'catalog' => $plan->experimentCatalog->name ?? '',
                        'duration' => $plan->planned_duration,
                        'className' => $plan->class_name
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            return $this->error('检查冲突失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 获取状态颜色
     */
    private function getStatusColor($status)
    {
        $colors = [
            'draft' => '#909399',      // 草稿 - 灰色
            'pending' => '#E6A23C',    // 待审批 - 橙色
            'approved' => '#67C23A',   // 已批准 - 绿色
            'rejected' => '#F56C6C',   // 已拒绝 - 红色
            'executing' => '#409EFF',  // 执行中 - 蓝色
            'completed' => '#909399',  // 已完成 - 灰色
            'cancelled' => '#F56C6C'   // 已取消 - 红色
        ];
        
        return $colors[$status] ?? '#909399';
    }
    
    /**
     * 检查是否逾期
     */
    private function isOverdue($plan)
    {
        return $plan->status === 'approved' && $plan->planned_date < now()->toDateString();
    }
    
    /**
     * 获取距离到期天数
     */
    private function getDaysUntilDue($plan)
    {
        if ($plan->status !== 'approved') {
            return null;
        }
        
        return now()->diffInDays($plan->planned_date, false);
    }
    
    /**
     * 格式化预警项目
     */
    private function formatAlertItem($plan, $daysUntil)
    {
        return [
            'id' => $plan->id,
            'name' => $plan->name,
            'code' => $plan->code,
            'plannedDate' => $plan->planned_date,
            'teacher' => $plan->teacher->real_name ?? $plan->teacher->username,
            'catalog' => $plan->experimentCatalog->name ?? '',
            'className' => $plan->class_name,
            'daysUntil' => $daysUntil,
            'urgencyLevel' => $this->getUrgencyLevel($daysUntil)
        ];
    }
    
    /**
     * 获取紧急程度
     */
    private function getUrgencyLevel($daysUntil)
    {
        if ($daysUntil < 0) return 'overdue';
        if ($daysUntil == 0) return 'urgent';
        if ($daysUntil == 1) return 'high';
        if ($daysUntil <= 3) return 'medium';
        return 'low';
    }
    
    /**
     * 获取日历摘要
     */
    private function getCalendarSummary($plans, $start, $end)
    {
        $statusCounts = $plans->groupBy('status')->map->count();
        $totalPlans = $plans->count();
        $overduePlans = $plans->filter(function ($plan) {
            return $this->isOverdue($plan);
        })->count();
        
        return [
            'totalPlans' => $totalPlans,
            'overduePlans' => $overduePlans,
            'statusCounts' => $statusCounts,
            'dateRange' => [
                'start' => $start,
                'end' => $end
            ]
        ];
    }
}
