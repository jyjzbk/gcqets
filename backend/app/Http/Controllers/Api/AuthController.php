<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * 用户登录
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['username', 'password']);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // 检查用户状态
            if (!$user->status) {
                Auth::logout();
                return response()->json([
                    'message' => '账户已被禁用',
                    'code' => 403
                ], 403);
            }

            // 更新最后登录信息
            $user->updateLastLogin($request->ip());

            // 创建访问令牌
            $token = $user->createToken('auth-token')->plainTextToken;

            // 加载用户相关数据
            $user->load(['organizations', 'roles', 'primaryOrganization']);

            return response()->json([
                'message' => '登录成功',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer'
                ],
                'code' => 200
            ]);
        }

        return response()->json([
            'message' => '用户名或密码错误',
            'code' => 401
        ], 401);
    }

    /**
     * 用户登出
     */
    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if ($user) {
            // 删除当前令牌
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => '登出成功',
            'code' => 200
        ]);
    }

    /**
     * 获取当前用户信息
     */
    public function user(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => '未认证的用户',
                'code' => 401
            ], 401);
        }

        // 暂时不加载任何关联数据，测试性能
        // $user->load(['primaryOrganization']);

        return response()->json([
            'message' => '获取用户信息成功',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
            ],
            'code' => 200
        ]);
    }

    /**
     * 刷新令牌
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => '未认证的用户',
                'code' => 401
            ], 401);
        }

        // 删除旧令牌
        $user->currentAccessToken()->delete();

        // 创建新令牌
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => '令牌刷新成功',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer'
            ],
            'code' => 200
        ]);
    }
} 