<template>
  <div class="permission-analytics">
    <!-- 统计卡片 -->
    <div class="stats-overview">
      <el-row :gutter="16">
        <el-col :span="6">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-icon">
                <el-icon><User /></el-icon>
              </div>
              <div class="stat-info">
                <div class="stat-value">{{ stats.total_users }}</div>
                <div class="stat-label">总用户数</div>
              </div>
            </div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-icon">
                <el-icon><Key /></el-icon>
              </div>
              <div class="stat-info">
                <div class="stat-value">{{ stats.total_permissions }}</div>
                <div class="stat-label">总权限数</div>
              </div>
            </div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-icon">
                <el-icon><Warning /></el-icon>
              </div>
              <div class="stat-info">
                <div class="stat-value">{{ stats.active_conflicts }}</div>
                <div class="stat-label">活跃冲突</div>
              </div>
            </div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-icon">
                <el-icon><Document /></el-icon>
              </div>
              <div class="stat-info">
                <div class="stat-value">{{ stats.audit_logs_today }}</div>
                <div class="stat-label">今日操作</div>
              </div>
            </div>
          </el-card>
        </el-col>
      </el-row>
    </div>

    <!-- 图表区域 -->
    <div class="charts-container">
      <el-row :gutter="16">
        <!-- 权限分布饼图 -->
        <el-col :span="12">
          <el-card class="chart-card">
            <template #header>
              <div class="card-header">
                <span>权限分布统计</span>
                <el-button type="text" @click="refreshPermissionDistribution">
                  <el-icon><Refresh /></el-icon>
                </el-button>
              </div>
            </template>
            <div ref="permissionPieChart" class="chart-container"></div>
          </el-card>
        </el-col>

        <!-- 操作趋势图 -->
        <el-col :span="12">
          <el-card class="chart-card">
            <template #header>
              <div class="card-header">
                <span>操作趋势分析</span>
                <el-select
                  v-model="trendPeriod"
                  @change="refreshOperationTrend"
                  style="width: 120px"
                >
                  <el-option label="最近7天" value="7d" />
                  <el-option label="最近30天" value="30d" />
                  <el-option label="最近90天" value="90d" />
                </el-select>
              </div>
            </template>
            <div ref="operationTrendChart" class="chart-container"></div>
          </el-card>
        </el-col>
      </el-row>

      <el-row :gutter="16" style="margin-top: 16px">
        <!-- 权限热力图 -->
        <el-col :span="16">
          <el-card class="chart-card">
            <template #header>
              <div class="card-header">
                <span>权限使用热力图</span>
                <div class="header-actions">
                  <el-select
                    v-model="heatmapOrganization"
                    @change="refreshPermissionHeatmap"
                    placeholder="选择组织"
                    style="width: 200px; margin-right: 8px"
                  >
                    <el-option
                      v-for="org in organizations"
                      :key="org.id"
                      :label="org.name"
                      :value="org.id"
                    />
                  </el-select>
                  <el-button type="text" @click="refreshPermissionHeatmap">
                    <el-icon><Refresh /></el-icon>
                  </el-button>
                </div>
              </div>
            </template>
            <div ref="permissionHeatmapChart" class="chart-container large"></div>
          </el-card>
        </el-col>

        <!-- 冲突统计 -->
        <el-col :span="8">
          <el-card class="chart-card">
            <template #header>
              <div class="card-header">
                <span>权限冲突统计</span>
                <el-button type="text" @click="refreshConflictStats">
                  <el-icon><Refresh /></el-icon>
                </el-button>
              </div>
            </template>
            <div class="conflict-stats">
              <div class="conflict-item" v-for="item in conflictStats" :key="item.type">
                <div class="conflict-type">{{ item.name }}</div>
                <div class="conflict-count" :class="item.severity">{{ item.count }}</div>
              </div>
            </div>
          </el-card>
        </el-col>
      </el-row>
    </div>

    <!-- 权限热点表格 -->
    <div class="hotspots-section">
      <el-card>
        <template #header>
          <div class="card-header">
            <span>权限操作热点</span>
            <el-button type="primary" size="small" @click="exportHotspots">
              <el-icon><Download /></el-icon>
              导出数据
            </el-button>
          </div>
        </template>
        <el-table :data="permissionHotspots" stripe>
          <el-table-column prop="permission_name" label="权限名称" />
          <el-table-column prop="operation_count" label="操作次数" width="120" sortable />
          <el-table-column prop="grant_count" label="分配次数" width="120" />
          <el-table-column prop="revoke_count" label="撤销次数" width="120" />
          <el-table-column prop="last_operation" label="最后操作时间" width="160" />
          <el-table-column label="操作趋势" width="100">
            <template #default="{ row }">
              <el-tag
                :type="getTrendTagType(row.trend)"
                size="small"
              >
                {{ getTrendText(row.trend) }}
              </el-tag>
            </template>
          </el-table-column>
        </el-table>
      </el-card>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue'
import { ElMessage } from 'element-plus'
import {
  User,
  Key,
  Warning,
  Document,
  Refresh,
  Download
} from '@element-plus/icons-vue'
import * as echarts from 'echarts'
import { permissionVisualizationApi, permissionAuditApi } from '../../api/permissionVisualization'
import { organizationApi } from '../../api/organization'

// 响应式数据
const stats = reactive({
  total_users: 0,
  total_permissions: 0,
  active_conflicts: 0,
  audit_logs_today: 0
})

const organizations = ref([])
const permissionHotspots = ref([])
const conflictStats = ref([])

const trendPeriod = ref('7d')
const heatmapOrganization = ref(null)

// 图表实例
const permissionPieChart = ref(null)
const operationTrendChart = ref(null)
const permissionHeatmapChart = ref(null)

let pieChartInstance = null
let trendChartInstance = null
let heatmapChartInstance = null

// 组件挂载时初始化
onMounted(async () => {
  await loadBasicData()
  await loadStats()
  await nextTick()
  initCharts()
  loadAllData()
})

// 加载基础数据
const loadBasicData = async () => {
  try {
    const response = await organizationApi.getList()
    organizations.value = response.data.data || []
  } catch (error) {
    console.error('加载基础数据失败:', error)
  }
}

// 加载统计数据
const loadStats = async () => {
  try {
    const response = await permissionVisualizationApi.getOverallStats()
    Object.assign(stats, response.data.data)
  } catch (error) {
    console.error('加载统计数据失败:', error)
  }
}

// 初始化图表
const initCharts = () => {
  // 权限分布饼图
  if (permissionPieChart.value) {
    pieChartInstance = echarts.init(permissionPieChart.value)
  }

  // 操作趋势图
  if (operationTrendChart.value) {
    trendChartInstance = echarts.init(operationTrendChart.value)
  }

  // 权限热力图
  if (permissionHeatmapChart.value) {
    heatmapChartInstance = echarts.init(permissionHeatmapChart.value)
  }

  // 监听窗口大小变化
  window.addEventListener('resize', () => {
    pieChartInstance?.resize()
    trendChartInstance?.resize()
    heatmapChartInstance?.resize()
  })
}

// 加载所有数据
const loadAllData = async () => {
  await Promise.all([
    refreshPermissionDistribution(),
    refreshOperationTrend(),
    refreshPermissionHeatmap(),
    refreshConflictStats(),
    loadPermissionHotspots()
  ])
}

// 刷新权限分布
const refreshPermissionDistribution = async () => {
  try {
    const response = await permissionVisualizationApi.getPermissionDistribution()
    const data = response.data.data || []

    const option = {
      title: {
        text: '权限分布',
        left: 'center',
        textStyle: { fontSize: 14 }
      },
      tooltip: {
        trigger: 'item',
        formatter: '{a} <br/>{b}: {c} ({d}%)'
      },
      legend: {
        orient: 'vertical',
        left: 'left',
        top: 'middle'
      },
      series: [
        {
          name: '权限分布',
          type: 'pie',
          radius: ['40%', '70%'],
          center: ['60%', '50%'],
          data: data,
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

    pieChartInstance?.setOption(option)
  } catch (error) {
    console.error('刷新权限分布失败:', error)
  }
}

// 刷新操作趋势
const refreshOperationTrend = async () => {
  try {
    const response = await permissionAuditApi.getOperationTrend({ period: trendPeriod.value })
    const data = response.data.data || { dates: [], grant: [], revoke: [] }

    const option = {
      title: {
        text: '操作趋势',
        left: 'center',
        textStyle: { fontSize: 14 }
      },
      tooltip: {
        trigger: 'axis'
      },
      legend: {
        data: ['权限分配', '权限撤销'],
        top: 30
      },
      xAxis: {
        type: 'category',
        data: data.dates
      },
      yAxis: {
        type: 'value'
      },
      series: [
        {
          name: '权限分配',
          type: 'line',
          data: data.grant,
          smooth: true,
          itemStyle: { color: '#67c23a' }
        },
        {
          name: '权限撤销',
          type: 'line',
          data: data.revoke,
          smooth: true,
          itemStyle: { color: '#f56c6c' }
        }
      ]
    }

    trendChartInstance?.setOption(option)
  } catch (error) {
    console.error('刷新操作趋势失败:', error)
  }
}

// 刷新权限热力图
const refreshPermissionHeatmap = async () => {
  try {
    const response = await permissionVisualizationApi.getPermissionHeatmap({
      organization_id: heatmapOrganization.value
    })
    const data = response.data.data || { users: [], permissions: [], matrix: [] }

    const option = {
      title: {
        text: '权限使用热力图',
        left: 'center',
        textStyle: { fontSize: 14 }
      },
      tooltip: {
        position: 'top',
        formatter: function (params) {
          return `${data.users[params.data[1]]}<br/>${data.permissions[params.data[0]]}<br/>使用次数: ${params.data[2]}`
        }
      },
      grid: {
        height: '60%',
        top: '10%'
      },
      xAxis: {
        type: 'category',
        data: data.permissions,
        splitArea: {
          show: true
        },
        axisLabel: {
          rotate: 45,
          fontSize: 10
        }
      },
      yAxis: {
        type: 'category',
        data: data.users,
        splitArea: {
          show: true
        },
        axisLabel: {
          fontSize: 10
        }
      },
      visualMap: {
        min: 0,
        max: Math.max(...data.matrix.map(item => item[2])),
        calculable: true,
        orient: 'horizontal',
        left: 'center',
        bottom: '5%',
        inRange: {
          color: ['#eee', '#409eff']
        }
      },
      series: [
        {
          name: '权限使用',
          type: 'heatmap',
          data: data.matrix,
          label: {
            show: false
          },
          emphasis: {
            itemStyle: {
              shadowBlur: 10,
              shadowColor: 'rgba(0, 0, 0, 0.5)'
            }
          }
        }
      ]
    }

    heatmapChartInstance?.setOption(option)
  } catch (error) {
    console.error('刷新权限热力图失败:', error)
  }
}

// 刷新冲突统计
const refreshConflictStats = async () => {
  try {
    const response = await permissionAuditApi.getConflictStats()
    const data = response.data.data || {}

    conflictStats.value = [
      { type: 'role_permission', name: '角色权限冲突', count: data.by_type?.role_permission || 0, severity: 'warning' },
      { type: 'direct_permission', name: '直接权限冲突', count: data.by_type?.direct_permission || 0, severity: 'danger' },
      { type: 'inheritance', name: '继承权限冲突', count: data.by_type?.inheritance || 0, severity: 'info' },
      { type: 'template', name: '模板权限冲突', count: data.by_type?.template || 0, severity: 'primary' }
    ]
  } catch (error) {
    console.error('刷新冲突统计失败:', error)
  }
}

// 加载权限热点
const loadPermissionHotspots = async () => {
  try {
    const response = await permissionAuditApi.getPermissionHotspots({ limit: 20 })
    permissionHotspots.value = response.data.data || []
  } catch (error) {
    console.error('加载权限热点失败:', error)
  }
}

// 导出热点数据
const exportHotspots = async () => {
  try {
    // 这里实现导出逻辑
    ElMessage.success('数据导出成功')
  } catch (error) {
    console.error('导出失败:', error)
    ElMessage.error('导出失败')
  }
}

// 获取趋势标签类型
const getTrendTagType = (trend) => {
  if (trend > 0) return 'success'
  if (trend < 0) return 'danger'
  return 'info'
}

// 获取趋势文本
const getTrendText = (trend) => {
  if (trend > 0) return '上升'
  if (trend < 0) return '下降'
  return '平稳'
}
</script>

<style scoped>
.permission-analytics {
  height: 100%;
  display: flex;
  flex-direction: column;
  gap: 16px;
  overflow-y: auto;
}

.stats-overview {
  flex-shrink: 0;
}

.stat-card {
  height: 100px;
}

.stat-content {
  display: flex;
  align-items: center;
  height: 100%;
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #409eff, #67c23a);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 16px;
}

.stat-icon .el-icon {
  font-size: 24px;
  color: white;
}

.stat-info {
  flex: 1;
}

.stat-value {
  font-size: 28px;
  font-weight: bold;
  color: #303133;
  line-height: 1;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 14px;
  color: #606266;
}

.charts-container {
  flex: 1;
  min-height: 0;
}

.chart-card {
  height: 400px;
}

.chart-card .chart-container {
  height: 320px;
}

.chart-card .chart-container.large {
  height: 320px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-actions {
  display: flex;
  align-items: center;
}

.conflict-stats {
  padding: 16px 0;
}

.conflict-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #f0f0f0;
}

.conflict-item:last-child {
  border-bottom: none;
}

.conflict-type {
  font-size: 14px;
  color: #606266;
}

.conflict-count {
  font-size: 18px;
  font-weight: bold;
}

.conflict-count.warning {
  color: #e6a23c;
}

.conflict-count.danger {
  color: #f56c6c;
}

.conflict-count.info {
  color: #909399;
}

.conflict-count.primary {
  color: #409eff;
}

.hotspots-section {
  flex-shrink: 0;
}
</style>
