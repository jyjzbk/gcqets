<template>
  <div class="review-list">
    <!-- 页面标题 -->
    <div class="page-header">
      <h2>实验记录审核</h2>
      <p>审核实验记录的完整性和合规性</p>
    </div>

    <!-- 统计卡片 -->
    <div class="stats-cards">
      <el-row :gutter="20">
        <el-col :span="6">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.total || 0 }}</div>
              <div class="stat-label">总审核数</div>
            </div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ pendingCount || 0 }}</div>
              <div class="stat-label">待审核</div>
            </div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ Math.round(statistics.avg_score || 0) }}</div>
              <div class="stat-label">平均评分</div>
            </div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ Math.round(statistics.avg_duration || 0) }}分</div>
              <div class="stat-label">平均耗时</div>
            </div>
          </el-card>
        </el-col>
      </el-row>
    </div>

    <!-- 搜索和操作区域 -->
    <el-card class="search-card">
      <el-form :model="searchForm" inline>
        <el-form-item label="搜索">
          <el-input
            v-model="searchForm.search"
            placeholder="记录内容、计划名称"
            clearable
            style="width: 200px"
            @keyup.enter="loadData"
          />
        </el-form-item>
        <el-form-item label="完成状态">
          <el-select v-model="searchForm.completion_status" placeholder="选择完成状态" clearable style="width: 120px">
            <el-option
              v-for="option in completionStatusOptions"
              :key="option.value"
              :label="option.label"
              :value="option.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="紧急程度">
          <el-select v-model="searchForm.is_urgent" placeholder="选择紧急程度" clearable style="width: 120px">
            <el-option label="紧急" :value="true" />
            <el-option label="普通" :value="false" />
          </el-select>
        </el-form-item>
        <el-form-item label="执行日期">
          <el-date-picker
            v-model="dateRange"
            type="daterange"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            format="YYYY-MM-DD"
            value-format="YYYY-MM-DD"
            style="width: 240px"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadData">
            <el-icon><Search /></el-icon>
            搜索
          </el-button>
          <el-button @click="resetSearch">
            <el-icon><Refresh /></el-icon>
            重置
          </el-button>
        </el-form-item>
      </el-form>

      <!-- 批量操作 -->
      <div v-if="selectedRecords.length > 0" class="batch-actions">
        <el-alert
          :title="`已选择 ${selectedRecords.length} 条记录`"
          type="info"
          show-icon
          :closable="false"
        />
        <div class="batch-buttons">
          <el-button type="success" @click="showBatchDialog('approve')">
            <el-icon><Check /></el-icon>
            批量通过
          </el-button>
          <el-button type="danger" @click="showBatchDialog('reject')">
            <el-icon><Close /></el-icon>
            批量拒绝
          </el-button>
          <el-button @click="clearSelection">
            <el-icon><RefreshLeft /></el-icon>
            清空选择
          </el-button>
        </div>
      </div>
    </el-card>

    <!-- 数据表格 -->
    <el-card>
      <el-table
        ref="tableRef"
        v-loading="loading"
        :data="tableData"
        stripe
        @selection-change="handleSelectionChange"
        @sort-change="handleSortChange"
      >
        <el-table-column type="selection" width="55" />
        <el-table-column label="实验计划" min-width="200" show-overflow-tooltip>
          <template #default="{ row }">
            <div v-if="row.experiment_plan">
              <div class="plan-name">{{ row.experiment_plan.name }}</div>
              <div class="plan-code">{{ row.experiment_plan.code }}</div>
              <div class="plan-class">{{ row.experiment_plan.class_name }}</div>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="execution_date" label="执行日期" width="120" />
        <el-table-column label="完成状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getCompletionStatusType(row.completion_status)">
              {{ getCompletionStatusLabel(row.completion_status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="完成度" width="120">
          <template #default="{ row }">
            <el-progress
              :percentage="row.completion_percentage || 0"
              :color="getCompletionColor(row.completion_percentage || 0)"
              :stroke-width="6"
              text-inside
            />
          </template>
        </el-table-column>
        <el-table-column label="照片数" width="80">
          <template #default="{ row }">
            <el-badge :value="row.photo_count || 0" class="photo-badge">
              <el-icon><Picture /></el-icon>
            </el-badge>
          </template>
        </el-table-column>
        <el-table-column label="器材确认" width="100">
          <template #default="{ row }">
            <el-tag :type="row.equipment_confirmed ? 'success' : 'warning'" size="small">
              {{ row.equipment_confirmed ? '已确认' : '未确认' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="创建人" width="120">
          <template #default="{ row }">
            {{ row.creator?.real_name || row.creator?.username }}
          </template>
        </el-table-column>
        <el-table-column label="提交时间" width="160" sortable="custom">
          <template #default="{ row }">
            {{ formatDateTime(row.updated_at) }}
          </template>
        </el-table-column>
        <el-table-column label="操作" width="280" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" @click="viewDetail(row)">
              查看详情
            </el-button>
            <el-button type="success" size="small" @click="showReviewDialog(row, 'approve')">
              通过
            </el-button>
            <el-button type="danger" size="small" @click="showReviewDialog(row, 'reject')">
              拒绝
            </el-button>
            <el-dropdown @command="handleCommand">
              <el-button type="info" size="small">
                更多<el-icon class="el-icon--right"><arrow-down /></el-icon>
              </el-button>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item :command="{ action: 'revision', row }">
                    要求修改
                  </el-dropdown-item>
                  <el-dropdown-item :command="{ action: 'ai_check', row }">
                    AI检查
                  </el-dropdown-item>
                  <el-dropdown-item
                    v-if="canForceComplete"
                    :command="{ action: 'force_complete', row }"
                    divided
                  >
                    强制完成
                  </el-dropdown-item>
                  <el-dropdown-item :command="{ action: 'logs', row }" divided>
                    审核日志
                  </el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <div class="pagination-wrapper">
        <el-pagination
          v-model:current-page="pagination.current"
          v-model:page-size="pagination.size"
          :page-sizes="[10, 20, 50, 100]"
          :total="pagination.total"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </el-card>

    <!-- 审核对话框 -->
    <ReviewDialog
      v-model:visible="reviewVisible"
      :record-data="currentRecord"
      :review-action="reviewAction"
      @success="handleReviewSuccess"
    />

    <!-- 批量审核对话框 -->
    <BatchReviewDialog
      v-model:visible="batchVisible"
      :selected-records="selectedRecords"
      :batch-action="batchAction"
      @success="handleBatchSuccess"
    />

    <!-- 记录详情对话框 -->
    <RecordDetail
      v-model:visible="detailVisible"
      :record-id="currentRecordId"
    />

    <!-- 审核日志对话框 -->
    <ReviewLogsDialog
      v-model:visible="logsVisible"
      :record-id="currentRecordId"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh, Check, Close, RefreshLeft, ArrowDown, Picture } from '@element-plus/icons-vue'
import { 
  experimentReviewApi,
  getReviewConfirmMessage
} from '@/api/experimentReview'
import { 
  completionStatusOptions,
  getCompletionStatusType, 
  getCompletionStatusLabel,
  getCompletionColor
} from '@/api/experimentRecord'
import { useAuthStore } from '@/stores/auth'
import ReviewDialog from './components/ReviewDialog.vue'
import BatchReviewDialog from './components/BatchReviewDialog.vue'
import RecordDetail from '../record/components/RecordDetail.vue'
import ReviewLogsDialog from './components/ReviewLogsDialog.vue'

// 状态管理
const authStore = useAuthStore()

// 响应式数据
const tableRef = ref()
const loading = ref(false)
const tableData = ref([])
const statistics = ref({})
const selectedRecords = ref([])

// 搜索表单
const searchForm = reactive({
  search: '',
  completion_status: '',
  is_urgent: ''
})

// 日期范围
const dateRange = ref([])

// 分页数据
const pagination = reactive({
  current: 1,
  size: 20,
  total: 0
})

// 对话框状态
const reviewVisible = ref(false)
const batchVisible = ref(false)
const detailVisible = ref(false)
const logsVisible = ref(false)
const currentRecord = ref(null)
const currentRecordId = ref(null)
const reviewAction = ref('')
const batchAction = ref('')

// 计算属性
const pendingCount = computed(() => tableData.value.length)

const canForceComplete = computed(() => {
  return authStore.user?.user_type === 'admin'
})

// 监听日期范围变化
watch(dateRange, (newVal) => {
  if (newVal && newVal.length === 2) {
    searchForm.start_date = newVal[0]
    searchForm.end_date = newVal[1]
  } else {
    delete searchForm.start_date
    delete searchForm.end_date
  }
})

// 方法
const loadData = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.current,
      per_page: pagination.size,
      ...searchForm
    }
    
    const response = await experimentReviewApi.getPendingRecords(params)
    if (response.data.success) {
      tableData.value = response.data.data.data
      pagination.total = response.data.data.total
    }
  } catch (error) {
    ElMessage.error('获取数据失败：' + error.message)
  } finally {
    loading.value = false
  }
}

const loadStatistics = async () => {
  try {
    const response = await experimentReviewApi.getStatistics()
    if (response.data.success) {
      statistics.value = response.data.data
    }
  } catch (error) {
    console.error('获取统计数据失败：', error)
  }
}

const resetSearch = () => {
  Object.keys(searchForm).forEach(key => {
    searchForm[key] = ''
  })
  dateRange.value = []
  pagination.current = 1
  loadData()
}

const handleSortChange = ({ prop, order }) => {
  if (order) {
    searchForm.sort_by = prop
    searchForm.sort_order = order === 'ascending' ? 'asc' : 'desc'
  } else {
    delete searchForm.sort_by
    delete searchForm.sort_order
  }
  loadData()
}

const handleSelectionChange = (selection) => {
  selectedRecords.value = selection
}

const clearSelection = () => {
  tableRef.value.clearSelection()
  selectedRecords.value = []
}

const viewDetail = (row) => {
  currentRecordId.value = row.id
  detailVisible.value = true
}

const showReviewDialog = (row, action) => {
  currentRecord.value = row
  reviewAction.value = action
  reviewVisible.value = true
}

const showBatchDialog = (action) => {
  batchAction.value = action
  batchVisible.value = true
}

const handleCommand = async ({ action, row }) => {
  switch (action) {
    case 'revision':
      showReviewDialog(row, 'revision')
      break
    case 'ai_check':
      await performAiCheck(row)
      break
    case 'force_complete':
      showReviewDialog(row, 'force_complete')
      break
    case 'logs':
      currentRecordId.value = row.id
      logsVisible.value = true
      break
  }
}

const performAiCheck = async (row) => {
  try {
    await ElMessageBox.confirm(getReviewConfirmMessage('ai_check'), '确认操作', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'info'
    })

    const response = await experimentReviewApi.aiPhotoCheck(row.id)
    if (response.data.success) {
      const result = response.data.data
      ElMessage.success(`AI检查完成！合规率：${result.compliance_rate}%`)
      loadData() // 重新加载数据
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('AI检查失败：' + error.message)
    }
  }
}

const handleReviewSuccess = () => {
  reviewVisible.value = false
  loadData()
  loadStatistics()
}

const handleBatchSuccess = () => {
  batchVisible.value = false
  clearSelection()
  loadData()
  loadStatistics()
}

const formatDateTime = (dateTime) => {
  if (!dateTime) return '-'
  return new Date(dateTime).toLocaleString('zh-CN')
}

const handleSizeChange = (size) => {
  pagination.size = size
  pagination.current = 1
  loadData()
}

const handleCurrentChange = (page) => {
  pagination.current = page
  loadData()
}

// 初始化
onMounted(() => {
  loadData()
  loadStatistics()
})
</script>

<style scoped>
.review-list {
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

.stats-cards {
  margin-bottom: 20px;
}

.stat-card {
  text-align: center;
}

.stat-content {
  padding: 10px;
}

.stat-number {
  font-size: 24px;
  font-weight: bold;
  color: #303133;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 14px;
  color: #909399;
}

.search-card {
  margin-bottom: 20px;
}

.batch-actions {
  margin-top: 15px;
  padding-top: 15px;
  border-top: 1px solid #ebeef5;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.batch-buttons {
  display: flex;
  gap: 10px;
}

.plan-name {
  font-weight: 500;
  color: #303133;
}

.plan-code {
  font-size: 12px;
  color: #909399;
  margin-top: 2px;
}

.plan-class {
  font-size: 12px;
  color: #67c23a;
  margin-top: 2px;
}

.photo-badge {
  cursor: pointer;
}

.pagination-wrapper {
  margin-top: 20px;
  text-align: right;
}
</style>
