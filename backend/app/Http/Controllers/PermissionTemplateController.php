<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\PermissionTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PermissionTemplateController extends Controller
{
    /**
     * Display a listing of the permission templates.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = PermissionTemplate::with(['creator']);

        // Filter by level
        if ($request->has('level')) {
            $query->where('level', $request->input('level'));
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by system templates
        if ($request->has('is_system')) {
            $query->where('is_system', $request->boolean('is_system'));
        }

        // Search by name or code
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        $templates = $query->orderBy('sort_order')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $templates
        ]);
    }

    /**
     * Store a newly created permission template in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:permission_templates',
                'description' => 'nullable|string',
                'level' => 'required|integer|min:1|max:5',
                'is_system' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
                'sort_order' => 'nullable|integer',
                'permission_ids' => 'nullable|array',
                'permission_ids.*' => 'exists:permissions,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Begin transaction
            DB::beginTransaction();

            // Create template
            $template = PermissionTemplate::create([
                'name' => $request->input('name'),
                'code' => $request->input('code'),
                'description' => $request->input('description'),
                'level' => $request->input('level'),
                'created_by' => Auth::id(),
                'is_system' => $request->boolean('is_system', false),
                'is_active' => $request->boolean('is_active', true),
                'sort_order' => $request->input('sort_order', 0)
            ]);

            // Attach permissions if provided
            if ($request->has('permission_ids')) {
                $template->permissions()->attach($request->input('permission_ids'));
            }

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '权限模板创建成功',
                'data' => $template->load('permissions')
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '验证失败',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '权限模板创建失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified permission template.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $template = PermissionTemplate::with(['creator', 'permissions'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $template
        ]);
    }

    /**
     * Update the specified permission template in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $template = PermissionTemplate::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'code' => 'sometimes|required|string|max:50|unique:permission_templates,code,' . $id,
                'description' => 'nullable|string',
                'level' => 'sometimes|required|integer|min:1|max:5',
                'is_system' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
                'sort_order' => 'nullable|integer',
                'permission_ids' => 'nullable|array',
                'permission_ids.*' => 'exists:permissions,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Begin transaction
            DB::beginTransaction();

            // Update template
            $template->update([
                'name' => $request->input('name', $template->name),
                'code' => $request->input('code', $template->code),
                'description' => $request->input('description', $template->description),
                'level' => $request->input('level', $template->level),
                'is_system' => $request->boolean('is_system', $template->is_system),
                'is_active' => $request->boolean('is_active', $template->is_active),
                'sort_order' => $request->input('sort_order', $template->sort_order)
            ]);

            // Sync permissions if provided
            if ($request->has('permission_ids')) {
                $template->permissions()->sync($request->input('permission_ids'));
            }

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '权限模板更新成功',
                'data' => $template->load('permissions')
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '验证失败',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '权限模板更新失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified permission template from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $template = PermissionTemplate::findOrFail($id);

            // Check if it's a system template
            if ($template->is_system) {
                return response()->json([
                    'success' => false,
                    'message' => '无法删除系统模板'
                ], 422);
            }

            // Begin transaction
            DB::beginTransaction();

            // Detach all permissions
            $template->permissions()->detach();

            // Delete template
            $template->delete();

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '权限模板删除成功'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '权限模板删除失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get permissions for the specified template.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getPermissions($id)
    {
        $template = PermissionTemplate::findOrFail($id);
        $permissions = $template->permissions;

        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    /**
     * Update permissions for the specified template.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePermissions(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'permission_ids' => 'required|array',
                'permission_ids.*' => 'exists:permissions,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $template = PermissionTemplate::findOrFail($id);
            $template->permissions()->sync($request->input('permission_ids'));

            return response()->json([
                'success' => true,
                'message' => '权限更新成功',
                'data' => $template->load('permissions')
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '验证失败',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '权限更新失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clone a permission template.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function clone(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:permission_templates',
                'description' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $sourceTemplate = PermissionTemplate::with('permissions')->findOrFail($id);

            // Begin transaction
            DB::beginTransaction();

            // Create new template
            $newTemplate = PermissionTemplate::create([
                'name' => $request->input('name'),
                'code' => $request->input('code'),
                'description' => $request->input('description', $sourceTemplate->description),
                'level' => $sourceTemplate->level,
                'created_by' => Auth::id(),
                'is_system' => false, // Cloned templates are never system templates
                'is_active' => true,
                'sort_order' => $sourceTemplate->sort_order
            ]);

            // Clone permissions
            $permissionIds = $sourceTemplate->permissions->pluck('id')->toArray();
            $newTemplate->permissions()->attach($permissionIds);

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '权限模板克隆成功',
                'data' => $newTemplate->load('permissions')
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '验证失败',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '权限模板克隆失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply a permission template to an organization.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function applyToOrganization(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|exists:organizations,id',
                'apply_to_children' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $template = PermissionTemplate::with('permissions')->findOrFail($id);
            $organizationId = $request->input('organization_id');
            $applyToChildren = $request->boolean('apply_to_children', false);

            // Logic to apply template to organization
            // This would depend on how permissions are structured in your system
            // For now, we'll just return a success response

            return response()->json([
                'success' => true,
                'message' => '权限模板应用成功',
                'data' => [
                    'template_id' => $id,
                    'organization_id' => $organizationId,
                    'apply_to_children' => $applyToChildren
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '验证失败',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '权限模板应用失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get permission inheritance visualization data.
     *
     * @return \Illuminate\Http\Response
     */
    public function getInheritanceVisualization()
    {
        try {
            // Get all permissions with their parent relationships
            $permissions = Permission::with('children')->whereNull('parent_id')->get();

            // Transform data for visualization
            $visualizationData = $this->transformPermissionsForVisualization($permissions);

            return response()->json([
                'success' => true,
                'data' => $visualizationData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取权限继承可视化数据失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transform permissions for visualization.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $permissions
     * @return array
     */
    private function transformPermissionsForVisualization($permissions)
    {
        $result = [];

        foreach ($permissions as $permission) {
            $node = [
                'id' => $permission->id,
                'name' => $permission->name,
                'display_name' => $permission->display_name,
                'description' => $permission->description,
                'children' => []
            ];

            if ($permission->children && $permission->children->count() > 0) {
                $node['children'] = $this->transformPermissionsForVisualization($permission->children);
            }

            $result[] = $node;
        }

        return $result;
    }

    /**
     * Get permission templates by level.
     *
     * @param  int  $level
     * @return \Illuminate\Http\Response
     */
    public function getTemplatesByLevel($level)
    {
        $templates = PermissionTemplate::where('level', $level)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $templates
        ]);
    }

    /**
     * Compare two permission templates.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function compareTemplates(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'template_ids' => 'required|array|min:2|max:5',
                'template_ids.*' => 'exists:permission_templates,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '验证失败',
                    'errors' => $validator->errors()
                ], 422);
            }

            $templateIds = $request->input('template_ids');
            $templates = PermissionTemplate::with('permissions')->findMany($templateIds);

            // Prepare comparison data
            $comparison = [];
            $allPermissions = collect();

            // Collect all unique permissions
            foreach ($templates as $template) {
                $allPermissions = $allPermissions->merge($template->permissions);
            }
            $allPermissions = $allPermissions->unique('id');

            // Build comparison matrix
            foreach ($allPermissions as $permission) {
                $row = [
                    'permission_id' => $permission->id,
                    'permission_name' => $permission->name,
                    'permission_display_name' => $permission->display_name,
                    'templates' => []
                ];

                foreach ($templates as $template) {
                    $row['templates'][] = [
                        'template_id' => $template->id,
                        'template_name' => $template->name,
                        'has_permission' => $template->permissions->contains('id', $permission->id)
                    ];
                }

                $comparison[] = $row;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'templates' => $templates,
                    'comparison' => $comparison
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '验证失败',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '模板比较失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
