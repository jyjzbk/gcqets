<template>
  <el-container class="layout-container">
    <!-- 侧边栏 -->
    <el-aside width="250px" class="sidebar">
      <div class="logo">
        <h2>实验教学管理系统</h2>
      </div>
      
      <el-menu
        :default-active="$route.path"
        class="sidebar-menu"
        router
        background-color="#304156"
        text-color="#bfcbd9"
        active-text-color="#409EFF"
      >
        <el-menu-item index="/">
          <el-icon><Monitor /></el-icon>
          <span>仪表板</span>
        </el-menu-item>
        
        <el-sub-menu index="/organizations">
          <template #title>
            <el-icon><OfficeBuilding /></el-icon>
            <span>组织机构管理</span>
          </template>
          <el-menu-item index="/organizations">组织机构列表</el-menu-item>
          <el-menu-item index="/organizations/create">创建组织机构</el-menu-item>
        </el-sub-menu>

        <el-sub-menu index="/schools">
          <template #title>
            <el-icon><School /></el-icon>
            <span>学校信息管理</span>
          </template>
          <el-menu-item index="/schools">学校管理</el-menu-item>
          <el-menu-item index="/schools/import">批量导入</el-menu-item>
          <el-menu-item index="/schools/dashboard">统计仪表板</el-menu-item>
        </el-sub-menu>
        
        <el-sub-menu index="/users">
          <template #title>
            <el-icon><User /></el-icon>
            <span>用户管理</span>
          </template>
          <el-menu-item index="/users">用户列表</el-menu-item>
          <el-menu-item index="/users/create">创建用户</el-menu-item>
        </el-sub-menu>
        
        <el-sub-menu index="/roles">
          <template #title>
            <el-icon><Key /></el-icon>
            <span>角色管理</span>
          </template>
          <el-menu-item index="/roles">角色列表</el-menu-item>
          <el-menu-item index="/roles/create">创建角色</el-menu-item>
        </el-sub-menu>

        <el-sub-menu index="permissions">
          <template #title>
            <el-icon><Grid /></el-icon>
            <span>权限管理</span>
          </template>
          <el-menu-item index="/education-permissions">
            <el-icon><School /></el-icon>
            <span>教育权限管理</span>
            <el-tag size="small" type="primary" style="margin-left: 8px">简化版</el-tag>
          </el-menu-item>
          <el-menu-item index="/permission-test">
            <el-icon><Grid /></el-icon>
            <span>权限可视化管理</span>
            <el-tag size="small" type="success" style="margin-left: 8px">完整版</el-tag>
          </el-menu-item>
        </el-sub-menu>

        <el-sub-menu index="/experiment">
          <template #title>
            <el-icon><DataAnalysis /></el-icon>
            <span>基础数据管理</span>
          </template>
          <el-menu-item index="/experiment-catalogs">
            <el-icon><Document /></el-icon>
            <span>实验目录管理</span>
          </el-menu-item>
          <el-menu-item index="/curriculum-standards">
            <el-icon><Reading /></el-icon>
            <span>课程标准管理</span>
          </el-menu-item>
          <el-menu-item index="/photo-templates">
            <el-icon><Picture /></el-icon>
            <span>照片模板管理</span>
          </el-menu-item>
        </el-sub-menu>

        <el-sub-menu index="/equipment">
          <template #title>
            <el-icon><Box /></el-icon>
            <span>设备物料管理</span>
          </template>
          <el-menu-item index="/equipment">
            <el-icon><Monitor /></el-icon>
            <span>设备管理</span>
          </el-menu-item>
          <el-menu-item index="/materials">
            <el-icon><Goods /></el-icon>
            <span>材料管理</span>
          </el-menu-item>
          <el-menu-item index="/equipment-borrowings">
            <el-icon><User /></el-icon>
            <span>设备借用管理</span>
          </el-menu-item>
          <el-menu-item index="/material-usages">
            <el-icon><DataLine /></el-icon>
            <span>材料使用记录</span>
          </el-menu-item>
        </el-sub-menu>

        <el-sub-menu index="/experiment-process">
          <template #title>
            <el-icon><Operation /></el-icon>
            <span>实验过程管理</span>
            <el-tag size="small" type="success" style="margin-left: 8px">新功能</el-tag>
          </template>
          <el-menu-item index="/experiment-plans">
            <el-icon><Calendar /></el-icon>
            <span>实验计划申报</span>
          </el-menu-item>
          <el-menu-item index="/experiment-records">
            <el-icon><EditPen /></el-icon>
            <span>实验记录填报</span>
          </el-menu-item>
          <el-menu-item
            v-if="canAccessReview"
            index="/experiment-review"
          >
            <el-icon><Select /></el-icon>
            <span>实验记录审核</span>
          </el-menu-item>
          <el-menu-item index="/experiment-calendar">
            <el-icon><Calendar /></el-icon>
            <span>实验日历</span>
          </el-menu-item>
          <el-menu-item index="/experiment-monitor">
            <el-icon><DataAnalysis /></el-icon>
            <span>实验执行监控</span>
          </el-menu-item>
          <el-menu-item index="/experiment-calendar">
            <el-icon><Calendar /></el-icon>
            <span>实验日历</span>
            <el-tag size="small" type="success" style="margin-left: 8px">新功能</el-tag>
          </el-menu-item>
          <el-menu-item
            v-if="canAccessTest"
            index="/system-test"
          >
            <el-icon><Tools /></el-icon>
            <span>系统功能测试</span>
            <el-tag size="small" type="warning" style="margin-left: 8px">测试</el-tag>
          </el-menu-item>
          <el-menu-item index="/menu-test">
            <el-icon><Grid /></el-icon>
            <span>菜单功能测试</span>
            <el-tag size="small" type="info" style="margin-left: 8px">调试</el-tag>
          </el-menu-item>
          <el-menu-item index="/icon-test">
            <el-icon><Picture /></el-icon>
            <span>图标测试</span>
            <el-tag size="small" type="info" style="margin-left: 8px">调试</el-tag>
          </el-menu-item>
          <el-menu-item index="/role-test">
            <el-icon><User /></el-icon>
            <span>角色权限测试</span>
            <el-tag size="small" type="info" style="margin-left: 8px">调试</el-tag>
          </el-menu-item>
          <el-menu-item index="/calendar-test">
            <el-icon><Calendar /></el-icon>
            <span>日历功能测试</span>
            <el-tag size="small" type="info" style="margin-left: 8px">调试</el-tag>
          </el-menu-item>
          <el-menu-item index="/monitor-test">
            <el-icon><DataAnalysis /></el-icon>
            <span>监控功能测试</span>
            <el-tag size="small" type="info" style="margin-left: 8px">调试</el-tag>
          </el-menu-item>
          <el-menu-item index="/import-test">
            <el-icon><Tools /></el-icon>
            <span>导入测试</span>
            <el-tag size="small" type="warning" style="margin-left: 8px">修复</el-tag>
          </el-menu-item>
        </el-sub-menu>
      </el-menu>
    </el-aside>
    
    <!-- 主内容区 -->
    <el-container>
      <!-- 顶部导航 -->
      <el-header class="header">
        <div class="header-left">
          <el-breadcrumb separator="/">
            <el-breadcrumb-item :to="{ path: '/' }">首页</el-breadcrumb-item>
            <el-breadcrumb-item v-if="$route.meta.title">{{ $route.meta.title }}</el-breadcrumb-item>
          </el-breadcrumb>
        </div>
        
        <div class="header-right">
          <el-dropdown @command="handleCommand">
            <span class="user-info">
              <el-avatar :size="32" :src="authStore.user?.avatar">
                {{ authStore.user?.real_name?.charAt(0) }}
              </el-avatar>
              <span class="username">{{ authStore.user?.real_name }}</span>
              <el-icon><ArrowDown /></el-icon>
            </span>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item command="profile">个人信息</el-dropdown-item>
                <el-dropdown-item command="password">修改密码</el-dropdown-item>
                <el-dropdown-item divided command="logout">退出登录</el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
        </div>
      </el-header>
      
      <!-- 内容区 -->
      <el-main class="main-content">
        <router-view />
      </el-main>
    </el-container>
  </el-container>
</template>

<script setup>
import { useAuthStore } from '../../stores/auth'
import { useRouter } from 'vue-router'
import { ElMessageBox } from 'element-plus'
import { computed } from 'vue'
import {
  Monitor,
  OfficeBuilding,
  User,
  Key,
  Grid,
  ArrowDown,
  School,
  DataAnalysis,
  Document,
  Reading,
  Picture,
  Box,
  Goods,
  TrendCharts,
  DataLine,
  Operation,
  Calendar,
  EditPen,
  Select,
  Tools
} from '@element-plus/icons-vue'

const authStore = useAuthStore()
const router = useRouter()

// 权限检查
const isAdmin = computed(() => {
  return authStore.user?.user_type === 'admin'
})

const isManager = computed(() => {
  const userType = authStore.user?.user_type
  return ['admin', 'school_admin', 'district_admin'].includes(userType)
})

const isTeacher = computed(() => {
  const userType = authStore.user?.user_type
  return userType === 'teacher'
})

const canAccessReview = computed(() => {
  // 只有管理员可以访问审核功能
  return isManager.value
})

const canAccessTest = computed(() => {
  // 系统管理员和开发测试时可以访问测试功能
  return isAdmin.value || import.meta.env.DEV
})

const handleCommand = async (command) => {
  switch (command) {
    case 'profile':
      // 跳转到个人信息页面
      break
    case 'password':
      // 跳转到修改密码页面
      break
    case 'logout':
      try {
        await ElMessageBox.confirm('确定要退出登录吗？', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        })
        await authStore.logout()
        router.push('/login')
      } catch {
        // 用户取消
      }
      break
  }
}
</script>

<style scoped>
.layout-container {
  height: 100vh;
}

.sidebar {
  background-color: #304156;
  color: #bfcbd9;
}

.logo {
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #2b2f3a;
  color: #fff;
}

.logo h2 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
}

.sidebar-menu {
  border: none;
}

.header {
  background-color: #fff;
  border-bottom: 1px solid #e6e6e6;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
}

.header-left {
  flex: 1;
}

.header-right {
  display: flex;
  align-items: center;
}

.user-info {
  display: flex;
  align-items: center;
  cursor: pointer;
  padding: 8px 12px;
  border-radius: 4px;
  transition: background-color 0.3s;
}

.user-info:hover {
  background-color: #f5f5f5;
}

.username {
  margin: 0 8px;
  font-size: 14px;
  color: #333;
}

.main-content {
  background-color: #f0f2f5;
  padding: 20px;
}
</style> 