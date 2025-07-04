<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExperimentRecord;
use App\Models\ExperimentPhoto;
use App\Models\ExperimentReviewLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExperimentReviewController extends Controller
{
    /**
     * 获取待审核记录列表
     */
    public function getPendingRecords(Request $request): JsonResponse
    {
        try {
            $query = ExperimentRecord::with([
                'experimentPlan:id,name,code,class_name',
                'experimentPlan.teacher:id,username,real_name',
                'organization:id,name',
                'creator:id,username,real_name',
                'photos'
            ])->where('status', 'submitted');

            // 权限过滤：系统管理员可查看所有数据，其他管理员只能查看本组织数据
            $user = Auth::user();
            if (!in_array($user->user_type, ['admin'])) {
                $query->where('organization_id', $user->organization_id);
            }

            // 搜索过滤
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('execution_notes', 'like', "%{$search}%")
                      ->orWhereHas('experimentPlan', function ($planQuery) use ($search) {
                          $planQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('code', 'like', "%{$search}%");
                      });
                });
            }

            // 完成状态过滤
            if ($request->filled('completion_status')) {
                $query->where('completion_status', $request->completion_status);
            }

            // 紧急程度过滤
            if ($request->filled('is_urgent')) {
                $query->where('is_urgent', $request->boolean('is_urgent'));
            }

            // 日期范围过滤
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('execution_date', [$request->start_date, $request->end_date]);
            }

            // 排序
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'asc'); // 待审核默认按时间正序
            $query->orderBy($sortBy, $sortOrder);

            // 分页
            $perPage = $request->input('per_page', 20);
            $records = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => '获取待审核记录列表成功',
                'data' => $records
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取待审核记录列表失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 审核通过
     */
    public function approve(Request $request, $id): JsonResponse
    {
        try {
            $record = ExperimentRecord::findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if (!$this->canReview($user, $record)) {
                return response()->json([
                    'success' => false,
                    'message' => '无权进行审核操作'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'review_notes' => 'nullable|string|max:1000',
                'review_score' => 'nullable|integer|min:1|max:10',
                'review_category' => 'nullable|in:format,content,photo,data,safety,completeness,other'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '数据验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $previousStatus = $record->status;
            
            // 更新记录状态
            $record->approve($user->id, $request->review_notes);

            // 创建审核日志
            ExperimentReviewLog::createLog(
                $record->id,
                'approve',
                $previousStatus,
                $record->status,
                $user->id,
                $user->user_type,
                $user->real_name ?? $user->username,
                $record->organization_id,
                [
                    'review_notes' => $request->review_notes,
                    'review_score' => $request->review_score,
                    'review_category' => $request->review_category,
                    'review_duration' => $request->input('review_duration'),
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '审核通过成功',
                'data' => $record
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '审核失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 审核拒绝
     */
    public function reject(Request $request, $id): JsonResponse
    {
        try {
            $record = ExperimentRecord::findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if (!$this->canReview($user, $record)) {
                return response()->json([
                    'success' => false,
                    'message' => '无权进行审核操作'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'review_notes' => 'required|string|max:1000',
                'review_category' => 'required|in:format,content,photo,data,safety,completeness,other'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '数据验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $previousStatus = $record->status;
            
            // 更新记录状态
            $record->reject($user->id, $request->review_notes);

            // 创建审核日志
            ExperimentReviewLog::createLog(
                $record->id,
                'reject',
                $previousStatus,
                $record->status,
                $user->id,
                $user->user_type,
                $user->real_name ?? $user->username,
                $record->organization_id,
                [
                    'review_notes' => $request->review_notes,
                    'review_category' => $request->review_category,
                    'review_duration' => $request->input('review_duration'),
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '审核拒绝成功',
                'data' => $record
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '审核拒绝失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 要求修改
     */
    public function requestRevision(Request $request, $id): JsonResponse
    {
        try {
            $record = ExperimentRecord::findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if (!$this->canReview($user, $record)) {
                return response()->json([
                    'success' => false,
                    'message' => '无权进行审核操作'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'review_notes' => 'required|string|max:1000',
                'review_category' => 'required|in:format,content,photo,data,safety,completeness,other',
                'revision_requirements' => 'nullable|array',
                'revision_deadline' => 'nullable|date|after:now'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '数据验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $previousStatus = $record->status;
            
            // 更新记录状态
            $record->requireRevision($user->id, $request->review_notes);

            // 创建审核日志
            ExperimentReviewLog::createLog(
                $record->id,
                'revision_request',
                $previousStatus,
                $record->status,
                $user->id,
                $user->user_type,
                $user->real_name ?? $user->username,
                $record->organization_id,
                [
                    'review_notes' => $request->review_notes,
                    'review_category' => $request->review_category,
                    'review_details' => [
                        'revision_requirements' => $request->revision_requirements,
                        'revision_deadline' => $request->revision_deadline,
                    ],
                    'review_deadline' => $request->revision_deadline,
                    'review_duration' => $request->input('review_duration'),
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '要求修改成功',
                'data' => $record
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '要求修改失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 强制完成
     */
    public function forceComplete(Request $request, $id): JsonResponse
    {
        try {
            $record = ExperimentRecord::findOrFail($id);

            // 权限检查：只有系统管理员可以强制完成
            $user = Auth::user();
            if ($user->user_type !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => '只有系统管理员可以强制完成'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'review_notes' => 'required|string|max:1000',
                'force_reason' => 'required|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '数据验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $previousStatus = $record->status;
            
            // 强制设置为已通过状态
            $record->status = 'approved';
            $record->reviewed_by = $user->id;
            $record->reviewed_at = now();
            $record->review_notes = $request->review_notes;
            $record->save();

            // 创建审核日志
            ExperimentReviewLog::createLog(
                $record->id,
                'force_complete',
                $previousStatus,
                $record->status,
                $user->id,
                $user->user_type,
                $user->real_name ?? $user->username,
                $record->organization_id,
                [
                    'review_notes' => $request->review_notes,
                    'review_details' => [
                        'force_reason' => $request->force_reason,
                        'is_forced' => true,
                    ],
                    'is_urgent' => true,
                    'review_duration' => $request->input('review_duration'),
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '强制完成成功',
                'data' => $record
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '强制完成失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 批量审核
     */
    public function batchReview(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'record_ids' => 'required|array|min:1',
                'record_ids.*' => 'exists:experiment_records,id',
                'action' => 'required|in:approve,reject',
                'review_notes' => 'required|string|max:1000',
                'review_category' => 'nullable|in:format,content,photo,data,safety,completeness,other'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '数据验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $recordIds = $request->record_ids;
            $action = $request->action;
            
            // 获取所有记录并检查权限
            $records = ExperimentRecord::whereIn('id', $recordIds)
                                     ->where('status', 'submitted')
                                     ->get();

            if ($records->count() !== count($recordIds)) {
                return response()->json([
                    'success' => false,
                    'message' => '部分记录不存在或状态不正确'
                ], 400);
            }

            // 权限检查
            foreach ($records as $record) {
                if (!$this->canReview($user, $record)) {
                    return response()->json([
                        'success' => false,
                        'message' => '无权审核部分记录'
                    ], 403);
                }
            }

            DB::beginTransaction();

            $successCount = 0;
            $failedRecords = [];

            foreach ($records as $record) {
                try {
                    $previousStatus = $record->status;
                    
                    if ($action === 'approve') {
                        $record->approve($user->id, $request->review_notes);
                        $reviewType = 'batch_approve';
                    } else {
                        $record->reject($user->id, $request->review_notes);
                        $reviewType = 'batch_reject';
                    }

                    // 创建审核日志
                    ExperimentReviewLog::createLog(
                        $record->id,
                        $reviewType,
                        $previousStatus,
                        $record->status,
                        $user->id,
                        $user->user_type,
                        $user->real_name ?? $user->username,
                        $record->organization_id,
                        [
                            'review_notes' => $request->review_notes,
                            'review_category' => $request->review_category,
                            'review_details' => [
                                'batch_operation' => true,
                                'batch_size' => count($recordIds),
                            ],
                        ]
                    );

                    $successCount++;
                } catch (\Exception $e) {
                    $failedRecords[] = [
                        'id' => $record->id,
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "批量审核完成，成功处理 {$successCount} 条记录",
                'data' => [
                    'success_count' => $successCount,
                    'failed_count' => count($failedRecords),
                    'failed_records' => $failedRecords
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '批量审核失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AI照片合规性检查
     */
    public function aiPhotoCheck(Request $request, $id): JsonResponse
    {
        try {
            $record = ExperimentRecord::with('photos')->findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if (!$this->canReview($user, $record)) {
                return response()->json([
                    'success' => false,
                    'message' => '无权进行AI检查'
                ], 403);
            }

            DB::beginTransaction();

            $checkResults = [];
            $totalPhotos = $record->photos->count();
            $compliantPhotos = 0;
            $nonCompliantPhotos = 0;

            foreach ($record->photos as $photo) {
                // 执行AI分析
                $analysisResult = $photo->analyzeWithAI();

                $checkResults[] = [
                    'photo_id' => $photo->id,
                    'photo_type' => $photo->photo_type,
                    'compliance_status' => $photo->compliance_status,
                    'analysis_result' => $analysisResult
                ];

                if ($photo->compliance_status === 'compliant') {
                    $compliantPhotos++;
                } elseif ($photo->compliance_status === 'non_compliant') {
                    $nonCompliantPhotos++;
                }
            }

            // 创建AI检查日志
            ExperimentReviewLog::createLog(
                $record->id,
                'ai_check',
                $record->status,
                $record->status, // 状态不变
                $user->id,
                $user->user_type,
                $user->real_name ?? $user->username,
                $record->organization_id,
                [
                    'is_ai_review' => true,
                    'ai_analysis_result' => [
                        'total_photos' => $totalPhotos,
                        'compliant_photos' => $compliantPhotos,
                        'non_compliant_photos' => $nonCompliantPhotos,
                        'compliance_rate' => $totalPhotos > 0 ? round(($compliantPhotos / $totalPhotos) * 100, 2) : 0,
                        'check_results' => $checkResults
                    ],
                    'review_notes' => "AI自动检查完成，共检查 {$totalPhotos} 张照片",
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'AI照片合规性检查完成',
                'data' => [
                    'total_photos' => $totalPhotos,
                    'compliant_photos' => $compliantPhotos,
                    'non_compliant_photos' => $nonCompliantPhotos,
                    'compliance_rate' => $totalPhotos > 0 ? round(($compliantPhotos / $totalPhotos) * 100, 2) : 0,
                    'check_results' => $checkResults
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'AI检查失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取审核日志
     */
    public function getReviewLogs(Request $request, $id): JsonResponse
    {
        try {
            $record = ExperimentRecord::findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $record->organization_id !== $user->organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权查看审核日志'
                ], 403);
            }

            $logs = ExperimentReviewLog::with('reviewer:id,username,real_name')
                                     ->where('experiment_record_id', $id)
                                     ->orderBy('created_at', 'desc')
                                     ->get();

            return response()->json([
                'success' => true,
                'message' => '获取审核日志成功',
                'data' => $logs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取审核日志失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取审核统计
     */
    public function getReviewStatistics(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $organizationId = $user->user_type === 'admin' ? null : $user->organization_id;

            $dateRange = null;
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $dateRange = [$request->start_date, $request->end_date];
            }

            $stats = ExperimentReviewLog::getReviewStatistics($organizationId, $dateRange);

            return response()->json([
                'success' => true,
                'message' => '获取审核统计成功',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取审核统计失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取审核趋势
     */
    public function getReviewTrend(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $organizationId = $user->user_type === 'admin' ? null : $user->organization_id;
            $days = $request->input('days', 30);

            $trend = ExperimentReviewLog::getReviewTrend($organizationId, $days);

            return response()->json([
                'success' => true,
                'message' => '获取审核趋势成功',
                'data' => $trend
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取审核趋势失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取审核人排行
     */
    public function getReviewerRanking(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $organizationId = $user->user_type === 'admin' ? null : $user->organization_id;
            $limit = $request->input('limit', 10);

            $ranking = ExperimentReviewLog::getReviewerRanking($organizationId, $limit);

            return response()->json([
                'success' => true,
                'message' => '获取审核人排行成功',
                'data' => $ranking
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取审核人排行失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 检查权限
     */
    private function canReview($user, $record): bool
    {
        // 系统管理员可以审核所有记录
        if ($user->user_type === 'admin') {
            return true;
        }

        // 其他管理员只能审核本组织的记录
        if (in_array($user->user_type, ['school_admin', 'district_admin'])) {
            return $record->organization_id === $user->organization_id;
        }

        return false;
    }
}
