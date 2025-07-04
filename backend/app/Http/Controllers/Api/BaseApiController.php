<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BaseApiController extends Controller
{
    /**
     * 获取当前用户
     */
    protected function getCurrentUser()
    {
        return Auth::user();
    }

    /**
     * 成功响应
     */
    protected function success($data = null, string $message = '操作成功'): JsonResponse
    {
        return ApiResponse::success($data, $message);
    }

    /**
     * 错误响应
     */
    protected function error(string $message = '操作失败', int $code = 400, $errors = null): JsonResponse
    {
        return ApiResponse::error($message, $code, $errors);
    }

    /**
     * 验证错误响应
     */
    protected function validationError($errors, string $message = '数据验证失败'): JsonResponse
    {
        return ApiResponse::validationError($errors, $message);
    }

    /**
     * 未授权响应
     */
    protected function unauthorized(string $message = '未授权访问'): JsonResponse
    {
        return ApiResponse::unauthorized($message);
    }

    /**
     * 禁止访问响应
     */
    protected function forbidden(string $message = '禁止访问'): JsonResponse
    {
        return ApiResponse::forbidden($message);
    }

    /**
     * 资源未找到响应
     */
    protected function notFound(string $message = '资源未找到'): JsonResponse
    {
        return ApiResponse::notFound($message);
    }

    /**
     * 服务器错误响应
     */
    protected function serverError(string $message = '服务器内部错误'): JsonResponse
    {
        return ApiResponse::serverError($message);
    }

    /**
     * 创建成功响应
     */
    protected function created($data = null, string $message = '创建成功'): JsonResponse
    {
        return ApiResponse::created($data, $message);
    }

    /**
     * 更新成功响应
     */
    protected function updated($data = null, string $message = '更新成功'): JsonResponse
    {
        return ApiResponse::updated($data, $message);
    }

    /**
     * 删除成功响应
     */
    protected function deleted(string $message = '删除成功'): JsonResponse
    {
        return ApiResponse::deleted($message);
    }

    /**
     * 分页响应
     */
    protected function paginated($paginator, string $message = '获取数据成功'): JsonResponse
    {
        return ApiResponse::paginated($paginator, $message);
    }

    /**
     * 列表响应
     */
    protected function collection($data, string $message = '获取列表成功'): JsonResponse
    {
        return ApiResponse::collection($data, $message);
    }

    /**
     * 详情响应
     */
    protected function show($data, string $message = '获取详情成功'): JsonResponse
    {
        return ApiResponse::show($data, $message);
    }

    /**
     * 统计数据响应
     */
    protected function statistics($data, string $message = '获取统计数据成功'): JsonResponse
    {
        return ApiResponse::statistics($data, $message);
    }

    /**
     * 选项数据响应
     */
    protected function options($data, string $message = '获取选项数据成功'): JsonResponse
    {
        return ApiResponse::options($data, $message);
    }

    /**
     * 批量操作响应
     */
    protected function batch($data, string $message = '批量操作完成'): JsonResponse
    {
        return ApiResponse::batch($data, $message);
    }

    /**
     * 业务逻辑错误响应
     */
    protected function businessError(string $message, $data = null): JsonResponse
    {
        return ApiResponse::businessError($message, $data);
    }

    /**
     * 警告响应
     */
    protected function warning(string $message, $data = null): JsonResponse
    {
        return ApiResponse::warning($message, $data);
    }

    /**
     * 处理异常
     */
    protected function handleException(\Exception $e, string $message = null): JsonResponse
    {
        // 记录错误日志
        Log::error('API Exception: ' . $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'user_id' => $this->getCurrentUser()?->id,
            'request_url' => request()->fullUrl(),
            'request_method' => request()->method(),
            'request_data' => request()->all()
        ]);

        return ApiResponse::exception($e, $message);
    }

    /**
     * 检查权限
     */
    protected function checkPermission(string $permission): bool
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }

        // 系统管理员拥有所有权限
        if ($user->user_type === 'admin') {
            return true;
        }

        // 这里可以扩展更复杂的权限检查逻辑
        return true;
    }

    /**
     * 检查组织权限
     */
    protected function checkOrganizationAccess($organizationId): bool
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }

        // 系统管理员可以访问所有组织
        if ($user->user_type === 'admin') {
            return true;
        }

        // 其他用户只能访问自己的组织
        return $user->organization_id == $organizationId;
    }

    /**
     * 获取用户可访问的组织ID列表
     */
    protected function getAccessibleOrganizationIds(): array
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return [];
        }

        // 系统管理员可以访问所有组织
        if ($user->user_type === 'admin') {
            return []; // 空数组表示不限制
        }

        // 其他用户只能访问自己的组织
        return [$user->organization_id];
    }

    /**
     * 应用组织过滤
     */
    protected function applyOrganizationFilter($query, string $column = 'organization_id')
    {
        $organizationIds = $this->getAccessibleOrganizationIds();
        
        if (!empty($organizationIds)) {
            $query->whereIn($column, $organizationIds);
        }

        return $query;
    }

    /**
     * 验证请求数据
     */
    protected function validateRequest(array $rules, array $messages = []): array
    {
        $validator = validator(request()->all(), $rules, $messages);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * 获取分页参数
     */
    protected function getPaginationParams(): array
    {
        return [
            'page' => request()->input('page', 1),
            'per_page' => min(request()->input('per_page', 20), 100), // 最大100条
            'sort_by' => request()->input('sort_by', 'created_at'),
            'sort_order' => request()->input('sort_order', 'desc')
        ];
    }

    /**
     * 应用排序
     */
    protected function applySorting($query, string $defaultColumn = 'created_at', string $defaultOrder = 'desc')
    {
        $sortBy = request()->input('sort_by', $defaultColumn);
        $sortOrder = request()->input('sort_order', $defaultOrder);

        // 验证排序字段和方向
        $allowedOrders = ['asc', 'desc'];
        if (!in_array(strtolower($sortOrder), $allowedOrders)) {
            $sortOrder = $defaultOrder;
        }

        return $query->orderBy($sortBy, $sortOrder);
    }

    /**
     * 应用搜索过滤
     */
    protected function applySearch($query, array $searchFields, string $searchTerm = null)
    {
        $searchTerm = $searchTerm ?: request()->input('search');
        
        if ($searchTerm && !empty($searchFields)) {
            $query->where(function ($q) use ($searchFields, $searchTerm) {
                foreach ($searchFields as $field) {
                    if (strpos($field, '.') !== false) {
                        // 关联字段搜索
                        [$relation, $column] = explode('.', $field, 2);
                        $q->orWhereHas($relation, function ($relationQuery) use ($column, $searchTerm) {
                            $relationQuery->where($column, 'like', "%{$searchTerm}%");
                        });
                    } else {
                        // 普通字段搜索
                        $q->orWhere($field, 'like', "%{$searchTerm}%");
                    }
                }
            });
        }

        return $query;
    }
}
