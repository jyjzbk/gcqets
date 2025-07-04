<template>
  <div class="monitor-test">
    <el-card>
      <template #header>
        <h3>实验监控功能测试</h3>
        <p>测试监控API和数据可视化功能</p>
      </template>

      <!-- API测试 -->
      <div class="test-section">
        <h4>监控API测试</h4>
        <el-space wrap>
          <el-button type="primary" @click="testDashboard" :loading="loading.dashboard">
            测试监控看板API
          </el-button>
          <el-button type="success" @click="testProgressStats" :loading="loading.progress">
            测试进度统计API
          </el-button>
          <el-button type="warning" @click="testAnomalyAnalysis" :loading="loading.anomaly">
            测试异常分析API
          </el-button>
          <el-button type="info" @click="testRealTimeStats" :loading="loading.realtime">
            测试实时统计API
          </el-button>
        </el-space>
      </div>

      <!-- 时间范围测试 -->
      <div class="test-section">
        <h4>时间范围测试</h4>
        <el-radio-group v-model="timeRange" @change="handleTimeRangeChange">
          <el-radio-button value="week">本周</el-radio-button>
          <el-radio-button value="month">本月</el-radio-button>
          <el-radio-button value="quarter">本季度</el-radio-button>
          <el-radio-button value="year">本年</el-radio-button>
        </el-radio-group>
      </div>

      <!-- 测试结果 -->
      <div class="test-results">
        <h4>测试结果</h4>
        
        <el-collapse v-model="activeCollapse">
          <el-collapse-item title="监控看板API测试结果" name="dashboard">
            <div v-if="results.dashboard">
              <el-tag :type="results.dashboard.success ? 'success' : 'danger'">
                {{ results.dashboard.success ? '成功' : '失败' }}
              </el-tag>
              <div class="result-summary" v-if="results.dashboard.success">
                <h5>数据摘要</h5>
                <el-descriptions :column="2" border>
                  <el-descriptions-item label="总实验计划">
                    {{ results.dashboard.data?.basicStats?.totalPlans || 0 }}
                  </el-descriptions-item>
                  <el-descriptions-item label="已批准计划">
                    {{ results.dashboard.data?.basicStats?.approvedPlans || 0 }}
                  </el-descriptions-item>
                  <el-descriptions-item label="已完成计划">
                    {{ results.dashboard.data?.basicStats?.completedPlans || 0 }}
                  </el-descriptions-item>
                  <el-descriptions-item label="逾期计划">
                    {{ results.dashboard.data?.basicStats?.overduePlans || 0 }}
                  </el-descriptions-item>
                  <el-descriptions-item label="完成率">
                    {{ results.dashboard.data?.basicStats?.completionRate || 0 }}%
                  </el-descriptions-item>
                  <el-descriptions-item label="时间范围">
                    {{ results.dashboard.data?.timeRange || '-' }}
                  </el-descriptions-item>
                </el-descriptions>
              </div>
              <pre class="test-result-data">{{ JSON.stringify(results.dashboard.data, null, 2) }}</pre>
            </div>
            <div v-else class="no-result">暂无测试结果</div>
          </el-collapse-item>

          <el-collapse-item title="进度统计API测试结果" name="progress">
            <div v-if="results.progress">
              <el-tag :type="results.progress.success ? 'success' : 'danger'">
                {{ results.progress.success ? '成功' : '失败' }}
              </el-tag>
              <pre class="test-result-data">{{ JSON.stringify(results.progress.data, null, 2) }}</pre>
            </div>
            <div v-else class="no-result">暂无测试结果</div>
          </el-collapse-item>

          <el-collapse-item title="异常分析API测试结果" name="anomaly">
            <div v-if="results.anomaly">
              <el-tag :type="results.anomaly.success ? 'success' : 'danger'">
                {{ results.anomaly.success ? '成功' : '失败' }}
              </el-tag>
              <pre class="test-result-data">{{ JSON.stringify(results.anomaly.data, null, 2) }}</pre>
            </div>
            <div v-else class="no-result">暂无测试结果</div>
          </el-collapse-item>

          <el-collapse-item title="实时统计API测试结果" name="realtime">
            <div v-if="results.realtime">
              <el-tag :type="results.realtime.success ? 'success' : 'danger'">
                {{ results.realtime.success ? '成功' : '失败' }}
              </el-tag>
              <pre class="test-result-data">{{ JSON.stringify(results.realtime.data, null, 2) }}</pre>
            </div>
            <div v-else class="no-result">暂无测试结果</div>
          </el-collapse-item>
        </el-collapse>
      </div>

      <!-- 模拟数据测试 -->
      <div class="test-section">
        <h4>模拟数据测试</h4>
        <el-button @click="generateMockData">生成模拟监控数据</el-button>
        <div v-if="mockData.length > 0" class="mock-data">
          <h5>模拟统计数据</h5>
          <el-table :data="mockData" border>
            <el-table-column prop="date" label="日期" />
            <el-table-column prop="totalPlans" label="总计划" />
            <el-table-column prop="approvedPlans" label="已批准" />
            <el-table-column prop="completedPlans" label="已完成" />
            <el-table-column prop="completionRate" label="完成率">
              <template #default="{ row }">
                <el-progress 
                  :percentage="row.completionRate" 
                  :color="getProgressColor(row.completionRate)"
                  :stroke-width="8"
                />
              </template>
            </el-table-column>
          </el-table>
        </div>
      </div>

      <!-- 导航测试 -->
      <div class="test-section">
        <h4>导航测试</h4>
        <el-space>
          <el-button @click="goToMonitor">前往监控看板</el-button>
          <el-button @click="goToCalendar">前往实验日历</el-button>
          <el-button @click="goToReview">前往记录审核</el-button>
        </el-space>
      </div>

      <!-- 性能测试 -->
      <div class="test-section">
        <h4>性能测试</h4>
        <el-space>
          <el-button @click="performanceTest" :loading="loading.performance">
            API响应时间测试
          </el-button>
          <el-button @click="batchTest" :loading="loading.batch">
            批量请求测试
          </el-button>
        </el-space>
        <div v-if="performanceResults.length > 0" class="performance-results">
          <h5>性能测试结果</h5>
          <el-table :data="performanceResults" border>
            <el-table-column prop="api" label="API接口" />
            <el-table-column prop="responseTime" label="响应时间(ms)" />
            <el-table-column prop="status" label="状态">
              <template #default="{ row }">
                <el-tag :type="row.status === 'success' ? 'success' : 'danger'">
                  {{ row.status === 'success' ? '成功' : '失败' }}
                </el-tag>
              </template>
            </el-table-column>
          </el-table>
        </div>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { experimentMonitorApi } from '@/api/experiment-monitor'

const router = useRouter()

// 响应式数据
const timeRange = ref('month')
const activeCollapse = ref(['dashboard'])
const loading = reactive({
  dashboard: false,
  progress: false,
  anomaly: false,
  realtime: false,
  performance: false,
  batch: false
})

const results = reactive({
  dashboard: null,
  progress: null,
  anomaly: null,
  realtime: null
})

const mockData = ref([])
const performanceResults = ref([])

// API测试方法
const testDashboard = async () => {
  loading.dashboard = true
  const startTime = Date.now()
  
  try {
    const response = await experimentMonitorApi.getDashboard({ time_range: timeRange.value })
    
    results.dashboard = {
      success: true,
      data: response.data,
      responseTime: Date.now() - startTime
    }
    
    ElMessage.success('监控看板API测试成功')
  } catch (error) {
    results.dashboard = {
      success: false,
      data: {
        error: error.message,
        response: error.response?.data
      },
      responseTime: Date.now() - startTime
    }
    
    ElMessage.error('监控看板API测试失败: ' + error.message)
  } finally {
    loading.dashboard = false
  }
}

const testProgressStats = async () => {
  loading.progress = true
  try {
    const response = await experimentMonitorApi.getProgressStats({ time_range: timeRange.value })
    
    results.progress = {
      success: true,
      data: response.data
    }
    
    ElMessage.success('进度统计API测试成功')
  } catch (error) {
    results.progress = {
      success: false,
      data: {
        error: error.message,
        response: error.response?.data
      }
    }
    
    ElMessage.error('进度统计API测试失败: ' + error.message)
  } finally {
    loading.progress = false
  }
}

const testAnomalyAnalysis = async () => {
  loading.anomaly = true
  try {
    const response = await experimentMonitorApi.getAnomalyAnalysis({ time_range: timeRange.value })
    
    results.anomaly = {
      success: true,
      data: response.data
    }
    
    ElMessage.success('异常分析API测试成功')
  } catch (error) {
    results.anomaly = {
      success: false,
      data: {
        error: error.message,
        response: error.response?.data
      }
    }
    
    ElMessage.error('异常分析API测试失败: ' + error.message)
  } finally {
    loading.anomaly = false
  }
}

const testRealTimeStats = async () => {
  loading.realtime = true
  try {
    const response = await experimentMonitorApi.getRealTimeStats()
    
    results.realtime = {
      success: true,
      data: response.data
    }
    
    ElMessage.success('实时统计API测试成功')
  } catch (error) {
    results.realtime = {
      success: false,
      data: {
        error: error.message,
        response: error.response?.data
      }
    }
    
    ElMessage.error('实时统计API测试失败: ' + error.message)
  } finally {
    loading.realtime = false
  }
}

// 时间范围变化处理
const handleTimeRangeChange = () => {
  ElMessage.info(`时间范围已切换到: ${timeRange.value}`)
}

// 生成模拟数据
const generateMockData = () => {
  mockData.value = []
  
  for (let i = 0; i < 7; i++) {
    const date = new Date()
    date.setDate(date.getDate() - i)
    
    const totalPlans = Math.floor(Math.random() * 20) + 5
    const approvedPlans = Math.floor(totalPlans * (0.6 + Math.random() * 0.3))
    const completedPlans = Math.floor(approvedPlans * (0.4 + Math.random() * 0.5))
    const completionRate = totalPlans > 0 ? Math.round((completedPlans / totalPlans) * 100) : 0
    
    mockData.value.push({
      date: date.toLocaleDateString('zh-CN'),
      totalPlans,
      approvedPlans,
      completedPlans,
      completionRate
    })
  }
  
  ElMessage.success('已生成7天的模拟监控数据')
}

// 获取进度条颜色
const getProgressColor = (percentage) => {
  if (percentage < 30) return '#f56c6c'
  if (percentage < 60) return '#e6a23c'
  if (percentage < 80) return '#409eff'
  return '#67c23a'
}

// 性能测试
const performanceTest = async () => {
  loading.performance = true
  performanceResults.value = []
  
  const apis = [
    { name: '监控看板', method: () => experimentMonitorApi.getDashboard({ time_range: 'month' }) },
    { name: '进度统计', method: () => experimentMonitorApi.getProgressStats({ time_range: 'month' }) },
    { name: '异常分析', method: () => experimentMonitorApi.getAnomalyAnalysis({ time_range: 'month' }) },
    { name: '实时统计', method: () => experimentMonitorApi.getRealTimeStats() }
  ]
  
  for (const api of apis) {
    const startTime = Date.now()
    try {
      await api.method()
      const responseTime = Date.now() - startTime
      
      performanceResults.value.push({
        api: api.name,
        responseTime,
        status: 'success'
      })
    } catch (error) {
      const responseTime = Date.now() - startTime
      
      performanceResults.value.push({
        api: api.name,
        responseTime,
        status: 'error'
      })
    }
  }
  
  loading.performance = false
  ElMessage.success('性能测试完成')
}

// 批量测试
const batchTest = async () => {
  loading.batch = true
  
  try {
    const promises = [
      experimentMonitorApi.getDashboard({ time_range: 'month' }),
      experimentMonitorApi.getProgressStats({ time_range: 'month' }),
      experimentMonitorApi.getAnomalyAnalysis({ time_range: 'month' }),
      experimentMonitorApi.getRealTimeStats()
    ]
    
    const startTime = Date.now()
    await Promise.all(promises)
    const totalTime = Date.now() - startTime
    
    ElMessage.success(`批量请求测试完成，总耗时: ${totalTime}ms`)
  } catch (error) {
    ElMessage.error('批量请求测试失败: ' + error.message)
  } finally {
    loading.batch = false
  }
}

// 导航方法
const goToMonitor = () => {
  router.push('/experiment-monitor')
}

const goToCalendar = () => {
  router.push('/experiment-calendar')
}

const goToReview = () => {
  router.push('/experiment-review')
}
</script>

<style scoped>
.monitor-test {
  padding: 20px;
}

.test-section {
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 1px solid #ebeef5;
}

.test-section h4 {
  margin: 0 0 15px 0;
  color: #303133;
  font-size: 16px;
}

.test-section h5 {
  margin: 15px 0 10px 0;
  color: #606266;
  font-size: 14px;
}

.test-results {
  margin-top: 20px;
}

.test-result-data {
  background-color: #f8f9fa;
  padding: 15px;
  border-radius: 4px;
  border-left: 3px solid #409eff;
  font-size: 12px;
  max-height: 300px;
  overflow-y: auto;
  margin-top: 10px;
}

.result-summary {
  margin: 15px 0;
}

.no-result {
  color: #909399;
  font-style: italic;
  padding: 20px;
  text-align: center;
}

.mock-data {
  margin-top: 15px;
}

.performance-results {
  margin-top: 15px;
}
</style>
