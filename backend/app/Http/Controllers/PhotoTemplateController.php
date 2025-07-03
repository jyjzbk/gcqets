<?php

namespace App\Http\Controllers;

use App\Models\PhotoTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PhotoTemplateController extends Controller
{
    /**
     * 获取照片模板列表
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = PhotoTemplate::with(['organization', 'creator']);

            // 根据用户权限过滤数据
            $user = Auth::user();
            // 只有非管理员用户才按组织过滤数据
            if ($user->organization_id && $user->user_type !== 'admin') {
                $query->where('organization_id', $user->organization_id);
            }

            // 搜索过滤
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // 学科过滤
            if ($request->filled('subject')) {
                $query->where('subject', $request->subject);
            }

            // 年级过滤
            if ($request->filled('grade')) {
                $query->where('grade', $request->grade);
            }

            // 实验类型过滤
            if ($request->filled('experiment_type')) {
                $query->where('experiment_type', $request->experiment_type);
            }

            // 状态过滤
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // 排序
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // 分页
            $perPage = $request->get('per_page', 15);
            $templates = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $templates,
                'message' => '获取照片模板列表成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取照片模板列表失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取照片模板详情
     */
    public function show($id): JsonResponse
    {
        try {
            $template = PhotoTemplate::with([
                'organization',
                'creator',
                'updater'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $template,
                'message' => '获取照片模板详情成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取照片模板详情失败：' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * 创建照片模板
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:200',
                'code' => 'required|string|max:50|unique:photo_templates,code',
                'subject' => 'required|in:physics,chemistry,biology,science',
                'grade' => 'required|in:grade1,grade2,grade3,grade4,grade5,grade6,grade7,grade8,grade9',
                'textbook_version' => 'required|string|max:100',
                'experiment_type' => 'required|in:demonstration,group,individual,inquiry',
                'description' => 'nullable|string',
                'required_photos' => 'required|array|min:1',
                'required_photos.*.name' => 'required|string',
                'required_photos.*.description' => 'required|string',
                'required_photos.*.timing' => 'required|in:before,during,after',
                'required_photos.*.angle' => 'required|string',
                'optional_photos' => 'nullable|array',
                'optional_photos.*.name' => 'required|string',
                'optional_photos.*.description' => 'required|string',
                'optional_photos.*.timing' => 'required|in:before,during,after',
                'optional_photos.*.angle' => 'required|string',
                'photo_specifications' => 'nullable|array',
                'extra_data' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '数据验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            $data['organization_id'] = Auth::user()->organization_id;
            $data['created_by'] = Auth::id();
            $data['status'] = $request->get('status', 'draft');

            $template = PhotoTemplate::create($data);

            return response()->json([
                'success' => true,
                'data' => $template->load(['organization', 'creator']),
                'message' => '创建照片模板成功'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '创建照片模板失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新照片模板
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $template = PhotoTemplate::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:200',
                'code' => 'required|string|max:50|unique:photo_templates,code,' . $id,
                'subject' => 'required|in:physics,chemistry,biology,science',
                'grade' => 'required|in:grade1,grade2,grade3,grade4,grade5,grade6,grade7,grade8,grade9',
                'textbook_version' => 'required|string|max:100',
                'experiment_type' => 'required|in:demonstration,group,individual,inquiry',
                'description' => 'nullable|string',
                'required_photos' => 'required|array|min:1',
                'required_photos.*.name' => 'required|string',
                'required_photos.*.description' => 'required|string',
                'required_photos.*.timing' => 'required|in:before,during,after',
                'required_photos.*.angle' => 'required|string',
                'optional_photos' => 'nullable|array',
                'optional_photos.*.name' => 'required|string',
                'optional_photos.*.description' => 'required|string',
                'optional_photos.*.timing' => 'required|in:before,during,after',
                'optional_photos.*.angle' => 'required|string',
                'photo_specifications' => 'nullable|array',
                'status' => 'required|in:active,inactive,draft',
                'extra_data' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '数据验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            $data['updated_by'] = Auth::id();

            $template->update($data);

            return response()->json([
                'success' => true,
                'data' => $template->load(['organization', 'creator', 'updater']),
                'message' => '更新照片模板成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '更新照片模板失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除照片模板
     */
    public function destroy($id): JsonResponse
    {
        try {
            $template = PhotoTemplate::findOrFail($id);
            $template->delete();

            return response()->json([
                'success' => true,
                'message' => '删除照片模板成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '删除照片模板失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取选项数据
     */
    public function options(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'subjects' => PhotoTemplate::getSubjectOptions(),
                'grades' => PhotoTemplate::getGradeOptions(),
                'experiment_types' => PhotoTemplate::getExperimentTypeOptions(),
                'statuses' => PhotoTemplate::getStatusOptions(),
                'photo_timings' => [
                    'before' => '实验前',
                    'during' => '实验中',
                    'after' => '实验后'
                ],
                'photo_angles' => [
                    'overview' => '全景',
                    'close-up' => '特写',
                    'document' => '文档',
                    'group' => '小组',
                    'safety' => '安全',
                    'audience' => '观众',
                    'classroom' => '课堂'
                ]
            ]
        ]);
    }

    /**
     * 根据条件获取匹配的照片模板
     */
    public function getMatchingTemplates(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'subject' => 'required|in:physics,chemistry,biology,science',
                'grade' => 'required|in:grade1,grade2,grade3,grade4,grade5,grade6,grade7,grade8,grade9',
                'textbook_version' => 'nullable|string',
                'experiment_type' => 'required|in:demonstration,group,individual,inquiry'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '参数验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = PhotoTemplate::active()
                                 ->where('subject', $request->subject)
                                 ->where('grade', $request->grade)
                                 ->where('experiment_type', $request->experiment_type);

            // 根据用户权限过滤数据
            $user = Auth::user();
            // 只有非管理员用户才按组织过滤数据
            if ($user->organization_id && $user->user_type !== 'admin') {
                $query->where('organization_id', $user->organization_id);
            }

            if ($request->filled('textbook_version')) {
                $query->where('textbook_version', $request->textbook_version);
            }

            $templates = $query->select('id', 'name', 'code', 'required_photos', 'optional_photos', 'photo_specifications')
                              ->orderBy('name')
                              ->get();

            return response()->json([
                'success' => true,
                'data' => $templates,
                'message' => '获取匹配照片模板成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取匹配照片模板失败：' . $e->getMessage()
            ], 500);
        }
    }
}
