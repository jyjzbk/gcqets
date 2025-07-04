<template>
  <div class="import-test">
    <el-card>
      <template #header>
        <h3>导入测试页面</h3>
        <p>测试各种模块的导入是否正常工作</p>
      </template>

      <div class="test-section">
        <h4>API 导入测试</h4>
        <el-space wrap>
          <el-button @click="testCalendarApi" :loading="loading.calendar">
            测试日历API导入
          </el-button>
          <el-button @click="testMonitorApi" :loading="loading.monitor">
            测试监控API导入
          </el-button>
          <el-button @click="testRequestUtil" :loading="loading.request">
            测试请求工具导入
          </el-button>
        </el-space>
      </div>

      <div class="test-results">
        <h4>测试结果</h4>
        <div v-for="(result, index) in testResults" :key="index" class="test-result">
          <el-tag :type="result.success ? 'success' : 'danger'">
            {{ result.name }}: {{ result.success ? '成功' : '失败' }}
          </el-tag>
          <span v-if="result.error" class="error-message">{{ result.error }}</span>
        </div>
      </div>

      <div class="test-section">
        <h4>组件导入测试</h4>
        <el-space wrap>
          <el-button @click="testCalendarComponents">
            测试日历组件
          </el-button>
          <el-button @click="testMonitorComponents">
            测试监控组件
          </el-button>
        </el-space>
      </div>

      <div class="test-section">
        <h4>工具函数测试</h4>
        <el-space wrap>
          <el-button @click="testErrorHandler">
            测试错误处理器
          </el-button>
          <el-button @click="testLazyLoader">
            测试懒加载组件
          </el-button>
        </el-space>
      </div>

      <div class="navigation-test">
        <h4>页面导航测试</h4>
        <el-space wrap>
          <el-button @click="goToCalendar">
            前往实验日历
          </el-button>
          <el-button @click="goToMonitor">
            前往实验监控
          </el-button>
          <el-button @click="goToPlans">
            前往实验计划
          </el-button>
        </el-space>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'

const router = useRouter()

// 响应式数据
const loading = reactive({
  calendar: false,
  monitor: false,
  request: false
})

const testResults = ref([])

// 添加测试结果
const addTestResult = (name, success, error = null) => {
  testResults.value.push({
    name,
    success,
    error,
    timestamp: new Date().toLocaleTimeString()
  })
}

// 测试日历API导入
const testCalendarApi = async () => {
  loading.calendar = true
  try {
    // 动态导入测试
    const { experimentCalendarApi } = await import('@/api/experiment-calendar')
    
    if (experimentCalendarApi && typeof experimentCalendarApi.getCalendarData === 'function') {
      addTestResult('日历API导入', true)
      ElMessage.success('日历API导入成功')
    } else {
      addTestResult('日历API导入', false, 'API对象结构不正确')
      ElMessage.error('日历API结构不正确')
    }
  } catch (error) {
    addTestResult('日历API导入', false, error.message)
    ElMessage.error('日历API导入失败: ' + error.message)
  } finally {
    loading.calendar = false
  }
}

// 测试监控API导入
const testMonitorApi = async () => {
  loading.monitor = true
  try {
    const { experimentMonitorApi } = await import('@/api/experiment-monitor')
    
    if (experimentMonitorApi && typeof experimentMonitorApi.getDashboard === 'function') {
      addTestResult('监控API导入', true)
      ElMessage.success('监控API导入成功')
    } else {
      addTestResult('监控API导入', false, 'API对象结构不正确')
      ElMessage.error('监控API结构不正确')
    }
  } catch (error) {
    addTestResult('监控API导入', false, error.message)
    ElMessage.error('监控API导入失败: ' + error.message)
  } finally {
    loading.monitor = false
  }
}

// 测试请求工具导入
const testRequestUtil = async () => {
  loading.request = true
  try {
    const request = await import('@/utils/request')
    
    if (request.default && typeof request.default.get === 'function') {
      addTestResult('请求工具导入', true)
      ElMessage.success('请求工具导入成功')
    } else {
      addTestResult('请求工具导入', false, '请求工具结构不正确')
      ElMessage.error('请求工具结构不正确')
    }
  } catch (error) {
    addTestResult('请求工具导入', false, error.message)
    ElMessage.error('请求工具导入失败: ' + error.message)
  } finally {
    loading.request = false
  }
}

// 测试日历组件
const testCalendarComponents = async () => {
  try {
    await import('@/views/experiment-process/calendar/ExperimentCalendar.vue')
    await import('@/views/experiment-process/calendar/components/ExperimentDetail.vue')
    await import('@/views/experiment-process/calendar/components/OverdueAlerts.vue')
    
    addTestResult('日历组件导入', true)
    ElMessage.success('日历组件导入成功')
  } catch (error) {
    addTestResult('日历组件导入', false, error.message)
    ElMessage.error('日历组件导入失败: ' + error.message)
  }
}

// 测试监控组件
const testMonitorComponents = async () => {
  try {
    await import('@/views/experiment-process/monitor/Dashboard.vue')
    await import('@/views/experiment-process/monitor/components/RealTimeStats.vue')
    
    addTestResult('监控组件导入', true)
    ElMessage.success('监控组件导入成功')
  } catch (error) {
    addTestResult('监控组件导入', false, error.message)
    ElMessage.error('监控组件导入失败: ' + error.message)
  }
}

// 测试错误处理器
const testErrorHandler = async () => {
  try {
    const errorHandler = await import('@/utils/errorHandler')
    
    if (errorHandler.globalErrorHandler) {
      addTestResult('错误处理器导入', true)
      ElMessage.success('错误处理器导入成功')
    } else {
      addTestResult('错误处理器导入', false, '错误处理器结构不正确')
      ElMessage.error('错误处理器结构不正确')
    }
  } catch (error) {
    addTestResult('错误处理器导入', false, error.message)
    ElMessage.error('错误处理器导入失败: ' + error.message)
  }
}

// 测试懒加载组件
const testLazyLoader = async () => {
  try {
    await import('@/components/LazyLoader.vue')
    
    addTestResult('懒加载组件导入', true)
    ElMessage.success('懒加载组件导入成功')
  } catch (error) {
    addTestResult('懒加载组件导入', false, error.message)
    ElMessage.error('懒加载组件导入失败: ' + error.message)
  }
}

// 导航方法
const goToCalendar = () => {
  router.push('/experiment-calendar')
}

const goToMonitor = () => {
  router.push('/experiment-monitor')
}

const goToPlans = () => {
  router.push('/experiment-plans')
}
</script>

<style scoped>
.import-test {
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

.test-results {
  margin: 20px 0;
}

.test-result {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 8px 0;
}

.error-message {
  color: #f56c6c;
  font-size: 12px;
}

.navigation-test {
  margin-top: 30px;
  padding-top: 20px;
  border-top: 1px solid #ebeef5;
}

.navigation-test h4 {
  margin: 0 0 15px 0;
  color: #303133;
  font-size: 16px;
}
</style>
