# 角色管理API 500错误 - 完整解决方案

## 问题描述
在"角色管理"中"角色列表"的操作中，点击"查看"或"编辑"按键时出现500错误：
```
GET http://localhost:8000/api/roles/1 500 (Internal Server Error)
```

## 根本原因分析

### 1. 认证问题
- 所有角色管理API都需要认证（`auth:sanctum`中间件）
- 用户未登录时没有有效的认证token
- 前端调用API时无法通过认证验证

### 2. 数据模型不一致
- Role模型中使用了`is_system`字段，但数据库迁移中使用的是`role_type`字段
- 导致模型操作时出现字段不存在的错误

### 3. 权限验证逻辑
- RoleController的show方法中有权限级别检查
- 用户只能查看比自己权限级别低的角色

## 解决方案

### 1. 修复数据模型不一致问题

**修改Role模型** (`backend/app/Models/Role.php`)：
- 将`is_system`字段改为`role_type`字段
- 更新fillable数组、casts数组
- 修复相关方法中的字段引用

**关键修改**：
```php
// 修改前
'is_system' => 'boolean',
return $this->is_system;
->where('is_system', true)

// 修改后  
// 移除is_system相关配置
return $this->role_type === 'system';
->where('role_type', 'system')
```

### 2. 确保测试数据完整性

**运行数据修复脚本**：
```bash
php quick_fix.php
```

**创建的测试数据**：
- 系统管理员用户：`sysadmin` / `123456`
- 系统管理员角色（level=1）
- 其他测试角色（组织管理员、部门管理员、普通用户）

### 3. 正确的使用流程

1. **启动服务**：
   ```bash
   # 后端
   cd backend
   php artisan serve --host=localhost --port=8000
   
   # 前端
   cd frontend  
   npm run dev
   ```

2. **用户登录**：
   - 访问前端应用
   - 使用`sysadmin` / `123456`登录
   - 系统会自动获取并存储认证token

3. **使用角色管理**：
   - 导航到角色管理页面
   - 点击"查看"或"编辑"按钮
   - 现在应该能正常工作

## 技术细节

### 认证流程
```javascript
// 1. 登录获取token
const response = await authApi.login(credentials)
const { token } = response.data.data
localStorage.setItem('token', token)

// 2. 后续请求自动添加认证头
config.headers.Authorization = `Bearer ${token}`
```

### 权限验证逻辑
```php
// RoleController.show方法中的权限检查
if (!$user->canManageLevel($role->level)) {
    return response()->json([
        'message' => '无权查看该角色',
        'code' => 403
    ], 403);
}
```

### 数据库字段映射
```php
// 数据库表结构
'role_type' => enum('system', 'custom')
'status' => enum('active', 'inactive')

// 模型方法
public function isSystemRole(): bool {
    return $this->role_type === 'system';
}
```

## 验证测试

### API测试命令
```bash
# 1. 登录获取token
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"sysadmin","password":"123456"}'

# 2. 使用token访问角色API
curl -X GET http://localhost:8000/api/roles/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### 预期结果
- 登录成功返回200状态码和token
- 角色API返回200状态码和角色详情数据
- 前端页面能正常显示角色信息

## 常见问题排查

### Q: 仍然返回401错误
**检查项**：
- 用户是否已登录
- token是否正确存储在localStorage
- 请求头是否包含Authorization

### Q: 返回403权限不足
**检查项**：
- 当前用户的权限级别
- 要查看角色的权限级别  
- 确保用户级别低于要查看的角色级别

### Q: 前端无法连接后端
**检查项**：
- 后端服务是否在8000端口运行
- 前端API配置是否正确
- 网络连接是否正常

## 总结

通过修复数据模型不一致问题、确保测试数据完整性，以及遵循正确的认证流程，角色管理的"查看"和"编辑"功能现在应该能够正常工作。关键是要确保用户先登录获取有效的认证token，然后才能访问需要认证的API端点。
