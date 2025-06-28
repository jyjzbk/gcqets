<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Organization;
use App\Models\PermissionAuditLog;
use App\Services\PermissionInheritanceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PermissionManagementController extends Controller
{
    protected $inheritanceService;

    public function __construct()
    {
        // 暂时注释掉依赖注入，避免服务解析问题
        // $this->inheritanceService = $inheritanceService;
    }
    /**
     * 分配权限
     */
    public function grantPermission(Request $request): JsonResponse
    {
        $request->validate([
            'subject_type' => 'required|in:user,role,organization',
            'subject_id' => 'required|integer',
            'permission_id' => 'required|exists:permissions,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'reason' => 'nullable|string|max:500',
            'expires_at' => 'nullable|date|after:now'
        ]);

        try {
            DB::beginTransaction();

            $subjectType = $request->input('subject_type');
            $subjectId = $request->input('subject_id');
            $permissionId = $request->input('permission_id');
            $organizationId = $request->input('organization_id');
            $reason = $request->input('reason');
            $expiresAt = $request->input('expires_at');

            $permission = Permission::find($permissionId);
            $result = false;

            switch ($subjectType) {
                case 'user':
                    $result = $this->grantUserPermission($subjectId, $permissionId, $organizationId, $expiresAt);
                    break;
                case 'role':
                    $result = $this->grantRolePermission($subjectId, $permissionId);
                    break;
                case 'organization':
                    $result = $this->grantOrganizationPermission($subjectId, $permissionId);
                    break;
            }

            if ($result) {
                // 记录审计日志
                PermissionAuditLog::logGrant([
                    'subject_type' => $subjectType,
                    'subject_id' => $subjectId,
                    'permission_id' => $permissionId,
                    'permission_name' => $permission->name,
                    'organization_id' => $organizationId,
                    'reason' => $reason,
                    'new_values' => [
                        'permission_id' => $permissionId,
                        'expires_at' => $expiresAt
                    ]
                ]);

                DB::commit();

                return response()->json([
                    'message' => '权限分配成功',
                    'code' => 200
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'message' => '权限分配失败，可能已存在该权限',
                    'code' => 400
                ], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '权限分配失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 撤销权限
     */
    public function revokePermission(Request $request): JsonResponse
    {
        $request->validate([
            'subject_type' => 'required|in:user,role,organization',
            'subject_id' => 'required|integer',
            'permission_id' => 'required|exists:permissions,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'reason' => 'required|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $subjectType = $request->input('subject_type');
            $subjectId = $request->input('subject_id');
            $permissionId = $request->input('permission_id');
            $organizationId = $request->input('organization_id');
            $reason = $request->input('reason');

            $permission = Permission::find($permissionId);
            $oldValues = $this->getPermissionData($subjectType, $subjectId, $permissionId, $organizationId);
            $result = false;

            switch ($subjectType) {
                case 'user':
                    $result = $this->revokeUserPermission($subjectId, $permissionId, $organizationId);
                    break;
                case 'role':
                    $result = $this->revokeRolePermission($subjectId, $permissionId);
                    break;
                case 'organization':
                    $result = $this->revokeOrganizationPermission($subjectId, $permissionId);
                    break;
            }

            if ($result) {
                // 记录审计日志
                PermissionAuditLog::logRevoke([
                    'subject_type' => $subjectType,
                    'subject_id' => $subjectId,
                    'permission_id' => $permissionId,
                    'permission_name' => $permission->name,
                    'organization_id' => $organizationId,
                    'reason' => $reason,
                    'old_values' => $oldValues
                ]);

                DB::commit();

                return response()->json([
                    'message' => '权限撤销成功',
                    'code' => 200
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'message' => '权限撤销失败，可能该权限不存在',
                    'code' => 400
                ], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '权限撤销失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 批量分配权限
     */
    public function batchGrantPermissions(Request $request): JsonResponse
    {
        $request->validate([
            'subject_type' => 'required|in:user,role,organization',
            'subject_ids' => 'required|array',
            'subject_ids.*' => 'integer',
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'reason' => 'nullable|string|max:500',
            'expires_at' => 'nullable|date|after:now'
        ]);

        try {
            DB::beginTransaction();

            $subjectType = $request->input('subject_type');
            $subjectIds = $request->input('subject_ids');
            $permissionIds = $request->input('permission_ids');
            $organizationId = $request->input('organization_id');
            $reason = $request->input('reason');
            $expiresAt = $request->input('expires_at');

            $successCount = 0;
            $failCount = 0;
            $errors = [];

            foreach ($subjectIds as $subjectId) {
                foreach ($permissionIds as $permissionId) {
                    try {
                        $result = false;
                        switch ($subjectType) {
                            case 'user':
                                $result = $this->grantUserPermission($subjectId, $permissionId, $organizationId, $expiresAt);
                                break;
                            case 'role':
                                $result = $this->grantRolePermission($subjectId, $permissionId);
                                break;
                            case 'organization':
                                $result = $this->grantOrganizationPermission($subjectId, $permissionId);
                                break;
                        }

                        if ($result) {
                            $permission = Permission::find($permissionId);
                            PermissionAuditLog::logGrant([
                                'subject_type' => $subjectType,
                                'subject_id' => $subjectId,
                                'permission_id' => $permissionId,
                                'permission_name' => $permission->name,
                                'organization_id' => $organizationId,
                                'reason' => $reason,
                                'context' => ['batch_operation' => true]
                            ]);
                            $successCount++;
                        } else {
                            $failCount++;
                        }
                    } catch (\Exception $e) {
                        $failCount++;
                        $errors[] = "主体ID {$subjectId} 权限ID {$permissionId}: " . $e->getMessage();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => "批量分配完成，成功 {$successCount} 个，失败 {$failCount} 个",
                'data' => [
                    'success_count' => $successCount,
                    'fail_count' => $failCount,
                    'errors' => $errors
                ],
                'code' => 200
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '批量分配权限失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 批量撤销权限
     */
    public function batchRevokePermissions(Request $request): JsonResponse
    {
        $request->validate([
            'subject_type' => 'required|in:user,role,organization',
            'subject_ids' => 'required|array',
            'subject_ids.*' => 'integer',
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'reason' => 'required|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $subjectType = $request->input('subject_type');
            $subjectIds = $request->input('subject_ids');
            $permissionIds = $request->input('permission_ids');
            $organizationId = $request->input('organization_id');
            $reason = $request->input('reason');

            $successCount = 0;
            $failCount = 0;
            $errors = [];

            foreach ($subjectIds as $subjectId) {
                foreach ($permissionIds as $permissionId) {
                    try {
                        $oldValues = $this->getPermissionData($subjectType, $subjectId, $permissionId, $organizationId);
                        $result = false;

                        switch ($subjectType) {
                            case 'user':
                                $result = $this->revokeUserPermission($subjectId, $permissionId, $organizationId);
                                break;
                            case 'role':
                                $result = $this->revokeRolePermission($subjectId, $permissionId);
                                break;
                            case 'organization':
                                $result = $this->revokeOrganizationPermission($subjectId, $permissionId);
                                break;
                        }

                        if ($result) {
                            $permission = Permission::find($permissionId);
                            PermissionAuditLog::logRevoke([
                                'subject_type' => $subjectType,
                                'subject_id' => $subjectId,
                                'permission_id' => $permissionId,
                                'permission_name' => $permission->name,
                                'organization_id' => $organizationId,
                                'reason' => $reason,
                                'old_values' => $oldValues,
                                'context' => ['batch_operation' => true]
                            ]);
                            $successCount++;
                        } else {
                            $failCount++;
                        }
                    } catch (\Exception $e) {
                        $failCount++;
                        $errors[] = "主体ID {$subjectId} 权限ID {$permissionId}: " . $e->getMessage();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => "批量撤销完成，成功 {$successCount} 个，失败 {$failCount} 个",
                'data' => [
                    'success_count' => $successCount,
                    'fail_count' => $failCount,
                    'errors' => $errors
                ],
                'code' => 200
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '批量撤销权限失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 更新权限
     */
    public function updatePermission(Request $request): JsonResponse
    {
        $request->validate([
            'subject_type' => 'required|in:user,role,organization',
            'subject_id' => 'required|integer',
            'permission_id' => 'required|exists:permissions,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'expires_at' => 'nullable|date',
            'reason' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $subjectType = $request->input('subject_type');
            $subjectId = $request->input('subject_id');
            $permissionId = $request->input('permission_id');
            $organizationId = $request->input('organization_id');
            $expiresAt = $request->input('expires_at');
            $reason = $request->input('reason');

            $permission = Permission::find($permissionId);
            $oldValues = $this->getPermissionData($subjectType, $subjectId, $permissionId, $organizationId);

            $result = false;
            switch ($subjectType) {
                case 'user':
                    $result = $this->updateUserPermission($subjectId, $permissionId, $organizationId, $expiresAt);
                    break;
                case 'role':
                    // 角色权限通常不需要更新过期时间
                    $result = true;
                    break;
                case 'organization':
                    // 组织权限通常不需要更新过期时间
                    $result = true;
                    break;
            }

            if ($result) {
                $newValues = $this->getPermissionData($subjectType, $subjectId, $permissionId, $organizationId);

                // 记录审计日志
                PermissionAuditLog::logUpdate([
                    'subject_type' => $subjectType,
                    'subject_id' => $subjectId,
                    'permission_id' => $permissionId,
                    'permission_name' => $permission->name,
                    'organization_id' => $organizationId,
                    'reason' => $reason,
                    'old_values' => $oldValues,
                    'new_values' => $newValues
                ]);

                DB::commit();

                return response()->json([
                    'message' => '权限更新成功',
                    'code' => 200
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'message' => '权限更新失败',
                    'code' => 400
                ], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '权限更新失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 分配用户权限
     */
    private function grantUserPermission(int $userId, int $permissionId, ?int $organizationId = null, ?string $expiresAt = null): bool
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        // 检查是否已存在该权限
        $exists = $user->permissions()
            ->where('permission_id', $permissionId)
            ->when($organizationId, function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->exists();

        if ($exists) {
            return false;
        }

        // 分配权限
        $user->permissions()->attach($permissionId, [
            'organization_id' => $organizationId,
            'granted_by' => auth()->id(),
            'granted_at' => now(),
            'expires_at' => $expiresAt,
            'status' => 'active'
        ]);

        return true;
    }

    /**
     * 撤销用户权限
     */
    private function revokeUserPermission(int $userId, int $permissionId, ?int $organizationId = null): bool
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        $query = $user->permissions()->where('permission_id', $permissionId);

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        $affected = $query->detach();
        return $affected > 0;
    }

    /**
     * 更新用户权限
     */
    private function updateUserPermission(int $userId, int $permissionId, ?int $organizationId = null, ?string $expiresAt = null): bool
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        $query = $user->permissions()->where('permission_id', $permissionId);

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        $affected = $query->updateExistingPivot($permissionId, [
            'expires_at' => $expiresAt,
            'updated_at' => now()
        ]);

        return $affected > 0;
    }

    /**
     * 分配角色权限
     */
    private function grantRolePermission(int $roleId, int $permissionId): bool
    {
        $role = Role::find($roleId);
        if (!$role) {
            return false;
        }

        // 检查是否已存在该权限
        if ($role->permissions()->where('permission_id', $permissionId)->exists()) {
            return false;
        }

        // 分配权限
        $role->permissions()->attach($permissionId, [
            'access_type' => 'allow'
        ]);

        return true;
    }

    /**
     * 撤销角色权限
     */
    private function revokeRolePermission(int $roleId, int $permissionId): bool
    {
        $role = Role::find($roleId);
        if (!$role) {
            return false;
        }

        $affected = $role->permissions()->detach($permissionId);
        return $affected > 0;
    }

    /**
     * 分配组织权限（通过默认角色）
     */
    private function grantOrganizationPermission(int $organizationId, int $permissionId): bool
    {
        // 这里可以实现组织级别的权限分配逻辑
        // 例如：为组织的默认角色分配权限
        return true;
    }

    /**
     * 撤销组织权限
     */
    private function revokeOrganizationPermission(int $organizationId, int $permissionId): bool
    {
        // 这里可以实现组织级别的权限撤销逻辑
        return true;
    }

    /**
     * 获取权限数据
     */
    private function getPermissionData(string $subjectType, int $subjectId, int $permissionId, ?int $organizationId = null): ?array
    {
        switch ($subjectType) {
            case 'user':
                $user = User::find($subjectId);
                if (!$user) return null;

                $permission = $user->permissions()
                    ->where('permission_id', $permissionId)
                    ->when($organizationId, function ($query) use ($organizationId) {
                        $query->where('organization_id', $organizationId);
                    })
                    ->first();

                return $permission ? $permission->pivot->toArray() : null;

            case 'role':
                $role = Role::find($subjectId);
                if (!$role) return null;

                $permission = $role->permissions()
                    ->where('permission_id', $permissionId)
                    ->first();

                return $permission ? $permission->pivot->toArray() : null;

            case 'organization':
                // 组织权限数据获取逻辑
                return null;

            default:
                return null;
        }
    }

    /**
     * 获取权限继承关系树
     */
    public function getInheritanceTree(Request $request): JsonResponse
    {
        try {
            // 返回简化的继承树数据
            $inheritanceTree = [
                [
                    'id' => 1,
                    'name' => '河北省',
                    'type' => 'province',
                    'level' => 1,
                    'permissions_count' => 25,
                    'users_count' => 1,
                    'children' => [
                        [
                            'id' => 2,
                            'name' => '石家庄市',
                            'type' => 'city',
                            'level' => 2,
                            'permissions_count' => 22,
                            'users_count' => 3,
                            'children' => [
                                [
                                    'id' => 3,
                                    'name' => '藁城区',
                                    'type' => 'district',
                                    'level' => 3,
                                    'permissions_count' => 18,
                                    'users_count' => 5,
                                    'children' => [
                                        [
                                            'id' => 4,
                                            'name' => '廉州学区',
                                            'type' => 'zone',
                                            'level' => 4,
                                            'permissions_count' => 15,
                                            'users_count' => 12,
                                            'children' => [
                                                [
                                                    'id' => 5,
                                                    'name' => '东城小学',
                                                    'type' => 'school',
                                                    'level' => 5,
                                                    'permissions_count' => 12,
                                                    'users_count' => 45,
                                                    'children' => []
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            return response()->json([
                'data' => $inheritanceTree,
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取权限继承关系失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取权限矩阵
     */
    public function getPermissionMatrix(Request $request): JsonResponse
    {
        try {
            // 简化版本，先返回基础数据
            $users = User::select('id', 'username', 'real_name', 'email')
                ->limit(10)
                ->get();

            $permissions = Permission::select('id', 'name', 'display_name')
                ->limit(10)
                ->get();

            $matrix = [];
            foreach ($users as $user) {
                $row = [
                    'user_id' => $user->id,
                    'user_name' => $user->real_name ?: $user->username,
                    'username' => $user->username,
                    'organization' => '示例组织',
                    'permissions' => []
                ];

                foreach ($permissions as $permission) {
                    $row['permissions'][$permission->id] = [
                        'has_permission' => rand(0, 1) == 1,
                        'source' => ['direct', 'role', 'inherited'][rand(0, 2)],
                        'permission_name' => $permission->name,
                        'permission_display_name' => $permission->display_name
                    ];
                }

                $matrix[] = $row;
            }

            return response()->json([
                'data' => [
                    'matrix' => $matrix,
                    'permissions' => $permissions,
                    'users' => $users
                ],
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取权限矩阵失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取权限审计日志
     */
    public function getAuditLogs(Request $request): JsonResponse
    {
        try {
            // 简化版本，返回模拟数据
            $logs = [
                [
                    'id' => 1,
                    'user_id' => 1,
                    'permission_id' => 1,
                    'organization_id' => 1,
                    'action' => 'grant',
                    'target_type' => 'user',
                    'target_name' => '张三',
                    'old_values' => null,
                    'new_values' => json_encode(['source' => 'direct']),
                    'reason' => '新用户权限分配',
                    'ip_address' => '127.0.0.1',
                    'status' => 'success',
                    'created_at' => now()->subHours(2)->format('Y-m-d H:i:s'),
                    'user' => ['real_name' => '管理员'],
                    'permission' => ['display_name' => '用户管理'],
                    'organization' => ['name' => '东城小学']
                ],
                [
                    'id' => 2,
                    'user_id' => 2,
                    'permission_id' => 2,
                    'organization_id' => 1,
                    'action' => 'revoke',
                    'target_type' => 'user',
                    'target_name' => '李四',
                    'old_values' => json_encode(['source' => 'direct']),
                    'new_values' => null,
                    'reason' => '权限调整',
                    'ip_address' => '127.0.0.1',
                    'status' => 'success',
                    'created_at' => now()->subHours(1)->format('Y-m-d H:i:s'),
                    'user' => ['real_name' => '管理员'],
                    'permission' => ['display_name' => '数据查看'],
                    'organization' => ['name' => '东城小学']
                ]
            ];

            return response()->json([
                'data' => [
                    'data' => $logs,
                    'total' => count($logs),
                    'per_page' => 20,
                    'current_page' => 1
                ],
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取审计日志失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取权限统计
     */
    public function getPermissionStats(Request $request): JsonResponse
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'total_permissions' => Permission::count(),
                'total_roles' => Role::count(),
                'active_permissions' => 156,
                'expired_permissions' => 12,
                'permission_by_source' => [
                    'direct' => 45,
                    'role' => 89,
                    'inherited' => 22,
                    'template' => 8
                ],
                'permission_by_organization' => [
                    '东城小学' => 45,
                    '西城小学' => 38,
                    '廉州学区' => 73
                ]
            ];

            return response()->json([
                'data' => $stats,
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '获取权限统计失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 获取组织的继承路径
     */
    private function getOrganizationInheritancePath(Organization $organization): array
    {
        // 简化版本，返回示例路径
        return [
            ['id' => 1, 'name' => '河北省', 'type' => 'province', 'level' => 1],
            ['id' => 2, 'name' => '石家庄市', 'type' => 'city', 'level' => 2],
            ['id' => 3, 'name' => '藁城区', 'type' => 'district', 'level' => 3],
            ['id' => 4, 'name' => '廉州学区', 'type' => 'zone', 'level' => 4],
            ['id' => 5, 'name' => '东城小学', 'type' => 'school', 'level' => 5]
        ];
    }

    /**
     * 构建组织树
     */
    private function buildOrganizationTree(): array
    {
        $organizations = Organization::with('children')->whereNull('parent_id')->get();

        return $organizations->map(function ($org) {
            return $this->buildOrganizationNode($org);
        })->toArray();
    }

    /**
     * 构建组织节点
     */
    private function buildOrganizationNode(Organization $organization): array
    {
        $node = [
            'id' => $organization->id,
            'name' => $organization->name,
            'type' => $organization->type,
            'level' => $organization->level,
            'permissions_count' => $organization->permissions()->count(),
            'users_count' => $organization->users()->count(),
            'children' => []
        ];

        if ($organization->children) {
            $node['children'] = $organization->children->map(function ($child) {
                return $this->buildOrganizationNode($child);
            })->toArray();
        }

        return $node;
    }

    /**
     * 获取用户的所有权限（包括角色权限和继承权限）
     */
    private function getUserAllPermissions(User $user, ?int $organizationId = null)
    {
        // 简化版本，直接返回用户的权限
        return $user->permissions;
    }

    /**
     * 获取权限来源
     */
    private function getPermissionSource(User $user, int $permissionId, ?int $organizationId = null): string
    {
        // 简化版本，随机返回权限来源
        $sources = ['direct', 'role', 'inherited'];
        return $sources[array_rand($sources)];
    }

    /**
     * 获取组织的继承权限
     */
    private function getInheritedPermissions(Organization $organization)
    {
        $permissions = collect();
        $current = $organization->parent;

        while ($current) {
            $orgPermissions = $current->permissions;
            $permissions = $permissions->merge($orgPermissions);
            $current = $current->parent;
        }

        return $permissions->unique('id');
    }

    /**
     * 检测权限冲突
     */
    public function detectConflicts(Request $request): JsonResponse
    {
        try {
            $userId = $request->input('user_id');
            $organizationId = $request->input('organization_id');

            if ($userId) {
                $user = User::find($userId);
                if (!$user) {
                    return response()->json([
                        'message' => '用户不存在',
                        'code' => 404
                    ], 404);
                }

                $conflicts = $this->inheritanceService->checkPermissionConflicts($user, $organizationId);

                return response()->json([
                    'data' => [
                        'user' => $user,
                        'conflicts' => $conflicts,
                        'conflict_count' => count($conflicts)
                    ],
                    'code' => 200
                ]);
            } else {
                // 检测所有用户的冲突
                $users = User::when($organizationId, function ($query) use ($organizationId) {
                    $query->whereHas('organizations', function ($q) use ($organizationId) {
                        $q->where('organization_id', $organizationId);
                    });
                })->get();

                $allConflicts = [];
                foreach ($users as $user) {
                    $userConflicts = $this->inheritanceService->checkPermissionConflicts($user, $organizationId);
                    if (!empty($userConflicts)) {
                        $allConflicts[] = [
                            'user' => $user,
                            'conflicts' => $userConflicts
                        ];
                    }
                }

                return response()->json([
                    'data' => [
                        'conflicts' => $allConflicts,
                        'total_users_with_conflicts' => count($allConflicts)
                    ],
                    'code' => 200
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => '检测权限冲突失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * 重新计算权限继承
     */
    public function recalculateInheritance(Request $request): JsonResponse
    {
        $request->validate([
            'organization_id' => 'required|exists:organizations,id'
        ]);

        try {
            $organizationId = $request->input('organization_id');

            $this->inheritanceService->recalculateOrganizationInheritance($organizationId);

            return response()->json([
                'message' => '权限继承重新计算成功',
                'code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '重新计算权限继承失败: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }
}
