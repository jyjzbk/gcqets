<template>
  <div class="calendar-test">
    <el-card>
      <template #header>
        <h3>实验日历功能测试</h3>
        <p>测试日历API和功能是否正常工作</p>
      </template>

      <!-- API测试 -->
      <div class="test-section">
        <h4>API连接测试</h4>
        <el-space wrap>
          <el-button type="primary" @click="testCalendarData" :loading="loading.calendar">
            测试日历数据API
          </el-button>
          <el-button type="warning" @click="testOverdueAlerts" :loading="loading.alerts">
            测试逾期预警API
          </el-button>
          <el-button type="success" @click="testConflictCheck" :loading="loading.conflicts">
            测试冲突检测API
          </el-button>
        </el-space>
      </div>

      <!-- 测试结果 -->
      <div class="test-results">
        <h4>测试结果</h4>
        
        <!-- 日历数据测试结果 -->
        <el-collapse v-model="activeCollapse">
          <el-collapse-item title="日历数据API测试结果" name="calendar">
            <div v-if="results.calendar">
              <el-tag :type="results.calendar.success ? 'success' : 'danger'">
                {{ results.calendar.success ? '成功' : '失败' }}
              </el-tag>
              <pre class="test-result-data">{{ JSON.stringify(results.calendar.data, null, 2) }}</pre>
            </div>
            <div v-else class="no-result">暂无测试结果</div>
          </el-collapse-item>

          <el-collapse-item title="逾期预警API测试结果" name="alerts">
            <div v-if="results.alerts">
              <el-tag :type="results.alerts.success ? 'success' : 'danger'">
                {{ results.alerts.success ? '成功' : '失败' }}
              </el-tag>
              <pre class="test-result-data">{{ JSON.stringify(results.alerts.data, null, 2) }}</pre>
            </div>
            <div v-else class="no-result">暂无测试结果</div>
          </el-collapse-item>

          <el-collapse-item title="冲突检测API测试结果" name="conflicts">
            <div v-if="results.conflicts">
              <el-tag :type="results.conflicts.success ? 'success' : 'danger'">
                {{ results.conflicts.success ? '成功' : '失败' }}
              </el-tag>
              <pre class="test-result-data">{{ JSON.stringify(results.conflicts.data, null, 2) }}</pre>
            </div>
            <div v-else class="no-result">暂无测试结果</div>
          </el-collapse-item>
        </el-collapse>
      </div>

      <!-- 功能测试 -->
      <div class="test-section">
        <h4>功能测试</h4>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-card shadow="hover">
              <h5>日历组件测试</h5>
              <p>测试Element Plus日历组件的基本功能</p>
              <el-calendar v-model="testDate" style="height: 300px;">
                <template #date-cell="{ data }">
                  <div class="test-calendar-cell">
                    <div>{{ data.day.split('-').pop() }}</div>
                    <div v-if="isTestDate(data.day)" class="test-event">
                      测试事件
                    </div>
                  </div>
                </template>
              </el-calendar>
            </el-card>
          </el-col>
          
          <el-col :span="12">
            <el-card shadow="hover">
              <h5>日期处理测试</h5>
              <el-descriptions :column="1" border>
                <el-descriptions-item label="当前日期">
                  {{ formatDate(new Date()) }}
                </el-descriptions-item>
                <el-descriptions-item label="本月开始">
                  {{ formatDate(getMonthStart()) }}
                </el-descriptions-item>
                <el-descriptions-item label="本月结束">
                  {{ formatDate(getMonthEnd()) }}
                </el-descriptions-item>
                <el-descriptions-item label="下月开始">
                  {{ formatDate(getNextMonthStart()) }}
                </el-descriptions-item>
              </el-descriptions>
            </el-card>
          </el-col>
        </el-row>
      </div>

      <!-- 模拟数据测试 -->
      <div class="test-section">
        <h4>模拟数据测试</h4>
        <el-button @click="generateMockData">生成模拟日历数据</el-button>
        <div v-if="mockEvents.length > 0" class="mock-events">
          <h5>模拟事件列表</h5>
          <el-table :data="mockEvents" border>
            <el-table-column prop="title" label="事件标题" />
            <el-table-column prop="start" label="开始日期" />
            <el-table-column prop="status" label="状态">
              <template #default="{ row }">
                <el-tag :type="getStatusType(row.status)">
                  {{ row.statusLabel }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="isOverdue" label="是否逾期">
              <template #default="{ row }">
                <el-tag :type="row.isOverdue ? 'danger' : 'success'">
                  {{ row.isOverdue ? '是' : '否' }}
                </el-tag>
              </template>
            </el-table-column>
          </el-table>
        </div>
      </div>

      <!-- 导航测试 -->
      <div class="test-section">
        <h4>导航测试</h4>
        <el-space>
          <el-button @click="goToCalendar">前往实验日历页面</el-button>
          <el-button @click="goToPlans">前往实验计划页面</el-button>
          <el-button @click="goToRecords">前往实验记录页面</el-button>
        </el-space>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { experimentCalendarApi } from '@/api/experiment-calendar'

const router = useRouter()

// 响应式数据
const testDate = ref(new Date())
const activeCollapse = ref(['calendar'])
const loading = reactive({
  calendar: false,
  alerts: false,
  conflicts: false
})

const results = reactive({
  calendar: null,
  alerts: null,
  conflicts: null
})

const mockEvents = ref([])

// API测试方法
const testCalendarData = async () => {
  loading.calendar = true
  try {
    const start = getMonthStart().toISOString().split('T')[0]
    const end = getMonthEnd().toISOString().split('T')[0]
    
    const response = await experimentCalendarApi.getCalendarData({ start, end })
    
    results.calendar = {
      success: true,
      data: response.data
    }
    
    ElMessage.success('日历数据API测试成功')
  } catch (error) {
    results.calendar = {
      success: false,
      data: {
        error: error.message,
        response: error.response?.data
      }
    }
    
    ElMessage.error('日历数据API测试失败: ' + error.message)
  } finally {
    loading.calendar = false
  }
}

const testOverdueAlerts = async () => {
  loading.alerts = true
  try {
    const response = await experimentCalendarApi.getOverdueAlerts({ days: 7 })
    
    results.alerts = {
      success: true,
      data: response.data
    }
    
    ElMessage.success('逾期预警API测试成功')
  } catch (error) {
    results.alerts = {
      success: false,
      data: {
        error: error.message,
        response: error.response?.data
      }
    }
    
    ElMessage.error('逾期预警API测试失败: ' + error.message)
  } finally {
    loading.alerts = false
  }
}

const testConflictCheck = async () => {
  loading.conflicts = true
  try {
    const response = await experimentCalendarApi.checkConflicts({
      date: new Date().toISOString().split('T')[0],
      teacher_id: 1
    })
    
    results.conflicts = {
      success: true,
      data: response.data
    }
    
    ElMessage.success('冲突检测API测试成功')
  } catch (error) {
    results.conflicts = {
      success: false,
      data: {
        error: error.message,
        response: error.response?.data
      }
    }
    
    ElMessage.error('冲突检测API测试失败: ' + error.message)
  } finally {
    loading.conflicts = false
  }
}

// 工具方法
const formatDate = (date) => {
  return date.toLocaleDateString('zh-CN')
}

const getMonthStart = () => {
  const now = new Date()
  return new Date(now.getFullYear(), now.getMonth(), 1)
}

const getMonthEnd = () => {
  const now = new Date()
  return new Date(now.getFullYear(), now.getMonth() + 1, 0)
}

const getNextMonthStart = () => {
  const now = new Date()
  return new Date(now.getFullYear(), now.getMonth() + 1, 1)
}

const isTestDate = (dateStr) => {
  const today = new Date().toISOString().split('T')[0]
  return dateStr === today
}

const getStatusType = (status) => {
  const types = {
    'draft': 'info',
    'pending': 'warning',
    'approved': 'success',
    'rejected': 'danger',
    'executing': 'primary',
    'completed': 'info',
    'cancelled': 'danger'
  }
  return types[status] || 'info'
}

// 生成模拟数据
const generateMockData = () => {
  const statuses = ['draft', 'pending', 'approved', 'rejected', 'executing', 'completed']
  const statusLabels = {
    'draft': '草稿',
    'pending': '待审批',
    'approved': '已批准',
    'rejected': '已拒绝',
    'executing': '执行中',
    'completed': '已完成'
  }
  
  mockEvents.value = []
  
  for (let i = 0; i < 10; i++) {
    const date = new Date()
    date.setDate(date.getDate() + Math.floor(Math.random() * 30) - 15)
    
    const status = statuses[Math.floor(Math.random() * statuses.length)]
    const isOverdue = status === 'approved' && date < new Date()
    
    mockEvents.value.push({
      id: i + 1,
      title: `模拟实验 ${i + 1}`,
      start: date.toISOString().split('T')[0],
      status: status,
      statusLabel: statusLabels[status],
      isOverdue: isOverdue
    })
  }
  
  ElMessage.success('已生成10个模拟事件')
}

// 导航方法
const goToCalendar = () => {
  router.push('/experiment-calendar')
}

const goToPlans = () => {
  router.push('/experiment-plans')
}

const goToRecords = () => {
  router.push('/experiment-records')
}
</script>

<style scoped>
.calendar-test {
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
  margin: 0 0 10px 0;
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
}

.no-result {
  color: #909399;
  font-style: italic;
  padding: 20px;
  text-align: center;
}

.test-calendar-cell {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.test-event {
  background-color: #409eff;
  color: white;
  padding: 2px 6px;
  border-radius: 3px;
  font-size: 10px;
  margin-top: 2px;
}

.mock-events {
  margin-top: 15px;
}

.mock-events h5 {
  margin-bottom: 10px;
}
</style>
