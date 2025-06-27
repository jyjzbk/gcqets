<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\OrganizationImportRequest;
use App\Models\Organization;
use App\Models\User;
use App\Services\OrganizationImportService;
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

        // 类型过滤
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // 层级过滤
        if ($request->filled('level')) {
            $query->level($request->input('level'));
        }

        // 状态过滤
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
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
        
        // 获取完整的组织树结构
        $organizations = Organization::getTree($parentId);

        // 根据用户权限过滤可访问的组织范围
        $accessScope = $user->getDataAccessScope();
        
        if ($accessScope['type'] === 'specific') {
            // 只保留用户有权限访问的组织及其祖先
            $filteredOrganizations = $this->filterTreeByAccess($organizations, $accessScope['organizations']);
            $organizations = $filteredOrganizations;
        }

        return response()->json([
            'message' => '获取组织机构树成功',
            'data' => $organizations,
            'code' => 200
        ]);
    }

    /**
     * 根据权限过滤组织树
     */
    private function filterTreeByAccess($organizations, $allowedIds)
    {
        $filtered = [];
        
        foreach ($organizations as $org) {
            // 如果组织在允许列表中，保留它及其所有祖先
            if (in_array($org->id, $allowedIds)) {
                $filtered[] = $org;
            } else {
                // 检查是否有子组织在允许列表中
                $filteredChildren = $this->filterTreeByAccess($org->children_tree, $allowedIds);
                if (count($filteredChildren) > 0) {
                    $org->children_tree = $filteredChildren;
                    $filtered[] = $org;
                }
            }
        }
        
        return $filtered;
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

        // 检查用户是否有权限修改该组织
        if (!$user->canAccessOrganization($organization->id)) {
            return response()->json([
                'message' => '无权修改该组织机构',
                'code' => 403
            ], 403);
        }

        // 如果要修改父级组织，检查新父级组织的访问权限
        if (isset($data['parent_id']) && $data['parent_id'] != $organization->parent_id) {
            if (!$user->canAccessOrganization($data['parent_id'])) {
                return response()->json([
                    'message' => '无权将组织移动到指定父级组织下',
                    'code' => 403
                ], 403);
            }

            // 检查是否会造成循环引用
            if ($organization->isDescendantOf(Organization::find($data['parent_id']))) {
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
     * 批量导入组织
     */
    public function import(OrganizationImportRequest $request, OrganizationImportService $importService): JsonResponse
    {
        $user = Auth::user();
        $file = $request->file('file');
        $parentId = $request->input('parent_id');
        $overwrite = $request->boolean('overwrite', false);
        $validateOnly = $request->boolean('validate_only', false);

        // 检查用户是否有权限在指定父级组织下创建子组织
        if ($parentId && !$user->canAccessOrganization($parentId)) {
            return response()->json([
                'message' => '无权在指定父级组织下创建子组织',
                'code' => 403
            ], 403);
        }

        try {
            $results = $importService->import($file, $parentId, $overwrite, $validateOnly, $user);

            $message = $validateOnly ? '数据验证完成' : '导入完成';

            return response()->json([
                'message' => $message,
                'data' => $results,
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '导入失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 下载导入模板
     */
    public function downloadTemplate(OrganizationImportService $importService)
    {
        try {
            $csvContent = $importService->generateCsvTemplate();
            $filename = '组织机构导入模板_' . date('Y-m-d') . '.csv';

            return response($csvContent)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            return response()->json([
                'message' => '生成模板失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取导入历史
     */
    public function importHistory(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = \App\Models\OrganizationImportLog::query();

        // 根据用户权限过滤数据范围
        $accessScope = $user->getDataAccessScope();
        if ($accessScope['type'] === 'specific') {
            $query->where('user_id', $user->id);
        } elseif ($accessScope['type'] === 'none') {
            $query->whereRaw('1 = 0');
        }

        // 搜索条件
        if ($request->filled('filename')) {
            $query->where('filename', 'like', '%' . $request->input('filename') . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // 排序
        $query->orderBy('created_at', 'desc');

        // 分页
        $perPage = $request->input('per_page', 15);
        $logs = $query->with(['user', 'parentOrganization'])->paginate($perPage);

        return response()->json([
            'message' => '获取导入历史成功',
            'data' => $logs,
            'code' => 200
        ]);
    }
}