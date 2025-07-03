<?php

namespace App\Http\Controllers;

use App\Models\MaterialCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaterialCategoryController extends Controller
{
    /**
     * 获取材料分类列表
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = MaterialCategory::with(['parent', 'organization', 'creator']);

            // 权限过滤：系统管理员可查看所有数据，其他用户只能查看本组织数据
            $user = Auth::user();
            if ($user->user_type !== 'admin') {
                $query->where('organization_id', $user->organization_id);
            }

            // 筛选条件
            if ($request->filled('subject')) {
                $query->where('subject', $request->subject);
            }

            if ($request->filled('material_type')) {
                $query->where('material_type', $request->material_type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('parent_id')) {
                $query->where('parent_id', $request->parent_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // 排序
            $query->orderBy('sort_order')->orderBy('created_at', 'desc');

            // 分页
            $perPage = $request->get('per_page', 15);
            $categories = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $categories,
                'message' => '获取材料分类列表成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取材料分类列表失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取材料分类树形结构
     */
    public function tree(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $query = MaterialCategory::active();
            
            // 权限过滤
            if ($user->user_type !== 'admin') {
                $query->where('organization_id', $user->organization_id);
            }

            if ($request->filled('subject')) {
                $query->where('subject', $request->subject);
            }

            if ($request->filled('material_type')) {
                $query->where('material_type', $request->material_type);
            }

            $categories = $query->orderBy('sort_order')->get();

            // 构建树形结构
            $tree = $this->buildTree($categories);

            return response()->json([
                'success' => true,
                'data' => $tree,
                'message' => '获取材料分类树成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取材料分类树失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 创建材料分类
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:material_categories',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:material_categories,id',
            'subject' => 'nullable|string|max:50',
            'grade_range' => 'nullable|string|max:100',
            'material_type' => 'required|in:consumable,reusable,chemical,biological',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            
            $category = MaterialCategory::create([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'parent_id' => $request->parent_id,
                'subject' => $request->subject,
                'grade_range' => $request->grade_range,
                'material_type' => $request->material_type,
                'sort_order' => $request->sort_order ?? 0,
                'status' => 'active',
                'organization_id' => $user->organization_id,
                'created_by' => $user->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $category->load(['parent', 'organization', 'creator']),
                'message' => '创建材料分类成功'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '创建材料分类失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取材料分类详情
     */
    public function show($id): JsonResponse
    {
        try {
            $category = MaterialCategory::with(['parent', 'children', 'organization', 'creator', 'updater'])
                ->findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $category->organization_id !== $user->organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权访问该材料分类'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $category,
                'message' => '获取材料分类详情成功'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取材料分类详情失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新材料分类
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:material_categories,code,' . $id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:material_categories,id',
            'subject' => 'nullable|string|max:50',
            'grade_range' => 'nullable|string|max:100',
            'material_type' => 'required|in:consumable,reusable,chemical,biological',
            'sort_order' => 'nullable|integer',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            DB::beginTransaction();

            $category = MaterialCategory::findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $category->organization_id !== $user->organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权修改该材料分类'
                ], 403);
            }

            // 检查是否设置自己为父分类
            if ($request->parent_id == $id) {
                return response()->json([
                    'success' => false,
                    'message' => '不能将自己设置为父分类'
                ], 400);
            }

            $category->update([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'parent_id' => $request->parent_id,
                'subject' => $request->subject,
                'grade_range' => $request->grade_range,
                'material_type' => $request->material_type,
                'sort_order' => $request->sort_order ?? $category->sort_order,
                'status' => $request->status,
                'updated_by' => $user->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $category->load(['parent', 'organization', 'creator', 'updater']),
                'message' => '更新材料分类成功'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '更新材料分类失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除材料分类
     */
    public function destroy($id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $category = MaterialCategory::findOrFail($id);

            // 权限检查
            $user = Auth::user();
            if ($user->user_type !== 'admin' && $category->organization_id !== $user->organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => '无权删除该材料分类'
                ], 403);
            }

            // 检查是否有子分类
            if ($category->children()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => '该分类下还有子分类，无法删除'
                ], 400);
            }

            // 检查是否有关联的材料
            if ($category->materials()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => '该分类下还有材料，无法删除'
                ], 400);
            }

            $category->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '删除材料分类成功'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '删除材料分类失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 构建树形结构
     */
    private function buildTree($categories, $parentId = null)
    {
        $tree = [];
        
        foreach ($categories as $category) {
            if ($category->parent_id == $parentId) {
                $children = $this->buildTree($categories, $category->id);
                $categoryArray = $category->toArray();
                if (!empty($children)) {
                    $categoryArray['children'] = $children;
                }
                $tree[] = $categoryArray;
            }
        }
        
        return $tree;
    }
}
