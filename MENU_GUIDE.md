# 菜单功能说明

## 📋 新增菜单项

我们已经在左侧菜单栏中添加了新的"实验过程管理"菜单组，包含以下功能：

### 🔧 实验过程管理

#### 1. 实验计划申报 📅
- **路径**: `/experiment-plans`
- **权限**: 所有登录用户
- **功能**: 创建、编辑、提交实验计划
- **图标**: Calendar (日历)

#### 2. 实验记录填报 ✏️
- **路径**: `/experiment-records`
- **权限**: 所有登录用户
- **功能**: 填写实验执行记录，上传照片
- **图标**: EditPen (编辑笔)

#### 3. 实验记录审核 ✅
- **路径**: `/experiment-review`
- **权限**: 管理员 (admin, school_admin, district_admin)
- **功能**: 审核实验记录，批量操作
- **图标**: Select (选择)
- **特殊**: 只有管理员角色可见

#### 4. 系统功能测试 🔧
- **路径**: `/system-test`
- **权限**: 系统管理员 (admin)
- **功能**: 系统功能测试和性能测试
- **图标**: Tools (工具)
- **特殊**: 只有系统管理员可见

#### 5. 菜单功能测试 🧪
- **路径**: `/menu-test`
- **权限**: 所有登录用户
- **功能**: 测试菜单权限和导航功能
- **图标**: Grid (网格)
- **特殊**: 调试用途

## 🔐 权限控制

### 用户角色权限

| 功能 | 普通用户 | 教师 | 学校管理员 | 区域管理员 | 系统管理员 |
|------|----------|------|------------|------------|------------|
| 实验计划申报 | ✅ | ✅ | ✅ | ✅ | ✅ |
| 实验记录填报 | ✅ | ✅ | ✅ | ✅ | ✅ |
| 实验记录审核 | ❌ | ❌ | ✅ | ✅ | ✅ |
| 系统功能测试 | ❌ | ❌ | ❌ | ❌ | ✅ |
| 菜单功能测试 | ✅ | ✅ | ✅ | ✅ | ✅ |

### 权限实现

权限控制通过Vue的计算属性实现：

```javascript
// 系统管理员检查
const isAdmin = computed(() => {
  return authStore.user?.user_type === 'admin'
})

// 审核权限检查
const canAccessReview = computed(() => {
  const userType = authStore.user?.user_type
  return ['admin', 'school_admin', 'district_admin'].includes(userType)
})
```

## 🎨 菜单样式

### 视觉标识

- **新功能标签**: 绿色 "新功能" 标签标识新开发的功能
- **测试标签**: 橙色 "测试" 标签标识测试功能
- **调试标签**: 蓝色 "调试" 标签标识调试功能

### 图标设计

所有菜单项都使用Element Plus图标库中的图标：
- 📅 Calendar: 计划相关
- ✏️ EditPen: 编辑相关
- ✅ Select: 选择/审核相关
- 🔧 Tools: 工具相关
- 🧪 Grid: 网格/测试相关

## 🧭 导航测试

### 测试方法

1. **访问菜单测试页面**: `/menu-test`
2. **查看权限表格**: 显示当前用户对各菜单的访问权限
3. **测试导航按钮**: 点击按钮测试页面跳转
4. **检查路由信息**: 查看当前路由的详细信息

### 测试账号

使用不同角色的测试账号验证权限：

```
系统管理员：sysadmin / 123456
学校管理员：dongcheng_principal / 123456
区域管理员：lianzhou_admin / 123456
教师账号：hh78@163.com / 123456
```

## 🔧 技术实现

### 菜单结构

菜单在 `frontend/src/views/layout/Layout.vue` 中定义：

```vue
<el-sub-menu index="/experiment-process">
  <template #title>
    <el-icon><Operation /></el-icon>
    <span>实验过程管理</span>
    <el-tag size="small" type="success">新功能</el-tag>
  </template>
  <!-- 子菜单项 -->
</el-sub-menu>
```

### 路由配置

路由在 `frontend/src/router/index.js` 中配置：

```javascript
{
  path: 'experiment-plans',
  name: 'ExperimentPlans',
  component: () => import('../views/experiment-process/plan/PlanList.vue'),
  meta: { title: '实验计划申报' }
}
```

### 权限控制

使用Vue的条件渲染实现权限控制：

```vue
<el-menu-item 
  v-if="canAccessReview" 
  index="/experiment-review"
>
  <el-icon><Select /></el-icon>
  <span>实验记录审核</span>
</el-menu-item>
```

## 📱 响应式设计

菜单支持响应式设计，在不同屏幕尺寸下都能正常显示和使用。

## 🔄 后续优化

1. **动态权限**: 可以考虑从后端动态获取用户权限
2. **菜单收藏**: 添加常用菜单收藏功能
3. **搜索功能**: 添加菜单搜索功能
4. **面包屑导航**: 完善面包屑导航显示
5. **快捷键**: 添加键盘快捷键支持

## 🎯 使用建议

1. **首次使用**: 建议先访问"菜单功能测试"页面了解权限
2. **功能测试**: 使用"系统功能测试"页面验证API功能
3. **权限测试**: 使用不同角色账号测试权限控制
4. **导航测试**: 确保所有菜单项都能正常跳转

---

**菜单功能已完成！** 🎉 现在用户可以通过左侧菜单栏访问所有新开发的功能模块。
