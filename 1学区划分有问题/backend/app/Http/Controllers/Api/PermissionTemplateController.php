<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PermissionTemplate;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class PermissionTemplateController extends Controller
{
    /**
     * 获取权限模板列表
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = PermissionTemplate::with(['creator', 'updater']);

            // 筛选条件
            if ($request->has('template_type')) {
                $query->byType($request->input('template_type'));
            }

            if ($request->has('target_level')) {
                $query->byLevel($request->input('target_level'));
            }

            if ($request->has('is_system')) {
                $query->where('is_system', $request->boolean('is_system'));
            }

            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            } else {
                $query->active();
            }

            // 搜索
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('display_name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // 排序
            $sortBy = $request->input('sort_by', 'sort_order');
            $sortOrder = $request->input('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // 分页
            $perPage = $request->input('per_page', 15);
            $templates = $query->paginate($perPage);

            // 添加权限数量
            $templates->getCollection()->transform(function ($template) {
                $template->permission_count = $template->permission_count;
                return $template;
            });

            return response()->json([
                'message' => '获取权限模板列表成功',
                'data' => $templates,
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取权限模板列表失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取权限模板详情
     */
    public function show(int $id): JsonResponse
    {
        try {
            $template = PermissionTemplate::with(['creator', 'updater'])->find($id);
            
            if (!$template) {
                return response()->json([
                    'message' => '权限模板不存在',
                    'code' => 404
                ], 404);
            }

            // 获取权限详情
            $template->permission_objects = $template->permission_objects;

            return response()->json([
                'message' => '获取权限模板详情成功',
                'data' => $template,
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取权限模板详情失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 创建权限模板
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:permission_templates,name',
            'display_name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'template_type' => 'required|in:role,organization,user',
            'target_level' => 'nullable|integer|min:1|max:5',
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
            'conditions' => 'nullable|array',
            'is_default' => 'boolean',
            'status' => 'in:active,inactive',
            'sort_order' => 'integer'
        ]);

        try {
            $data = $request->all();
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();

            $template = PermissionTemplate::create($data);

            return response()->json([
                'message' => '创建权限模板成功',
                'data' => $template,
                'code' => 201
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '创建权限模板失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 更新权限模板
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $template = PermissionTemplate::find($id);
        
        if (!$template) {
            return response()->json([
                'message' => '权限模板不存在',
                'code' => 404
            ], 404);
        }

        // 检查是否为系统模板
        if ($template->is_system && !auth()->user()->hasRole('system_admin')) {
            return response()->json([
                'message' => '无权修改系统模板',
                'code' => 403
            ], 403);
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('permission_templates')->ignore($id)
            ],
            'display_name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'template_type' => 'required|in:role,organization,user',
            'target_level' => 'nullable|integer|min:1|max:5',
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
            'conditions' => 'nullable|array',
            'is_default' => 'boolean',
            'status' => 'in:active,inactive',
            'sort_order' => 'integer'
        ]);

        try {
            $data = $request->all();
            $data['updated_by'] = auth()->id();

            $template->update($data);

            return response()->json([
                'message' => '更新权限模板成功',
                'data' => $template,
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '更新权限模板失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 删除权限模板
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $template = PermissionTemplate::find($id);
            
            if (!$template) {
                return response()->json([
                    'message' => '权限模板不存在',
                    'code' => 404
                ], 404);
            }

            // 检查是否为系统模板
            if ($template->is_system) {
                return response()->json([
                    'message' => '系统模板不能删除',
                    'code' => 403
                ], 403);
            }

            $template->delete();

            return response()->json([
                'message' => '删除权限模板成功',
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '删除权限模板失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 应用权限模板到角色
     */
    public function applyToRole(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        try {
            $template = PermissionTemplate::find($id);
            $role = Role::find($request->input('role_id'));

            if (!$template || !$role) {
                return response()->json([
                    'message' => '模板或角色不存在',
                    'code' => 404
                ], 404);
            }

            $success = $template->applyToRole($role, auth()->id());

            if ($success) {
                return response()->json([
                    'message' => '应用权限模板到角色成功',
                    'code' => 200
                ]);
            } else {
                return response()->json([
                    'message' => '应用权限模板到角色失败',
                    'code' => 400
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => '应用权限模板失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 应用权限模板到用户
     */
    public function applyToUser(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'organization_id' => 'nullable|exists:organizations,id'
        ]);

        try {
            $template = PermissionTemplate::find($id);
            $user = User::find($request->input('user_id'));

            if (!$template || !$user) {
                return response()->json([
                    'message' => '模板或用户不存在',
                    'code' => 404
                ], 404);
            }

            $success = $template->applyToUser(
                $user, 
                $request->input('organization_id'), 
                auth()->id()
            );

            if ($success) {
                return response()->json([
                    'message' => '应用权限模板到用户成功',
                    'code' => 200
                ]);
            } else {
                return response()->json([
                    'message' => '应用权限模板到用户失败',
                    'code' => 400
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => '应用权限模板失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取推荐模板
     */
    public function getRecommended(Request $request): JsonResponse
    {
        $request->validate([
            'target_type' => 'required|in:role,organization,user',
            'target_level' => 'nullable|integer|min:1|max:5'
        ]);

        try {
            $templates = PermissionTemplate::getRecommendedTemplates(
                $request->input('target_type'),
                $request->input('target_level')
            );

            return response()->json([
                'message' => '获取推荐模板成功',
                'data' => $templates,
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取推荐模板失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 复制权限模板
     */
    public function duplicate(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:permission_templates,name',
            'display_name' => 'required|string|max:150'
        ]);

        try {
            $originalTemplate = PermissionTemplate::find($id);
            
            if (!$originalTemplate) {
                return response()->json([
                    'message' => '原始模板不存在',
                    'code' => 404
                ], 404);
            }

            $newTemplate = $originalTemplate->replicate();
            $newTemplate->name = $request->input('name');
            $newTemplate->display_name = $request->input('display_name');
            $newTemplate->is_system = false;
            $newTemplate->is_default = false;
            $newTemplate->created_by = auth()->id();
            $newTemplate->updated_by = auth()->id();
            $newTemplate->save();

            return response()->json([
                'message' => '复制权限模板成功',
                'data' => $newTemplate,
                'code' => 201
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '复制权限模板失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }
}
