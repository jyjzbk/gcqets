<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\PermissionManagementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\SchoolImportController;
use App\Http\Controllers\EducationZoneController;
use App\Http\Controllers\PermissionTemplateController;
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

// 测试路由
Route::get('test', function () {
    return response()->json(['message' => 'API is working', 'time' => now()]);
});

// 测试用户路由（无需认证）
Route::get('test-user', function () {
    $user = \App\Models\User::first();
    return response()->json(['message' => 'User test', 'user' => $user ? $user->email : 'No user']);
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
        Route::get('/{organization}', [OrganizationController::class, 'show']);
        Route::post('/', [OrganizationController::class, 'store']); // ->middleware('permission:organization.create');
        Route::put('/{organization}', [OrganizationController::class, 'update']); // ->middleware('permission:organization.update');
        Route::delete('/{organization}', [OrganizationController::class, 'destroy']); // ->middleware('permission:organization.delete');
        Route::post('/{organization}/move', [OrganizationController::class, 'move']); // ->middleware('permission:organization.move');
        Route::get('/{organization}/users', [OrganizationController::class, 'users']);
        Route::get('/{organization}/children', [OrganizationController::class, 'children']);
        Route::get('/{organization}/ancestors', [OrganizationController::class, 'ancestors']);
        Route::get('/{organization}/descendants', [OrganizationController::class, 'descendants']);

        // 学校导入相关路由
        Route::prefix('schools')->group(function () {
            Route::post('/import', [OrganizationController::class, 'importSchools'])
                ->middleware('permission:school.import');
            Route::post('/import/preview', [OrganizationController::class, 'previewSchoolImport'])
                ->middleware('permission:school.import');
            Route::post('/import/analyze', [OrganizationController::class, 'analyzeSchoolImportFile'])
                ->middleware('permission:school.import');
            Route::get('/import/template', [OrganizationController::class, 'downloadSchoolTemplate'])
                ->middleware('permission:school.import');
            Route::get('/import/history', [OrganizationController::class, 'getSchoolImportHistory'])
                ->middleware(\App\Http\Middleware\CheckPermission::class . ':school.import');
            Route::get('/import/history/{importLog}', [OrganizationController::class, 'getSchoolImportDetail'])
                ->middleware('permission:school.import');
            Route::get('/import/history/{importLog}/audit', [OrganizationController::class, 'generateImportAuditReport'])
                ->middleware('permission:school.import');
            Route::post('/import/history/{importLog}/rollback', [OrganizationController::class, 'rollbackImport'])
                ->middleware('permission:school.rollback');
            Route::post('/import/compare', [OrganizationController::class, 'compareImports'])
                ->middleware('permission:school.import');
            Route::get('/import/timeline', [OrganizationController::class, 'getImportTimeline'])
                ->middleware('permission:school.import');
            Route::get('/import/stats', [OrganizationController::class, 'getSchoolImportStats'])
                ->middleware(\App\Http\Middleware\CheckPermission::class . ':school.import');

            // 学校管理相关路由
            Route::get('/export', [OrganizationController::class, 'exportSchools'])
                ->middleware('permission:school.export');
            Route::delete('/batch', [OrganizationController::class, 'batchDeleteSchools'])
                ->middleware('permission:school.delete');
        });
    });

    // 用户管理路由
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::post('/', [UserController::class, 'store']); // ->middleware('permission:user.create');
        Route::put('/{user}', [UserController::class, 'update']); // ->middleware('permission:user.update');
        Route::delete('/{user}', [UserController::class, 'destroy']); // ->middleware('permission:user.delete');
        Route::post('/{user}/assign-role', [UserController::class, 'assignRole']); // ->middleware('permission:user.assign-role');
        Route::delete('/{user}/remove-role', [UserController::class, 'removeRole']); // ->middleware('permission:user.remove-role');
        Route::post('/{user}/give-permission', [UserController::class, 'givePermission']); // ->middleware('permission:user.give-permission');
        Route::delete('/{user}/revoke-permission', [UserController::class, 'revokePermission']); // ->middleware('permission:user.revoke-permission');
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
        Route::post('/', [RoleController::class, 'store']); // ->middleware('permission:role.create');
        Route::put('/{role}', [RoleController::class, 'update']); // ->middleware('permission:role.update');
        Route::delete('/{role}', [RoleController::class, 'destroy']); // ->middleware('permission:role.delete');
        Route::post('/{role}/assign-permissions', [RoleController::class, 'assignPermissions']); // ->middleware('permission:role.assign-permissions');
        Route::delete('/{role}/remove-permissions', [RoleController::class, 'removePermissions']); // ->middleware('permission:role.remove-permissions');
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

    // 学校批量导入路由
    Route::prefix('school-import')->group(function () {
        Route::get('/', [SchoolImportController::class, 'index'])->middleware('permission:school.import.view');
        Route::post('/upload', [SchoolImportController::class, 'upload'])->middleware('permission:school.import.create');
        Route::get('/status/{id}', [SchoolImportController::class, 'status'])->middleware('permission:school.import.view');
        Route::get('/template', [SchoolImportController::class, 'downloadTemplate'])->middleware('permission:school.import.view');
        Route::post('/process/{id}', [SchoolImportController::class, 'processImport'])->middleware('permission:school.import.create');
    });

    // 学区管理路由
    Route::prefix('education-zones')->group(function () {
        Route::get('/', [EducationZoneController::class, 'index'])->middleware('permission:education-zone.view');
        Route::post('/', [EducationZoneController::class, 'store'])->middleware('permission:education-zone.create');
        Route::get('/{id}', [EducationZoneController::class, 'show'])->middleware('permission:education-zone.view');
        Route::put('/{id}', [EducationZoneController::class, 'update'])->middleware('permission:education-zone.update');
        Route::delete('/{id}', [EducationZoneController::class, 'destroy'])->middleware('permission:education-zone.delete');
        Route::get('/{id}/schools', [EducationZoneController::class, 'getSchools'])->middleware('permission:education-zone.view');
        Route::post('/{id}/schools', [EducationZoneController::class, 'assignSchools'])->middleware('permission:education-zone.update');
        Route::delete('/{id}/schools', [EducationZoneController::class, 'removeSchools'])->middleware('permission:education-zone.update');
        Route::post('/auto-assign', [EducationZoneController::class, 'autoAssignSchools'])->middleware('permission:education-zone.update');
    });

    // 权限模板管理路由 (暂时注释掉，控制器不存在)
    /*
    Route::prefix('permission-templates')->group(function () {
        Route::get('/', [PermissionTemplateController::class, 'index'])->middleware('permission:permission-template.view');
        Route::post('/', [PermissionTemplateController::class, 'store'])->middleware('permission:permission-template.create');
        Route::get('/{id}', [PermissionTemplateController::class, 'show'])->middleware('permission:permission-template.view');
        Route::put('/{id}', [PermissionTemplateController::class, 'update'])->middleware('permission:permission-template.update');
        Route::delete('/{id}', [PermissionTemplateController::class, 'destroy'])->middleware('permission:permission-template.delete');
        Route::post('/apply', [PermissionTemplateController::class, 'apply'])->middleware('permission:permission-template.update');
        Route::get('/export', [PermissionTemplateController::class, 'export'])->middleware('permission:permission-template.view');
        Route::post('/import', [PermissionTemplateController::class, 'import'])->middleware('permission:permission-template.create');
        Route::get('/stats', [PermissionTemplateController::class, 'getStats'])->middleware('permission:permission-template.view');
    });
    */

    // 权限可视化路由 (暂时注释掉，控制器不存在)
    /*
    Route::prefix('permission-visualization')->group(function () {
        Route::get('/inheritance-tree', [PermissionVisualizationController::class, 'getInheritanceTree'])->middleware('permission:permission.view');
        Route::get('/inheritance-path', [PermissionVisualizationController::class, 'getInheritancePath'])->middleware('permission:permission.view');
        Route::post('/detect-conflicts', [PermissionVisualizationController::class, 'detectConflicts'])->middleware('permission:permission.view');
        Route::get('/permission-matrix', [PermissionVisualizationController::class, 'getPermissionMatrix'])->middleware('permission:permission.view');
        Route::get('/effective-permissions', [PermissionVisualizationController::class, 'calculateEffectivePermissions'])->middleware('permission:permission.view');
    });
    */

    // 权限审计路由 (暂时注释掉，控制器不存在)
    /*
    Route::prefix('permission-audit')->group(function () {
        Route::get('/logs', [PermissionAuditController::class, 'getLogs'])->middleware('permission:permission.view');
        Route::get('/logs/{id}', [PermissionAuditController::class, 'getLogDetail'])->middleware('permission:permission.view');
        Route::get('/user-stats', [PermissionAuditController::class, 'getUserStats'])->middleware('permission:permission.view');
        Route::get('/organization-stats', [PermissionAuditController::class, 'getOrganizationStats'])->middleware('permission:permission.view');
        Route::get('/permission-hotspots', [PermissionAuditController::class, 'getPermissionHotspots'])->middleware('permission:permission.view');
        Route::get('/conflicts', [PermissionAuditController::class, 'getConflicts'])->middleware('permission:permission.view');
        Route::get('/conflict-stats', [PermissionAuditController::class, 'getConflictStats'])->middleware('permission:permission.view');
    });
    */

    // 权限管理路由
    Route::prefix('permission-management')->group(function () {
        Route::post('/grant', [PermissionManagementController::class, 'grantPermission'])->middleware('permission:permission.grant');
        Route::post('/revoke', [PermissionManagementController::class, 'revokePermission'])->middleware('permission:permission.revoke');
        Route::post('/batch-grant', [PermissionManagementController::class, 'batchGrantPermissions'])->middleware('permission:permission.grant');
        Route::post('/batch-revoke', [PermissionManagementController::class, 'batchRevokePermissions'])->middleware('permission:permission.revoke');
        Route::post('/update', [PermissionManagementController::class, 'updatePermission'])->middleware('permission:permission.update');
    });
});
