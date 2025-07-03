<?php

namespace App\Http\Controllers;

use App\Models\CurriculumStandard;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CurriculumStandardController extends Controller
{
    /**
     * 获取课程标准列表
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = CurriculumStandard::with(['organization', 'creator']);

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
                      ->orWhere('version', 'like', "%{$search}%");
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

            // 状态过滤
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // 有效性过滤
            if ($request->filled('valid_only') && $request->valid_only) {
                $query->valid();
            }

            // 排序
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // 分页
            $perPage = $request->get('per_page', 15);
            $standards = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $standards,
                'message' => '获取课程标准列表成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取课程标准列表失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取课程标准详情
     */
    public function show($id): JsonResponse
    {
        try {
            $standard = CurriculumStandard::with([
                'organization',
                'creator',
                'updater',
                'experimentCatalogs'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $standard,
                'message' => '获取课程标准详情成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取课程标准详情失败：' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * 创建课程标准
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:200',
                'code' => 'required|string|max:50|unique:curriculum_standards,code',
                'version' => 'required|string|max:50',
                'subject' => 'required|in:physics,chemistry,biology,science',
                'grade' => 'required|in:grade1,grade2,grade3,grade4,grade5,grade6,grade7,grade8,grade9',
                'content' => 'nullable|string',
                'learning_objectives' => 'nullable|string',
                'key_concepts' => 'nullable|string',
                'skills_requirements' => 'nullable|string',
                'assessment_criteria' => 'nullable|string',
                'effective_date' => 'nullable|date',
                'expiry_date' => 'nullable|date|after:effective_date',
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

            $standard = CurriculumStandard::create($data);

            return response()->json([
                'success' => true,
                'data' => $standard->load(['organization', 'creator']),
                'message' => '创建课程标准成功'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '创建课程标准失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新课程标准
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $standard = CurriculumStandard::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:200',
                'code' => 'required|string|max:50|unique:curriculum_standards,code,' . $id,
                'version' => 'required|string|max:50',
                'subject' => 'required|in:physics,chemistry,biology,science',
                'grade' => 'required|in:grade1,grade2,grade3,grade4,grade5,grade6,grade7,grade8,grade9',
                'content' => 'nullable|string',
                'learning_objectives' => 'nullable|string',
                'key_concepts' => 'nullable|string',
                'skills_requirements' => 'nullable|string',
                'assessment_criteria' => 'nullable|string',
                'effective_date' => 'nullable|date',
                'expiry_date' => 'nullable|date|after:effective_date',
                'status' => 'required|in:active,inactive,draft,archived',
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

            $standard->update($data);

            return response()->json([
                'success' => true,
                'data' => $standard->load(['organization', 'creator', 'updater']),
                'message' => '更新课程标准成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '更新课程标准失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除课程标准
     */
    public function destroy($id): JsonResponse
    {
        try {
            $standard = CurriculumStandard::findOrFail($id);

            // 检查是否有关联的实验目录
            if ($standard->experimentCatalogs()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => '该课程标准已关联实验目录，无法删除'
                ], 400);
            }

            $standard->delete();

            return response()->json([
                'success' => true,
                'message' => '删除课程标准成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '删除课程标准失败：' . $e->getMessage()
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
                'subjects' => CurriculumStandard::getSubjectOptions(),
                'grades' => CurriculumStandard::getGradeOptions(),
                'statuses' => CurriculumStandard::getStatusOptions(),
            ]
        ]);
    }

    /**
     * 获取有效的课程标准（用于下拉选择）
     */
    public function validStandards(Request $request): JsonResponse
    {
        try {
            $query = CurriculumStandard::valid();

            // 根据用户权限过滤数据
            $user = Auth::user();
            // 只有非管理员用户才按组织过滤数据
            if ($user->organization_id && $user->user_type !== 'admin') {
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
                'message' => '获取有效课程标准成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取有效课程标准失败：' . $e->getMessage()
            ], 500);
        }
    }
}
