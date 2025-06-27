<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Organization;
use App\Models\PermissionAuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PermissionManagementController extends Controller
{
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
}
