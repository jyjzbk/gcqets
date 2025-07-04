<template>
  <div class="calendar-test">
    <el-card>
      <template #header>
        <h3>实验日历功能测试</h3>
      </template>

      <!-- 基本信息 -->
      <div class="test-section">
        <h4>API连接测试</h4>
        <el-button @click="testCalendarAPI" :loading="loading">测试日历API</el-button>
        <el-button @click="testAlertsAPI" :loading="loading">测试预警API</el-button>
        
        <div v-if="apiResult" class="api-result">
          <h5>API响应结果:</h5>
          <pre>{{ JSON.stringify(apiResult, null, 2) }}</pre>
        </div>
      </div>

      <!-- 简单日历显示 -->
      <div class="test-section">
        <h4>日历显示测试</h4>
        <el-calendar v-model="selectedDate">
          <template #date-cell="{ data }">
            <div class="test-calendar-day">
              <div class="day-number">{{ data.day.split('-').pop() }}</div>
              <div class="test-events">
                <div 
                  v-for="event in getTestEvents(data.day)" 
                  :key="event.id"
                  class="test-event"
                  :style="{ backgroundColor: event.color }"
                >
                  {{ event.title }}
                </div>
              </div>
            </div>
          </template>
        </el-calendar>
      </div>

      <!-- 测试数据 -->
      <div class="test-section">
        <h4>测试数据</h4>
        <el-button @click="addTestEvent">添加测试事件</el-button>
        <el-button @click="clearTestEvents">清空测试事件</el-button>
        
        <div class="test-events-list">
          <div v-for="event in testEvents" :key="event.id" class="test-event-item">
            <span>{{ event.title }}</span>
            <span>{{ event.date }}</span>
            <el-tag :type="event.type">{{ event.status }}</el-tag>
          </div>
        </div>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { ElMessage } from 'element-plus'
import { experimentCalendarApi } from '@/api/experiment'

// 响应式数据
const selectedDate = ref(new Date())
const loading = ref(false)
const apiResult = ref(null)
const testEvents = ref([
  {
    id: 1,
    title: '化学实验',
    date: '2025-07-03',
    status: '已批准',
    type: 'success',
    color: '#67c23a'
  },
  {
    id: 2,
    title: '物理实验',
    date: '2025-07-05',
    status: '待审批',
    type: 'warning',
    color: '#e6a23c'
  },
  {
    id: 3,
    title: '生物实验',
    date: '2025-07-08',
    status: '逾期',
    type: 'danger',
    color: '#f56c6c'
  }
])

// 方法
const testCalendarAPI = async () => {
  loading.value = true
  try {
    const start = '2025-07-01'
    const end = '2025-07-31'
    
    const response = await experimentCalendarApi.getCalendarData({ start, end })
    apiResult.value = response.data
    
    if (response.data.success) {
      ElMessage.success('日历API测试成功')
    } else {
      ElMessage.error('日历API返回错误: ' + response.data.message)
    }
  } catch (error) {
    console.error('API测试失败:', error)
    apiResult.value = { error: error.message }
    ElMessage.error('API测试失败: ' + error.message)
  } finally {
    loading.value = false
  }
}

const testAlertsAPI = async () => {
  loading.value = true
  try {
    const response = await experimentCalendarApi.getOverdueAlerts({ days: 7 })
    apiResult.value = response.data
    
    if (response.data.success) {
      ElMessage.success('预警API测试成功')
    } else {
      ElMessage.error('预警API返回错误: ' + response.data.message)
    }
  } catch (error) {
    console.error('API测试失败:', error)
    apiResult.value = { error: error.message }
    ElMessage.error('API测试失败: ' + error.message)
  } finally {
    loading.value = false
  }
}

const getTestEvents = (day) => {
  return testEvents.value.filter(event => event.date === day)
}

const addTestEvent = () => {
  const newEvent = {
    id: Date.now(),
    title: `测试实验 ${testEvents.value.length + 1}`,
    date: new Date().toISOString().split('T')[0],
    status: '测试',
    type: 'info',
    color: '#409eff'
  }
  testEvents.value.push(newEvent)
  ElMessage.success('添加测试事件成功')
}

const clearTestEvents = () => {
  testEvents.value = []
  ElMessage.success('清空测试事件成功')
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

.api-result {
  margin-top: 15px;
  padding: 15px;
  background-color: #f8f9fa;
  border-radius: 4px;
  border-left: 3px solid #409eff;
}

.api-result h5 {
  margin: 0 0 10px 0;
  color: #606266;
}

.api-result pre {
  margin: 0;
  font-size: 12px;
  color: #303133;
  max-height: 300px;
  overflow-y: auto;
}

.test-calendar-day {
  height: 80px;
  padding: 4px;
}

.day-number {
  font-weight: 600;
  color: #303133;
  margin-bottom: 4px;
}

.test-events {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.test-event {
  color: white;
  padding: 2px 4px;
  border-radius: 2px;
  font-size: 10px;
  text-align: center;
}

.test-events-list {
  margin-top: 15px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.test-event-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  background-color: #f8f9fa;
  border-radius: 4px;
  border: 1px solid #ebeef5;
}

/* Element Plus 日历样式 */
:deep(.el-calendar-table .el-calendar-day) {
  height: 100px;
  padding: 0;
}

:deep(.el-calendar-table td) {
  vertical-align: top;
}
</style>
