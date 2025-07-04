<template>
  <div class="record-list">
    <!-- 页面标题 -->
    <div class="page-header">
      <h2>实验记录管理</h2>
      <p>管理实验执行记录的填报、审核等功能</p>
    </div>

    <!-- 统计卡片 -->
    <div class="stats-cards">
      <el-row :gutter="20">
        <el-col :span="6">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.total || 0 }}</div>
              <div class="stat-label">总记录数</div>
            </div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.submitted || 0 }}</div>
              <div class="stat-label">待审核</div>
            </div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.completed || 0 }}</div>
              <div class="stat-label">已完成</div>
            </div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ Math.round(statistics.avg_completion || 0) }}%</div>
              <div class="stat-label">平均完成度</div>
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
        <el-form-item label="审核状态">
          <el-select v-model="searchForm.status" placeholder="选择状态" clearable style="width: 120px">
            <el-option
              v-for="option in recordStatusOptions"
              :key="option.value"
              :label="option.label"
              :value="option.value"
            />
          </el-select>
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
          <el-button type="success" @click="showCreateDialog">
            <el-icon><Plus /></el-icon>
            新建记录
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- 数据表格 -->
    <el-card>
      <el-table
        v-loading="loading"
        :data="tableData"
        stripe
        @sort-change="handleSortChange"
      >
        <el-table-column label="实验计划" min-width="200" show-overflow-tooltip>
          <template #default="{ row }">
            <div v-if="row.experiment_plan">
              <div class="plan-name">{{ row.experiment_plan.name }}</div>
              <div class="plan-code">{{ row.experiment_plan.code }}</div>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="execution_date" label="执行日期" width="120" />
        <el-table-column label="执行时长" width="100">
          <template #default="{ row }">
            {{ row.actual_duration ? formatDuration(row.actual_duration) : '-' }}
          </template>
        </el-table-column>
        <el-table-column prop="actual_student_count" label="学生数" width="80" />
        <el-table-column label="完成状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getCompletionStatusType(row.completion_status)">
              {{ getCompletionStatusLabel(row.completion_status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="审核状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getRecordStatusType(row.status)">
              {{ getRecordStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="完成度" width="120">
          <template #default="{ row }">
            <el-progress
              :percentage="row.completion_percentage || 0"
              :color="getCompletionColor(row.completion_percentage || 0)"
              :stroke-width="8"
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
            <el-tag :type="row.equipment_confirmed ? 'success' : 'info'" size="small">
              {{ row.equipment_confirmed ? '已确认' : '未确认' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="创建人" width="120">
          <template #default="{ row }">
            {{ row.creator?.real_name || row.creator?.username }}
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="创建时间" width="160" sortable="custom" />
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" @click="viewDetail(row)">
              查看
            </el-button>
            <el-button
              v-if="canEdit(row)"
              type="warning"
              size="small"
              @click="editRecord(row)"
            >
              编辑
            </el-button>
            <el-button
              v-if="canSubmit(row)"
              type="success"
              size="small"
              @click="submitRecord(row)"
            >
              提交
            </el-button>
            <el-dropdown v-if="hasMoreActions(row)" @command="handleCommand">
              <el-button type="info" size="small">
                更多<el-icon class="el-icon--right"><arrow-down /></el-icon>
              </el-button>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item
                    v-if="canConfirmEquipment(row)"
                    :command="{ action: 'confirmEquipment', row }"
                  >
                    确认器材
                  </el-dropdown-item>
                  <el-dropdown-item
                    v-if="canValidate(row)"
                    :command="{ action: 'validate', row }"
                  >
                    验证数据
                  </el-dropdown-item>
                  <el-dropdown-item
                    v-if="canDelete(row)"
                    :command="{ action: 'delete', row }"
                    divided
                  >
                    删除
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

    <!-- 创建/编辑对话框 -->
    <RecordForm
      v-model:visible="formVisible"
      :record-data="currentRecord"
      @success="handleFormSuccess"
    />

    <!-- 详情对话框 -->
    <RecordDetail
      v-model:visible="detailVisible"
      :record-id="currentRecordId"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh, Plus, ArrowDown, Picture } from '@element-plus/icons-vue'
import { 
  experimentRecordApi, 
  recordStatusOptions, 
  completionStatusOptions,
  getRecordStatusType, 
  getRecordStatusLabel,
  getCompletionStatusType, 
  getCompletionStatusLabel,
  formatDuration,
  getCompletionColor
} from '@/api/experimentRecord'
import { useAuthStore } from '@/stores/auth'
import RecordForm from './components/RecordForm.vue'
import RecordDetail from './components/RecordDetail.vue'

// 状态管理
const authStore = useAuthStore()

// 响应式数据
const loading = ref(false)
const tableData = ref([])
const statistics = ref({})

// 搜索表单
const searchForm = reactive({
  search: '',
  status: '',
  completion_status: '',
  experiment_plan_id: ''
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
const formVisible = ref(false)
const detailVisible = ref(false)
const currentRecord = ref(null)
const currentRecordId = ref(null)

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

// 计算属性
const canEdit = computed(() => (row) => {
  return (row.status === 'draft' || row.status === 'revision_required') && 
         (authStore.user?.user_type === 'admin' || row.created_by === authStore.user?.id)
})

const canSubmit = computed(() => (row) => {
  return row.status === 'draft' && 
         row.completion_status !== 'not_started' &&
         row.created_by === authStore.user?.id
})

const canConfirmEquipment = computed(() => (row) => {
  return !row.equipment_confirmed && 
         (authStore.user?.user_type === 'admin' || row.created_by === authStore.user?.id)
})

const canValidate = computed(() => (row) => {
  return authStore.user?.user_type === 'admin' || row.created_by === authStore.user?.id
})

const canDelete = computed(() => (row) => {
  return row.status === 'draft' && 
         (authStore.user?.user_type === 'admin' || row.created_by === authStore.user?.id)
})

const hasMoreActions = computed(() => (row) => {
  return canConfirmEquipment.value(row) || canValidate.value(row) || canDelete.value(row)
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
    
    const response = await experimentRecordApi.getList(params)
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
    const response = await experimentRecordApi.getStatistics()
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

const showCreateDialog = () => {
  currentRecord.value = null
  formVisible.value = true
}

const viewDetail = (row) => {
  currentRecordId.value = row.id
  detailVisible.value = true
}

const editRecord = (row) => {
  currentRecord.value = { ...row }
  formVisible.value = true
}

const submitRecord = async (row) => {
  try {
    await ElMessageBox.confirm('确定要提交该记录进行审核吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })

    const response = await experimentRecordApi.submit(row.id)
    if (response.data.success) {
      ElMessage.success('提交成功')
      loadData()
      loadStatistics()
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('提交失败：' + error.message)
    }
  }
}

const handleCommand = async ({ action, row }) => {
  switch (action) {
    case 'confirmEquipment':
      await confirmEquipment(row)
      break
    case 'validate':
      await validateData(row)
      break
    case 'delete':
      await deleteRecord(row)
      break
  }
}

const confirmEquipment = async (row) => {
  try {
    await ElMessageBox.confirm('确定要确认器材准备完成吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'info'
    })

    const response = await experimentRecordApi.confirmEquipment(row.id)
    if (response.data.success) {
      ElMessage.success('器材确认成功')
      loadData()
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('器材确认失败：' + error.message)
    }
  }
}

const validateData = async (row) => {
  try {
    const response = await experimentRecordApi.validateData(row.id)
    if (response.data.success) {
      const validation = response.data.data
      let message = `验证完成！完成度：${validation.completion_percentage}%`
      
      if (validation.errors.length > 0) {
        message += `\n错误：${validation.errors.join('、')}`
      }
      
      if (validation.warnings.length > 0) {
        message += `\n建议：${validation.warnings.join('、')}`
      }
      
      ElMessage({
        message,
        type: validation.valid ? 'success' : 'warning',
        duration: 5000
      })
    }
  } catch (error) {
    ElMessage.error('数据验证失败：' + error.message)
  }
}

const deleteRecord = async (row) => {
  try {
    await ElMessageBox.confirm('确定要删除该记录吗？删除后无法恢复。', '警告', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })

    const response = await experimentRecordApi.delete(row.id)
    if (response.data.success) {
      ElMessage.success('删除成功')
      loadData()
      loadStatistics()
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败：' + error.message)
    }
  }
}

const handleFormSuccess = () => {
  formVisible.value = false
  loadData()
  loadStatistics()
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
.record-list {
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

.plan-name {
  font-weight: 500;
  color: #303133;
}

.plan-code {
  font-size: 12px;
  color: #909399;
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
