<template>
  <div class="monitor-dashboard">
    <!-- 页面头部 -->
    <div class="dashboard-header">
      <div class="header-left">
        <h2>实验执行监控</h2>
        <p>实时监控实验教学执行情况和数据分析</p>
      </div>
      <div class="header-right">
        <el-select v-model="timeRange" @change="handleTimeRangeChange" style="width: 120px;">
          <el-option label="本周" value="week" />
          <el-option label="本月" value="month" />
          <el-option label="本季度" value="quarter" />
          <el-option label="本年" value="year" />
        </el-select>
        <el-button @click="refreshData" :loading="loading">
          <el-icon><Refresh /></el-icon>
          刷新
        </el-button>
        <el-button type="primary" @click="showRealTimeStats">
          <el-icon><DataAnalysis /></el-icon>
          实时统计
        </el-button>
      </div>
    </div>

    <!-- 基础统计卡片 -->
    <div class="stats-cards">
      <el-row :gutter="20">
        <el-col :span="6">
          <el-card class="stats-card">
            <div class="card-content">
              <div class="card-icon total">
                <el-icon><Document /></el-icon>
              </div>
              <div class="card-info">
                <div class="card-number">{{ dashboardData.basicStats?.totalPlans || 0 }}</div>
                <div class="card-label">总实验计划</div>
              </div>
            </div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card class="stats-card">
            <div class="card-content">
              <div class="card-icon approved">
                <el-icon><CircleCheck /></el-icon>
              </div>
              <div class="card-info">
                <div class="card-number">{{ dashboardData.basicStats?.approvedPlans || 0 }}</div>
                <div class="card-label">已批准计划</div>
              </div>
            </div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card class="stats-card">
            <div class="card-content">
              <div class="card-icon completed">
                <el-icon><Select /></el-icon>
              </div>
              <div class="card-info">
                <div class="card-number">{{ dashboardData.basicStats?.completedPlans || 0 }}</div>
                <div class="card-label">已完成计划</div>
              </div>
            </div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card class="stats-card">
            <div class="card-content">
              <div class="card-icon overdue">
                <el-icon><Warning /></el-icon>
              </div>
              <div class="card-info">
                <div class="card-number">{{ dashboardData.basicStats?.overduePlans || 0 }}</div>
                <div class="card-label">逾期计划</div>
              </div>
            </div>
          </el-card>
        </el-col>
      </el-row>
    </div>

    <!-- 图表区域 -->
    <div class="charts-section">
      <el-row :gutter="20">
        <!-- 状态分布饼图 -->
        <el-col :span="12">
          <el-card>
            <template #header>
              <h3>实验状态分布</h3>
            </template>
            <div ref="statusChartRef" class="chart-container"></div>
          </el-card>
        </el-col>
        
        <!-- 完成率趋势图 -->
        <el-col :span="12">
          <el-card>
            <template #header>
              <h3>完成率趋势</h3>
            </template>
            <div ref="trendChartRef" class="chart-container"></div>
          </el-card>
        </el-col>
      </el-row>
    </div>

    <!-- 排行榜和异常分析 -->
    <div class="analysis-section">
      <el-row :gutter="20">
        <!-- 教师活跃度排行 -->
        <el-col :span="8">
          <el-card>
            <template #header>
              <h3>教师活跃度排行</h3>
            </template>
            <div class="ranking-list">
              <div 
                v-for="(item, index) in dashboardData.rankings?.teacherRanking || []" 
                :key="index"
                class="ranking-item"
              >
                <div class="rank-number" :class="getRankClass(index)">{{ index + 1 }}</div>
                <div class="rank-info">
                  <div class="rank-name">{{ item.teacher }}</div>
                  <div class="rank-count">{{ item.planCount }} 个计划</div>
                </div>
              </div>
            </div>
          </el-card>
        </el-col>
        
        <!-- 实验目录使用排行 -->
        <el-col :span="8">
          <el-card>
            <template #header>
              <h3>实验目录使用排行</h3>
            </template>
            <div class="ranking-list">
              <div 
                v-for="(item, index) in dashboardData.rankings?.catalogRanking || []" 
                :key="index"
                class="ranking-item"
              >
                <div class="rank-number" :class="getRankClass(index)">{{ index + 1 }}</div>
                <div class="rank-info">
                  <div class="rank-name">{{ item.catalog }}</div>
                  <div class="rank-count">{{ item.usageCount }} 次使用</div>
                </div>
              </div>
            </div>
          </el-card>
        </el-col>
        
        <!-- 异常预警 -->
        <el-col :span="8">
          <el-card>
            <template #header>
              <h3>异常预警</h3>
            </template>
            <div class="anomaly-list">
              <div class="anomaly-item">
                <el-icon class="anomaly-icon overdue"><Warning /></el-icon>
                <div class="anomaly-info">
                  <div class="anomaly-title">逾期实验</div>
                  <div class="anomaly-count">{{ dashboardData.anomalyData?.overdueExperiments?.length || 0 }} 个</div>
                </div>
              </div>
              <div class="anomaly-item">
                <el-icon class="anomaly-icon pending"><Clock /></el-icon>
                <div class="anomaly-info">
                  <div class="anomaly-title">待审核记录</div>
                  <div class="anomaly-count">{{ dashboardData.anomalyData?.pendingRecords?.length || 0 }} 个</div>
                </div>
              </div>
              <div class="anomaly-item">
                <el-icon class="anomaly-icon low-completion"><TrendCharts /></el-icon>
                <div class="anomaly-info">
                  <div class="anomaly-title">低完成率实验</div>
                  <div class="anomaly-count">{{ dashboardData.anomalyData?.lowCompletionExperiments?.length || 0 }} 个</div>
                </div>
              </div>
            </div>
          </el-card>
        </el-col>
      </el-row>
    </div>

    <!-- 实时统计对话框 -->
    <el-dialog
      v-model="realTimeDialogVisible"
      title="实时统计"
      width="600px"
      :close-on-click-modal="false"
    >
      <RealTimeStats
        v-if="realTimeDialogVisible"
        @close="realTimeDialogVisible = false"
      />
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { ElMessage } from 'element-plus'
import { 
  Refresh, 
  DataAnalysis, 
  Document, 
  CircleCheck, 
  Select, 
  Warning,
  Clock,
  TrendCharts
} from '@element-plus/icons-vue'
import * as echarts from 'echarts'
import RealTimeStats from './components/RealTimeStats.vue'
import { experimentMonitorApi } from '@/api/experiment-monitor'

// 响应式数据
const timeRange = ref('month')
const loading = ref(false)
const realTimeDialogVisible = ref(false)
const dashboardData = ref({})

// 图表引用
const statusChartRef = ref(null)
const trendChartRef = ref(null)
const statusChart = ref(null)
const trendChart = ref(null)

// 加载监控数据
const loadDashboardData = async () => {
  loading.value = true
  try {
    const response = await experimentMonitorApi.getDashboard({ time_range: timeRange.value })
    
    if (response.data.success) {
      dashboardData.value = response.data.data
      
      // 更新图表
      await nextTick()
      updateCharts()
    }
  } catch (error) {
    console.error('加载监控数据失败:', error)
    ElMessage.error('加载监控数据失败')
  } finally {
    loading.value = false
  }
}

// 更新图表
const updateCharts = () => {
  updateStatusChart()
  updateTrendChart()
}

// 更新状态分布图
const updateStatusChart = () => {
  if (!statusChart.value) {
    statusChart.value = echarts.init(statusChartRef.value)
  }
  
  const statusStats = dashboardData.value.progressStats?.statusStats || {}
  const statusLabels = {
    'draft': '草稿',
    'pending': '待审批',
    'approved': '已批准',
    'rejected': '已拒绝',
    'executing': '执行中',
    'completed': '已完成',
    'cancelled': '已取消'
  }
  
  const data = Object.entries(statusStats).map(([status, count]) => ({
    name: statusLabels[status] || status,
    value: count
  }))
  
  const option = {
    tooltip: {
      trigger: 'item',
      formatter: '{a} <br/>{b}: {c} ({d}%)'
    },
    legend: {
      orient: 'vertical',
      left: 'left'
    },
    series: [
      {
        name: '实验状态',
        type: 'pie',
        radius: '50%',
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
  
  statusChart.value.setOption(option)
}

// 更新趋势图
const updateTrendChart = () => {
  if (!trendChart.value) {
    trendChart.value = echarts.init(trendChartRef.value)
  }
  
  const trendData = dashboardData.value.trendData || []
  const dates = trendData.map(item => item.date)
  const totalData = trendData.map(item => item.total)
  const approvedData = trendData.map(item => item.approved)
  const completedData = trendData.map(item => item.completed)
  
  const option = {
    tooltip: {
      trigger: 'axis'
    },
    legend: {
      data: ['总计划', '已批准', '已完成']
    },
    xAxis: {
      type: 'category',
      data: dates
    },
    yAxis: {
      type: 'value'
    },
    series: [
      {
        name: '总计划',
        type: 'line',
        data: totalData,
        smooth: true
      },
      {
        name: '已批准',
        type: 'line',
        data: approvedData,
        smooth: true
      },
      {
        name: '已完成',
        type: 'line',
        data: completedData,
        smooth: true
      }
    ]
  }
  
  trendChart.value.setOption(option)
}

// 处理时间范围变化
const handleTimeRangeChange = () => {
  loadDashboardData()
}

// 刷新数据
const refreshData = () => {
  loadDashboardData()
}

// 显示实时统计
const showRealTimeStats = () => {
  realTimeDialogVisible.value = true
}

// 获取排名样式
const getRankClass = (index) => {
  if (index === 0) return 'rank-first'
  if (index === 1) return 'rank-second'
  if (index === 2) return 'rank-third'
  return ''
}

// 生命周期
onMounted(() => {
  loadDashboardData()
  
  // 监听窗口大小变化
  window.addEventListener('resize', () => {
    statusChart.value?.resize()
    trendChart.value?.resize()
  })
})
</script>

<style scoped>
.monitor-dashboard {
  padding: 20px;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #ebeef5;
}

.header-left h2 {
  margin: 0 0 5px 0;
  color: #303133;
}

.header-left p {
  margin: 0;
  color: #909399;
  font-size: 14px;
}

.header-right {
  display: flex;
  gap: 10px;
  align-items: center;
}

.stats-cards {
  margin-bottom: 20px;
}

.stats-card {
  height: 120px;
}

.card-content {
  display: flex;
  align-items: center;
  height: 100%;
}

.card-icon {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
  margin-right: 15px;
}

.card-icon.total {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card-icon.approved {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.card-icon.completed {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.card-icon.overdue {
  background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.card-info {
  flex: 1;
}

.card-number {
  font-size: 28px;
  font-weight: bold;
  color: #303133;
  line-height: 1;
}

.card-label {
  font-size: 14px;
  color: #909399;
  margin-top: 5px;
}

.charts-section {
  margin-bottom: 20px;
}

.chart-container {
  height: 300px;
}

.analysis-section {
  margin-bottom: 20px;
}

.ranking-list {
  max-height: 300px;
  overflow-y: auto;
}

.ranking-item {
  display: flex;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px solid #f0f0f0;
}

.ranking-item:last-child {
  border-bottom: none;
}

.rank-number {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: white;
  background-color: #c0c4cc;
  margin-right: 10px;
}

.rank-number.rank-first {
  background-color: #f56c6c;
}

.rank-number.rank-second {
  background-color: #e6a23c;
}

.rank-number.rank-third {
  background-color: #67c23a;
}

.rank-info {
  flex: 1;
}

.rank-name {
  font-weight: 500;
  color: #303133;
}

.rank-count {
  font-size: 12px;
  color: #909399;
}

.anomaly-list {
  max-height: 300px;
  overflow-y: auto;
}

.anomaly-item {
  display: flex;
  align-items: center;
  padding: 15px 0;
  border-bottom: 1px solid #f0f0f0;
}

.anomaly-item:last-child {
  border-bottom: none;
}

.anomaly-icon {
  font-size: 20px;
  margin-right: 10px;
}

.anomaly-icon.overdue {
  color: #f56c6c;
}

.anomaly-icon.pending {
  color: #e6a23c;
}

.anomaly-icon.low-completion {
  color: #409eff;
}

.anomaly-info {
  flex: 1;
}

.anomaly-title {
  font-weight: 500;
  color: #303133;
}

.anomaly-count {
  font-size: 12px;
  color: #909399;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .dashboard-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
  
  .header-right {
    width: 100%;
    justify-content: space-between;
  }
  
  .chart-container {
    height: 250px;
  }
}
</style>
