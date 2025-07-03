<?php

namespace App\Http\Controllers;

use App\Models\MaterialUsage;
use App\Models\Material;
use App\Models\ExperimentCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaterialUsageController extends Controller
{
    /**
     * 获取材料使用记录列表
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = MaterialUsage::with([
                'material.category',
                'user',
                'experimentCatalog',
                'organization',
                'creator'
            ]);

            // 权限过滤：系统管理员可查看所有数据，其他用户只能查看本组织数据
            $user = Auth::user();
            if ($user->user_type !== 'admin') {
                $query->where('organization_id', $user->primary_organization_id);
            }

            // 筛选条件
            if ($request->filled('material_id')) {
                $query->where('material_id', $request->material_id);
            }

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->filled('usage_type')) {
                $query->where('usage_type', $request->usage_type);
            }

            if ($request->filled('experiment_catalog_id')) {
                $query->where('experiment_catalog_id', $request->experiment_catalog_id);
            }

            if ($request->filled('date_range')) {
                $dateRange = explode(',', $request->date_range);
                if (count($dateRange) === 2) {
                    $query->whereBetween('used_at', $dateRange);
                }
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('usage_code', 'like', "%{$search}%")
                      ->orWhere('purpose', 'like', "%{$search}%")
                      ->orWhere('class_name', 'like', "%{$search}%")
                      ->orWhereHas('material', function ($m) use ($search) {
                          $m->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                      });
                });
            }

            // 排序
            $sortBy = $request->get('sort_by', 'used_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // 分页
            $perPage = $request->get('per_page', 15);
            $usages = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $usages,
                'message' => '获取材料使用记录列表成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取材料使用记录列表失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 创建材料使用记录
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'material_id' => 'required|exists:materials,id',
            'quantity_used' => 'required|integer|min:1',
            'purpose' => 'required|string',
            'used_at' => 'required|date',
            'experiment_catalog_id' => 'nullable|exists:experiment_catalogs,id',
            'usage_type' => 'required|in:experiment,maintenance,teaching,other',
            'class_name' => 'nullable|string|max:100',
            'student_count' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();

            // 检查材料是否存在且属于当前组织
            $material = Material::findOrFail($request->material_id);
            if ($user->user_type !== 'admin' && $material->organization_id !== $user->primary_organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权使用该材料'
                ], 403);
            }

            // 检查实验目录（如果提供）
            if ($request->experiment_catalog_id) {
                $experimentCatalog = ExperimentCatalog::findOrFail($request->experiment_catalog_id);
                if ($user->user_type !== 'admin' && $experimentCatalog->organization_id !== $user->primary_organization_id) {
                    return response()->json([
                        'success' => false,
                        'message' => '无权关联该实验目录'
                    ], 403);
                }
            }

            // 生成使用编号
            $usageCode = 'USE_' . date('YmdHis') . '_' . $material->id;

            // 创建使用记录并更新库存
            $usage = MaterialUsage::createUsage([
                'usage_code' => $usageCode,
                'material_id' => $request->material_id,
                'user_id' => $user->id,
                'experiment_catalog_id' => $request->experiment_catalog_id,
                'quantity_used' => $request->quantity_used,
                'purpose' => $request->purpose,
                'used_at' => $request->used_at,
                'notes' => $request->notes,
                'usage_type' => $request->usage_type,
                'class_name' => $request->class_name,
                'student_count' => $request->student_count,
                'organization_id' => $user->primary_organization_id,
                'created_by' => $user->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $usage->load([
                    'material.category',
                    'user',
                    'experimentCatalog',
                    'organization'
                ]),
                'message' => '创建材料使用记录成功'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '创建材料使用记录失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取材料使用记录详情
     */
    public function show($id): JsonResponse
    {
        try {
            $usage = MaterialUsage::with([
                'material.category',
                'user',
                'experimentCatalog',
                'organization',
                'creator',
                'updater'
            ])->findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $usage->organization_id !== $user->primary_organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权访问该使用记录'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $usage,
                'message' => '获取材料使用记录详情成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取材料使用记录详情失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新材料使用记录
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'purpose' => 'required|string',
            'used_at' => 'required|date',
            'experiment_catalog_id' => 'nullable|exists:experiment_catalogs,id',
            'usage_type' => 'required|in:experiment,maintenance,teaching,other',
            'class_name' => 'nullable|string|max:100',
            'student_count' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $usage = MaterialUsage::findOrFail($id);
            $user = Auth::user();

            // 权限检查
            if ($user->user_type !== 'admin' && $usage->organization_id !== $user->primary_organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权修改该使用记录'
                ], 403);
            }

            // 检查实验目录（如果提供）
            if ($request->experiment_catalog_id) {
                $experimentCatalog = ExperimentCatalog::findOrFail($request->experiment_catalog_id);
                if ($user->user_type !== 'admin' && $experimentCatalog->organization_id !== $user->primary_organization_id) {
                    return response()->json([
                        'success' => false,
                        'message' => '无权关联该实验目录'
                    ], 403);
                }
            }

            $usage->update([
                'purpose' => $request->purpose,
                'used_at' => $request->used_at,
                'experiment_catalog_id' => $request->experiment_catalog_id,
                'usage_type' => $request->usage_type,
                'class_name' => $request->class_name,
                'student_count' => $request->student_count,
                'notes' => $request->notes,
                'updated_by' => $user->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $usage->load([
                    'material.category',
                    'user',
                    'experimentCatalog',
                    'organization',
                    'creator',
                    'updater'
                ]),
                'message' => '更新材料使用记录成功'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '更新材料使用记录失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除材料使用记录
     */
    public function destroy($id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $usage = MaterialUsage::findOrFail($id);
            $user = Auth::user();

            // 权限检查
            if ($user->user_type !== 'admin' && $usage->organization_id !== $user->primary_organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权删除该使用记录'
                ], 403);
            }

            // 恢复库存（将使用的数量加回去）
            $usage->material->updateStock(
                $usage->quantity_used,
                'adjustment',
                '删除使用记录，恢复库存',
                $user->id,
                'material_usage_delete',
                $usage->id
            );

            // 更新材料的总消耗量
            $usage->material->decrement('total_consumed', $usage->quantity_used);

            $usage->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '删除材料使用记录成功'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '删除材料使用记录失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取使用统计信息
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $baseQuery = MaterialUsage::query();
            
            // 权限过滤
            if ($user->user_type !== 'admin') {
                $baseQuery->where('organization_id', $user->primary_organization_id);
            }

            // 时间范围过滤
            if ($request->filled('date_range')) {
                $dateRange = explode(',', $request->date_range);
                if (count($dateRange) === 2) {
                    $baseQuery->whereBetween('used_at', $dateRange);
                }
            } else {
                // 默认显示本月数据
                $baseQuery->whereMonth('used_at', now()->month)
                         ->whereYear('used_at', now()->year);
            }

            $total = (clone $baseQuery)->count();
            $experimentUsage = (clone $baseQuery)->where('usage_type', 'experiment')->count();
            $teachingUsage = (clone $baseQuery)->where('usage_type', 'teaching')->count();
            $maintenanceUsage = (clone $baseQuery)->where('usage_type', 'maintenance')->count();
            $otherUsage = (clone $baseQuery)->where('usage_type', 'other')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'experiment_usage' => $experimentUsage,
                    'teaching_usage' => $teachingUsage,
                    'maintenance_usage' => $maintenanceUsage,
                    'other_usage' => $otherUsage,
                ],
                'message' => '获取使用统计信息成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取使用统计信息失败：' . $e->getMessage()
            ], 500);
        }
    }
}
