<template>
  <div class="role-test">
    <el-card>
      <template #header>
        <h3>角色权限测试</h3>
        <p>快速切换不同角色测试菜单权限</p>
      </template>

      <!-- 当前用户信息 -->
      <div class="current-user">
        <h4>当前登录用户</h4>
        <el-descriptions :column="2" border>
          <el-descriptions-item label="用户名">{{ currentUser?.username }}</el-descriptions-item>
          <el-descriptions-item label="真实姓名">{{ currentUser?.real_name }}</el-descriptions-item>
          <el-descriptions-item label="用户类型">
            <el-tag :type="getUserTypeColor(currentUser?.user_type)">
              {{ getUserTypeLabel(currentUser?.user_type) }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="组织ID">{{ currentUser?.organization_id }}</el-descriptions-item>
        </el-descriptions>
      </div>

      <!-- 快速登录测试 -->
      <div class="quick-login">
        <h4>快速切换测试账号</h4>
        <el-space wrap>
          <el-button 
            type="danger" 
            @click="quickLogin('sysadmin', '123456')"
            :loading="loginLoading"
          >
            系统管理员
          </el-button>
          <el-button 
            type="warning" 
            @click="quickLogin('dongcheng_principal', '123456')"
            :loading="loginLoading"
          >
            学校管理员
          </el-button>
          <el-button 
            type="primary" 
            @click="quickLogin('lianzhou_admin', '123456')"
            :loading="loginLoading"
          >
            区域管理员
          </el-button>
          <el-button 
            type="success" 
            @click="quickLogin('hh78@163.com', '123456')"
            :loading="loginLoading"
          >
            教师
          </el-button>
        </el-space>
      </div>

      <!-- 权限矩阵 -->
      <div class="permission-matrix">
        <h4>当前用户权限矩阵</h4>
        <el-table :data="permissionMatrix" border>
          <el-table-column prop="feature" label="功能模块" width="200" />
          <el-table-column prop="permission" label="权限要求" width="200" />
          <el-table-column prop="hasAccess" label="是否可访问" width="120">
            <template #default="{ row }">
              <el-tag :type="row.hasAccess ? 'success' : 'danger'">
                {{ row.hasAccess ? '✅ 可访问' : '❌ 无权限' }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="description" label="说明" />
        </el-table>
      </div>

      <!-- 菜单可见性测试 -->
      <div class="menu-visibility">
        <h4>菜单可见性测试</h4>
        <el-alert
          title="提示"
          description="切换不同角色后，左侧菜单栏会根据权限动态显示不同的菜单项"
          type="info"
          show-icon
          :closable="false"
        />
        
        <div class="menu-items">
          <div class="menu-item" v-for="item in menuItems" :key="item.path">
            <div class="menu-info">
              <el-icon>
                <component :is="item.icon" />
              </el-icon>
              <span class="menu-name">{{ item.name }}</span>
            </div>
            <el-tag :type="item.visible ? 'success' : 'info'">
              {{ item.visible ? '可见' : '隐藏' }}
            </el-tag>
          </div>
        </div>
      </div>

      <!-- 测试建议 -->
      <div class="test-suggestions">
        <h4>测试建议</h4>
        <el-steps :active="currentStep" direction="vertical">
          <el-step title="系统管理员测试" description="登录系统管理员账号，应该能看到所有菜单项" />
          <el-step title="学校管理员测试" description="登录学校管理员账号，应该能看到除系统测试外的所有功能" />
          <el-step title="教师测试" description="登录教师账号，应该只能看到基础功能和实验相关功能" />
          <el-step title="权限验证" description="尝试直接访问无权限的URL，应该被拒绝或重定向" />
        </el-steps>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import { 
  Calendar, 
  EditPen, 
  Select, 
  Tools, 
  Grid, 
  Picture 
} from '@element-plus/icons-vue'

const authStore = useAuthStore()
const router = useRouter()

// 响应式数据
const loginLoading = ref(false)
const currentStep = ref(0)

// 当前用户
const currentUser = computed(() => authStore.user)

// 权限检查
const isAdmin = computed(() => currentUser.value?.user_type === 'admin')
const isManager = computed(() => ['admin', 'school_admin', 'district_admin'].includes(currentUser.value?.user_type))
const isTeacher = computed(() => currentUser.value?.user_type === 'teacher')

// 权限矩阵
const permissionMatrix = computed(() => [
  {
    feature: '实验计划申报',
    permission: '所有登录用户',
    hasAccess: true,
    description: '教师创建计划，管理员审批'
  },
  {
    feature: '实验记录填报',
    permission: '所有登录用户',
    hasAccess: true,
    description: '教师填报记录，管理员审核'
  },
  {
    feature: '实验记录审核',
    permission: '管理员',
    hasAccess: isManager.value,
    description: '只有管理员可以审核实验记录'
  },
  {
    feature: '系统功能测试',
    permission: '系统管理员',
    hasAccess: isAdmin.value,
    description: '系统管理员专用的测试功能'
  },
  {
    feature: '菜单功能测试',
    permission: '所有登录用户',
    hasAccess: true,
    description: '用于测试菜单权限的调试功能'
  },
  {
    feature: '图标测试',
    permission: '所有登录用户',
    hasAccess: true,
    description: '用于测试图标显示的调试功能'
  }
])

// 菜单项可见性
const menuItems = computed(() => [
  {
    name: '实验计划申报',
    path: '/experiment-plans',
    icon: 'Calendar',
    visible: true
  },
  {
    name: '实验记录填报',
    path: '/experiment-records',
    icon: 'EditPen',
    visible: true
  },
  {
    name: '实验记录审核',
    path: '/experiment-review',
    icon: 'Select',
    visible: isManager.value
  },
  {
    name: '系统功能测试',
    path: '/system-test',
    icon: 'Tools',
    visible: isAdmin.value
  },
  {
    name: '菜单功能测试',
    path: '/menu-test',
    icon: 'Grid',
    visible: true
  },
  {
    name: '图标测试',
    path: '/icon-test',
    icon: 'Picture',
    visible: true
  }
])

// 方法
const getUserTypeLabel = (userType) => {
  const labels = {
    'admin': '系统管理员',
    'school_admin': '学校管理员',
    'district_admin': '区域管理员',
    'teacher': '教师'
  }
  return labels[userType] || userType
}

const getUserTypeColor = (userType) => {
  const colors = {
    'admin': 'danger',
    'school_admin': 'warning',
    'district_admin': 'primary',
    'teacher': 'success'
  }
  return colors[userType] || 'info'
}

const quickLogin = async (username, password) => {
  loginLoading.value = true
  try {
    await authStore.login({ username, password })
    ElMessage.success(`已切换到：${getUserTypeLabel(authStore.user?.user_type)}`)
    
    // 刷新页面以更新菜单
    setTimeout(() => {
      window.location.reload()
    }, 1000)
  } catch (error) {
    ElMessage.error('登录失败：' + error.message)
  } finally {
    loginLoading.value = false
  }
}

// 初始化
onMounted(() => {
  // 根据当前用户类型设置测试步骤
  if (isAdmin.value) {
    currentStep.value = 0
  } else if (isManager.value) {
    currentStep.value = 1
  } else if (isTeacher.value) {
    currentStep.value = 2
  } else {
    currentStep.value = 3
  }
})
</script>

<style scoped>
.role-test {
  padding: 20px;
}

.current-user,
.quick-login,
.permission-matrix,
.menu-visibility,
.test-suggestions {
  margin-bottom: 30px;
}

.current-user h4,
.quick-login h4,
.permission-matrix h4,
.menu-visibility h4,
.test-suggestions h4 {
  margin-bottom: 15px;
  color: #303133;
  font-size: 16px;
}

.menu-items {
  margin-top: 15px;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 10px;
}

.menu-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
  border: 1px solid #ebeef5;
  border-radius: 4px;
  background-color: #fafafa;
}

.menu-info {
  display: flex;
  align-items: center;
  gap: 8px;
}

.menu-name {
  font-weight: 500;
}
</style>
