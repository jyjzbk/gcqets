<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EquipmentController extends Controller
{
    /**
     * 获取设备列表
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Equipment::with(['category', 'organization', 'creator', 'currentBorrowing.borrower']);

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

            if ($request->filled('location')) {
                $query->where('location', 'like', '%' . $request->location . '%');
            }

            if ($request->filled('brand')) {
                $query->where('brand', 'like', '%' . $request->brand . '%');
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
                      ->orWhere('serial_number', 'like', "%{$search}%");
                });
            }

            // 特殊筛选
            if ($request->filled('needs_maintenance')) {
                $query->needsMaintenance();
            }

            if ($request->filled('warranty_expiring')) {
                $days = $request->get('warranty_days', 30);
                $query->warrantyExpiring($days);
            }

            // 排序
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // 分页
            $perPage = $request->get('per_page', 15);
            $equipment = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $equipment,
                'message' => '获取设备列表成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取设备列表失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 创建设备
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'code' => 'required|string|max:100|unique:equipment',
            'model' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:equipment_categories,id',
            'serial_number' => 'nullable|string|max:100',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'supplier' => 'nullable|string|max:200',
            'warranty_date' => 'nullable|date|after:purchase_date',
            'location' => 'nullable|string|max:200',
            'usage_notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();

            // 验证分类是否属于当前组织
            $category = EquipmentCategory::findOrFail($request->category_id);
            if ($user->user_type !== 'admin' && $category->organization_id !== $user->organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权使用该设备分类'
                ], 403);
            }

            $equipment = Equipment::create([
                'name' => $request->name,
                'code' => $request->code,
                'model' => $request->model,
                'brand' => $request->brand,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'serial_number' => $request->serial_number,
                'purchase_price' => $request->purchase_price,
                'purchase_date' => $request->purchase_date,
                'supplier' => $request->supplier,
                'warranty_date' => $request->warranty_date,
                'location' => $request->location,
                'usage_notes' => $request->usage_notes,
                'status' => 'available',
                'organization_id' => $user->organization_id,
                'created_by' => $user->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $equipment->load(['category', 'organization', 'creator']),
                'message' => '创建设备成功'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '创建设备失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取设备详情
     */
    public function show($id): JsonResponse
    {
        try {
            $equipment = Equipment::with([
                'category', 
                'organization', 
                'creator', 
                'updater',
                'borrowings.borrower',
                'borrowings.approver',
                'currentBorrowing.borrower'
            ])->findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $equipment->organization_id !== $user->organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权访问该设备'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $equipment,
                'message' => '获取设备详情成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取设备详情失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新设备
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:200',
            'code' => 'required|string|max:100|unique:equipment,code,' . $id,
            'model' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:equipment_categories,id',
            'serial_number' => 'nullable|string|max:100',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'supplier' => 'nullable|string|max:200',
            'warranty_date' => 'nullable|date|after:purchase_date',
            'status' => 'required|in:available,borrowed,maintenance,damaged,scrapped',
            'location' => 'nullable|string|max:200',
            'usage_notes' => 'nullable|string',
            'maintenance_notes' => 'nullable|string',
            'last_maintenance_date' => 'nullable|date',
            'next_maintenance_date' => 'nullable|date|after:last_maintenance_date',
        ]);

        try {
            DB::beginTransaction();

            $equipment = Equipment::findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $equipment->organization_id !== $user->organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权修改该设备'
                ], 403);
            }

            // 验证分类是否属于当前组织
            $category = EquipmentCategory::findOrFail($request->category_id);
            if ($user->user_type !== 'admin' && $category->organization_id !== $user->organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权使用该设备分类'
                ], 403);
            }

            $equipment->update([
                'name' => $request->name,
                'code' => $request->code,
                'model' => $request->model,
                'brand' => $request->brand,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'serial_number' => $request->serial_number,
                'purchase_price' => $request->purchase_price,
                'purchase_date' => $request->purchase_date,
                'supplier' => $request->supplier,
                'warranty_date' => $request->warranty_date,
                'status' => $request->status,
                'location' => $request->location,
                'usage_notes' => $request->usage_notes,
                'maintenance_notes' => $request->maintenance_notes,
                'last_maintenance_date' => $request->last_maintenance_date,
                'next_maintenance_date' => $request->next_maintenance_date,
                'updated_by' => $user->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $equipment->load(['category', 'organization', 'creator', 'updater']),
                'message' => '更新设备成功'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '更新设备失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除设备
     */
    public function destroy($id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $equipment = Equipment::findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $equipment->organization_id !== $user->organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权删除该设备'
                ], 403);
            }

            // 检查是否有未完成的借用记录
            $activeBorrowings = $equipment->borrowings()
                ->whereIn('status', ['pending', 'approved', 'borrowed'])
                ->count();

            if ($activeBorrowings > 0) {
                return response()->json([
                    'success' => false,
                    'message' => '该设备还有未完成的借用记录，无法删除'
                ], 400);
            }

            $equipment->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '删除设备成功'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '删除设备失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取设备统计信息
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $baseQuery = Equipment::query();

            // 权限过滤
            if ($user->user_type !== 'admin') {
                $baseQuery->where('organization_id', $user->organization_id);
            }

            $total = (clone $baseQuery)->count();
            $available = (clone $baseQuery)->where('status', 'available')->count();
            $borrowed = (clone $baseQuery)->where('status', 'borrowed')->count();
            $maintenance = (clone $baseQuery)->where('status', 'maintenance')->count();
            $damaged = (clone $baseQuery)->where('status', 'damaged')->count();
            $needsMaintenance = (clone $baseQuery)->needsMaintenance()->count();
            $warrantyExpiring = (clone $baseQuery)->warrantyExpiring(30)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'available' => $available,
                    'borrowed' => $borrowed,
                    'maintenance' => $maintenance,
                    'damaged' => $damaged,
                    'needs_maintenance' => $needsMaintenance,
                    'warranty_expiring' => $warrantyExpiring,
                ],
                'message' => '获取设备统计信息成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取设备统计信息失败：' . $e->getMessage()
            ], 500);
        }
    }
}
