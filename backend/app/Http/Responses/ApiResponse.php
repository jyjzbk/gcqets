<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    /**
     * 成功响应
     */
    public static function success($data = null, string $message = '操作成功', int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'code' => $code,
            'timestamp' => now()->toISOString()
        ];

        if ($data !== null) {
            // 处理分页数据
            if ($data instanceof LengthAwarePaginator) {
                $response['data'] = [
                    'data' => $data->items(),
                    'pagination' => [
                        'current_page' => $data->currentPage(),
                        'per_page' => $data->perPage(),
                        'total' => $data->total(),
                        'last_page' => $data->lastPage(),
                        'from' => $data->firstItem(),
                        'to' => $data->lastItem(),
                        'has_more_pages' => $data->hasMorePages()
                    ]
                ];
            } else {
                $response['data'] = $data;
            }
        }

        return response()->json($response, $code);
    }

    /**
     * 错误响应
     */
    public static function error(string $message = '操作失败', int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'code' => $code,
            'timestamp' => now()->toISOString()
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * 验证错误响应
     */
    public static function validationError($errors, string $message = '数据验证失败'): JsonResponse
    {
        return self::error($message, 422, $errors);
    }

    /**
     * 未授权响应
     */
    public static function unauthorized(string $message = '未授权访问'): JsonResponse
    {
        return self::error($message, 401);
    }

    /**
     * 禁止访问响应
     */
    public static function forbidden(string $message = '禁止访问'): JsonResponse
    {
        return self::error($message, 403);
    }

    /**
     * 资源未找到响应
     */
    public static function notFound(string $message = '资源未找到'): JsonResponse
    {
        return self::error($message, 404);
    }

    /**
     * 服务器错误响应
     */
    public static function serverError(string $message = '服务器内部错误'): JsonResponse
    {
        return self::error($message, 500);
    }

    /**
     * 创建成功响应
     */
    public static function created($data = null, string $message = '创建成功'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    /**
     * 更新成功响应
     */
    public static function updated($data = null, string $message = '更新成功'): JsonResponse
    {
        return self::success($data, $message, 200);
    }

    /**
     * 删除成功响应
     */
    public static function deleted(string $message = '删除成功'): JsonResponse
    {
        return self::success(null, $message, 200);
    }

    /**
     * 无内容响应
     */
    public static function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * 分页响应
     */
    public static function paginated(LengthAwarePaginator $paginator, string $message = '获取数据成功'): JsonResponse
    {
        return self::success($paginator, $message);
    }

    /**
     * 列表响应
     */
    public static function collection($data, string $message = '获取列表成功'): JsonResponse
    {
        return self::success($data, $message);
    }

    /**
     * 详情响应
     */
    public static function show($data, string $message = '获取详情成功'): JsonResponse
    {
        return self::success($data, $message);
    }

    /**
     * 统计数据响应
     */
    public static function statistics($data, string $message = '获取统计数据成功'): JsonResponse
    {
        return self::success($data, $message);
    }

    /**
     * 选项数据响应
     */
    public static function options($data, string $message = '获取选项数据成功'): JsonResponse
    {
        return self::success($data, $message);
    }

    /**
     * 批量操作响应
     */
    public static function batch($data, string $message = '批量操作完成'): JsonResponse
    {
        return self::success($data, $message);
    }

    /**
     * 导出响应
     */
    public static function export($data, string $message = '导出成功'): JsonResponse
    {
        return self::success($data, $message);
    }

    /**
     * 上传响应
     */
    public static function upload($data, string $message = '上传成功'): JsonResponse
    {
        return self::success($data, $message);
    }

    /**
     * 自定义响应
     */
    public static function custom($data = null, string $message = '操作完成', int $code = 200, bool $success = true): JsonResponse
    {
        $response = [
            'success' => $success,
            'message' => $message,
            'code' => $code,
            'timestamp' => now()->toISOString()
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * 格式化异常响应
     */
    public static function exception(\Exception $e, string $message = null): JsonResponse
    {
        $message = $message ?: $e->getMessage();
        
        $response = [
            'success' => false,
            'message' => $message,
            'code' => 500,
            'timestamp' => now()->toISOString()
        ];

        // 在开发环境中添加调试信息
        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];
        }

        return response()->json($response, 500);
    }

    /**
     * 处理Laravel验证器错误
     */
    public static function validatorErrors($validator): JsonResponse
    {
        return self::validationError($validator->errors());
    }

    /**
     * 处理模型验证错误
     */
    public static function modelValidationError($errors): JsonResponse
    {
        return self::validationError($errors, '模型验证失败');
    }

    /**
     * 业务逻辑错误响应
     */
    public static function businessError(string $message, $data = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'code' => 400,
            'timestamp' => now()->toISOString()
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, 400);
    }

    /**
     * 操作确认响应
     */
    public static function confirm(string $message, $data = null): JsonResponse
    {
        return self::success($data, $message);
    }

    /**
     * 警告响应
     */
    public static function warning(string $message, $data = null): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'code' => 200,
            'warning' => true,
            'timestamp' => now()->toISOString()
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, 200);
    }
}
