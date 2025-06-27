# 角色管理API 500错误解决方案

## 问题分析

角色管理中"查看"和"编辑"功能返回500错误的根本原因：

1. **认证要求**：所有角色管理API都需要认证（`auth:sanctum`中间件）
2. **用户未登录**：前端调用API时没有提供有效的认证token
3. **权限验证失败**：RoleController的show方法中有权限检查逻辑

## 解决步骤

### 1. 确保数据库数据完整

```bash
cd backend
php artisan migrate:fresh --seed
```

这将重新创建数据库表并填充测试数据，包括：
- 系统管理员用户：`sysadmin` / `123456`
- 学区管理员用户：`lianzhou_admin` / `123456`
- 校长用户：`dongcheng_principal` / `123456`

### 2. 启动后端服务

```bash
cd backend
php artisan serve --host=localhost --port=8000
```

### 3. 启动前端服务

```bash
cd frontend
npm run dev
```

### 4. 正确的使用流程

1. **登录系统**
   - 访问前端应用（通常是 http://localhost:3000 或 http://localhost:5173）
   - 使用测试账户登录：
     - 用户名：`sysadmin`
     - 密码：`123456`

2. **访问角色管理**
   - 登录成功后，导航到"角色管理"页面
   - 现在可以正常使用"查看"和"编辑"功能

### 5. API认证流程说明

前端的认证流程：
1. 用户登录 → 调用 `/api/auth/login`
2. 后端返回token → 前端存储在localStorage
3. 后续API调用 → 自动在请求头中添加 `Authorization: Bearer {token}`

### 6. 权限验证逻辑

RoleController.show方法的权限检查：
```php
// 检查用户是否有权限查看该角色
if (!$user->canManageLevel($role->level)) {
    return response()->json([
        'message' => '无权查看该角色',
        'code' => 403
    ], 403);
}
```

用户只能查看和管理比自己权限级别低的角色。

## 测试验证

### 手动测试步骤

1. 确保后端服务运行在 http://localhost:8000
2. 打开前端应用
3. 使用 `sysadmin` / `123456` 登录
4. 导航到角色管理页面
5. 点击任意角色的"查看"或"编辑"按钮
6. 应该能正常显示角色详情

### API测试

可以使用以下curl命令测试：

```bash
# 1. 登录获取token
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"sysadmin","password":"123456"}'

# 2. 使用token访问角色API（替换YOUR_TOKEN为实际token）
curl -X GET http://localhost:8000/api/roles/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

## 常见问题

### Q: 仍然返回401 Unauthenticated错误
A: 检查：
- 用户是否已登录
- token是否正确存储在localStorage
- 请求头是否正确设置Authorization

### Q: 返回403权限不足错误
A: 检查：
- 当前用户的权限级别
- 要查看的角色的权限级别
- 用户只能管理比自己级别低的角色

### Q: 前端无法连接到后端
A: 检查：
- 后端服务是否正在运行
- 端口是否正确（默认8000）
- CORS配置是否正确

## 技术细节

### 认证中间件
```php
Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('roles')->group(function () {
        Route::get('/{role}', [RoleController::class, 'show']);
        // ...
    });
});
```

### 前端请求拦截器
```javascript
request.interceptors.request.use((config) => {
    const authStore = useAuthStore()
    if (authStore.token) {
        config.headers.Authorization = `Bearer ${authStore.token}`
    }
    return config
})
```

这个解决方案应该能完全解决角色管理API的500错误问题。
