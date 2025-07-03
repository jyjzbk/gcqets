<template>
  <div class="borrowing-list">
    <!-- 页面标题 -->
    <div class="page-header">
      <h2>设备借用管理</h2>
      <p>管理设备借用申请、审批和归还流程</p>
    </div>

    <!-- 搜索和筛选 -->
    <el-card class="search-card" shadow="never">
      <el-form :model="searchForm" inline>
        <el-form-item label="借用编号">
          <el-input
            v-model="searchForm.search"
            placeholder="请输入借用编号或设备名称"
            clearable
            style="width: 200px"
          />
        </el-form-item>
        <el-form-item label="借用状态">
          <el-select
            v-model="searchForm.status"
            placeholder="请选择借用状态"
            clearable
            style="width: 150px"
          >
            <el-option label="待审批" value="pending" />
            <el-option label="已批准" value="approved" />
            <el-option label="已拒绝" value="rejected" />
            <el-option label="已借出" value="borrowed" />
            <el-option label="已归还" value="returned" />
            <el-option label="已逾期" value="overdue" />
            <el-option label="已取消" value="cancelled" />
          </el-select>
        </el-form-item>
        <el-form-item label="借用人">
          <el-input
            v-model="searchForm.borrower"
            placeholder="请输入借用人姓名"
            clearable
            style="width: 150px"
          />
        </el-form-item>
        <el-form-item label="时间范围">
          <el-date-picker
            v-model="searchForm.dateRange"
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
          <el-button type="primary" @click="handleSearch">
            <el-icon><Search /></el-icon>
            搜索
          </el-button>
          <el-button @click="handleReset">
            <el-icon><Refresh /></el-icon>
            重置
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- 统计卡片 -->
    <div class="stats-cards">
      <el-row :gutter="20">
        <el-col :span="4">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.total || 0 }}</div>
              <div class="stat-label">借用总数</div>
            </div>
            <el-icon class="stat-icon total"><Document /></el-icon>
          </el-card>
        </el-col>
        <el-col :span="4">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.pending || 0 }}</div>
              <div class="stat-label">待审批</div>
            </div>
            <el-icon class="stat-icon pending"><Clock /></el-icon>
          </el-card>
        </el-col>
        <el-col :span="4">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.approved || 0 }}</div>
              <div class="stat-label">已批准</div>
            </div>
            <el-icon class="stat-icon approved"><CircleCheck /></el-icon>
          </el-card>
        </el-col>
        <el-col :span="4">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.borrowed || 0 }}</div>
              <div class="stat-label">已借出</div>
            </div>
            <el-icon class="stat-icon borrowed"><User /></el-icon>
          </el-card>
        </el-col>
        <el-col :span="4">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.returned || 0 }}</div>
              <div class="stat-label">已归还</div>
            </div>
            <el-icon class="stat-icon returned"><Check /></el-icon>
          </el-card>
        </el-col>
        <el-col :span="4">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.overdue || 0 }}</div>
              <div class="stat-label">已逾期</div>
            </div>
            <el-icon class="stat-icon overdue"><Warning /></el-icon>
          </el-card>
        </el-col>
      </el-row>
    </div>

    <!-- 操作按钮 -->
    <el-card class="action-card" shadow="never">
      <el-button type="primary" @click="handleAdd">
        <el-icon><Plus /></el-icon>
        申请借用
      </el-button>
      <el-button @click="handleRefresh">
        <el-icon><Refresh /></el-icon>
        刷新
      </el-button>
    </el-card>

    <!-- 借用记录列表 -->
    <el-card class="table-card" shadow="never">
      <el-table
        v-loading="loading"
        :data="borrowingList"
        stripe
        style="width: 100%"
      >
        <el-table-column prop="borrowing_code" label="借用编号" width="150" />
        <el-table-column prop="equipment.name" label="设备名称" min-width="150" />
        <el-table-column prop="borrower.real_name" label="借用人" width="100" />
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">
              {{ getStatusText(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="planned_start_time" label="计划开始时间" width="150">
          <template #default="{ row }">
            {{ formatDateTime(row.planned_start_time) }}
          </template>
        </el-table-column>
        <el-table-column prop="planned_end_time" label="计划结束时间" width="150">
          <template #default="{ row }">
            {{ formatDateTime(row.planned_end_time) }}
          </template>
        </el-table-column>
        <el-table-column prop="purpose" label="借用目的" min-width="150" />
        <el-table-column label="操作" width="300" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" @click="handleView(row)">
              查看
            </el-button>
            <el-button 
              v-if="row.status === 'pending'" 
              type="success" 
              size="small" 
              @click="handleApprove(row)"
            >
              审批
            </el-button>
            <el-button 
              v-if="row.status === 'approved'" 
              type="warning" 
              size="small" 
              @click="handleBorrow(row)"
            >
              借出
            </el-button>
            <el-button 
              v-if="row.status === 'borrowed'" 
              type="info" 
              size="small" 
              @click="handleReturn(row)"
            >
              归还
            </el-button>
            <el-button 
              v-if="['pending', 'approved'].includes(row.status)" 
              type="danger" 
              size="small" 
              @click="handleCancel(row)"
            >
              取消
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <div class="pagination-wrapper">
        <el-pagination
          v-model:current-page="pagination.current_page"
          v-model:page-size="pagination.per_page"
          :total="pagination.total"
          :page-sizes="[10, 20, 50, 100]"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </el-card>

    <!-- 借用详情对话框 -->
    <BorrowingDetail
      v-model:visible="detailVisible"
      :borrowing-id="selectedBorrowingId"
      @refresh="loadBorrowingList"
    />

    <!-- 借用申请对话框 -->
    <BorrowingForm
      v-model:visible="formVisible"
      @success="handleFormSuccess"
    />

    <!-- 审批对话框 -->
    <BorrowingApproval
      v-model:visible="approvalVisible"
      :borrowing-id="selectedBorrowingId"
      @success="handleApprovalSuccess"
    />

    <!-- 借出对话框 -->
    <BorrowingBorrow
      v-model:visible="borrowVisible"
      :borrowing-id="selectedBorrowingId"
      @success="handleBorrowSuccess"
    />

    <!-- 归还对话框 -->
    <BorrowingReturn
      v-model:visible="returnVisible"
      :borrowing-id="selectedBorrowingId"
      @success="handleReturnSuccess"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { 
  Search, Refresh, Plus, Document, Clock, CircleCheck, User, Check, Warning 
} from '@element-plus/icons-vue'
import { equipmentBorrowingApi } from '@/api/equipment'
import BorrowingDetail from './components/BorrowingDetail.vue'
import BorrowingForm from './components/BorrowingForm.vue'
import BorrowingApproval from './components/BorrowingApproval.vue'
import BorrowingBorrow from './components/BorrowingBorrow.vue'
import BorrowingReturn from './components/BorrowingReturn.vue'

// 响应式数据
const loading = ref(false)
const borrowingList = ref([])
const statistics = ref({})
const detailVisible = ref(false)
const formVisible = ref(false)
const approvalVisible = ref(false)
const borrowVisible = ref(false)
const returnVisible = ref(false)
const selectedBorrowingId = ref(null)

// 搜索表单
const searchForm = reactive({
  search: '',
  status: '',
  borrower: '',
  dateRange: null
})

// 分页信息
const pagination = reactive({
  current_page: 1,
  per_page: 20,
  total: 0
})

// 计算属性
const searchParams = computed(() => {
  const params = {
    ...searchForm,
    page: pagination.current_page,
    per_page: pagination.per_page
  }
  
  if (searchForm.dateRange && searchForm.dateRange.length === 2) {
    params.date_range = searchForm.dateRange.join(',')
  }
  delete params.dateRange
  
  return params
})

// 获取状态类型
const getStatusType = (status) => {
  const types = {
    pending: 'warning',
    approved: 'success',
    rejected: 'danger',
    borrowed: 'primary',
    returned: 'success',
    overdue: 'danger',
    cancelled: 'info'
  }
  return types[status] || 'info'
}

// 获取状态文本
const getStatusText = (status) => {
  const texts = {
    pending: '待审批',
    approved: '已批准',
    rejected: '已拒绝',
    borrowed: '已借出',
    returned: '已归还',
    overdue: '已逾期',
    cancelled: '已取消'
  }
  return texts[status] || status
}

// 格式化日期时间
const formatDateTime = (dateTime) => {
  if (!dateTime) return '-'
  return new Date(dateTime).toLocaleString('zh-CN')
}

// 加载借用记录列表
const loadBorrowingList = async () => {
  try {
    loading.value = true
    const response = await equipmentBorrowingApi.getList(searchParams.value)
    if (response.data.success) {
      borrowingList.value = response.data.data.data
      pagination.current_page = response.data.data.current_page
      pagination.per_page = response.data.data.per_page
      pagination.total = response.data.data.total
    }
  } catch (error) {
    console.error('加载借用记录列表失败:', error)
    ElMessage.error('加载借用记录列表失败')
  } finally {
    loading.value = false
  }
}

// 加载统计信息
const loadStatistics = async () => {
  try {
    const response = await equipmentBorrowingApi.getStatistics()
    if (response.data.success) {
      statistics.value = response.data.data
    }
  } catch (error) {
    console.error('加载统计信息失败:', error)
  }
}

// 搜索
const handleSearch = () => {
  pagination.current_page = 1
  loadBorrowingList()
}

// 重置
const handleReset = () => {
  Object.keys(searchForm).forEach(key => {
    searchForm[key] = key === 'dateRange' ? null : ''
  })
  pagination.current_page = 1
  loadBorrowingList()
}

// 刷新
const handleRefresh = () => {
  loadBorrowingList()
  loadStatistics()
}

// 申请借用
const handleAdd = () => {
  formVisible.value = true
}

// 查看详情
const handleView = (row) => {
  selectedBorrowingId.value = row.id
  detailVisible.value = true
}

// 审批
const handleApprove = (row) => {
  selectedBorrowingId.value = row.id
  approvalVisible.value = true
}

// 借出
const handleBorrow = (row) => {
  selectedBorrowingId.value = row.id
  borrowVisible.value = true
}

// 归还
const handleReturn = (row) => {
  selectedBorrowingId.value = row.id
  returnVisible.value = true
}

// 取消
const handleCancel = async (row) => {
  try {
    await ElMessageBox.confirm(
      `确定要取消借用申请"${row.borrowing_code}"吗？`,
      '确认取消',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )

    const response = await equipmentBorrowingApi.cancel(row.id, {
      notes: '用户取消申请'
    })
    if (response.data.success) {
      ElMessage.success('取消成功')
      loadBorrowingList()
      loadStatistics()
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('取消借用申请失败:', error)
      ElMessage.error('取消借用申请失败')
    }
  }
}

// 表单成功回调
const handleFormSuccess = () => {
  formVisible.value = false
  loadBorrowingList()
  loadStatistics()
}

// 审批成功回调
const handleApprovalSuccess = () => {
  approvalVisible.value = false
  loadBorrowingList()
  loadStatistics()
}

// 借出成功回调
const handleBorrowSuccess = () => {
  borrowVisible.value = false
  loadBorrowingList()
  loadStatistics()
}

// 归还成功回调
const handleReturnSuccess = () => {
  returnVisible.value = false
  loadBorrowingList()
  loadStatistics()
}

// 分页大小改变
const handleSizeChange = (size) => {
  pagination.per_page = size
  pagination.current_page = 1
  loadBorrowingList()
}

// 当前页改变
const handleCurrentChange = (page) => {
  pagination.current_page = page
  loadBorrowingList()
}

// 初始化
onMounted(() => {
  loadBorrowingList()
  loadStatistics()
})
</script>

<style scoped>
.borrowing-list {
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

.search-card {
  margin-bottom: 20px;
}

.stats-cards {
  margin-bottom: 20px;
}

.stat-card {
  position: relative;
  overflow: hidden;
}

.stat-content {
  position: relative;
  z-index: 2;
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

.stat-icon {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 32px;
  opacity: 0.3;
}

.stat-icon.total { color: #409EFF; }
.stat-icon.pending { color: #E6A23C; }
.stat-icon.approved { color: #67C23A; }
.stat-icon.borrowed { color: #409EFF; }
.stat-icon.returned { color: #67C23A; }
.stat-icon.overdue { color: #F56C6C; }

.action-card {
  margin-bottom: 20px;
}

.table-card {
  margin-bottom: 20px;
}

.pagination-wrapper {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}
</style>
