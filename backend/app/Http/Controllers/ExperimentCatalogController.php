<?php

namespace App\Http\Controllers;

use App\Models\ExperimentCatalog;
use App\Models\CatalogVersion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExperimentCatalogController extends Controller
{
    /**
     * 获取实验目录列表
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ExperimentCatalog::with(['curriculumStandard', 'organization', 'creator']);

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
            $catalogs = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $catalogs,
                'message' => '获取实验目录列表成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取实验目录列表失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取实验目录详情
     */
    public function show($id): JsonResponse
    {
        try {
            $catalog = ExperimentCatalog::with([
                'curriculumStandard',
                'organization',
                'creator',
                'updater',
                'versions' => function ($query) {
                    $query->latest()->limit(5);
                }
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $catalog,
                'message' => '获取实验目录详情成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取实验目录详情失败：' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * 创建实验目录
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:200',
                'code' => 'required|string|max:50|unique:experiment_catalogs,code',
                'subject' => 'required|in:physics,chemistry,biology,science',
                'grade' => 'required|in:grade1,grade2,grade3,grade4,grade5,grade6,grade7,grade8,grade9',
                'textbook_version' => 'required|string|max:100',
                'experiment_type' => 'required|in:demonstration,group,individual,inquiry',
                'description' => 'nullable|string',
                'objectives' => 'nullable|string',
                'materials' => 'nullable|string',
                'procedures' => 'nullable|string',
                'safety_notes' => 'nullable|string',
                'duration_minutes' => 'nullable|integer|min:1|max:300',
                'student_count' => 'nullable|integer|min:1|max:100',
                'difficulty_level' => 'required|in:easy,medium,hard',
                'curriculum_standard_id' => 'nullable|exists:curriculum_standards,id',
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
            $data['created_by'] = Auth::id();
            $data['status'] = $request->get('status', 'draft');

            $catalog = ExperimentCatalog::create($data);

            // 创建版本记录
            CatalogVersion::createVersion(
                $catalog->id,
                'create',
                ['action' => '创建实验目录'],
                $request->get('change_reason', '新建实验目录'),
                Auth::id()
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $catalog->load(['curriculumStandard', 'organization', 'creator']),
                'message' => '创建实验目录成功'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '创建实验目录失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新实验目录
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $catalog = ExperimentCatalog::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:200',
                'code' => 'required|string|max:50|unique:experiment_catalogs,code,' . $id,
                'subject' => 'required|in:physics,chemistry,biology,science',
                'grade' => 'required|in:grade1,grade2,grade3,grade4,grade5,grade6,grade7,grade8,grade9',
                'textbook_version' => 'required|string|max:100',
                'experiment_type' => 'required|in:demonstration,group,individual,inquiry',
                'description' => 'nullable|string',
                'objectives' => 'nullable|string',
                'materials' => 'nullable|string',
                'procedures' => 'nullable|string',
                'safety_notes' => 'nullable|string',
                'duration_minutes' => 'nullable|integer|min:1|max:300',
                'student_count' => 'nullable|integer|min:1|max:100',
                'difficulty_level' => 'required|in:easy,medium,hard',
                'status' => 'required|in:active,inactive,draft',
                'curriculum_standard_id' => 'nullable|exists:curriculum_standards,id',
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

            // 记录变更
            $oldData = $catalog->toArray();
            $newData = $validator->validated();
            $changes = $this->getChanges($oldData, $newData);

            $newData['updated_by'] = Auth::id();
            $catalog->update($newData);

            // 创建版本记录
            if (!empty($changes)) {
                CatalogVersion::createVersion(
                    $catalog->id,
                    'update',
                    $changes,
                    $request->get('change_reason', '更新实验目录'),
                    Auth::id()
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $catalog->load(['curriculumStandard', 'organization', 'creator', 'updater']),
                'message' => '更新实验目录成功'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '更新实验目录失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除实验目录
     */
    public function destroy($id): JsonResponse
    {
        try {
            $catalog = ExperimentCatalog::findOrFail($id);

            DB::beginTransaction();

            // 创建删除版本记录
            CatalogVersion::createVersion(
                $catalog->id,
                'delete',
                ['action' => '删除实验目录'],
                '删除实验目录',
                Auth::id()
            );

            $catalog->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '删除实验目录成功'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '删除实验目录失败：' . $e->getMessage()
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
                'subjects' => ExperimentCatalog::getSubjectOptions(),
                'grades' => ExperimentCatalog::getGradeOptions(),
                'experiment_types' => ExperimentCatalog::getExperimentTypeOptions(),
                'difficulty_levels' => ExperimentCatalog::getDifficultyLevelOptions(),
                'statuses' => ExperimentCatalog::getStatusOptions(),
            ]
        ]);
    }

    /**
     * 比较数据变更
     */
    private function getChanges(array $oldData, array $newData): array
    {
        $changes = [];
        $trackFields = ['name', 'subject', 'grade', 'textbook_version', 'experiment_type', 'status', 'difficulty_level'];

        foreach ($trackFields as $field) {
            if (isset($oldData[$field]) && isset($newData[$field]) && $oldData[$field] !== $newData[$field]) {
                $changes[$field] = [
                    'old' => $oldData[$field],
                    'new' => $newData[$field]
                ];
            }
        }

        return $changes;
    }
}
