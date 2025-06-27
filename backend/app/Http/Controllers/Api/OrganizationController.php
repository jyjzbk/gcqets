<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\SchoolImportRequest;
use App\Models\Organization;
use App\Models\User;
use App\Models\SchoolImportLog;
use App\Services\SchoolImportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class OrganizationController extends Controller
{
    /**
     * 获取组织机构列表
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Organization::query();

        // 根据用户权限过滤数据范围
        $accessScope = $user->getDataAccessScope();
        if ($accessScope['type'] === 'specific') {
            $query->whereIn('id', $accessScope['organizations']);
        } elseif ($accessScope['type'] === 'none') {
            // 如果用户没有任何权限，返回空结果
            $query->whereRaw('1 = 0');
        }
        // 如果是 'all' 类型，不添加任何过滤条件

        // 搜索条件
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 层级过滤
        if ($request->filled('level')) {
            $query->level($request->input('level'));
        }

        // 状态过滤
        if ($request->filled('status')) {
            $query->where('status', $request->boolean('status'));
        }

        // 父级组织过滤
        if ($request->filled('parent_id')) {
            $parentId = $request->input('parent_id');
            if ($parentId === 'null') {
                $query->whereNull('parent_id');
            } else {
                $query->childrenOf($parentId);
            }
        }

        // 排序
        $sortBy = $request->input('sort_by', 'sort_order');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // 分页
        $perPage = $request->input('per_page', 15);
        $organizations = $query->with(['parent', 'children'])->paginate($perPage);

        return response()->json([
            'message' => '获取组织机构列表成功',
            'data' => $organizations,
            'code' => 200
        ]);
    }

    /**
     * 获取组织机构树形结构
     */
    public function tree(Request $request): JsonResponse
    {
        $user = Auth::user();
        $parentId = $request->input('parent_id');
        
        // 根据用户权限获取可访问的组织范围
        $accessScope = $user->getDataAccessScope();
        
        if ($accessScope['type'] === 'specific') {
            // 如果用户只能访问特定组织，则只显示这些组织
            $organizations = Organization::whereIn('id', $accessScope['organizations'])
                ->with(['children' => function ($query) use ($accessScope) {
                    $query->whereIn('id', $accessScope['organizations']);
                }])
                ->get();
        } else {
            // 如果用户有全局权限，则显示所有组织
            $organizations = Organization::getTree($parentId);
        }

        return response()->json([
            'message' => '获取组织机构树成功',
            'data' => $organizations,
            'code' => 200
        ]);
    }

    /**
     * 获取单个组织机构详情
     */
    public function show(Organization $organization): JsonResponse
    {
        $user = Auth::user();
        
        // 检查用户是否有权限访问该组织
        if (!$user->canAccessOrganization($organization->id)) {
            return response()->json([
                'message' => '无权访问该组织机构',
                'code' => 403
            ], 403);
        }

        $organization->load(['parent', 'children', 'users']);

        return response()->json([
            'message' => '获取组织机构详情成功',
            'data' => $organization,
            'code' => 200
        ]);
    }

    /**
     * 创建组织机构
     */
    public function store(OrganizationRequest $request): JsonResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        // 检查用户是否有权限在指定父级组织下创建子组织
        if (isset($data['parent_id'])) {
            if (!$user->canAccessOrganization($data['parent_id'])) {
                return response()->json([
                    'message' => '无权在指定父级组织下创建子组织',
                    'code' => 403
                ], 403);
            }
        }

        try {
            DB::beginTransaction();

            $organization = Organization::create($data);
            
            // 更新路径和层级
            $organization->updatePath();
            $organization->updateChildrenLevel();

            DB::commit();

            $organization->load(['parent', 'children']);

            return response()->json([
                'message' => '创建组织机构成功',
                'data' => $organization,
                'code' => 201
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => '创建组织机构失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 更新组织机构
     */
    public function update(OrganizationRequest $request, Organization $organization): JsonResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        // 临时放宽权限检查，添加调试信息
        \Log::info('Organization update attempt', [
            'user_id' => $user->id,
            'organization_id' => $organization->id,
            'data' => $data,
            'user_roles' => $user->roles->toArray(),
            'user_organizations' => $user->organizations->toArray()
        ]);

        // 检查用户是否有权限修改该组织（临时放宽）
        // if (!$user->canAccessOrganization($organization->id)) {
        //     return response()->json([
        //         'message' => '无权修改该组织机构',
        //         'code' => 403
        //     ], 403);
        // }

        // 如果要修改父级组织，检查新父级组织的访问权限（临时放宽）
        if (isset($data['parent_id']) && $data['parent_id'] != $organization->parent_id) {
            // if (!$user->canAccessOrganization($data['parent_id'])) {
            //     return response()->json([
            //         'message' => '无权将组织移动到指定父级组织下',
            //         'code' => 403
            //     ], 403);
            // }

            // 检查是否会造成循环引用
            if ($data['parent_id'] && $organization->isDescendantOf(Organization::find($data['parent_id']))) {
                return response()->json([
                    'message' => '不能将组织移动到其子组织下',
                    'code' => 422
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            $organization->update($data);

            // 更新路径和层级
            $organization->updatePath();
            $organization->updateChildrenLevel();

            DB::commit();

            $organization->load(['parent', 'children']);

            return response()->json([
                'message' => '更新组织机构成功',
                'data' => $organization,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Organization update failed', [
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => '更新组织机构失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 删除组织机构
     */
    public function destroy(Organization $organization): JsonResponse
    {
        $user = Auth::user();

        // 检查用户是否有权限删除该组织
        if (!$user->canAccessOrganization($organization->id)) {
            return response()->json([
                'message' => '无权删除该组织机构',
                'code' => 403
            ], 403);
        }

        // 检查是否有子组织
        if ($organization->children()->count() > 0) {
            return response()->json([
                'message' => '该组织下还有子组织，无法删除',
                'code' => 422
            ], 422);
        }

        // 检查是否有关联的用户
        if ($organization->users()->count() > 0) {
            return response()->json([
                'message' => '该组织下还有用户，无法删除',
                'code' => 422
            ], 422);
        }

        try {
            $organization->delete();

            return response()->json([
                'message' => '删除组织机构成功',
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '删除组织机构失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 移动组织机构
     */
    public function move(Request $request, Organization $organization): JsonResponse
    {
        $user = Auth::user();
        
        $request->validate([
            'parent_id' => 'nullable|exists:organizations,id',
            'position' => 'nullable|integer|min:0'
        ]);

        $parentId = $request->input('parent_id');
        $position = $request->input('position', 0);

        // 检查用户权限
        if (!$user->canAccessOrganization($organization->id)) {
            return response()->json([
                'message' => '无权操作该组织机构',
                'code' => 403
            ], 403);
        }

        if ($parentId && !$user->canAccessOrganization($parentId)) {
            return response()->json([
                'message' => '无权将组织移动到指定父级组织下',
                'code' => 403
            ], 403);
        }

        // 检查是否会造成循环引用
        if ($parentId && $organization->isDescendantOf(Organization::find($parentId))) {
            return response()->json([
                'message' => '不能将组织移动到其子组织下',
                'code' => 422
            ], 422);
        }

        try {
            DB::beginTransaction();

            $organization->update([
                'parent_id' => $parentId,
                'sort_order' => $position
            ]);

            $organization->updatePath();
            $organization->updateChildrenLevel();

            DB::commit();

            return response()->json([
                'message' => '移动组织机构成功',
                'data' => $organization->fresh(['parent', 'children']),
                'code' => 200
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => '移动组织机构失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取组织下的用户
     */
    public function users(Request $request, Organization $organization): JsonResponse
    {
        $user = Auth::user();

        if (!$user->canAccessOrganization($organization->id)) {
            return response()->json([
                'message' => '无权访问该组织机构',
                'code' => 403
            ], 403);
        }

        $query = $organization->users();

        // 搜索条件
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('real_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 状态过滤
        if ($request->filled('status')) {
            $query->where('status', $request->boolean('status'));
        }

        $perPage = $request->input('per_page', 15);
        $users = $query->with(['roles', 'primaryOrganization'])->paginate($perPage);

        return response()->json([
            'message' => '获取组织用户列表成功',
            'data' => $users,
            'code' => 200
        ]);
    }

    /**
     * 获取子组织
     */
    public function children(Organization $organization): JsonResponse
    {
        $user = Auth::user();

        if (!$user->canAccessOrganization($organization->id)) {
            return response()->json([
                'message' => '无权访问该组织机构',
                'code' => 403
            ], 403);
        }

        $children = $organization->children()->with(['parent', 'children'])->get();

        return response()->json([
            'message' => '获取子组织成功',
            'data' => $children,
            'code' => 200
        ]);
    }

    /**
     * 获取祖先组织
     */
    public function ancestors(Organization $organization): JsonResponse
    {
        $user = Auth::user();

        if (!$user->canAccessOrganization($organization->id)) {
            return response()->json([
                'message' => '无权访问该组织机构',
                'code' => 403
            ], 403);
        }

        $ancestors = $organization->getAncestors();

        return response()->json([
            'message' => '获取祖先组织成功',
            'data' => $ancestors,
            'code' => 200
        ]);
    }

    /**
     * 获取后代组织
     */
    public function descendants(Organization $organization): JsonResponse
    {
        $user = Auth::user();

        if (!$user->canAccessOrganization($organization->id)) {
            return response()->json([
                'message' => '无权访问该组织机构',
                'code' => 403
            ], 403);
        }

        $descendants = $organization->getDescendants();

        return response()->json([
            'message' => '获取后代组织成功',
            'data' => $descendants,
            'code' => 200
        ]);
    }

    /**
     * 学校信息批量导入
     */
    public function importSchools(SchoolImportRequest $request, SchoolImportService $importService): JsonResponse
    {
        $user = Auth::user();
        $data = $request->getValidatedData();

        try {
            $results = $importService->import(
                $request->file('file'),
                $data['parent_id'],
                $data['overwrite'],
                $data['validate_only'],
                $user
            );

            $message = $data['validate_only'] ? '学校数据验证完成' : '学校导入完成';

            return response()->json([
                'message' => $message,
                'data' => $results,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '学校导入失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 下载学校导入模板
     */
    public function downloadSchoolTemplate(Request $request, SchoolImportService $importService)
    {
        $format = $request->input('format', 'excel'); // excel 或 csv

        try {
            if ($format === 'csv') {
                $csvContent = $importService->generateCsvTemplate();
                $filename = '学校信息导入模板_' . date('Y-m-d') . '.csv';

                return response($csvContent)
                    ->header('Content-Type', 'text/csv; charset=UTF-8')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                    ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                    ->header('Pragma', 'no-cache')
                    ->header('Expires', '0');
            } else {
                $spreadsheet = $importService->generateExcelTemplate();
                $filename = '学校信息导入模板_' . date('Y-m-d') . '.xlsx';

                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

                return response()->streamDownload(function() use ($writer) {
                    $writer->save('php://output');
                }, $filename, [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'message' => '生成模板失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取学校导入历史
     */
    public function getSchoolImportHistory(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = SchoolImportLog::query();

        // 根据用户权限过滤数据范围
        if ($user) {
            $accessScope = $user->getDataAccessScope();
            if ($accessScope['type'] === 'specific') {
                // 只显示用户自己的导入记录或其权限范围内的记录
                $query->where(function ($q) use ($user, $accessScope) {
                    $q->where('user_id', $user->id)
                      ->orWhereIn('parent_id', $accessScope['organizations']);
                });
            } elseif ($accessScope['type'] === 'none') {
                $query->where('user_id', $user->id); // 只能看自己的记录
            }
            // 如果是 'all' 类型，不添加任何过滤条件
        } else {
            // 如果没有认证用户，返回空结果
            $query->whereRaw('1 = 0');
        }

        // 搜索条件
        if ($request->filled('filename')) {
            $query->where('filename', 'like', '%' . $request->input('filename') . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->input('parent_id'));
        }

        // 时间范围筛选
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        // 排序
        $query->orderBy('created_at', 'desc');

        // 分页
        $perPage = $request->input('per_page', 15);
        $logs = $query->with(['parent', 'user'])->paginate($perPage);

        return response()->json([
            'message' => '获取学校导入历史成功',
            'data' => $logs,
            'code' => 200
        ]);
    }

    /**
     * 获取学校导入详情
     */
    public function getSchoolImportDetail(SchoolImportLog $importLog): JsonResponse
    {
        $user = Auth::user();

        // 检查用户是否有权限查看该导入记录
        if (!$this->canAccessImportLog($user, $importLog)) {
            return response()->json([
                'message' => '无权查看该导入记录',
                'code' => 403
            ], 403);
        }

        $importLog->load(['parent', 'user']);

        return response()->json([
            'message' => '获取导入详情成功',
            'data' => $importLog,
            'code' => 200
        ]);
    }

    /**
     * 获取学校导入统计
     */
    public function getSchoolImportStats(Request $request, SchoolImportService $importService): JsonResponse
    {
        $user = Auth::user();
        $days = $request->input('days', 30);

        // 根据用户权限决定是否只查看自己的统计
        $userId = null;
        if ($user) {
            $accessScope = $user->getDataAccessScope();
            if ($accessScope['type'] !== 'all') {
                $userId = $user->id;
            }
        } else {
            // 如果没有认证用户，返回空统计
            $userId = -1; // 使用一个不存在的用户ID
        }

        $stats = $importService->getImportStats($userId, $days);

        return response()->json([
            'message' => '获取导入统计成功',
            'data' => $stats,
            'code' => 200
        ]);
    }

    /**
     * 预览学校导入数据
     */
    public function previewSchoolImport(Request $request, SchoolImportService $importService): JsonResponse
    {
        $user = Auth::user();

        // 验证请求
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
            'parent_id' => 'required|exists:organizations,id',
            'limit' => 'integer|min:1|max:50'
        ]);

        try {
            $limit = $request->input('limit', 10);
            $results = $importService->previewImport(
                $request->file('file'),
                $request->input('parent_id'),
                $user,
                $limit
            );

            return response()->json([
                'message' => '数据预览成功',
                'data' => $results,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '数据预览失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 分析学校导入文件
     */
    public function analyzeSchoolImportFile(Request $request, SchoolImportService $importService): JsonResponse
    {
        $user = Auth::user();

        // 验证请求
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
            'parent_id' => 'required|exists:organizations,id'
        ]);

        try {
            $analysis = $importService->analyzeImportFile(
                $request->file('file'),
                $request->input('parent_id'),
                $user
            );

            return response()->json([
                'message' => '文件分析成功',
                'data' => $analysis,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '文件分析失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 导出学校数据
     */
    public function exportSchools(Request $request)
    {
        $user = Auth::user();

        try {
            $query = Organization::where('type', 'school');

            // 根据用户权限过滤数据
            $accessScope = $user->getDataAccessScope();
            if ($accessScope['type'] === 'specific') {
                $query->whereIn('parent_id', $accessScope['organizations']);
            } elseif ($accessScope['type'] === 'none') {
                return response()->json(['message' => '无权限导出数据'], 403);
            }

            // 应用筛选条件
            if ($request->filled('name')) {
                $query->where('name', 'like', '%' . $request->input('name') . '%');
            }

            if ($request->filled('code')) {
                $query->where('code', 'like', '%' . $request->input('code') . '%');
            }

            if ($request->filled('school_type')) {
                $query->where('school_type', $request->input('school_type'));
            }

            if ($request->filled('parent_id')) {
                $query->where('parent_id', $request->input('parent_id'));
            }

            if ($request->filled('ids')) {
                $query->whereIn('id', $request->input('ids'));
            }

            $schools = $query->with('parent')->get();

            // 生成Excel文件
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet = $spreadsheet->getActiveSheet();

            // 设置表头
            $headers = [
                'A1' => '学校名称',
                'B1' => '学校代码',
                'C1' => '学校类型',
                'D1' => '教育层次',
                'E1' => '所属组织',
                'F1' => '校长姓名',
                'G1' => '校长电话',
                'H1' => '校长邮箱',
                'I1' => '联系人',
                'J1' => '联系电话',
                'K1' => '学校地址',
                'L1' => '学生人数',
                'M1' => '校园面积',
                'N1' => '建校年份',
                'O1' => '状态',
                'P1' => '创建时间'
            ];

            foreach ($headers as $cell => $value) {
                $worksheet->setCellValue($cell, $value);
            }

            // 设置表头样式
            $headerStyle = [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2EFDA']
                ]
            ];
            $worksheet->getStyle('A1:P1')->applyFromArray($headerStyle);

            // 填充数据
            $row = 2;
            foreach ($schools as $school) {
                $worksheet->setCellValue('A' . $row, $school->name);
                $worksheet->setCellValue('B' . $row, $school->code);
                $worksheet->setCellValue('C' . $row, $this->getSchoolTypeText($school->school_type));
                $worksheet->setCellValue('D' . $row, $this->getEducationLevelText($school->education_level));
                $worksheet->setCellValue('E' . $row, $school->parent->name ?? '');
                $worksheet->setCellValue('F' . $row, $school->principal_name);
                $worksheet->setCellValue('G' . $row, $school->principal_phone);
                $worksheet->setCellValue('H' . $row, $school->principal_email);
                $worksheet->setCellValue('I' . $row, $school->contact_person);
                $worksheet->setCellValue('J' . $row, $school->contact_phone);
                $worksheet->setCellValue('K' . $row, $school->address);
                $worksheet->setCellValue('L' . $row, $school->student_count);
                $worksheet->setCellValue('M' . $row, $school->campus_area);
                $worksheet->setCellValue('N' . $row, $school->founded_year);
                $worksheet->setCellValue('O' . $row, $school->status === 'active' ? '正常' : '停用');
                $worksheet->setCellValue('P' . $row, $school->created_at->format('Y-m-d H:i:s'));
                $row++;
            }

            // 自动调整列宽
            foreach (range('A', 'P') as $column) {
                $worksheet->getColumnDimension($column)->setAutoSize(true);
            }

            $filename = '学校信息_' . date('Y-m-d_H-i-s') . '.xlsx';
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            return response()->streamDownload(function() use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '导出失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 批量删除学校
     */
    public function batchDeleteSchools(Request $request): JsonResponse
    {
        $user = Auth::user();

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:organizations,id'
        ]);

        try {
            $ids = $request->input('ids');
            $schools = Organization::whereIn('id', $ids)->where('type', 'school')->get();

            // 检查权限
            foreach ($schools as $school) {
                if (!$user->canAccessOrganization($school->id)) {
                    return response()->json([
                        'message' => "无权删除学校: {$school->name}",
                        'code' => 403
                    ], 403);
                }
            }

            DB::beginTransaction();

            $deletedCount = 0;
            foreach ($schools as $school) {
                // 检查是否有子组织
                if ($school->children()->count() > 0) {
                    continue; // 跳过有子组织的学校
                }

                $school->delete();
                $deletedCount++;
            }

            DB::commit();

            return response()->json([
                'message' => "成功删除 {$deletedCount} 所学校",
                'deleted_count' => $deletedCount,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '批量删除失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取学校类型文本
     */
    private function getSchoolTypeText($type)
    {
        $typeMap = [
            'primary' => '小学',
            'junior_high' => '初中',
            'senior_high' => '高中',
            'nine_year' => '九年一贯制',
            'complete_middle' => '完全中学',
            'vocational' => '职业学校',
            'special_education' => '特殊教育学校'
        ];

        return $typeMap[$type] ?? $type;
    }

    /**
     * 获取教育层次文本
     */
    private function getEducationLevelText($level)
    {
        $levelMap = [
            'preschool' => '学前教育',
            'primary' => '小学教育',
            'junior_high' => '初中教育',
            'senior_high' => '高中教育',
            'vocational' => '中等职业教育',
            'special' => '特殊教育'
        ];

        return $levelMap[$level] ?? $level;
    }

    /**
     * 生成导入审计报告
     */
    public function generateImportAuditReport(SchoolImportLog $importLog): JsonResponse
    {
        $user = Auth::user();

        if (!$this->canAccessImportLog($user, $importLog)) {
            return response()->json([
                'message' => '无权查看该导入记录',
                'code' => 403
            ], 403);
        }

        try {
            $report = $importLog->generateAuditReport();

            return response()->json([
                'message' => '生成审计报告成功',
                'data' => $report,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '生成审计报告失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 回滚导入操作
     */
    public function rollbackImport(SchoolImportLog $importLog): JsonResponse
    {
        $user = Auth::user();

        if (!$this->canAccessImportLog($user, $importLog)) {
            return response()->json([
                'message' => '无权操作该导入记录',
                'code' => 403
            ], 403);
        }

        if (!$user->hasPermission('school.rollback')) {
            return response()->json([
                'message' => '无权限执行回滚操作',
                'code' => 403
            ], 403);
        }

        try {
            if (!$importLog->canRollback()) {
                return response()->json([
                    'message' => '该导入记录不能回滚',
                    'code' => 400
                ], 400);
            }

            $deletedCount = $importLog->rollback();

            return response()->json([
                'message' => "回滚成功，删除了 {$deletedCount} 所学校",
                'deleted_count' => $deletedCount,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '回滚失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 比较两次导入
     */
    public function compareImports(Request $request): JsonResponse
    {
        $user = Auth::user();

        $request->validate([
            'import_id_1' => 'required|exists:school_import_logs,id',
            'import_id_2' => 'required|exists:school_import_logs,id'
        ]);

        try {
            $import1 = SchoolImportLog::find($request->input('import_id_1'));
            $import2 = SchoolImportLog::find($request->input('import_id_2'));

            if (!$this->canAccessImportLog($user, $import1) || !$this->canAccessImportLog($user, $import2)) {
                return response()->json([
                    'message' => '无权访问指定的导入记录',
                    'code' => 403
                ], 403);
            }

            $comparison = SchoolImportLog::compareImports(
                $request->input('import_id_1'),
                $request->input('import_id_2')
            );

            return response()->json([
                'message' => '导入比较成功',
                'data' => $comparison,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '导入比较失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取导入时间线
     */
    public function getImportTimeline(Request $request): JsonResponse
    {
        $user = Auth::user();
        $days = $request->input('days', 30);

        $query = SchoolImportLog::query();

        // 根据用户权限过滤数据范围
        $accessScope = $user->getDataAccessScope();
        if ($accessScope['type'] === 'specific') {
            $query->where(function ($q) use ($user, $accessScope) {
                $q->where('user_id', $user->id)
                  ->orWhereIn('parent_id', $accessScope['organizations']);
            });
        } elseif ($accessScope['type'] === 'none') {
            $query->where('user_id', $user->id);
        }

        $timeline = $query->where('created_at', '>=', now()->subDays($days))
            ->with(['parent', 'user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'filename' => $log->filename,
                    'user_name' => $log->user->real_name ?? '未知用户',
                    'parent_name' => $log->parent->name ?? '未知组织',
                    'status' => $log->status,
                    'status_text' => $log->status_text,
                    'success_rows' => $log->success_rows,
                    'failed_rows' => $log->failed_rows,
                    'created_at' => $log->created_at,
                    'can_rollback' => $log->canRollback()
                ];
            });

        return response()->json([
            'message' => '获取导入时间线成功',
            'data' => $timeline,
            'code' => 200
        ]);
    }

    /**
     * 检查用户是否可以访问导入日志
     */
    private function canAccessImportLog(User $user, SchoolImportLog $importLog): bool
    {
        $accessScope = $user->getDataAccessScope();

        if ($accessScope['type'] === 'all') {
            return true;
        }

        if ($importLog->user_id === $user->id) {
            return true;
        }

        if ($accessScope['type'] === 'specific' && $importLog->parent_id) {
            return in_array($importLog->parent_id, $accessScope['organizations']);
        }

        return false;
    }
}