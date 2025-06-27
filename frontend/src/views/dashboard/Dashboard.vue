<template>
  <div class="dashboard">
    <div class="page-header">
      <h2>仪表板</h2>
      <el-button @click="handleRefresh">
        <el-icon><Refresh /></el-icon>
        刷新
      </el-button>
    </div>
    
    <!-- 统计卡片 -->
    <el-row :gutter="20" class="stats-row">
      <el-col :span="6">
        <el-card class="stats-card">
          <div class="stats-content">
            <div class="stats-icon organization">
              <el-icon><OfficeBuilding /></el-icon>
            </div>
            <div class="stats-info">
              <div class="stats-number">{{ stats.organizations }}</div>
              <div class="stats-label">组织机构</div>
            </div>
          </div>
        </el-card>
      </el-col>
      
      <el-col :span="6">
        <el-card class="stats-card">
          <div class="stats-content">
            <div class="stats-icon user">
              <el-icon><User /></el-icon>
            </div>
            <div class="stats-info">
              <div class="stats-number">{{ stats.users }}</div>
              <div class="stats-label">用户总数</div>
            </div>
          </div>
        </el-card>
      </el-col>
      
      <el-col :span="6">
        <el-card class="stats-card">
          <div class="stats-content">
            <div class="stats-icon role">
              <el-icon><Key /></el-icon>
            </div>
            <div class="stats-info">
              <div class="stats-number">{{ stats.roles }}</div>
              <div class="stats-label">角色总数</div>
            </div>
          </div>
        </el-card>
      </el-col>
      
      <el-col :span="6">
        <el-card class="stats-card">
          <div class="stats-content">
            <div class="stats-icon permission">
              <el-icon><Lock /></el-icon>
            </div>
            <div class="stats-info">
              <div class="stats-number">{{ stats.permissions }}</div>
              <div class="stats-label">权限总数</div>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>
    
    <!-- 图表区域 -->
    <el-row :gutter="20" class="charts-row">
      <el-col :span="12">
        <el-card>
          <template #header>
            <div class="card-header">
              <span>组织架构分布</span>
            </div>
          </template>
          <div class="chart-container">
            <div ref="organizationChartRef" style="height: 300px;"></div>
          </div>
        </el-card>
      </el-col>
      
      <el-col :span="12">
        <el-card>
          <template #header>
            <div class="card-header">
              <span>用户角色分布</span>
            </div>
          </template>
          <div class="chart-container">
            <div ref="roleChartRef" style="height: 300px;"></div>
          </div>
        </el-card>
      </el-col>
    </el-row>
    
    <!-- 最近活动 -->
    <el-row :gutter="20" class="activity-row">
      <el-col :span="12">
        <el-card>
          <template #header>
            <div class="card-header">
              <span>最近登录用户</span>
            </div>
          </template>
          <el-table :data="recentUsers" style="width: 100%">
            <el-table-column prop="real_name" label="用户" width="120" />
            <el-table-column prop="last_login_at" label="登录时间" width="180">
              <template #default="{ row }">
                {{ formatDate(row.last_login_at) }}
              </template>
            </el-table-column>
            <el-table-column prop="last_login_ip" label="登录IP" />
          </el-table>
        </el-card>
      </el-col>
      
      <el-col :span="12">
        <el-card>
          <template #header>
            <div class="card-header">
              <span>系统信息</span>
            </div>
          </template>
          <el-descriptions :column="1" border>
            <el-descriptions-item label="系统名称">
              实验教学管理系统
            </el-descriptions-item>
            <el-descriptions-item label="当前用户">
              {{ authStore.user?.real_name }}
            </el-descriptions-item>
            <el-descriptions-item label="用户角色">
              <el-tag
                v-for="role in authStore.userRoles"
                :key="role.id"
                style="margin-right: 4px"
              >
                {{ role.display_name }}
              </el-tag>
            </el-descriptions-item>
            <el-descriptions-item label="主要组织">
              {{ authStore.user?.primary_organization?.name }}
            </el-descriptions-item>
            <el-descriptions-item label="登录时间">
              {{ formatDate(authStore.user?.last_login_at) }}
            </el-descriptions-item>
          </el-descriptions>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { formatDate } from '../../utils/date'
import * as echarts from 'echarts'

const authStore = useAuthStore()

// 统计数据
const stats = ref({
  organizations: 0,
  users: 0,
  roles: 0,
  permissions: 0
})

// 最近用户
const recentUsers = ref([])

// 图表实例
let organizationChart = null
let roleChart = null

// 图表容器引用
const organizationChartRef = ref()
const roleChartRef = ref()

// 获取统计数据
const getStats = async () => {
  // 这里应该调用后端API获取统计数据
  // 暂时使用模拟数据
  stats.value = {
    organizations: 25,
    users: 156,
    roles: 8,
    permissions: 45
  }
}

// 获取最近用户
const getRecentUsers = async () => {
  // 这里应该调用后端API获取最近登录用户
  // 暂时使用模拟数据
  recentUsers.value = [
    {
      real_name: '张三',
      last_login_at: new Date(),
      last_login_ip: '192.168.1.100'
    },
    {
      real_name: '李四',
      last_login_at: new Date(Date.now() - 3600000),
      last_login_ip: '192.168.1.101'
    }
  ]
}

// 初始化组织架构图表
const initOrganizationChart = () => {
  if (organizationChartRef.value) {
    organizationChart = echarts.init(organizationChartRef.value)
    
    const option = {
      title: {
        text: '组织架构层级分布',
        left: 'center'
      },
      tooltip: {
        trigger: 'item'
      },
      series: [
        {
          name: '组织数量',
          type: 'pie',
          radius: '50%',
          data: [
            { value: 1, name: '省级' },
            { value: 5, name: '市级' },
            { value: 8, name: '区县级' },
            { value: 6, name: '学区级' },
            { value: 5, name: '学校级' }
          ],
          emphasis: {
            itemStyle: {
              shadowBlur: 10,
              shadowOffsetX: 0,
              shadowColor: 'rgba(0, 0, 0, 0.5)'
            }
          }
        }
      ]
    }
    
    organizationChart.setOption(option)
  }
}

// 初始化角色分布图表
const initRoleChart = () => {
  if (roleChartRef.value) {
    roleChart = echarts.init(roleChartRef.value)
    
    const option = {
      title: {
        text: '用户角色分布',
        left: 'center'
      },
      tooltip: {
        trigger: 'axis',
        axisPointer: {
          type: 'shadow'
        }
      },
      xAxis: {
        type: 'category',
        data: ['系统管理员', '组织管理员', '部门管理员', '普通用户', '受限用户']
      },
      yAxis: {
        type: 'value'
      },
      series: [
        {
          name: '用户数量',
          type: 'bar',
          data: [2, 8, 15, 120, 11],
          itemStyle: {
            color: '#409EFF'
          }
        }
      ]
    }
    
    roleChart.setOption(option)
  }
}

// 刷新
const handleRefresh = async () => {
  await Promise.all([
    getStats(),
    getRecentUsers()
  ])
  
  // 重新初始化图表
  initOrganizationChart()
  initRoleChart()
}

// 窗口大小改变时重绘图表
const handleResize = () => {
  if (organizationChart) {
    organizationChart.resize()
  }
  if (roleChart) {
    roleChart.resize()
  }
}

onMounted(async () => {
  await Promise.all([
    getStats(),
    getRecentUsers()
  ])
  
  // 初始化图表
  setTimeout(() => {
    initOrganizationChart()
    initRoleChart()
  }, 100)
  
  // 监听窗口大小改变
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  // 销毁图表实例
  if (organizationChart) {
    organizationChart.dispose()
  }
  if (roleChart) {
    roleChart.dispose()
  }
  
  // 移除事件监听
  window.removeEventListener('resize', handleResize)
})
</script>

<style scoped>
.dashboard {
  padding: 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.page-header h2 {
  margin: 0;
  color: #333;
}

.stats-row {
  margin-bottom: 20px;
}

.stats-card {
  height: 120px;
}

.stats-content {
  display: flex;
  align-items: center;
  height: 100%;
}

.stats-icon {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 16px;
  font-size: 24px;
  color: #fff;
}

.stats-icon.organization {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stats-icon.user {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stats-icon.role {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stats-icon.permission {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.stats-info {
  flex: 1;
}

.stats-number {
  font-size: 28px;
  font-weight: bold;
  color: #333;
  margin-bottom: 4px;
}

.stats-label {
  font-size: 14px;
  color: #666;
}

.charts-row {
  margin-bottom: 20px;
}

.chart-container {
  padding: 10px;
}

.activity-row {
  margin-bottom: 20px;
}

.card-header {
  font-weight: 600;
  color: #333;
}
</style> 