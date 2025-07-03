<?php

namespace App\Http\Controllers;

use App\Models\EquipmentBorrowing;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EquipmentBorrowingController extends Controller
{
    /**
     * 获取借用记录列表
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = EquipmentBorrowing::with([
                'equipment.category', 
                'borrower', 
                'approver', 
                'organization'
            ]);

            // 权限过滤：系统管理员可查看所有数据，其他用户只能查看本组织数据
            $user = Auth::user();
            if ($user->user_type !== 'admin') {
                $query->where('organization_id', $user->primary_organization_id);
            }

            // 筛选条件
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('equipment_id')) {
                $query->where('equipment_id', $request->equipment_id);
            }

            if ($request->filled('borrower_id')) {
                $query->where('borrower_id', $request->borrower_id);
            }

            if ($request->filled('date_range')) {
                $dateRange = explode(',', $request->date_range);
                if (count($dateRange) === 2) {
                    $query->whereBetween('planned_start_time', $dateRange);
                }
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('borrowing_code', 'like', "%{$search}%")
                      ->orWhere('purpose', 'like', "%{$search}%")
                      ->orWhereHas('equipment', function ($eq) use ($search) {
                          $eq->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                      })
                      ->orWhereHas('borrower', function ($br) use ($search) {
                          $br->where('name', 'like', "%{$search}%")
                            ->orWhere('real_name', 'like', "%{$search}%");
                      });
                });
            }

            // 排序
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // 分页
            $perPage = $request->get('per_page', 15);
            $borrowings = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $borrowings,
                'message' => '获取借用记录列表成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取借用记录列表失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 创建借用申请
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'equipment_id' => 'required|exists:equipment,id',
            'purpose' => 'required|string',
            'planned_start_time' => 'required|date|after:now',
            'planned_end_time' => 'required|date|after:planned_start_time',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();

            // 检查设备是否可借用
            $equipment = Equipment::findOrFail($request->equipment_id);
            
            // 权限检查
            if ($user->user_type !== 'admin' && $equipment->organization_id !== $user->primary_organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权借用该设备'
                ], 403);
            }

            if (!$equipment->isAvailableForBorrowing()) {
                return response()->json([
                    'success' => false,
                    'message' => '设备当前状态不可借用：' . $equipment->status_label
                ], 400);
            }

            // 检查时间冲突
            $conflictBorrowing = EquipmentBorrowing::where('equipment_id', $request->equipment_id)
                ->whereIn('status', ['approved', 'borrowed'])
                ->where(function ($q) use ($request) {
                    $q->whereBetween('planned_start_time', [$request->planned_start_time, $request->planned_end_time])
                      ->orWhereBetween('planned_end_time', [$request->planned_start_time, $request->planned_end_time])
                      ->orWhere(function ($q2) use ($request) {
                          $q2->where('planned_start_time', '<=', $request->planned_start_time)
                             ->where('planned_end_time', '>=', $request->planned_end_time);
                      });
                })
                ->first();

            if ($conflictBorrowing) {
                return response()->json([
                    'success' => false,
                    'message' => '该时间段设备已被预约'
                ], 400);
            }

            // 生成借用编号
            $borrowingCode = 'BOR_' . date('YmdHis') . '_' . $equipment->id;

            $borrowing = EquipmentBorrowing::create([
                'borrowing_code' => $borrowingCode,
                'equipment_id' => $request->equipment_id,
                'borrower_id' => $user->id,
                'purpose' => $request->purpose,
                'planned_start_time' => $request->planned_start_time,
                'planned_end_time' => $request->planned_end_time,
                'status' => 'pending',
                'organization_id' => $user->primary_organization_id,
                'created_by' => $user->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $borrowing->load(['equipment.category', 'borrower', 'organization']),
                'message' => '创建借用申请成功'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '创建借用申请失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取借用记录详情
     */
    public function show($id): JsonResponse
    {
        try {
            $borrowing = EquipmentBorrowing::with([
                'equipment.category',
                'borrower',
                'approver',
                'organization',
                'creator',
                'updater'
            ])->findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $borrowing->organization_id !== $user->primary_organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权访问该借用记录'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $borrowing,
                'message' => '获取借用记录详情成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取借用记录详情失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 审批借用申请
     */
    public function approve(Request $request, $id): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $borrowing = EquipmentBorrowing::findOrFail($id);
            $user = Auth::user();

            // 权限检查
            if ($user->user_type !== 'admin' && $borrowing->organization_id !== $user->primary_organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权审批该借用申请'
                ], 403);
            }

            if (!$borrowing->canApprove()) {
                return response()->json([
                    'success' => false,
                    'message' => '该申请当前状态不可审批'
                ], 400);
            }

            if ($request->action === 'approve') {
                $borrowing->approve($user->id, $request->notes);
                $message = '审批通过';
            } else {
                $borrowing->reject($user->id, $request->notes);
                $message = '审批拒绝';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $borrowing->load(['equipment.category', 'borrower', 'approver']),
                'message' => $message . '成功'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '审批失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 借出设备
     */
    public function borrow(Request $request, $id): JsonResponse
    {
        $request->validate([
            'notes' => 'nullable|string',
            'equipment_condition' => 'required|in:good,normal,damaged',
        ]);

        try {
            DB::beginTransaction();

            $borrowing = EquipmentBorrowing::findOrFail($id);
            $user = Auth::user();

            // 权限检查
            if ($user->user_type !== 'admin' && $borrowing->organization_id !== $user->primary_organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权操作该借用记录'
                ], 403);
            }

            if (!$borrowing->canBorrow()) {
                return response()->json([
                    'success' => false,
                    'message' => '该申请当前状态不可借出'
                ], 400);
            }

            $borrowing->borrow($request->notes, $request->equipment_condition);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $borrowing->load(['equipment.category', 'borrower', 'approver']),
                'message' => '设备借出成功'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '设备借出失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 归还设备
     */
    public function returnEquipment(Request $request, $id): JsonResponse
    {
        $request->validate([
            'notes' => 'nullable|string',
            'equipment_condition' => 'required|in:good,normal,damaged',
        ]);

        try {
            DB::beginTransaction();

            $borrowing = EquipmentBorrowing::findOrFail($id);
            $user = Auth::user();

            // 权限检查
            if ($user->user_type !== 'admin' && $borrowing->organization_id !== $user->primary_organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权操作该借用记录'
                ], 403);
            }

            if (!$borrowing->canReturn()) {
                return response()->json([
                    'success' => false,
                    'message' => '该申请当前状态不可归还'
                ], 400);
            }

            $borrowing->returnEquipment($request->notes, $request->equipment_condition);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $borrowing->load(['equipment.category', 'borrower', 'approver']),
                'message' => '设备归还成功'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '设备归还失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 取消借用申请
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        $request->validate([
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $borrowing = EquipmentBorrowing::findOrFail($id);
            $user = Auth::user();

            // 权限检查：只有申请人或管理员可以取消
            if ($user->user_type !== 'admin' &&
                $borrowing->borrower_id !== $user->id &&
                $borrowing->organization_id !== $user->primary_organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权取消该借用申请'
                ], 403);
            }

            if (!in_array($borrowing->status, ['pending', 'approved'])) {
                return response()->json([
                    'success' => false,
                    'message' => '该申请当前状态不可取消'
                ], 400);
            }

            $borrowing->cancel($request->notes);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $borrowing->load(['equipment.category', 'borrower', 'approver']),
                'message' => '取消借用申请成功'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '取消借用申请失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取借用统计信息
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $baseQuery = EquipmentBorrowing::query();

            // 权限过滤
            if ($user->user_type !== 'admin') {
                $baseQuery->where('organization_id', $user->primary_organization_id);
            }

            $total = (clone $baseQuery)->count();
            $pending = (clone $baseQuery)->where('status', 'pending')->count();
            $approved = (clone $baseQuery)->where('status', 'approved')->count();
            $borrowed = (clone $baseQuery)->where('status', 'borrowed')->count();
            $returned = (clone $baseQuery)->where('status', 'returned')->count();
            $overdue = (clone $baseQuery)->overdue()->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'pending' => $pending,
                    'approved' => $approved,
                    'borrowed' => $borrowed,
                    'returned' => $returned,
                    'overdue' => $overdue,
                ],
                'message' => '获取借用统计信息成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取借用统计信息失败：' . $e->getMessage()
            ], 500);
        }
    }
}
