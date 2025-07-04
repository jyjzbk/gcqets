<template>
  <div class="menu-test">
    <el-card>
      <template #header>
        <h3>菜单功能测试</h3>
      </template>
      
      <div class="test-info">
        <h4>当前用户信息</h4>
        <el-descriptions :column="2" border>
          <el-descriptions-item label="用户名">{{ user?.username }}</el-descriptions-item>
          <el-descriptions-item label="真实姓名">{{ user?.real_name }}</el-descriptions-item>
          <el-descriptions-item label="用户类型">{{ user?.user_type }}</el-descriptions-item>
          <el-descriptions-item label="组织ID">{{ user?.organization_id }}</el-descriptions-item>
        </el-descriptions>
      </div>

      <div class="menu-permissions">
        <h4>菜单权限检查</h4>
        <el-table :data="menuPermissions" border>
          <el-table-column prop="menu" label="菜单项" />
          <el-table-column prop="permission" label="权限要求" />
          <el-table-column prop="hasAccess" label="是否可访问">
            <template #default="{ row }">
              <el-tag :type="row.hasAccess ? 'success' : 'danger'">
                {{ row.hasAccess ? '可访问' : '无权限' }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="reason" label="说明" />
        </el-table>
      </div>

      <div class="navigation-test">
        <h4>导航测试</h4>
        <el-space wrap>
          <el-button type="primary" @click="navigateTo('/experiment-plans')">
            实验计划申报
          </el-button>
          <el-button type="primary" @click="navigateTo('/experiment-records')">
            实验记录填报
          </el-button>
          <el-button
            v-if="canAccessReview"
            type="success"
            @click="navigateTo('/experiment-review')"
          >
            实验记录审核
          </el-button>
          <el-button
            v-if="canAccessTest"
            type="warning"
            @click="navigateTo('/system-test')"
          >
            系统功能测试
          </el-button>
          <el-button
            type="info"
            @click="navigateTo('/menu-test')"
          >
            菜单功能测试
          </el-button>
          <el-button
            type="info"
            @click="navigateTo('/icon-test')"
          >
            图标测试
          </el-button>
        </el-space>
      </div>

      <div class="route-info">
        <h4>当前路由信息</h4>
        <el-descriptions :column="1" border>
          <el-descriptions-item label="路径">{{ $route.path }}</el-descriptions-item>
          <el-descriptions-item label="名称">{{ $route.name }}</el-descriptions-item>
          <el-descriptions-item label="标题">{{ $route.meta?.title }}</el-descriptions-item>
        </el-descriptions>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { ElMessage } from 'element-plus'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

// 用户信息
const user = computed(() => authStore.user)

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
  return isManager.value
})

const canAccessTest = computed(() => {
  return isAdmin.value || import.meta.env.DEV
})

// 菜单权限数据
const menuPermissions = computed(() => [
  {
    menu: '实验计划申报',
    permission: '所有登录用户',
    hasAccess: true,
    reason: '教师创建计划，管理员审批'
  },
  {
    menu: '实验记录填报',
    permission: '所有登录用户',
    hasAccess: true,
    reason: '教师填报记录，管理员审核'
  },
  {
    menu: '实验记录审核',
    permission: '管理员 (admin, school_admin, district_admin)',
    hasAccess: canAccessReview.value,
    reason: '只有管理员可以审核实验记录'
  },
  {
    menu: '系统功能测试',
    permission: '系统管理员 (admin) 或开发环境',
    hasAccess: canAccessTest.value,
    reason: '系统管理员或开发环境可以访问测试功能'
  },
  {
    menu: '菜单功能测试',
    permission: '所有登录用户',
    hasAccess: true,
    reason: '用于测试菜单权限和导航功能'
  },
  {
    menu: '图标测试',
    permission: '所有登录用户',
    hasAccess: true,
    reason: '用于测试图标显示是否正常'
  }
])

// 导航方法
const navigateTo = (path) => {
  try {
    router.push(path)
    ElMessage.success(`正在跳转到：${path}`)
  } catch (error) {
    ElMessage.error(`导航失败：${error.message}`)
  }
}
</script>

<style scoped>
.menu-test {
  padding: 20px;
}

.test-info,
.menu-permissions,
.navigation-test,
.route-info {
  margin-bottom: 30px;
}

.test-info h4,
.menu-permissions h4,
.navigation-test h4,
.route-info h4 {
  margin-bottom: 15px;
  color: #303133;
  font-size: 16px;
}
</style>
