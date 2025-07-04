<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExperimentPlan;
use App\Models\ExperimentCatalog;
use App\Models\CurriculumStandard;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExperimentPlanController extends Controller
{
    /**
     * 获取实验计划列表
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ExperimentPlan::with([
                'experimentCatalog:id,name,code,subject,grade',
                'curriculumStandard:id,name,code,version',
                'teacher:id,username,real_name',
                'organization:id,name',
                'creator:id,username,real_name'
            ]);

            // 权限过滤：系统管理员可查看所有数据，其他用户只能查看本组织数据
            $user = Auth::user();
            if ($user->user_type !== 'admin') {
                $query->where('organization_id', $user->organization_id);
            }

            // 搜索过滤
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('class_name', 'like', "%{$search}%");
                });
            }

            // 状态过滤
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // 教师过滤
            if ($request->filled('teacher_id')) {
                $query->where('teacher_id', $request->teacher_id);
            }

            // 实验目录过滤
            if ($request->filled('experiment_catalog_id')) {
                $query->where('experiment_catalog_id', $request->experiment_catalog_id);
            }

            // 日期范围过滤
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('planned_date', [$request->start_date, $request->end_date]);
            }

            // 优先级过滤
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            // 排序
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // 分页
            $perPage = $request->input('per_page', 20);
            $plans = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => '获取实验计划列表成功',
                'data' => $plans
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取实验计划列表失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 创建实验计划
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:200',
                'experiment_catalog_id' => 'required|exists:experiment_catalogs,id',
                'curriculum_standard_id' => 'nullable|exists:curriculum_standards,id',
                'class_name' => 'nullable|string|max:100',
                'student_count' => 'nullable|integer|min:1|max:100',
                'planned_date' => 'nullable|date|after_or_equal:today',
                'planned_duration' => 'nullable|integer|min:10|max:300',
                'description' => 'nullable|string',
                'objectives' => 'nullable|string',
                'key_points' => 'nullable|string',
                'safety_requirements' => 'nullable|string',
                'equipment_requirements' => 'nullable|array',
                'material_requirements' => 'nullable|array',
                'priority' => 'nullable|in:low,medium,high',
                'is_public' => 'nullable|boolean',
                'extra_data' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '数据验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $data = $validator->validated();
            $data['organization_id'] = Auth::user()->organization_id;
            $data['teacher_id'] = Auth::id();
            $data['created_by'] = Auth::id();
            $data['code'] = ExperimentPlan::generateCode(Auth::user()->organization_id);
            $data['status'] = 'draft';

            $plan = ExperimentPlan::create($data);

            // 加载关联数据
            $plan->load([
                'experimentCatalog:id,name,code,subject,grade',
                'curriculumStandard:id,name,code,version',
                'teacher:id,username,real_name',
                'organization:id,name'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '创建实验计划成功',
                'data' => $plan
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '创建实验计划失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取实验计划详情
     */
    public function show($id): JsonResponse
    {
        try {
            $plan = ExperimentPlan::with([
                'experimentCatalog',
                'curriculumStandard',
                'teacher:id,username,real_name,email',
                'approver:id,username,real_name',
                'organization:id,name',
                'creator:id,username,real_name',
                'updater:id,username,real_name'
            ])->findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $plan->organization_id !== $user->organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权访问该实验计划'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => '获取实验计划详情成功',
                'data' => $plan
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取实验计划详情失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新实验计划
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $plan = ExperimentPlan::findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $plan->organization_id !== $user->organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权修改该实验计划'
                ], 403);
            }

            // 状态检查：只有草稿和被拒绝的计划可以修改
            if (!$plan->canEdit()) {
                return response()->json([
                    'success' => false,
                    'message' => '当前状态不允许修改'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:200',
                'experiment_catalog_id' => 'required|exists:experiment_catalogs,id',
                'curriculum_standard_id' => 'nullable|exists:curriculum_standards,id',
                'class_name' => 'nullable|string|max:100',
                'student_count' => 'nullable|integer|min:1|max:100',
                'planned_date' => 'nullable|date|after_or_equal:today',
                'planned_duration' => 'nullable|integer|min:10|max:300',
                'description' => 'nullable|string',
                'objectives' => 'nullable|string',
                'key_points' => 'nullable|string',
                'safety_requirements' => 'nullable|string',
                'equipment_requirements' => 'nullable|array',
                'material_requirements' => 'nullable|array',
                'priority' => 'nullable|in:low,medium,high',
                'is_public' => 'nullable|boolean',
                'extra_data' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '数据验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $data = $validator->validated();
            $data['updated_by'] = Auth::id();

            // 如果是被拒绝的计划重新修改，重置状态为草稿
            if ($plan->status === 'rejected') {
                $data['status'] = 'draft';
                $data['rejection_reason'] = null;
                $data['approval_notes'] = null;
            }

            $plan->update($data);

            // 加载关联数据
            $plan->load([
                'experimentCatalog:id,name,code,subject,grade',
                'curriculumStandard:id,name,code,version',
                'teacher:id,username,real_name',
                'organization:id,name'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '更新实验计划成功',
                'data' => $plan
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '更新实验计划失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除实验计划
     */
    public function destroy($id): JsonResponse
    {
        try {
            $plan = ExperimentPlan::findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $plan->organization_id !== $user->organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权删除该实验计划'
                ], 403);
            }

            // 状态检查：只有草稿状态的计划可以删除
            if ($plan->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => '只有草稿状态的计划可以删除'
                ], 400);
            }

            $plan->delete();

            return response()->json([
                'success' => true,
                'message' => '删除实验计划成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '删除实验计划失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 提交审批
     */
    public function submit($id): JsonResponse
    {
        try {
            $plan = ExperimentPlan::findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $plan->teacher_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => '只有计划创建者可以提交审批'
                ], 403);
            }

            if (!$plan->submitForApproval()) {
                return response()->json([
                    'success' => false,
                    'message' => '当前状态不允许提交审批'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => '提交审批成功',
                'data' => $plan
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '提交审批失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 审批通过
     */
    public function approve(Request $request, $id): JsonResponse
    {
        try {
            $plan = ExperimentPlan::findOrFail($id);

            // 权限检查：只有管理员可以审批
            $user = Auth::user();
            if (!in_array($user->user_type, ['admin', 'school_admin', 'district_admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => '无权进行审批操作'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'approval_notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '数据验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!$plan->approve($user->id, $request->approval_notes)) {
                return response()->json([
                    'success' => false,
                    'message' => '当前状态不允许审批'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => '审批通过成功',
                'data' => $plan
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '审批失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 审批拒绝
     */
    public function reject(Request $request, $id): JsonResponse
    {
        try {
            $plan = ExperimentPlan::findOrFail($id);

            // 权限检查：只有管理员可以审批
            $user = Auth::user();
            if (!in_array($user->user_type, ['admin', 'school_admin', 'district_admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => '无权进行审批操作'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'rejection_reason' => 'required|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '数据验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!$plan->reject($user->id, $request->rejection_reason)) {
                return response()->json([
                    'success' => false,
                    'message' => '当前状态不允许拒绝'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => '审批拒绝成功',
                'data' => $plan
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '审批拒绝失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取实验目录选项（用于下拉选择）
     */
    public function getCatalogOptions(Request $request): JsonResponse
    {
        try {
            $query = ExperimentCatalog::where('status', 'active');

            // 权限过滤
            $user = Auth::user();
            if ($user->user_type !== 'admin') {
                $query->where('organization_id', $user->organization_id);
            }

            // 按学科过滤
            if ($request->filled('subject')) {
                $query->where('subject', $request->subject);
            }

            // 按年级过滤
            if ($request->filled('grade')) {
                $query->where('grade', $request->grade);
            }

            $catalogs = $query->select('id', 'name', 'code', 'subject', 'grade', 'experiment_type')
                             ->orderBy('subject')
                             ->orderBy('grade')
                             ->orderBy('name')
                             ->get();

            return response()->json([
                'success' => true,
                'data' => $catalogs,
                'message' => '获取实验目录选项成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取实验目录选项失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取课程标准选项（用于下拉选择）
     */
    public function getStandardOptions(Request $request): JsonResponse
    {
        try {
            $query = CurriculumStandard::where('status', 'active');

            // 权限过滤
            $user = Auth::user();
            if ($user->user_type !== 'admin') {
                $query->where('organization_id', $user->organization_id);
            }

            // 按学科过滤
            if ($request->filled('subject')) {
                $query->where('subject', $request->subject);
            }

            // 按年级过滤
            if ($request->filled('grade')) {
                $query->where('grade', $request->grade);
            }

            $standards = $query->select('id', 'name', 'code', 'version', 'subject', 'grade')
                              ->orderBy('subject')
                              ->orderBy('grade')
                              ->orderBy('name')
                              ->get();

            return response()->json([
                'success' => true,
                'data' => $standards,
                'message' => '获取课程标准选项成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取课程标准选项失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取统计数据
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $query = ExperimentPlan::query();

            // 权限过滤
            if ($user->user_type !== 'admin') {
                $query->where('organization_id', $user->organization_id);
            }

            // 如果是教师，只看自己的计划
            if ($user->user_type === 'teacher') {
                $query->where('teacher_id', $user->id);
            }

            $stats = [
                'total' => $query->count(),
                'draft' => $query->clone()->where('status', 'draft')->count(),
                'pending' => $query->clone()->where('status', 'pending')->count(),
                'approved' => $query->clone()->where('status', 'approved')->count(),
                'rejected' => $query->clone()->where('status', 'rejected')->count(),
                'executing' => $query->clone()->where('status', 'executing')->count(),
                'completed' => $query->clone()->where('status', 'completed')->count(),
                'this_month' => $query->clone()->whereMonth('created_at', now()->month)->count(),
                'this_week' => $query->clone()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => '获取统计数据成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取统计数据失败：' . $e->getMessage()
            ], 500);
        }
    }
}
