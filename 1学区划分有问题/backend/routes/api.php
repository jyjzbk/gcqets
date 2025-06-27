<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PermissionVisualizationController;
use App\Http\Controllers\Api\PermissionTemplateController;
use App\Http\Controllers\Api\PermissionAuditController;
use App\Models\Organization;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// 健康检查路由（无需认证）
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'data' => [
            'organizations' => Organization::count(),
            'users' => User::count(),
            'roles' => Role::count(),
            'permissions' => Permission::count(),
        ]
    ]);
});

// 认证相关路由
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('user', [AuthController::class, 'user'])->middleware('auth:sanctum');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:sanctum');
});

// 需要认证的路由组
Route::middleware(['auth:sanctum'])->group(function () {
    
    // 组织机构管理路由
    Route::prefix('organizations')->group(function () {
        Route::get('/', [OrganizationController::class, 'index']);
        Route::get('/tree', [OrganizationController::class, 'tree']);
        Route::get('/import/template', [OrganizationController::class, 'downloadTemplate'])->middleware('permission:organization.import');
        Route::get('/import/history', [OrganizationController::class, 'importHistory'])->middleware('permission:organization.import');
        Route::post('/import', [OrganizationController::class, 'import'])->middleware('permission:organization.import');
        Route::get('/{organization}', [OrganizationController::class, 'show']);
        Route::post('/', [OrganizationController::class, 'store'])->middleware('permission:organization.create');
        Route::put('/{organization}', [OrganizationController::class, 'update'])->middleware('permission:organization.update');
        Route::delete('/{organization}', [OrganizationController::class, 'destroy'])->middleware('permission:organization.delete');
        Route::post('/{organization}/move', [OrganizationController::class, 'move'])->middleware('permission:organization.move');
        Route::get('/{organization}/users', [OrganizationController::class, 'users']);
        Route::get('/{organization}/children', [OrganizationController::class, 'children']);
        Route::get('/{organization}/ancestors', [OrganizationController::class, 'ancestors']);
        Route::get('/{organization}/descendants', [OrganizationController::class, 'descendants']);
    });

    // 学区划分管理路由
    Route::prefix('districts')->group(function () {
        Route::get('/overview', [\App\Http\Controllers\Api\DistrictManagementController::class, 'overview']);
        Route::get('/schools/locations', [\App\Http\Controllers\Api\DistrictManagementController::class, 'getSchoolLocations']);
        Route::get('/boundaries', [\App\Http\Controllers\Api\DistrictManagementController::class, 'getDistrictBoundaries']);
        Route::post('/auto-assign', [\App\Http\Controllers\Api\DistrictManagementController::class, 'autoAssign'])->middleware('permission:district.manage');
        Route::post('/preview-auto-assign', [\App\Http\Controllers\Api\DistrictManagementController::class, 'previewAutoAssign']);
        Route::post('/manual-assign', [\App\Http\Controllers\Api\DistrictManagementController::class, 'manualAssign'])->middleware('permission:district.manage');
        Route::post('/batch-assign', [\App\Http\Controllers\Api\DistrictManagementController::class, 'batchAssign'])->middleware('permission:district.manage');
        Route::get('/assignment-history', [\App\Http\Controllers\Api\DistrictManagementController::class, 'getAssignmentHistory']);
        Route::post('/assignment-history/{history}/revert', [\App\Http\Controllers\Api\DistrictManagementController::class, 'revertAssignment'])->middleware('permission:district.manage');
        Route::get('/available-schools', [\App\Http\Controllers\Api\DistrictManagementController::class, 'getAvailableSchools']);
        Route::get('/{district}/schools', [\App\Http\Controllers\Api\DistrictManagementController::class, 'getDistrictSchools']);
        Route::get('/statistics', [\App\Http\Controllers\Api\DistrictManagementController::class, 'getStatistics']);
        Route::get('/export-report', [\App\Http\Controllers\Api\DistrictManagementController::class, 'exportReport']);
        Route::get('/load-balance-analysis', [\App\Http\Controllers\Api\DistrictManagementController::class, 'getLoadBalanceAnalysis']);
        Route::get('/distance-analysis', [\App\Http\Controllers\Api\DistrictManagementController::class, 'getDistanceAnalysis']);
        // 学区边界管理
        Route::post('/boundaries', [\App\Http\Controllers\Api\DistrictManagementController::class, 'createBoundary'])->middleware('permission:district.manage');
        Route::put('/boundaries/{boundary}', [\App\Http\Controllers\Api\DistrictManagementController::class, 'updateBoundary'])->middleware('permission:district.manage');
        Route::delete('/boundaries/{boundary}', [\App\Http\Controllers\Api\DistrictManagementController::class, 'deleteBoundary'])->middleware('permission:district.manage');
    });

    // 用户管理路由
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::post('/', [UserController::class, 'store'])->middleware('permission:user.create');
        Route::put('/{user}', [UserController::class, 'update'])->middleware('permission:user.update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->middleware('permission:user.delete');
        Route::post('/{user}/assign-role', [UserController::class, 'assignRole'])->middleware('permission:user.assign-role');
        Route::delete('/{user}/remove-role', [UserController::class, 'removeRole'])->middleware('permission:user.remove-role');
        Route::post('/{user}/give-permission', [UserController::class, 'givePermission'])->middleware('permission:user.give-permission');
        Route::delete('/{user}/revoke-permission', [UserController::class, 'revokePermission'])->middleware('permission:user.revoke-permission');
        Route::get('/{user}/permissions', [UserController::class, 'permissions']);
        Route::get('/{user}/roles', [UserController::class, 'roles']);
        Route::post('/{user}/change-password', [UserController::class, 'changePassword']);
        Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->middleware('permission:user.reset-password');
    });

    // 角色管理路由
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index']);
        Route::get('/options', [RoleController::class, 'options']); // 移到动态路由之前
        Route::get('/{role}', [RoleController::class, 'show']);
        Route::post('/', [RoleController::class, 'store'])->middleware('permission:role.create');
        Route::put('/{role}', [RoleController::class, 'update'])->middleware('permission:role.update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->middleware('permission:role.delete');
        Route::post('/{role}/assign-permissions', [RoleController::class, 'assignPermissions'])->middleware('permission:role.assign-permissions');
        Route::delete('/{role}/remove-permissions', [RoleController::class, 'removePermissions'])->middleware('permission:role.remove-permissions');
        Route::get('/{role}/permissions', [RoleController::class, 'permissions']);
        Route::get('/{role}/users', [RoleController::class, 'users']);
    });

    // 权限管理路由
    Route::prefix('permissions')->group(function () {
        Route::get('/', [PermissionController::class, 'index']);
        Route::get('/tree', [PermissionController::class, 'tree']);
        Route::get('/menu', [PermissionController::class, 'menu']);
        Route::get('/groups', [PermissionController::class, 'groups']); // 移到动态路由之前
        Route::get('/modules', [PermissionController::class, 'modules']); // 移到动态路由之前
        Route::get('/{permission}', [PermissionController::class, 'show']);
        Route::post('/', [PermissionController::class, 'store'])->middleware('permission:permission.create');
        Route::put('/{permission}', [PermissionController::class, 'update'])->middleware('permission:permission.update');
        Route::delete('/{permission}', [PermissionController::class, 'destroy'])->middleware('permission:permission.delete');
    });

    // 权限可视化路由
    Route::prefix('permission-visualization')->group(function () {
        Route::get('/inheritance-tree', [PermissionVisualizationController::class, 'getInheritanceTree']);
        Route::get('/inheritance-path', [PermissionVisualizationController::class, 'getInheritancePath']);
        Route::post('/detect-conflicts', [PermissionVisualizationController::class, 'detectConflicts']);
        Route::get('/permission-matrix', [PermissionVisualizationController::class, 'getPermissionMatrix']);
        Route::get('/effective-permissions', [PermissionVisualizationController::class, 'calculateEffectivePermissions']);
    });

    // 权限模板路由
    Route::prefix('permission-templates')->group(function () {
        Route::get('/', [PermissionTemplateController::class, 'index']);
        Route::get('/recommended', [PermissionTemplateController::class, 'getRecommended']);
        Route::get('/{template}', [PermissionTemplateController::class, 'show']);
        Route::post('/', [PermissionTemplateController::class, 'store'])->middleware('permission:permission.template.create');
        Route::put('/{template}', [PermissionTemplateController::class, 'update'])->middleware('permission:permission.template.update');
        Route::delete('/{template}', [PermissionTemplateController::class, 'destroy'])->middleware('permission:permission.template.delete');
        Route::post('/{template}/apply-to-role', [PermissionTemplateController::class, 'applyToRole'])->middleware('permission:permission.template.apply');
        Route::post('/{template}/apply-to-user', [PermissionTemplateController::class, 'applyToUser'])->middleware('permission:permission.template.apply');
        Route::post('/{template}/duplicate', [PermissionTemplateController::class, 'duplicate'])->middleware('permission:permission.template.create');
    });

    // 权限审计路由
    Route::prefix('permission-audit')->group(function () {
        Route::get('/logs', [PermissionAuditController::class, 'index']);
        Route::get('/logs/{log}', [PermissionAuditController::class, 'show']);
        Route::get('/user-stats', [PermissionAuditController::class, 'getUserStats']);
        Route::get('/organization-stats', [PermissionAuditController::class, 'getOrganizationStats']);
        Route::get('/permission-hotspots', [PermissionAuditController::class, 'getPermissionHotspots']);
        Route::get('/conflicts', [PermissionAuditController::class, 'getConflicts']);
        Route::get('/conflict-stats', [PermissionAuditController::class, 'getConflictStats']);
        Route::post('/conflicts/{conflict}/resolve', [PermissionAuditController::class, 'resolveConflict'])->middleware('permission:permission.conflict.resolve');
        Route::post('/conflicts/{conflict}/ignore', [PermissionAuditController::class, 'ignoreConflict'])->middleware('permission:permission.conflict.resolve');
        Route::post('/export', [PermissionAuditController::class, 'export'])->middleware('permission:permission.audit.export');
    });
});