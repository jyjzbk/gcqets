<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaterialController extends Controller
{
    /**
     * 获取材料列表
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Material::with(['category', 'organization', 'creator']);

            // 权限过滤：系统管理员可查看所有数据，其他用户只能查看本组织数据
            $user = Auth::user();
            if ($user->user_type !== 'admin') {
                $query->where('organization_id', $user->primary_organization_id);
            }

            // 筛选条件
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('brand')) {
                $query->where('brand', 'like', '%' . $request->brand . '%');
            }

            if ($request->filled('supplier')) {
                $query->where('supplier', 'like', '%' . $request->supplier . '%');
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('specification', 'like', "%{$search}%");
                });
            }

            // 特殊筛选
            if ($request->filled('low_stock')) {
                $query->lowStock();
            }

            if ($request->filled('out_of_stock')) {
                $query->outOfStock();
            }

            if ($request->filled('expiring_soon')) {
                $days = $request->get('expiry_days', 30);
                $query->expiringSoon($days);
            }

            if ($request->filled('expired')) {
                $query->expired();
            }

            // 排序
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // 分页
            $perPage = $request->get('per_page', 15);
            $materials = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $materials,
                'message' => '获取材料列表成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取材料列表失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 创建材料
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'code' => 'required|string|max:100|unique:materials',
            'specification' => 'nullable|string|max:200',
            'brand' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:material_categories,id',
            'unit' => 'required|string|max:20',
            'unit_price' => 'nullable|numeric|min:0',
            'current_stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'storage_location' => 'nullable|string|max:200',
            'storage_conditions' => 'nullable|string',
            'expiry_date' => 'nullable|date|after:today',
            'safety_notes' => 'nullable|string',
            'supplier' => 'nullable|string|max:200',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();

            // 验证分类是否属于当前组织
            $category = MaterialCategory::findOrFail($request->category_id);
            if ($user->user_type !== 'admin' && $category->organization_id !== $user->primary_organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权使用该材料分类'
                ], 403);
            }

            $material = Material::create([
                'name' => $request->name,
                'code' => $request->code,
                'specification' => $request->specification,
                'brand' => $request->brand,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'unit' => $request->unit,
                'unit_price' => $request->unit_price,
                'current_stock' => $request->current_stock,
                'min_stock' => $request->min_stock,
                'max_stock' => $request->max_stock,
                'total_purchased' => $request->current_stock, // 初始库存作为采购量
                'storage_location' => $request->storage_location,
                'storage_conditions' => $request->storage_conditions,
                'expiry_date' => $request->expiry_date,
                'safety_notes' => $request->safety_notes,
                'supplier' => $request->supplier,
                'last_purchase_date' => now(),
                'status' => $request->current_stock > 0 ? 'active' : 'out_of_stock',
                'organization_id' => $user->primary_organization_id,
                'created_by' => $user->id,
            ]);

            // 如果有初始库存，记录库存日志
            if ($request->current_stock > 0) {
                $material->updateStock(
                    0, // 不改变库存，只记录初始状态
                    'purchase',
                    '初始库存录入',
                    $user->id
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $material->load(['category', 'organization', 'creator']),
                'message' => '创建材料成功'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '创建材料失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取材料详情
     */
    public function show($id): JsonResponse
    {
        try {
            $material = Material::with([
                'category', 
                'organization', 
                'creator', 
                'updater',
                'usages.user',
                'usages.experimentCatalog',
                'stockLogs.operator'
            ])->findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $material->organization_id !== $user->primary_organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权访问该材料'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $material,
                'message' => '获取材料详情成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取材料详情失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新材料
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'code' => 'required|string|max:100|unique:materials,code,' . $id,
            'specification' => 'nullable|string|max:200',
            'brand' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:material_categories,id',
            'unit' => 'required|string|max:20',
            'unit_price' => 'nullable|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'storage_location' => 'nullable|string|max:200',
            'storage_conditions' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'safety_notes' => 'nullable|string',
            'status' => 'required|in:active,inactive,expired,out_of_stock',
            'supplier' => 'nullable|string|max:200',
        ]);

        try {
            DB::beginTransaction();

            $material = Material::findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $material->organization_id !== $user->primary_organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权修改该材料'
                ], 403);
            }

            // 验证分类是否属于当前组织
            $category = MaterialCategory::findOrFail($request->category_id);
            if ($user->user_type !== 'admin' && $category->organization_id !== $user->primary_organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权使用该材料分类'
                ], 403);
            }

            $material->update([
                'name' => $request->name,
                'code' => $request->code,
                'specification' => $request->specification,
                'brand' => $request->brand,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'unit' => $request->unit,
                'unit_price' => $request->unit_price,
                'min_stock' => $request->min_stock,
                'max_stock' => $request->max_stock,
                'storage_location' => $request->storage_location,
                'storage_conditions' => $request->storage_conditions,
                'expiry_date' => $request->expiry_date,
                'safety_notes' => $request->safety_notes,
                'status' => $request->status,
                'supplier' => $request->supplier,
                'updated_by' => $user->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $material->load(['category', 'organization', 'creator', 'updater']),
                'message' => '更新材料成功'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '更新材料失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除材料
     */
    public function destroy($id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $material = Material::findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $material->organization_id !== $user->primary_organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权删除该材料'
                ], 403);
            }

            // 检查是否有使用记录
            if ($material->usages()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => '该材料还有使用记录，无法删除'
                ], 400);
            }

            $material->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '删除材料成功'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '删除材料失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 库存调整
     */
    public function adjustStock(Request $request, $id): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $material = Material::findOrFail($id);
            $user = Auth::user();

            // 权限检查
            if ($user->user_type !== 'admin' && $material->organization_id !== $user->primary_organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权调整该材料库存'
                ], 403);
            }

            // 检查调整后库存是否为负数
            if ($material->current_stock + $request->quantity < 0) {
                return response()->json([
                    'success' => false,
                    'message' => '调整后库存不能为负数'
                ], 400);
            }

            $material->updateStock(
                $request->quantity,
                'adjustment',
                $request->reason,
                $user->id,
                null,
                null
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $material->fresh(['category', 'organization']),
                'message' => '库存调整成功'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '库存调整失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取材料统计信息
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $baseQuery = Material::query();

            // 权限过滤
            if ($user->user_type !== 'admin') {
                $baseQuery->where('organization_id', $user->primary_organization_id);
            }

            $total = (clone $baseQuery)->count();
            $active = (clone $baseQuery)->where('status', 'active')->count();
            $lowStock = (clone $baseQuery)->lowStock()->count();
            $outOfStock = (clone $baseQuery)->outOfStock()->count();
            $expiringSoon = (clone $baseQuery)->expiringSoon(30)->count();
            $expired = (clone $baseQuery)->expired()->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'active' => $active,
                    'low_stock' => $lowStock,
                    'out_of_stock' => $outOfStock,
                    'expiring_soon' => $expiringSoon,
                    'expired' => $expired,
                ],
                'message' => '获取材料统计信息成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取材料统计信息失败：' . $e->getMessage()
            ], 500);
        }
    }
}
