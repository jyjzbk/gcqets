<?php

namespace App\Http\Controllers;

use App\Models\CatalogVersion;
use App\Models\ExperimentCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CatalogVersionController extends Controller
{
    /**
     * 获取实验目录的版本历史
     */
    public function index(Request $request, $catalogId): JsonResponse
    {
        try {
            // 验证实验目录是否存在
            $catalog = ExperimentCatalog::findOrFail($catalogId);

            $query = CatalogVersion::with(['creator'])
                                  ->where('catalog_id', $catalogId);

            // 按变更类型过滤
            if ($request->filled('change_type')) {
                $query->where('change_type', $request->change_type);
            }

            // 按状态过滤
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // 排序
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // 分页
            $perPage = $request->get('per_page', 15);
            $versions = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'catalog' => $catalog->only(['id', 'name', 'code']),
                    'versions' => $versions
                ],
                'message' => '获取版本历史成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取版本历史失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取版本详情
     */
    public function show($catalogId, $versionId): JsonResponse
    {
        try {
            $version = CatalogVersion::with(['catalog', 'creator'])
                                   ->where('catalog_id', $catalogId)
                                   ->findOrFail($versionId);

            return response()->json([
                'success' => true,
                'data' => $version,
                'message' => '获取版本详情成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取版本详情失败：' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * 比较两个版本的差异
     */
    public function compare(Request $request, $catalogId): JsonResponse
    {
        try {
            $fromVersionId = $request->get('from_version');
            $toVersionId = $request->get('to_version');

            if (!$fromVersionId || !$toVersionId) {
                return response()->json([
                    'success' => false,
                    'message' => '请提供要比较的版本ID'
                ], 400);
            }

            $fromVersion = CatalogVersion::where('catalog_id', $catalogId)
                                       ->findOrFail($fromVersionId);
            $toVersion = CatalogVersion::where('catalog_id', $catalogId)
                                     ->findOrFail($toVersionId);

            $differences = $this->calculateDifferences(
                $fromVersion->catalog_data,
                $toVersion->catalog_data
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'from_version' => [
                        'id' => $fromVersion->id,
                        'version' => $fromVersion->version,
                        'created_at' => $fromVersion->created_at
                    ],
                    'to_version' => [
                        'id' => $toVersion->id,
                        'version' => $toVersion->version,
                        'created_at' => $toVersion->created_at
                    ],
                    'differences' => $differences
                ],
                'message' => '版本比较成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '版本比较失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 回滚到指定版本
     */
    public function rollback(Request $request, $catalogId, $versionId): JsonResponse
    {
        try {
            $catalog = ExperimentCatalog::findOrFail($catalogId);
            $version = CatalogVersion::where('catalog_id', $catalogId)
                                   ->findOrFail($versionId);

            DB::beginTransaction();

            // 备份当前数据
            $currentData = $catalog->toArray();
            CatalogVersion::createVersion(
                $catalog->id,
                'update',
                ['action' => '回滚前备份'],
                '回滚到版本 ' . $version->version . ' 前的备份',
                Auth::id()
            );

            // 恢复到指定版本的数据
            $rollbackData = $version->catalog_data;
            unset($rollbackData['id'], $rollbackData['created_at'], $rollbackData['updated_at'], $rollbackData['deleted_at']);
            $rollbackData['updated_by'] = Auth::id();

            $catalog->update($rollbackData);

            // 创建回滚记录
            CatalogVersion::createVersion(
                $catalog->id,
                'restore',
                [
                    'action' => '回滚到版本',
                    'target_version' => $version->version,
                    'rollback_reason' => $request->get('reason', '版本回滚')
                ],
                $request->get('reason', '回滚到版本 ' . $version->version),
                Auth::id()
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $catalog->fresh()->load(['curriculumStandard', 'organization', 'creator', 'updater']),
                'message' => '版本回滚成功'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '版本回滚失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取版本统计信息
     */
    public function statistics($catalogId): JsonResponse
    {
        try {
            $catalog = ExperimentCatalog::findOrFail($catalogId);

            $stats = [
                'total_versions' => CatalogVersion::where('catalog_id', $catalogId)->count(),
                'change_types' => CatalogVersion::where('catalog_id', $catalogId)
                                               ->selectRaw('change_type, COUNT(*) as count')
                                               ->groupBy('change_type')
                                               ->pluck('count', 'change_type'),
                'latest_version' => CatalogVersion::where('catalog_id', $catalogId)
                                                 ->latest()
                                                 ->first(['version', 'created_at', 'change_type']),
                'first_version' => CatalogVersion::where('catalog_id', $catalogId)
                                                ->oldest()
                                                ->first(['version', 'created_at', 'change_type']),
                'recent_activity' => CatalogVersion::where('catalog_id', $catalogId)
                                                  ->with('creator:id,name')
                                                  ->latest()
                                                  ->limit(5)
                                                  ->get(['id', 'version', 'change_type', 'created_by', 'created_at'])
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'catalog' => $catalog->only(['id', 'name', 'code']),
                    'statistics' => $stats
                ],
                'message' => '获取版本统计成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取版本统计失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 计算两个版本之间的差异
     */
    private function calculateDifferences(array $fromData, array $toData): array
    {
        $differences = [];
        $compareFields = [
            'name', 'subject', 'grade', 'textbook_version', 'experiment_type',
            'description', 'objectives', 'materials', 'procedures', 'safety_notes',
            'duration_minutes', 'student_count', 'difficulty_level', 'status'
        ];

        foreach ($compareFields as $field) {
            $fromValue = $fromData[$field] ?? null;
            $toValue = $toData[$field] ?? null;

            if ($fromValue !== $toValue) {
                $differences[$field] = [
                    'from' => $fromValue,
                    'to' => $toValue,
                    'type' => $this->getDifferenceType($fromValue, $toValue)
                ];
            }
        }

        return $differences;
    }

    /**
     * 获取差异类型
     */
    private function getDifferenceType($fromValue, $toValue): string
    {
        if ($fromValue === null && $toValue !== null) {
            return 'added';
        } elseif ($fromValue !== null && $toValue === null) {
            return 'removed';
        } else {
            return 'modified';
        }
    }
}
