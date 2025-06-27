<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PermissionAuditLog;
use App\Models\PermissionConflict;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PermissionAuditController extends Controller
{
    /**
     * 获取审计日志列表
     */
    public function getLogs(Request $request): JsonResponse
    {
        try {
            $query = PermissionAuditLog::with([
                'user:id,name,avatar',
                'targetUser:id,name',
                'organization:id,name',
                'permission:id,name,display_name',
                'role:id,name,display_name'
            ]);

            // 筛选条件
            if ($request->has('action')) {
                $query->byAction($request->input('action'));
            }

            if ($request->has('subject_type')) {
                $query->bySubjectType($request->input('subject_type'));
            }

            if ($request->has('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            }

            if ($request->has('organization_id')) {
                $query->where('organization_id', $request->input('organization_id'));
            }

            if ($request->has('result')) {
                $query->byResult($request->input('result'));
            }

            // 时间范围筛选
            if ($request->has('start_time') && $request->has('end_time')) {
                $query->byDateRange($request->input('start_time'), $request->input('end_time'));
            }

            // 排序
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // 分页
            $perPage = $request->input('per_page', 20);
            $logs = $query->paginate($perPage);

            // 格式化数据
            $logs->getCollection()->transform(function ($log) {
                return [
                    'id' => $log->id,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                    'action' => $log->action,
                    'subject_type' => $log->subject_type,
                    'target_name' => $this->getTargetName($log),
                    'permission_name' => $log->permission_name ?: $log->permission?->display_name,
                    'operator_name' => $log->user?->name,
                    'operator_avatar' => $log->user?->avatar,
                    'result' => $log->result,
                    'reason' => $log->reason,
                    'ip_address' => $log->ip_address,
                    'error_message' => $log->error_message
                ];
            });

            return response()->json([
                'message' => '获取审计日志成功',
                'data' => $logs,
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取审计日志失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取审计日志详情
     */
    public function getLogDetail(int $id): JsonResponse
    {
        try {
            $log = PermissionAuditLog::with([
                'user:id,name,avatar',
                'targetUser:id,name',
                'organization:id,name',
                'permission:id,name,display_name',
                'role:id,name,display_name'
            ])->find($id);

            if (!$log) {
                return response()->json([
                    'message' => '审计日志不存在',
                    'code' => 404
                ], 404);
            }

            $data = [
                'id' => $log->id,
                'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                'action' => $log->action,
                'subject_type' => $log->subject_type,
                'target_name' => $this->getTargetName($log),
                'permission_name' => $log->permission_name ?: $log->permission?->display_name,
                'operator_name' => $log->user?->name,
                'operator_avatar' => $log->user?->avatar,
                'result' => $log->result,
                'reason' => $log->reason,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'old_values' => $log->old_values,
                'new_values' => $log->new_values,
                'context' => $log->context,
                'error_message' => $log->error_message
            ];

            return response()->json([
                'message' => '获取审计日志详情成功',
                'data' => $data,
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取审计日志详情失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取用户统计
     */
    public function getUserStats(Request $request): JsonResponse
    {
        try {
            $filters = [];
            
            if ($request->has('user_id')) {
                $filters['user_id'] = $request->input('user_id');
            }

            if ($request->has('organization_id')) {
                $filters['organization_id'] = $request->input('organization_id');
            }

            if ($request->has('date_from')) {
                $filters['date_from'] = $request->input('date_from');
            }

            if ($request->has('date_to')) {
                $filters['date_to'] = $request->input('date_to');
            }

            $stats = PermissionAuditLog::getAuditStats($filters);

            return response()->json([
                'message' => '获取用户统计成功',
                'data' => $stats,
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取用户统计失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取组织统计
     */
    public function getOrganizationStats(Request $request): JsonResponse
    {
        try {
            $organizationId = $request->input('organization_id');
            
            if (!$organizationId) {
                return response()->json([
                    'message' => '请提供组织ID',
                    'code' => 400
                ], 400);
            }

            $stats = PermissionAuditLog::getAuditStats(['organization_id' => $organizationId]);

            return response()->json([
                'message' => '获取组织统计成功',
                'data' => $stats,
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取组织统计失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取权限热点
     */
    public function getPermissionHotspots(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);
            $hotspots = PermissionAuditLog::getPermissionHotspots($limit);

            return response()->json([
                'message' => '获取权限热点成功',
                'data' => $hotspots,
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取权限热点失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取权限冲突
     */
    public function getConflicts(Request $request): JsonResponse
    {
        try {
            $query = PermissionConflict::with([
                'user:id,name',
                'organization:id,name',
                'permission:id,name,display_name',
                'resolver:id,name'
            ]);

            // 筛选条件
            if ($request->has('status')) {
                if ($request->input('status') === 'active') {
                    $query->active();
                } elseif ($request->input('status') === 'resolved') {
                    $query->resolved();
                }
            }

            if ($request->has('severity')) {
                $query->bySeverity($request->input('severity'));
            }

            if ($request->has('conflict_type')) {
                $query->byType($request->input('conflict_type'));
            }

            if ($request->has('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            }

            if ($request->has('organization_id')) {
                $query->where('organization_id', $request->input('organization_id'));
            }

            // 排序
            $query->orderBy('severity', 'desc')->orderBy('detected_at', 'desc');

            // 分页
            $perPage = $request->input('per_page', 20);
            $conflicts = $query->paginate($perPage);

            return response()->json([
                'message' => '获取权限冲突成功',
                'data' => $conflicts,
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取权限冲突失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取冲突统计
     */
    public function getConflictStats(Request $request): JsonResponse
    {
        try {
            $filters = [];
            
            if ($request->has('user_id')) {
                $filters['user_id'] = $request->input('user_id');
            }

            if ($request->has('organization_id')) {
                $filters['organization_id'] = $request->input('organization_id');
            }

            if ($request->has('date_from')) {
                $filters['date_from'] = $request->input('date_from');
            }

            if ($request->has('date_to')) {
                $filters['date_to'] = $request->input('date_to');
            }

            $stats = PermissionConflict::getConflictStats($filters);

            return response()->json([
                'message' => '获取冲突统计成功',
                'data' => $stats,
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取冲突统计失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取目标名称
     */
    private function getTargetName($log): string
    {
        switch ($log->subject_type) {
            case 'user':
                return $log->targetUser?->name ?: '未知用户';
            case 'role':
                return $log->role?->display_name ?: '未知角色';
            case 'organization':
                return $log->organization?->name ?: '未知组织';
            case 'template':
                return '权限模板';
            default:
                return '未知';
        }
    }
}
