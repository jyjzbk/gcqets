<template>
  <div class="import-dashboard">
    <div class="dashboard-header">
      <h3>学校导入统计仪表板</h3>
      <div class="header-controls">
        <el-select v-model="selectedDays" @change="loadStats" style="width: 120px">
          <el-option label="7天" :value="7" />
          <el-option label="30天" :value="30" />
          <el-option label="90天" :value="90" />
          <el-option label="180天" :value="180" />
        </el-select>
        <el-button type="primary" :icon="Refresh" @click="loadStats" :loading="loading">
          刷新
        </el-button>
      </div>
    </div>

    <!-- 统计卡片 -->
    <el-row :gutter="20" class="stats-cards">
      <el-col :span="6">
        <el-card class="stat-card total-imports">
          <div class="stat-content">
            <div class="stat-icon">
              <el-icon size="32"><Upload /></el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-number">{{ stats.basic_stats?.total_imports || 0 }}</div>
              <div class="stat-label">总导入次数</div>
            </div>
          </div>
        </el-card>
      </el-col>
      
      <el-col :span="6">
        <el-card class="stat-card successful-imports">
          <div class="stat-content">
            <div class="stat-icon">
              <el-icon size="32"><SuccessFilled /></el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-number">{{ stats.basic_stats?.successful_imports || 0 }}</div>
              <div class="stat-label">成功导入</div>
              <div class="stat-rate">成功率: {{ stats.basic_stats?.success_rate || 0 }}%</div>
            </div>
          </div>
        </el-card>
      </el-col>

      <el-col :span="6">
        <el-card class="stat-card failed-imports">
          <div class="stat-content">
            <div class="stat-icon">
              <el-icon size="32"><CircleCloseFilled /></el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-number">{{ stats.basic_stats?.failed_imports || 0 }}</div>
              <div class="stat-label">失败导入</div>
              <div class="stat-rate">错误: {{ stats.basic_stats?.total_errors || 0 }}</div>
            </div>
          </div>
        </el-card>
      </el-col>

      <el-col :span="6">
        <el-card class="stat-card total-schools">
          <div class="stat-content">
            <div class="stat-icon">
              <el-icon size="32"><School /></el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-number">{{ stats.basic_stats?.total_schools_imported || 0 }}</div>
              <div class="stat-label">导入学校数</div>
              <div class="stat-rate">平均: {{ stats.basic_stats?.average_schools_per_import || 0 }}/次</div>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 图表区域 -->
    <el-row :gutter="20" class="charts-section">
      <el-col :span="12">
        <el-card class="chart-card">
          <template #header>
            <span>导入成功率</span>
          </template>
          <div ref="successRateChartRef" class="chart-container"></div>
        </el-card>
      </el-col>

      <el-col :span="12">
        <el-card class="chart-card">
          <template #header>
            <span>导入状态分布</span>
          </template>
          <div ref="statusDistributionChartRef" class="chart-container"></div>
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="20" class="charts-section">
      <el-col :span="24">
        <el-card class="chart-card">
          <template #header>
            <div class="chart-header">
              <span>导入趋势</span>
              <el-radio-group v-model="trendType" size="small" @change="updateTrendChart">
                <el-radio-button label="imports">导入次数</el-radio-button>
                <el-radio-button label="schools">学校数量</el-radio-button>
                <el-radio-button label="errors">错误数量</el-radio-button>
              </el-radio-group>
            </div>
          </template>
          <div ref="trendChartRef" class="chart-container trend-chart"></div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 错误分析和用户统计 -->
    <el-row :gutter="20" class="charts-section">
      <el-col :span="12">
        <el-card class="chart-card">
          <template #header>
            <span>错误类型分析</span>
          </template>
          <div ref="errorAnalysisChartRef" class="chart-container"></div>
        </el-card>
      </el-col>

      <el-col :span="12">
        <el-card class="chart-card">
          <template #header>
            <span>文件类型分布</span>
          </template>
          <div ref="fileTypeChartRef" class="chart-container"></div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 时间分析 -->
    <el-row :gutter="20" class="charts-section">
      <el-col :span="12">
        <el-card class="chart-card">
          <template #header>
            <span>每日导入分布</span>
          </template>
          <div ref="weeklyChartRef" class="chart-container"></div>
        </el-card>
      </el-col>

      <el-col :span="12">
        <el-card class="chart-card">
          <template #header>
            <span>每小时导入分布</span>
          </template>
          <div ref="hourlyChartRef" class="chart-container"></div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 最近导入记录 -->
    <el-card class="recent-imports-card">
      <template #header>
        <div class="card-header">
          <span>最近导入记录</span>
          <el-button type="text" @click="$emit('view-history')">
            查看全部
          </el-button>
        </div>
      </template>
      
      <el-table 
        :data="recentImports" 
        style="width: 100%"
        v-loading="loading"
      >
        <el-table-column prop="filename" label="文件名" width="200" />
        <el-table-column prop="parent.name" label="父级组织" width="150" />
        <el-table-column prop="total_rows" label="总行数" width="100" />
        <el-table-column prop="success_rows" label="成功" width="80" />
        <el-table-column prop="failed_rows" label="失败" width="80" />
        <el-table-column prop="success_rate" label="成功率" width="100">
          <template #default="{ row }">
            <el-progress 
              :percentage="row.success_rate" 
              :stroke-width="6"
              :show-text="false"
              :color="getProgressColor(row.success_rate)"
            />
            <span class="success-rate-text">{{ row.success_rate }}%</span>
          </template>
        </el-table-column>
        <el-table-column prop="status_text" label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">
              {{ row.status_text }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="导入时间" width="180">
          <template #default="{ row }">
            {{ formatDateTime(row.created_at) }}
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { ElMessage } from 'element-plus'
import { Refresh, Upload, SuccessFilled, CircleCloseFilled, School } from '@element-plus/icons-vue'
import { schoolImportApi } from '../../api/schoolImport'
import * as echarts from 'echarts'

// 定义事件
const emit = defineEmits(['view-history'])

// 响应式数据
const loading = ref(false)
const selectedDays = ref(30)
const stats = ref({})
const recentImports = ref([])
const trendType = ref('imports')

// 图表引用
const successRateChartRef = ref()
const statusDistributionChartRef = ref()
const trendChartRef = ref()
const errorAnalysisChartRef = ref()
const fileTypeChartRef = ref()
const weeklyChartRef = ref()
const hourlyChartRef = ref()

// 图表实例
let successRateChart = null
let statusDistributionChart = null
let trendChart = null
let errorAnalysisChart = null
let fileTypeChart = null
let weeklyChart = null
let hourlyChart = null

// 方法
const loadStats = async () => {
  loading.value = true
  try {
    const [statsResponse, historyResponse] = await Promise.all([
      schoolImportApi.getStats({ days: selectedDays.value }),
      schoolImportApi.getHistory({ per_page: 10 })
    ])
    
    stats.value = statsResponse.data.data
    recentImports.value = historyResponse.data.data.data
    
    // 更新图表
    await nextTick()
    updateCharts()
    
  } catch (error) {
    console.error('加载统计数据失败:', error)
    ElMessage.error('加载统计数据失败')
  } finally {
    loading.value = false
  }
}

const updateCharts = () => {
  updateSuccessRateChart()
  updateStatusDistributionChart()
  updateTrendChart()
  updateErrorAnalysisChart()
  updateFileTypeChart()
  updateWeeklyChart()
  updateHourlyChart()
}

const updateSuccessRateChart = () => {
  if (!successRateChart) {
    successRateChart = echarts.init(successRateChartRef.value)
  }

  const successRate = stats.value.basic_stats?.success_rate || 0
  
  const option = {
    series: [{
      type: 'gauge',
      startAngle: 180,
      endAngle: 0,
      center: ['50%', '75%'],
      radius: '90%',
      min: 0,
      max: 100,
      splitNumber: 8,
      axisLine: {
        lineStyle: {
          width: 6,
          color: [
            [0.25, '#FF6E76'],
            [0.5, '#FDDD60'],
            [0.75, '#58D9F9'],
            [1, '#7CFFB2']
          ]
        }
      },
      pointer: {
        icon: 'path://M12.8,0.7l12,40.1H0.7L12.8,0.7z',
        length: '12%',
        width: 20,
        offsetCenter: [0, '-60%'],
        itemStyle: {
          color: 'auto'
        }
      },
      axisTick: {
        length: 12,
        lineStyle: {
          color: 'auto',
          width: 2
        }
      },
      splitLine: {
        length: 20,
        lineStyle: {
          color: 'auto',
          width: 5
        }
      },
      axisLabel: {
        color: '#464646',
        fontSize: 12,
        distance: -60,
        formatter: function (value) {
          return value + '%'
        }
      },
      title: {
        offsetCenter: [0, '-20%'],
        fontSize: 16,
        color: '#464646'
      },
      detail: {
        fontSize: 24,
        offsetCenter: [0, '0%'],
        valueAnimation: true,
        formatter: function (value) {
          return Math.round(value) + '%'
        },
        color: 'auto'
      },
      data: [{
        value: successRate,
        name: '成功率'
      }]
    }]
  }
  
  successRateChart.setOption(option)
}

const updateStatusDistributionChart = () => {
  if (!statusDistributionChart) {
    statusDistributionChart = echarts.init(statusDistributionChartRef.value)
  }

  const data = [
    { value: stats.value.basic_stats?.successful_imports || 0, name: '成功', itemStyle: { color: '#67C23A' } },
    { value: stats.value.basic_stats?.partial_success_imports || 0, name: '部分成功', itemStyle: { color: '#E6A23C' } },
    { value: stats.value.basic_stats?.failed_imports || 0, name: '失败', itemStyle: { color: '#F56C6C' } }
  ]
  
  const option = {
    tooltip: {
      trigger: 'item',
      formatter: '{a} <br/>{b}: {c} ({d}%)'
    },
    legend: {
      orient: 'vertical',
      left: 'left'
    },
    series: [{
      name: '导入状态',
      type: 'pie',
      radius: ['40%', '70%'],
      avoidLabelOverlap: false,
      label: {
        show: false,
        position: 'center'
      },
      emphasis: {
        label: {
          show: true,
          fontSize: '18',
          fontWeight: 'bold'
        }
      },
      labelLine: {
        show: false
      },
      data: data
    }]
  }
  
  statusDistributionChart.setOption(option)
}

const updateTrendChart = () => {
  if (!trendChart) {
    trendChart = echarts.init(trendChartRef.value)
  }

  const trendData = stats.value.trend_data || []
  const dates = trendData.map(item => item.date)

  let seriesData = []
  let yAxisName = ''

  if (trendType.value === 'imports') {
    seriesData = [
      {
        name: '总导入',
        type: 'line',
        data: trendData.map(item => item.total_imports),
        itemStyle: { color: '#409EFF' }
      },
      {
        name: '成功导入',
        type: 'line',
        data: trendData.map(item => item.successful_imports),
        itemStyle: { color: '#67C23A' }
      },
      {
        name: '失败导入',
        type: 'line',
        data: trendData.map(item => item.failed_imports),
        itemStyle: { color: '#F56C6C' }
      }
    ]
    yAxisName = '导入次数'
  } else if (trendType.value === 'schools') {
    seriesData = [
      {
        name: '导入学校数',
        type: 'line',
        data: trendData.map(item => item.schools_imported),
        itemStyle: { color: '#409EFF' },
        areaStyle: { opacity: 0.3 }
      }
    ]
    yAxisName = '学校数量'
  } else if (trendType.value === 'errors') {
    seriesData = [
      {
        name: '错误数量',
        type: 'line',
        data: trendData.map(item => item.errors),
        itemStyle: { color: '#F56C6C' },
        areaStyle: { opacity: 0.3 }
      }
    ]
    yAxisName = '错误数量'
  }
  
  const option = {
    tooltip: {
      trigger: 'axis',
      axisPointer: {
        type: 'cross'
      }
    },
    legend: {
      data: seriesData.map(s => s.name)
    },
    grid: {
      left: '3%',
      right: '4%',
      bottom: '3%',
      containLabel: true
    },
    xAxis: {
      type: 'category',
      boundaryGap: false,
      data: dates,
      axisLabel: {
        formatter: function(value) {
          return new Date(value).toLocaleDateString('zh-CN', { month: 'short', day: 'numeric' })
        }
      }
    },
    yAxis: {
      type: 'value',
      name: yAxisName,
      nameTextStyle: {
        color: '#666'
      }
    },
    series: seriesData
  }
  
  trendChart.setOption(option)
}

const updateErrorAnalysisChart = () => {
  if (!errorAnalysisChart) {
    errorAnalysisChart = echarts.init(errorAnalysisChartRef.value)
  }

  const errorTypes = stats.value.error_analysis?.error_types || {}
  const data = Object.entries(errorTypes).map(([type, count]) => ({
    name: type,
    value: count
  }))

  const option = {
    tooltip: {
      trigger: 'item',
      formatter: '{a} <br/>{b}: {c} ({d}%)'
    },
    legend: {
      orient: 'vertical',
      left: 'left',
      textStyle: {
        fontSize: 12
      }
    },
    series: [{
      name: '错误类型',
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
    }]
  }

  errorAnalysisChart.setOption(option)
}

const updateFileTypeChart = () => {
  if (!fileTypeChart) {
    fileTypeChart = echarts.init(fileTypeChartRef.value)
  }

  const fileStats = stats.value.file_stats || {}
  const data = Object.entries(fileStats).map(([type, count]) => ({
    name: type.toUpperCase(),
    value: count
  }))

  const option = {
    tooltip: {
      trigger: 'item',
      formatter: '{a} <br/>{b}: {c} ({d}%)'
    },
    legend: {
      bottom: '5%',
      left: 'center'
    },
    series: [{
      name: '文件类型',
      type: 'pie',
      radius: ['30%', '60%'],
      data: data,
      itemStyle: {
        borderRadius: 10,
        borderColor: '#fff',
        borderWidth: 2
      }
    }]
  }

  fileTypeChart.setOption(option)
}

const updateWeeklyChart = () => {
  if (!weeklyChart) {
    weeklyChart = echarts.init(weeklyChartRef.value)
  }

  const weeklyData = stats.value.time_analysis?.weekly_distribution || []
  const weekDays = ['周日', '周一', '周二', '周三', '周四', '周五', '周六']

  const option = {
    tooltip: {
      trigger: 'axis',
      axisPointer: {
        type: 'shadow'
      }
    },
    grid: {
      left: '3%',
      right: '4%',
      bottom: '3%',
      containLabel: true
    },
    xAxis: {
      type: 'category',
      data: weekDays
    },
    yAxis: {
      type: 'value',
      name: '导入次数'
    },
    series: [{
      name: '导入次数',
      type: 'bar',
      data: weeklyData,
      itemStyle: {
        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
          { offset: 0, color: '#83bff6' },
          { offset: 0.5, color: '#188df0' },
          { offset: 1, color: '#188df0' }
        ])
      }
    }]
  }

  weeklyChart.setOption(option)
}

const updateHourlyChart = () => {
  if (!hourlyChart) {
    hourlyChart = echarts.init(hourlyChartRef.value)
  }

  const hourlyData = stats.value.time_analysis?.hourly_distribution || []
  const hours = Array.from({ length: 24 }, (_, i) => `${i}:00`)

  const option = {
    tooltip: {
      trigger: 'axis',
      axisPointer: {
        type: 'line'
      }
    },
    grid: {
      left: '3%',
      right: '4%',
      bottom: '3%',
      containLabel: true
    },
    xAxis: {
      type: 'category',
      data: hours,
      axisLabel: {
        interval: 2
      }
    },
    yAxis: {
      type: 'value',
      name: '导入次数'
    },
    series: [{
      name: '导入次数',
      type: 'line',
      data: hourlyData,
      smooth: true,
      itemStyle: {
        color: '#67C23A'
      },
      areaStyle: {
        opacity: 0.3,
        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
          { offset: 0, color: '#67C23A' },
          { offset: 1, color: 'rgba(103, 194, 58, 0.1)' }
        ])
      }
    }]
  }

  hourlyChart.setOption(option)
}

const getProgressColor = (percentage) => {
  if (percentage >= 80) return '#67c23a'
  if (percentage >= 60) return '#e6a23c'
  return '#f56c6c'
}

const getStatusType = (status) => {
  const statusMap = {
    'pending': 'info',
    'processing': 'warning',
    'success': 'success',
    'partial_success': 'warning',
    'failed': 'danger'
  }
  return statusMap[status] || 'info'
}

const formatDateTime = (dateTime) => {
  if (!dateTime) return '无'
  return new Date(dateTime).toLocaleString('zh-CN')
}

// 趋势类型切换
const changeTrendType = () => {
  updateTrendChart()
}

// 生命周期
onMounted(() => {
  loadStats()

  // 监听窗口大小变化，重新调整图表
  const handleResize = () => {
    if (successRateChart) successRateChart.resize()
    if (statusDistributionChart) statusDistributionChart.resize()
    if (trendChart) trendChart.resize()
    if (errorAnalysisChart) errorAnalysisChart.resize()
    if (fileTypeChart) fileTypeChart.resize()
    if (weeklyChart) weeklyChart.resize()
    if (hourlyChart) hourlyChart.resize()
  }

  window.addEventListener('resize', handleResize)

  // 组件卸载时移除监听器
  onUnmounted(() => {
    window.removeEventListener('resize', handleResize)
  })
})
</script>

<style scoped>
.import-dashboard {
  padding: 20px;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.dashboard-header h3 {
  margin: 0;
  color: #303133;
  font-size: 20px;
  font-weight: 600;
}

.header-controls {
  display: flex;
  gap: 12px;
  align-items: center;
}

/* 统计卡片样式 */
.stats-cards {
  margin-bottom: 20px;
}

.stat-card {
  height: 120px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-content {
  display: flex;
  align-items: center;
  height: 100%;
  padding: 0 10px;
}

.stat-icon {
  margin-right: 16px;
  padding: 12px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-info {
  flex: 1;
}

.stat-number {
  font-size: 28px;
  font-weight: 700;
  line-height: 1;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 14px;
  color: #909399;
  font-weight: 500;
}

.stat-rate {
  font-size: 12px;
  color: #606266;
  margin-top: 4px;
}

/* 不同类型卡片的颜色 */
.total-imports .stat-icon {
  background-color: #e3f2fd;
  color: #1976d2;
}

.total-imports .stat-number {
  color: #1976d2;
}

.successful-imports .stat-icon {
  background-color: #e8f5e8;
  color: #4caf50;
}

.successful-imports .stat-number {
  color: #4caf50;
}

.failed-imports .stat-icon {
  background-color: #ffebee;
  color: #f44336;
}

.failed-imports .stat-number {
  color: #f44336;
}

.total-schools .stat-icon {
  background-color: #fff3e0;
  color: #ff9800;
}

.total-schools .stat-number {
  color: #ff9800;
}

/* 图表区域样式 */
.charts-section {
  margin-bottom: 20px;
}

.chart-card {
  height: 400px;
}

.chart-container {
  height: 320px;
  width: 100%;
}

.trend-chart {
  height: 320px;
}

/* 最近导入记录样式 */
.recent-imports-card {
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.success-rate-text {
  margin-left: 8px;
  font-size: 12px;
  color: #606266;
}

/* 响应式设计 */
@media (max-width: 1200px) {
  .stats-cards .el-col {
    margin-bottom: 16px;
  }

  .charts-section .el-col {
    margin-bottom: 16px;
  }
}

@media (max-width: 768px) {
  .import-dashboard {
    padding: 10px;
  }

  .dashboard-header {
    flex-direction: column;
    gap: 16px;
    align-items: flex-start;
  }

  .header-controls {
    width: 100%;
    justify-content: flex-end;
  }

  .stat-card {
    height: 100px;
  }

  .stat-number {
    font-size: 24px !important;
  }

  .chart-card {
    height: 300px;
  }

  .chart-container {
    height: 220px;
  }
}

/* 表格样式调整 */
:deep(.el-table) {
  font-size: 13px;
}

:deep(.el-table .cell) {
  padding: 8px 12px;
}

/* 进度条样式 */
:deep(.el-progress-bar__outer) {
  border-radius: 4px;
  height: 6px;
}

:deep(.el-progress-bar__inner) {
  border-radius: 4px;
}

/* 卡片标题样式 */
:deep(.el-card__header) {
  background-color: #fafafa;
  border-bottom: 1px solid #ebeef5;
  font-weight: 600;
  color: #303133;
}

/* 图表头部样式 */
.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.chart-header span {
  font-weight: 600;
  color: #303133;
}
</style>
