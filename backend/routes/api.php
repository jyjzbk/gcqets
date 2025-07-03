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
use App\Http\Controllers\ExperimentCatalogController;
use App\Http\Controllers\CurriculumStandardController;
use App\Http\Controllers\PhotoTemplateController;
use App\Http\Controllers\CatalogVersionController;
use App\Http\Controllers\EquipmentCategoryController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\EquipmentBorrowingController;
use App\Http\Controllers\MaterialCategoryController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MaterialUsageController;
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
        Route::get('/parent-options', [OrganizationController::class, 'getParentOptions']);
        Route::get('/{parentId}/children', [OrganizationController::class, 'getChildren']);
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

        // 权限可视化管理路由
        Route::get('/inheritance-tree', [PermissionManagementController::class, 'getInheritanceTree']);
        Route::get('/permission-matrix', [PermissionManagementController::class, 'getPermissionMatrix']);
        Route::get('/audit-logs', [PermissionManagementController::class, 'getAuditLogs']);
        Route::get('/stats', [PermissionManagementController::class, 'getPermissionStats']);
        Route::get('/detect-conflicts', [PermissionManagementController::class, 'detectConflicts']);
        Route::post('/recalculate-inheritance', [PermissionManagementController::class, 'recalculateInheritance']);
    });

    // 实验目录管理路由
    Route::prefix('experiment-catalogs')->group(function () {
        Route::get('/', [ExperimentCatalogController::class, 'index']);
        Route::get('/options', [ExperimentCatalogController::class, 'options']);
        Route::get('/{id}', [ExperimentCatalogController::class, 'show']);
        Route::post('/', [ExperimentCatalogController::class, 'store']);
        Route::put('/{id}', [ExperimentCatalogController::class, 'update']);
        Route::delete('/{id}', [ExperimentCatalogController::class, 'destroy']);

        // 版本管理路由
        Route::prefix('{catalogId}/versions')->group(function () {
            Route::get('/', [CatalogVersionController::class, 'index']);
            Route::get('/{versionId}', [CatalogVersionController::class, 'show']);
            Route::post('/compare', [CatalogVersionController::class, 'compare']);
            Route::post('/{versionId}/rollback', [CatalogVersionController::class, 'rollback']);
            Route::get('/statistics', [CatalogVersionController::class, 'statistics']);
        });
    });

    // 课程标准管理路由
    Route::prefix('curriculum-standards')->group(function () {
        Route::get('/', [CurriculumStandardController::class, 'index']);
        Route::get('/options', [CurriculumStandardController::class, 'options']);
        Route::get('/valid', [CurriculumStandardController::class, 'validStandards']);
        Route::get('/{id}', [CurriculumStandardController::class, 'show']);
        Route::post('/', [CurriculumStandardController::class, 'store']);
        Route::put('/{id}', [CurriculumStandardController::class, 'update']);
        Route::delete('/{id}', [CurriculumStandardController::class, 'destroy']);
    });

    // 照片模板管理路由
    Route::prefix('photo-templates')->group(function () {
        Route::get('/', [PhotoTemplateController::class, 'index']);
        Route::get('/options', [PhotoTemplateController::class, 'options']);
        Route::get('/matching', [PhotoTemplateController::class, 'getMatchingTemplates']);
        Route::get('/{id}', [PhotoTemplateController::class, 'show']);
        Route::post('/', [PhotoTemplateController::class, 'store']);
        Route::put('/{id}', [PhotoTemplateController::class, 'update']);
        Route::delete('/{id}', [PhotoTemplateController::class, 'destroy']);
    });

    // 设备分类管理路由
    Route::prefix('equipment-categories')->group(function () {
        Route::get('/', [EquipmentCategoryController::class, 'index']);
        Route::get('/tree', [EquipmentCategoryController::class, 'tree']);
        Route::get('/{id}', [EquipmentCategoryController::class, 'show']);
        Route::post('/', [EquipmentCategoryController::class, 'store']);
        Route::put('/{id}', [EquipmentCategoryController::class, 'update']);
        Route::delete('/{id}', [EquipmentCategoryController::class, 'destroy']);
    });

    // 设备管理路由
    Route::prefix('equipment')->group(function () {
        Route::get('/', [EquipmentController::class, 'index']);
        Route::get('/statistics', [EquipmentController::class, 'statistics']);
        Route::get('/{id}', [EquipmentController::class, 'show']);
        Route::post('/', [EquipmentController::class, 'store']);
        Route::put('/{id}', [EquipmentController::class, 'update']);
        Route::delete('/{id}', [EquipmentController::class, 'destroy']);
    });

    // 设备借用管理路由
    Route::prefix('equipment-borrowings')->group(function () {
        Route::get('/', [EquipmentBorrowingController::class, 'index']);
        Route::get('/statistics', [EquipmentBorrowingController::class, 'statistics']);
        Route::get('/{id}', [EquipmentBorrowingController::class, 'show']);
        Route::post('/', [EquipmentBorrowingController::class, 'store']);
        Route::post('/{id}/approve', [EquipmentBorrowingController::class, 'approve']);
        Route::post('/{id}/borrow', [EquipmentBorrowingController::class, 'borrow']);
        Route::post('/{id}/return', [EquipmentBorrowingController::class, 'returnEquipment']);
        Route::post('/{id}/cancel', [EquipmentBorrowingController::class, 'cancel']);
    });

    // 材料分类管理路由
    Route::prefix('material-categories')->group(function () {
        Route::get('/', [MaterialCategoryController::class, 'index']);
        Route::get('/tree', [MaterialCategoryController::class, 'tree']);
        Route::get('/{id}', [MaterialCategoryController::class, 'show']);
        Route::post('/', [MaterialCategoryController::class, 'store']);
        Route::put('/{id}', [MaterialCategoryController::class, 'update']);
        Route::delete('/{id}', [MaterialCategoryController::class, 'destroy']);
    });

    // 材料管理路由
    Route::prefix('materials')->group(function () {
        Route::get('/', [MaterialController::class, 'index']);
        Route::get('/statistics', [MaterialController::class, 'statistics']);
        Route::get('/{id}', [MaterialController::class, 'show']);
        Route::post('/', [MaterialController::class, 'store']);
        Route::put('/{id}', [MaterialController::class, 'update']);
        Route::delete('/{id}', [MaterialController::class, 'destroy']);
        Route::post('/{id}/adjust-stock', [MaterialController::class, 'adjustStock']);
    });

    // 材料使用记录管理路由
    Route::prefix('material-usages')->group(function () {
        Route::get('/', [MaterialUsageController::class, 'index']);
        Route::get('/statistics', [MaterialUsageController::class, 'statistics']);
        Route::get('/{id}', [MaterialUsageController::class, 'show']);
        Route::post('/', [MaterialUsageController::class, 'store']);
        Route::put('/{id}', [MaterialUsageController::class, 'update']);
        Route::delete('/{id}', [MaterialUsageController::class, 'destroy']);
    });
});

// 测试路由（无需认证）
Route::get('/permission-management/test-stats', [PermissionManagementController::class, 'getPermissionStats']);
Route::get('/permission-management/test-matrix', [PermissionManagementController::class, 'getPermissionMatrix']);
Route::get('/permission-management/test-audit', [PermissionManagementController::class, 'getAuditLogs']);
Route::get('/permission-management/test-tree', [PermissionManagementController::class, 'getInheritanceTree']);

// 修复错误类型学校的路由
Route::get('/test/fix-wrong-type-schools', function () {
    $schoolNames = ['角中小学', '大同镇中'];
    $fixed = [];

    foreach ($schoolNames as $schoolName) {
        $org = Organization::where('name', $schoolName)->first();
        if ($org && $org->type !== 'school') {
            $oldType = $org->type;
            $org->type = 'school';
            $org->save();

            $fixed[] = [
                'id' => $org->id,
                'name' => $org->name,
                'old_type' => $oldType,
                'new_type' => 'school'
            ];
        }
    }

    // 重新查询学校数量
    $schoolsAfterFix = Organization::where('type', 'school')->count();

    return response()->json([
        'message' => '学校类型修复完成',
        'fixed_schools' => $fixed,
        'schools_count_after_fix' => $schoolsAfterFix
    ]);
});
