<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExperimentRecord;
use App\Models\ExperimentPlan;
use App\Models\ExperimentPhoto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ExperimentRecordController extends Controller
{
    /**
     * 获取实验记录列表
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ExperimentRecord::with([
                'experimentPlan:id,name,code,class_name,teacher_id',
                'experimentPlan.teacher:id,username,real_name',
                'organization:id,name',
                'creator:id,username,real_name',
                'reviewer:id,username,real_name'
            ]);

            // 权限过滤：系统管理员可查看所有数据，其他用户只能查看本组织数据
            $user = Auth::user();
            if ($user->user_type !== 'admin') {
                $query->where('organization_id', $user->organization_id);
            }

            // 如果是教师，只能查看自己的记录
            if ($user->user_type === 'teacher') {
                $query->where('created_by', $user->id);
            }

            // 搜索过滤
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('execution_notes', 'like', "%{$search}%")
                      ->orWhere('problems_encountered', 'like', "%{$search}%")
                      ->orWhereHas('experimentPlan', function ($planQuery) use ($search) {
                          $planQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('code', 'like', "%{$search}%");
                      });
                });
            }

            // 状态过滤
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // 完成状态过滤
            if ($request->filled('completion_status')) {
                $query->where('completion_status', $request->completion_status);
            }

            // 实验计划过滤
            if ($request->filled('experiment_plan_id')) {
                $query->where('experiment_plan_id', $request->experiment_plan_id);
            }

            // 日期范围过滤
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('execution_date', [$request->start_date, $request->end_date]);
            }

            // 排序
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // 分页
            $perPage = $request->input('per_page', 20);
            $records = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => '获取实验记录列表成功',
                'data' => $records
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取实验记录列表失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 创建实验记录
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'experiment_plan_id' => 'required|exists:experiment_plans,id',
                'execution_date' => 'required|date',
                'start_time' => 'nullable|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i|after:start_time',
                'actual_student_count' => 'nullable|integer|min:1|max:100',
                'completion_status' => 'required|in:not_started,in_progress,partial,completed,cancelled',
                'execution_notes' => 'nullable|string',
                'problems_encountered' => 'nullable|string',
                'solutions_applied' => 'nullable|string',
                'teaching_reflection' => 'nullable|string',
                'student_feedback' => 'nullable|string',
                'equipment_used' => 'nullable|array',
                'materials_consumed' => 'nullable|array',
                'data_records' => 'nullable|array',
                'safety_incidents' => 'nullable|string',
                'extra_data' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '数据验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 检查实验计划权限
            $plan = ExperimentPlan::findOrFail($request->experiment_plan_id);
            $user = Auth::user();
            
            if ($user->user_type !== 'admin' && $plan->organization_id !== $user->organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权为该实验计划创建记录'
                ], 403);
            }

            DB::beginTransaction();

            $data = $validator->validated();
            $data['organization_id'] = $plan->organization_id;
            $data['created_by'] = Auth::id();
            $data['status'] = 'draft';

            // 计算实际时长
            if ($data['start_time'] && $data['end_time']) {
                $start = \Carbon\Carbon::parse($data['start_time']);
                $end = \Carbon\Carbon::parse($data['end_time']);
                $data['actual_duration'] = $end->diffInMinutes($start);
            }

            $record = ExperimentRecord::create($data);

            // 加载关联数据
            $record->load([
                'experimentPlan:id,name,code,class_name',
                'organization:id,name'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '创建实验记录成功',
                'data' => $record
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '创建实验记录失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取实验记录详情
     */
    public function show($id): JsonResponse
    {
        try {
            $record = ExperimentRecord::with([
                'experimentPlan',
                'experimentPlan.teacher:id,username,real_name',
                'experimentPlan.experimentCatalog:id,name,code,subject,grade',
                'photos',
                'reviewer:id,username,real_name',
                'organization:id,name',
                'creator:id,username,real_name',
                'updater:id,username,real_name'
            ])->findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $record->organization_id !== $user->organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权访问该实验记录'
                ], 403);
            }

            // 如果是教师，只能查看自己的记录
            if ($user->user_type === 'teacher' && $record->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权访问该实验记录'
                ], 403);
            }

            // 验证记录完整性
            $validation = $record->validateRecord();
            $record->validation_results = $validation;

            return response()->json([
                'success' => true,
                'message' => '获取实验记录详情成功',
                'data' => $record
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取实验记录详情失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新实验记录
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $record = ExperimentRecord::findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $record->organization_id !== $user->organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权修改该实验记录'
                ], 403);
            }

            // 如果是教师，只能修改自己的记录
            if ($user->user_type === 'teacher' && $record->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权修改该实验记录'
                ], 403);
            }

            // 状态检查：只有草稿和需要修改的记录可以编辑
            if (!$record->canEdit()) {
                return response()->json([
                    'success' => false,
                    'message' => '当前状态不允许修改'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'execution_date' => 'required|date',
                'start_time' => 'nullable|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i|after:start_time',
                'actual_student_count' => 'nullable|integer|min:1|max:100',
                'completion_status' => 'required|in:not_started,in_progress,partial,completed,cancelled',
                'execution_notes' => 'nullable|string',
                'problems_encountered' => 'nullable|string',
                'solutions_applied' => 'nullable|string',
                'teaching_reflection' => 'nullable|string',
                'student_feedback' => 'nullable|string',
                'equipment_used' => 'nullable|array',
                'materials_consumed' => 'nullable|array',
                'data_records' => 'nullable|array',
                'safety_incidents' => 'nullable|string',
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

            // 计算实际时长
            if ($data['start_time'] && $data['end_time']) {
                $start = \Carbon\Carbon::parse($data['start_time']);
                $end = \Carbon\Carbon::parse($data['end_time']);
                $data['actual_duration'] = $end->diffInMinutes($start);
            }

            // 如果是需要修改的记录重新编辑，重置状态为草稿
            if ($record->status === 'revision_required') {
                $data['status'] = 'draft';
                $data['review_notes'] = null;
            }

            $record->update($data);

            // 加载关联数据
            $record->load([
                'experimentPlan:id,name,code,class_name',
                'organization:id,name'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '更新实验记录成功',
                'data' => $record
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '更新实验记录失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除实验记录
     */
    public function destroy($id): JsonResponse
    {
        try {
            $record = ExperimentRecord::findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $record->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权删除该实验记录'
                ], 403);
            }

            // 状态检查：只有草稿状态的记录可以删除
            if ($record->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => '只有草稿状态的记录可以删除'
                ], 400);
            }

            $record->delete();

            return response()->json([
                'success' => true,
                'message' => '删除实验记录成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '删除实验记录失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 提交审核
     */
    public function submit($id): JsonResponse
    {
        try {
            $record = ExperimentRecord::findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $record->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => '只有记录创建者可以提交审核'
                ], 403);
            }

            // 验证记录完整性
            $validation = $record->validateRecord();
            if (!$validation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => '记录不完整，无法提交审核',
                    'errors' => $validation['errors']
                ], 400);
            }

            if (!$record->submitForReview()) {
                return response()->json([
                    'success' => false,
                    'message' => '当前状态不允许提交审核'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => '提交审核成功',
                'data' => $record
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '提交审核失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 确认器材准备
     */
    public function confirmEquipment($id): JsonResponse
    {
        try {
            $record = ExperimentRecord::findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $record->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权确认器材准备'
                ], 403);
            }

            if (!$record->confirmEquipment()) {
                return response()->json([
                    'success' => false,
                    'message' => '器材确认失败'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => '器材准备确认成功',
                'data' => $record
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '器材确认失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 验证记录数据
     */
    public function validateData($id): JsonResponse
    {
        try {
            $record = ExperimentRecord::findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $record->organization_id !== $user->organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权验证该记录'
                ], 403);
            }

            $validation = $record->validateRecord();

            return response()->json([
                'success' => true,
                'message' => '数据验证完成',
                'data' => $validation
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '数据验证失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 上传照片
     */
    public function uploadPhoto(Request $request, $id): JsonResponse
    {
        try {
            $record = ExperimentRecord::findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $record->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权上传照片'
                ], 403);
            }

            // 状态检查
            if (!$record->canEdit()) {
                return response()->json([
                    'success' => false,
                    'message' => '当前状态不允许上传照片'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // 最大10MB
                'photo_type' => 'required|in:preparation,process,result,equipment,safety',
                'description' => 'nullable|string|max:500',
                'upload_method' => 'nullable|in:mobile,web',
                'location_info' => 'nullable|array',
                'location_info.latitude' => 'nullable|numeric|between:-90,90',
                'location_info.longitude' => 'nullable|numeric|between:-180,180'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '数据验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $file = $request->file('photo');
            $photoType = $request->input('photo_type');

            // 生成文件名
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = "experiment_photos/{$record->id}/{$photoType}/" . $fileName;

            // 存储文件
            $storedPath = $file->storeAs(dirname($filePath), basename($filePath), 'public');

            // 获取图片尺寸
            $imageSize = getimagesize($file->getPathname());
            $width = $imageSize[0] ?? null;
            $height = $imageSize[1] ?? null;

            // 创建照片记录
            $photoData = [
                'experiment_record_id' => $record->id,
                'photo_type' => $photoType,
                'file_path' => $storedPath,
                'file_name' => $fileName,
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'width' => $width,
                'height' => $height,
                'upload_method' => $request->input('upload_method', 'web'),
                'description' => $request->input('description'),
                'organization_id' => $record->organization_id,
                'created_by' => Auth::id()
            ];

            // 添加位置信息
            if ($request->filled('location_info')) {
                $photoData['location_info'] = $request->input('location_info');
            }

            // 添加时间戳信息
            $photoData['timestamp_info'] = [
                'upload_time' => now()->toISOString(),
                'timezone' => config('app.timezone')
            ];

            $photo = ExperimentPhoto::create($photoData);

            // 生成文件哈希
            $photo->hash = $photo->generateHash();
            $photo->save();

            // 提取EXIF数据
            $photo->extractExifData();

            // AI分析（如果启用）
            if (config('app.enable_ai_analysis', false)) {
                $photo->analyzeWithAI();
            }

            // 添加水印（如果启用）
            if (config('app.enable_watermark', false)) {
                $photo->addWatermark();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '照片上传成功',
                'data' => $photo
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '照片上传失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除照片
     */
    public function deletePhoto($recordId, $photoId): JsonResponse
    {
        try {
            $record = ExperimentRecord::findOrFail($recordId);
            $photo = ExperimentPhoto::where('experiment_record_id', $recordId)
                                   ->findOrFail($photoId);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $record->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权删除照片'
                ], 403);
            }

            // 状态检查
            if (!$record->canEdit()) {
                return response()->json([
                    'success' => false,
                    'message' => '当前状态不允许删除照片'
                ], 400);
            }

            $photo->delete();

            return response()->json([
                'success' => true,
                'message' => '照片删除成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '照片删除失败：' . $e->getMessage()
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
            $query = ExperimentRecord::query();

            // 权限过滤
            if ($user->user_type !== 'admin') {
                $query->where('organization_id', $user->organization_id);
            }

            // 如果是教师，只看自己的记录
            if ($user->user_type === 'teacher') {
                $query->where('created_by', $user->id);
            }

            $stats = [
                'total' => $query->count(),
                'draft' => $query->clone()->where('status', 'draft')->count(),
                'submitted' => $query->clone()->where('status', 'submitted')->count(),
                'approved' => $query->clone()->where('status', 'approved')->count(),
                'rejected' => $query->clone()->where('status', 'rejected')->count(),
                'completed' => $query->clone()->where('completion_status', 'completed')->count(),
                'in_progress' => $query->clone()->where('completion_status', 'in_progress')->count(),
                'this_month' => $query->clone()->whereMonth('created_at', now()->month)->count(),
                'this_week' => $query->clone()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'avg_completion' => $query->clone()->avg('completion_percentage') ?? 0
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

    /**
     * 获取可用的实验计划选项
     */
    public function getPlanOptions(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $query = ExperimentPlan::where('status', 'approved');

            // 权限过滤
            if ($user->user_type !== 'admin') {
                $query->where('organization_id', $user->organization_id);
            }

            // 如果是教师，只能看到自己的计划
            if ($user->user_type === 'teacher') {
                $query->where('teacher_id', $user->id);
            }

            $plans = $query->select('id', 'name', 'code', 'class_name', 'planned_date')
                          ->orderBy('planned_date', 'desc')
                          ->orderBy('name')
                          ->get();

            return response()->json([
                'success' => true,
                'data' => $plans,
                'message' => '获取实验计划选项成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取实验计划选项失败：' . $e->getMessage()
            ], 500);
        }
    }
}
