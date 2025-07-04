<template>
  <div class="system-test">
    <!-- 页面标题 -->
    <div class="page-header">
      <h2>系统功能测试</h2>
      <p>测试实验教学管理系统的各项功能</p>
    </div>

    <!-- 测试控制面板 -->
    <el-card class="test-control">
      <template #header>
        <div class="control-header">
          <span>测试控制面板</span>
          <div class="control-actions">
            <el-button type="primary" @click="runAllTests">
              <el-icon><VideoPlay /></el-icon>
              运行所有测试
            </el-button>
            <el-button @click="clearResults">
              <el-icon><Delete /></el-icon>
              清空结果
            </el-button>
          </div>
        </div>
      </template>

      <div class="test-summary">
        <el-row :gutter="20">
          <el-col :span="6">
            <div class="summary-item">
              <div class="summary-number">{{ testResults.total }}</div>
              <div class="summary-label">总测试数</div>
            </div>
          </el-col>
          <el-col :span="6">
            <div class="summary-item success">
              <div class="summary-number">{{ testResults.passed }}</div>
              <div class="summary-label">通过</div>
            </div>
          </el-col>
          <el-col :span="6">
            <div class="summary-item error">
              <div class="summary-number">{{ testResults.failed }}</div>
              <div class="summary-label">失败</div>
            </div>
          </el-col>
          <el-col :span="6">
            <div class="summary-item warning">
              <div class="summary-number">{{ testResults.skipped }}</div>
              <div class="summary-label">跳过</div>
            </div>
          </el-col>
        </el-row>
      </div>
    </el-card>

    <!-- 测试模块 -->
    <div class="test-modules">
      <!-- 实验计划模块测试 -->
      <el-card class="test-module">
        <template #header>
          <div class="module-header">
            <span>实验计划模块测试</span>
            <el-button size="small" @click="testExperimentPlans">
              <el-icon><VideoPlay /></el-icon>
              运行测试
            </el-button>
          </div>
        </template>

        <div class="test-cases">
          <div
            v-for="testCase in planTestCases"
            :key="testCase.name"
            class="test-case"
            :class="getTestCaseClass(testCase.status)"
          >
            <div class="test-case-header">
              <el-icon>
                <Loading v-if="testCase.status === 'running'" />
                <CircleCheck v-else-if="testCase.status === 'passed'" />
                <CircleClose v-else-if="testCase.status === 'failed'" />
                <Minus v-else />
              </el-icon>
              <span class="test-name">{{ testCase.name }}</span>
              <span v-if="testCase.duration" class="test-duration">{{ testCase.duration }}ms</span>
            </div>
            <div v-if="testCase.error" class="test-error">
              {{ testCase.error }}
            </div>
            <div v-if="testCase.result" class="test-result">
              <pre>{{ JSON.stringify(testCase.result, null, 2) }}</pre>
            </div>
          </div>
        </div>
      </el-card>

      <!-- 实验记录模块测试 -->
      <el-card class="test-module">
        <template #header>
          <div class="module-header">
            <span>实验记录模块测试</span>
            <el-button size="small" @click="testExperimentRecords">
              <el-icon><VideoPlay /></el-icon>
              运行测试
            </el-button>
          </div>
        </template>

        <div class="test-cases">
          <div
            v-for="testCase in recordTestCases"
            :key="testCase.name"
            class="test-case"
            :class="getTestCaseClass(testCase.status)"
          >
            <div class="test-case-header">
              <el-icon>
                <Loading v-if="testCase.status === 'running'" />
                <CircleCheck v-else-if="testCase.status === 'passed'" />
                <CircleClose v-else-if="testCase.status === 'failed'" />
                <Minus v-else />
              </el-icon>
              <span class="test-name">{{ testCase.name }}</span>
              <span v-if="testCase.duration" class="test-duration">{{ testCase.duration }}ms</span>
            </div>
            <div v-if="testCase.error" class="test-error">
              {{ testCase.error }}
            </div>
            <div v-if="testCase.result" class="test-result">
              <pre>{{ JSON.stringify(testCase.result, null, 2) }}</pre>
            </div>
          </div>
        </div>
      </el-card>

      <!-- 审核模块测试 -->
      <el-card class="test-module">
        <template #header>
          <div class="module-header">
            <span>审核模块测试</span>
            <el-button size="small" @click="testExperimentReview">
              <el-icon><VideoPlay /></el-icon>
              运行测试
            </el-button>
          </div>
        </template>

        <div class="test-cases">
          <div
            v-for="testCase in reviewTestCases"
            :key="testCase.name"
            class="test-case"
            :class="getTestCaseClass(testCase.status)"
          >
            <div class="test-case-header">
              <el-icon>
                <Loading v-if="testCase.status === 'running'" />
                <CircleCheck v-else-if="testCase.status === 'passed'" />
                <CircleClose v-else-if="testCase.status === 'failed'" />
                <Minus v-else />
              </el-icon>
              <span class="test-name">{{ testCase.name }}</span>
              <span v-if="testCase.duration" class="test-duration">{{ testCase.duration }}ms</span>
            </div>
            <div v-if="testCase.error" class="test-error">
              {{ testCase.error }}
            </div>
            <div v-if="testCase.result" class="test-result">
              <pre>{{ JSON.stringify(testCase.result, null, 2) }}</pre>
            </div>
          </div>
        </div>
      </el-card>

      <!-- 系统集成测试 -->
      <el-card class="test-module">
        <template #header>
          <div class="module-header">
            <span>系统集成测试</span>
            <el-button size="small" @click="testSystemIntegration">
              <el-icon><VideoPlay /></el-icon>
              运行测试
            </el-button>
          </div>
        </template>

        <div class="test-cases">
          <div
            v-for="testCase in integrationTestCases"
            :key="testCase.name"
            class="test-case"
            :class="getTestCaseClass(testCase.status)"
          >
            <div class="test-case-header">
              <el-icon>
                <Loading v-if="testCase.status === 'running'" />
                <CircleCheck v-else-if="testCase.status === 'passed'" />
                <CircleClose v-else-if="testCase.status === 'failed'" />
                <Minus v-else />
              </el-icon>
              <span class="test-name">{{ testCase.name }}</span>
              <span v-if="testCase.duration" class="test-duration">{{ testCase.duration }}ms</span>
            </div>
            <div v-if="testCase.error" class="test-error">
              {{ testCase.error }}
            </div>
            <div v-if="testCase.result" class="test-result">
              <pre>{{ JSON.stringify(testCase.result, null, 2) }}</pre>
            </div>
          </div>
        </div>
      </el-card>
    </div>

    <!-- 性能测试 -->
    <el-card class="performance-test">
      <template #header>
        <div class="module-header">
          <span>性能测试</span>
          <el-button size="small" @click="runPerformanceTests">
            <el-icon><Timer /></el-icon>
            运行性能测试
          </el-button>
        </div>
      </template>

      <div class="performance-results">
        <el-table :data="performanceResults" stripe>
          <el-table-column prop="api" label="API接口" />
          <el-table-column prop="method" label="请求方法" width="100" />
          <el-table-column prop="avgTime" label="平均响应时间" width="150">
            <template #default="{ row }">
              <span :class="getPerformanceClass(row.avgTime)">{{ row.avgTime }}ms</span>
            </template>
          </el-table-column>
          <el-table-column prop="minTime" label="最小时间" width="100" />
          <el-table-column prop="maxTime" label="最大时间" width="100" />
          <el-table-column prop="requests" label="请求次数" width="100" />
          <el-table-column prop="errors" label="错误次数" width="100" />
          <el-table-column prop="successRate" label="成功率" width="100">
            <template #default="{ row }">
              <span :class="getSuccessRateClass(row.successRate)">{{ row.successRate }}%</span>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { ElMessage } from 'element-plus'
import { 
  VideoPlay, 
  Delete, 
  Loading, 
  CircleCheck, 
  CircleClose, 
  Minus,
  Timer
} from '@element-plus/icons-vue'
import { experimentPlanApi } from '@/api/experimentPlan'
import { experimentRecordApi } from '@/api/experimentRecord'
import { experimentReviewApi } from '@/api/experimentReview'

// 测试结果统计
const testResults = reactive({
  total: 0,
  passed: 0,
  failed: 0,
  skipped: 0
})

// 实验计划测试用例
const planTestCases = ref([
  { name: '获取实验计划列表', status: 'pending', api: 'getList' },
  { name: '创建实验计划', status: 'pending', api: 'create' },
  { name: '获取实验计划详情', status: 'pending', api: 'getDetail' },
  { name: '更新实验计划', status: 'pending', api: 'update' },
  { name: '提交审核', status: 'pending', api: 'submit' },
  { name: '获取统计数据', status: 'pending', api: 'getStatistics' },
  { name: '获取目录选项', status: 'pending', api: 'getCatalogOptions' }
])

// 实验记录测试用例
const recordTestCases = ref([
  { name: '获取实验记录列表', status: 'pending', api: 'getList' },
  { name: '创建实验记录', status: 'pending', api: 'create' },
  { name: '获取实验记录详情', status: 'pending', api: 'getDetail' },
  { name: '更新实验记录', status: 'pending', api: 'update' },
  { name: '提交审核', status: 'pending', api: 'submit' },
  { name: '确认器材准备', status: 'pending', api: 'confirmEquipment' },
  { name: '验证记录数据', status: 'pending', api: 'validateData' },
  { name: '获取计划选项', status: 'pending', api: 'getPlanOptions' }
])

// 审核模块测试用例
const reviewTestCases = ref([
  { name: '获取待审核记录', status: 'pending', api: 'getPendingRecords' },
  { name: '审核通过', status: 'pending', api: 'approve' },
  { name: '审核拒绝', status: 'pending', api: 'reject' },
  { name: '要求修改', status: 'pending', api: 'requestRevision' },
  { name: 'AI照片检查', status: 'pending', api: 'aiPhotoCheck' },
  { name: '获取审核日志', status: 'pending', api: 'getReviewLogs' },
  { name: '获取审核统计', status: 'pending', api: 'getStatistics' }
])

// 系统集成测试用例
const integrationTestCases = ref([
  { name: '完整实验流程测试', status: 'pending', api: 'fullWorkflow' },
  { name: '权限控制测试', status: 'pending', api: 'permissionTest' },
  { name: '数据一致性测试', status: 'pending', api: 'dataConsistency' },
  { name: '并发操作测试', status: 'pending', api: 'concurrencyTest' }
])

// 性能测试结果
const performanceResults = ref([])

// 计算属性
const allTestCases = computed(() => [
  ...planTestCases.value,
  ...recordTestCases.value,
  ...reviewTestCases.value,
  ...integrationTestCases.value
])

// 方法
const getTestCaseClass = (status) => {
  return {
    'test-pending': status === 'pending',
    'test-running': status === 'running',
    'test-passed': status === 'passed',
    'test-failed': status === 'failed',
    'test-skipped': status === 'skipped'
  }
}

const getPerformanceClass = (time) => {
  if (time < 200) return 'performance-excellent'
  if (time < 500) return 'performance-good'
  if (time < 1000) return 'performance-fair'
  return 'performance-poor'
}

const getSuccessRateClass = (rate) => {
  if (rate >= 99) return 'success-rate-excellent'
  if (rate >= 95) return 'success-rate-good'
  if (rate >= 90) return 'success-rate-fair'
  return 'success-rate-poor'
}

const updateTestResults = () => {
  testResults.total = allTestCases.value.length
  testResults.passed = allTestCases.value.filter(t => t.status === 'passed').length
  testResults.failed = allTestCases.value.filter(t => t.status === 'failed').length
  testResults.skipped = allTestCases.value.filter(t => t.status === 'skipped').length
}

const runTestCase = async (testCase, apiFunction, params = {}) => {
  testCase.status = 'running'
  testCase.error = null
  testCase.result = null
  
  const startTime = Date.now()
  
  try {
    const result = await apiFunction(params)
    testCase.duration = Date.now() - startTime
    testCase.status = 'passed'
    testCase.result = result.data
  } catch (error) {
    testCase.duration = Date.now() - startTime
    testCase.status = 'failed'
    testCase.error = error.message || '测试失败'
  }
  
  updateTestResults()
}

const testExperimentPlans = async () => {
  for (const testCase of planTestCases.value) {
    switch (testCase.api) {
      case 'getList':
        await runTestCase(testCase, experimentPlanApi.getList, { page: 1, per_page: 10 })
        break
      case 'getStatistics':
        await runTestCase(testCase, experimentPlanApi.getStatistics)
        break
      case 'getCatalogOptions':
        await runTestCase(testCase, experimentPlanApi.getCatalogOptions)
        break
      default:
        testCase.status = 'skipped'
        testCase.error = '需要具体数据的测试已跳过'
    }
  }
}

const testExperimentRecords = async () => {
  for (const testCase of recordTestCases.value) {
    switch (testCase.api) {
      case 'getList':
        await runTestCase(testCase, experimentRecordApi.getList, { page: 1, per_page: 10 })
        break
      case 'getStatistics':
        await runTestCase(testCase, experimentRecordApi.getStatistics)
        break
      case 'getPlanOptions':
        await runTestCase(testCase, experimentRecordApi.getPlanOptions)
        break
      default:
        testCase.status = 'skipped'
        testCase.error = '需要具体数据的测试已跳过'
    }
  }
}

const testExperimentReview = async () => {
  for (const testCase of reviewTestCases.value) {
    switch (testCase.api) {
      case 'getPendingRecords':
        await runTestCase(testCase, experimentReviewApi.getPendingRecords, { page: 1, per_page: 10 })
        break
      case 'getStatistics':
        await runTestCase(testCase, experimentReviewApi.getStatistics)
        break
      default:
        testCase.status = 'skipped'
        testCase.error = '需要具体数据的测试已跳过'
    }
  }
}

const testSystemIntegration = async () => {
  for (const testCase of integrationTestCases.value) {
    testCase.status = 'skipped'
    testCase.error = '集成测试需要手动执行'
  }
  updateTestResults()
}

const runAllTests = async () => {
  ElMessage.info('开始运行所有测试...')
  
  await testExperimentPlans()
  await testExperimentRecords()
  await testExperimentReview()
  await testSystemIntegration()
  
  ElMessage.success('所有测试运行完成')
}

const clearResults = () => {
  const allCases = [
    ...planTestCases.value,
    ...recordTestCases.value,
    ...reviewTestCases.value,
    ...integrationTestCases.value
  ]
  
  allCases.forEach(testCase => {
    testCase.status = 'pending'
    testCase.error = null
    testCase.result = null
    testCase.duration = null
  })
  
  performanceResults.value = []
  updateTestResults()
}

const runPerformanceTests = async () => {
  ElMessage.info('开始性能测试...')
  
  const apis = [
    { name: '/api/experiment-plans', method: 'GET', func: () => experimentPlanApi.getList({ page: 1, per_page: 20 }) },
    { name: '/api/experiment-records', method: 'GET', func: () => experimentRecordApi.getList({ page: 1, per_page: 20 }) },
    { name: '/api/experiment-review/pending', method: 'GET', func: () => experimentReviewApi.getPendingRecords({ page: 1, per_page: 20 }) }
  ]
  
  performanceResults.value = []
  
  for (const api of apis) {
    const times = []
    let errors = 0
    const requests = 5
    
    for (let i = 0; i < requests; i++) {
      const startTime = Date.now()
      try {
        await api.func()
        times.push(Date.now() - startTime)
      } catch (error) {
        errors++
        times.push(Date.now() - startTime)
      }
    }
    
    const avgTime = Math.round(times.reduce((a, b) => a + b, 0) / times.length)
    const minTime = Math.min(...times)
    const maxTime = Math.max(...times)
    const successRate = Math.round(((requests - errors) / requests) * 100)
    
    performanceResults.value.push({
      api: api.name,
      method: api.method,
      avgTime,
      minTime,
      maxTime,
      requests,
      errors,
      successRate
    })
  }
  
  ElMessage.success('性能测试完成')
}

// 初始化
updateTestResults()
</script>

<style scoped>
.system-test {
  padding: 20px;
}

.page-header {
  margin-bottom: 20px;
}

.page-header h2 {
  margin: 0 0 8px 0;
  color: #303133;
}

.page-header p {
  margin: 0;
  color: #909399;
  font-size: 14px;
}

.test-control {
  margin-bottom: 20px;
}

.control-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.control-actions {
  display: flex;
  gap: 10px;
}

.test-summary {
  padding: 20px 0;
}

.summary-item {
  text-align: center;
  padding: 15px;
  border-radius: 8px;
  background-color: #f8f9fa;
}

.summary-item.success {
  background-color: #f0f9ff;
  color: #67c23a;
}

.summary-item.error {
  background-color: #fef0f0;
  color: #f56c6c;
}

.summary-item.warning {
  background-color: #fdf6ec;
  color: #e6a23c;
}

.summary-number {
  font-size: 24px;
  font-weight: bold;
  margin-bottom: 4px;
}

.summary-label {
  font-size: 14px;
}

.test-modules {
  display: grid;
  gap: 20px;
  margin-bottom: 20px;
}

.test-module {
  margin-bottom: 20px;
}

.module-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.test-cases {
  padding: 10px 0;
}

.test-case {
  padding: 10px;
  margin-bottom: 8px;
  border-radius: 4px;
  border: 1px solid #ebeef5;
}

.test-case.test-pending {
  background-color: #f8f9fa;
}

.test-case.test-running {
  background-color: #e6f7ff;
  border-color: #91d5ff;
}

.test-case.test-passed {
  background-color: #f6ffed;
  border-color: #b7eb8f;
}

.test-case.test-failed {
  background-color: #fff2f0;
  border-color: #ffccc7;
}

.test-case.test-skipped {
  background-color: #fffbe6;
  border-color: #ffe58f;
}

.test-case-header {
  display: flex;
  align-items: center;
  gap: 8px;
}

.test-name {
  flex: 1;
  font-weight: 500;
}

.test-duration {
  font-size: 12px;
  color: #909399;
}

.test-error {
  margin-top: 8px;
  padding: 8px;
  background-color: #fef0f0;
  border-radius: 4px;
  color: #f56c6c;
  font-size: 12px;
}

.test-result {
  margin-top: 8px;
  padding: 8px;
  background-color: #f8f9fa;
  border-radius: 4px;
  font-size: 12px;
  max-height: 200px;
  overflow-y: auto;
}

.test-result pre {
  margin: 0;
  white-space: pre-wrap;
  word-break: break-all;
}

.performance-test {
  margin-bottom: 20px;
}

.performance-results {
  padding: 10px 0;
}

.performance-excellent {
  color: #67c23a;
  font-weight: bold;
}

.performance-good {
  color: #95d475;
}

.performance-fair {
  color: #e6a23c;
}

.performance-poor {
  color: #f56c6c;
  font-weight: bold;
}

.success-rate-excellent {
  color: #67c23a;
  font-weight: bold;
}

.success-rate-good {
  color: #95d475;
}

.success-rate-fair {
  color: #e6a23c;
}

.success-rate-poor {
  color: #f56c6c;
  font-weight: bold;
}
</style>
