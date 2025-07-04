<template>
  <div class="plan-list">
    <!-- 页面标题 -->
    <div class="page-header">
      <h2>实验计划管理</h2>
      <p>管理实验教学计划的创建、修改、审批等功能</p>
    </div>

    <!-- 统计卡片 -->
    <div class="stats-cards">
      <el-row :gutter="20">
        <el-col :span="6">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.total || 0 }}</div>
              <div class="stat-label">总计划数</div>
            </div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.pending || 0 }}</div>
              <div class="stat-label">待审批</div>
            </div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.approved || 0 }}</div>
              <div class="stat-label">已批准</div>
            </div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.this_month || 0 }}</div>
              <div class="stat-label">本月新增</div>
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
            placeholder="计划名称、编码、班级"
            clearable
            style="width: 200px"
            @keyup.enter="loadData"
          />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="选择状态" clearable style="width: 120px">
            <el-option
              v-for="option in planStatusOptions"
              :key="option.value"
              :label="option.label"
              :value="option.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="优先级">
          <el-select v-model="searchForm.priority" placeholder="选择优先级" clearable style="width: 120px">
            <el-option
              v-for="option in priorityOptions"
              :key="option.value"
              :label="option.label"
              :value="option.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="日期范围">
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
            新建计划
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
        <el-table-column prop="code" label="计划编码" width="140" />
        <el-table-column prop="name" label="计划名称" min-width="200" show-overflow-tooltip />
        <el-table-column label="实验目录" min-width="180" show-overflow-tooltip>
          <template #default="{ row }">
            <div v-if="row.experiment_catalog">
              {{ row.experiment_catalog.name }}
              <el-tag size="small" class="ml-1">{{ getSubjectLabel(row.experiment_catalog.subject) }}</el-tag>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="class_name" label="班级" width="120" />
        <el-table-column prop="student_count" label="学生数" width="80" />
        <el-table-column prop="planned_date" label="计划日期" width="120" />
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">
              {{ getStatusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="优先级" width="80">
          <template #default="{ row }">
            <el-tag :type="getPriorityType(row.priority)" size="small">
              {{ getPriorityLabel(row.priority) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="教师" width="120">
          <template #default="{ row }">
            {{ row.teacher?.real_name || row.teacher?.username }}
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
              @click="editPlan(row)"
            >
              编辑
            </el-button>
            <el-button
              v-if="canSubmit(row)"
              type="success"
              size="small"
              @click="submitPlan(row)"
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
                    v-if="canApprove(row)"
                    :command="{ action: 'approve', row }"
                  >
                    审批通过
                  </el-dropdown-item>
                  <el-dropdown-item
                    v-if="canApprove(row)"
                    :command="{ action: 'reject', row }"
                  >
                    审批拒绝
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
    <PlanForm
      v-model:visible="formVisible"
      :plan-data="currentPlan"
      @success="handleFormSuccess"
    />

    <!-- 详情对话框 -->
    <PlanDetail
      v-model:visible="detailVisible"
      :plan-id="currentPlanId"
    />

    <!-- 审批对话框 -->
    <ApprovalDialog
      v-model:visible="approvalVisible"
      :plan-data="currentPlan"
      :approval-type="approvalType"
      @success="handleApprovalSuccess"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh, Plus, ArrowDown } from '@element-plus/icons-vue'
import { experimentPlanApi, planStatusOptions, priorityOptions, getStatusType, getStatusLabel, getPriorityType, getPriorityLabel, getSubjectLabel } from '@/api/experimentPlan'
import { useAuthStore } from '@/stores/auth'
import PlanForm from './components/PlanForm.vue'
import PlanDetail from './components/PlanDetail.vue'
import ApprovalDialog from './components/ApprovalDialog.vue'

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
  priority: '',
  teacher_id: '',
  experiment_catalog_id: ''
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
const approvalVisible = ref(false)
const currentPlan = ref(null)
const currentPlanId = ref(null)
const approvalType = ref('approve')

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
  return row.status === 'draft' || row.status === 'rejected'
})

const canSubmit = computed(() => (row) => {
  return row.status === 'draft' && row.teacher_id === authStore.user?.id
})

const canApprove = computed(() => (row) => {
  return row.status === 'pending' && ['admin', 'school_admin', 'district_admin'].includes(authStore.user?.user_type)
})

const canDelete = computed(() => (row) => {
  return row.status === 'draft'
})

const hasMoreActions = computed(() => (row) => {
  return canApprove.value(row) || canDelete.value(row)
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
    
    const response = await experimentPlanApi.getList(params)
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
    const response = await experimentPlanApi.getStatistics()
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
  currentPlan.value = null
  formVisible.value = true
}

const viewDetail = (row) => {
  currentPlanId.value = row.id
  detailVisible.value = true
}

const editPlan = (row) => {
  currentPlan.value = { ...row }
  formVisible.value = true
}

const submitPlan = async (row) => {
  try {
    await ElMessageBox.confirm('确定要提交该计划进行审批吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })

    const response = await experimentPlanApi.submit(row.id)
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

const handleCommand = ({ action, row }) => {
  switch (action) {
    case 'approve':
      currentPlan.value = row
      approvalType.value = 'approve'
      approvalVisible.value = true
      break
    case 'reject':
      currentPlan.value = row
      approvalType.value = 'reject'
      approvalVisible.value = true
      break
    case 'delete':
      deletePlan(row)
      break
  }
}

const deletePlan = async (row) => {
  try {
    await ElMessageBox.confirm('确定要删除该计划吗？删除后无法恢复。', '警告', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })

    const response = await experimentPlanApi.delete(row.id)
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

const handleApprovalSuccess = () => {
  approvalVisible.value = false
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
.plan-list {
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

.pagination-wrapper {
  margin-top: 20px;
  text-align: right;
}

.ml-1 {
  margin-left: 4px;
}
</style>
