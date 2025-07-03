<template>
  <div class="usage-list">
    <!-- 页面标题 -->
    <div class="page-header">
      <h2>材料使用记录</h2>
      <p>查看和管理材料的使用记录和消耗统计</p>
    </div>

    <!-- 搜索和筛选 -->
    <el-card class="search-card" shadow="never">
      <el-form :model="searchForm" inline>
        <el-form-item label="材料名称">
          <el-input
            v-model="searchForm.search"
            placeholder="请输入材料名称"
            clearable
            style="width: 200px"
          />
        </el-form-item>
        <el-form-item label="使用类型">
          <el-select
            v-model="searchForm.usage_type"
            placeholder="请选择使用类型"
            clearable
            style="width: 150px"
          >
            <el-option label="实验教学" value="experiment" />
            <el-option label="设备维护" value="maintenance" />
            <el-option label="课堂教学" value="teaching" />
            <el-option label="其他用途" value="other" />
          </el-select>
        </el-form-item>
        <el-form-item label="使用人">
          <el-input
            v-model="searchForm.user"
            placeholder="请输入使用人姓名"
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
        <el-col :span="6">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.total || 0 }}</div>
              <div class="stat-label">使用记录总数</div>
            </div>
            <el-icon class="stat-icon total"><Document /></el-icon>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.experiment_usage || 0 }}</div>
              <div class="stat-label">实验教学</div>
            </div>
            <el-icon class="stat-icon experiment"><Reading /></el-icon>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.teaching_usage || 0 }}</div>
              <div class="stat-label">课堂教学</div>
            </div>
            <el-icon class="stat-icon teaching"><School /></el-icon>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.maintenance_usage || 0 }}</div>
              <div class="stat-label">设备维护</div>
            </div>
            <el-icon class="stat-icon maintenance"><Tools /></el-icon>
          </el-card>
        </el-col>
      </el-row>
    </div>

    <!-- 操作按钮 -->
    <el-card class="action-card" shadow="never">
      <el-button type="primary" @click="handleAdd">
        <el-icon><Plus /></el-icon>
        添加使用记录
      </el-button>
      <el-button @click="handleRefresh">
        <el-icon><Refresh /></el-icon>
        刷新
      </el-button>
    </el-card>

    <!-- 使用记录列表 -->
    <el-card class="table-card" shadow="never">
      <el-table
        v-loading="loading"
        :data="usageList"
        stripe
        style="width: 100%"
      >
        <el-table-column prop="usage_code" label="使用编号" width="150" />
        <el-table-column prop="material.name" label="材料名称" min-width="150" />
        <el-table-column prop="user.real_name" label="使用人" width="100" />
        <el-table-column label="使用数量" width="120">
          <template #default="{ row }">
            {{ row.quantity_used }} {{ row.material?.unit }}
          </template>
        </el-table-column>
        <el-table-column prop="usage_type" label="使用类型" width="100">
          <template #default="{ row }">
            {{ getUsageTypeText(row.usage_type) }}
          </template>
        </el-table-column>
        <el-table-column prop="used_at" label="使用时间" width="150">
          <template #default="{ row }">
            {{ formatDateTime(row.used_at) }}
          </template>
        </el-table-column>
        <el-table-column prop="purpose" label="使用目的" min-width="150" />
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" @click="handleView(row)">
              查看
            </el-button>
            <el-button type="warning" size="small" @click="handleEdit(row)">
              编辑
            </el-button>
            <el-button type="danger" size="small" @click="handleDelete(row)">
              删除
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

    <!-- 使用记录详情对话框 -->
    <UsageDetail
      v-model:visible="detailVisible"
      :usage-id="selectedUsageId"
      @refresh="loadUsageList"
    />

    <!-- 使用记录表单对话框 -->
    <UsageForm
      v-model:visible="formVisible"
      :usage-id="selectedUsageId"
      @success="handleFormSuccess"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { 
  Search, Refresh, Plus, Document, Reading, School, Tools 
} from '@element-plus/icons-vue'
import { materialUsageApi } from '@/api/equipment'
import UsageDetail from './components/UsageDetail.vue'
import UsageForm from './components/UsageForm.vue'

// 响应式数据
const loading = ref(false)
const usageList = ref([])
const statistics = ref({})
const detailVisible = ref(false)
const formVisible = ref(false)
const selectedUsageId = ref(null)

// 搜索表单
const searchForm = reactive({
  search: '',
  usage_type: '',
  user: '',
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

// 获取使用类型文本
const getUsageTypeText = (type) => {
  const texts = {
    experiment: '实验教学',
    maintenance: '设备维护',
    teaching: '课堂教学',
    other: '其他用途'
  }
  return texts[type] || type
}

// 格式化日期时间
const formatDateTime = (dateTime) => {
  if (!dateTime) return '-'
  return new Date(dateTime).toLocaleString('zh-CN')
}

// 加载使用记录列表
const loadUsageList = async () => {
  try {
    loading.value = true
    const response = await materialUsageApi.getList(searchParams.value)
    if (response.data.success) {
      usageList.value = response.data.data.data
      pagination.current_page = response.data.data.current_page
      pagination.per_page = response.data.data.per_page
      pagination.total = response.data.data.total
    }
  } catch (error) {
    console.error('加载使用记录列表失败:', error)
    ElMessage.error('加载使用记录列表失败')
  } finally {
    loading.value = false
  }
}

// 加载统计信息
const loadStatistics = async () => {
  try {
    const response = await materialUsageApi.getStatistics()
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
  loadUsageList()
}

// 重置
const handleReset = () => {
  Object.keys(searchForm).forEach(key => {
    searchForm[key] = key === 'dateRange' ? null : ''
  })
  pagination.current_page = 1
  loadUsageList()
}

// 刷新
const handleRefresh = () => {
  loadUsageList()
  loadStatistics()
}

// 添加使用记录
const handleAdd = () => {
  selectedUsageId.value = null
  formVisible.value = true
}

// 查看详情
const handleView = (row) => {
  selectedUsageId.value = row.id
  detailVisible.value = true
}

// 编辑使用记录
const handleEdit = (row) => {
  selectedUsageId.value = row.id
  formVisible.value = true
}

// 删除使用记录
const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(
      `确定要删除使用记录"${row.usage_code}"吗？此操作不可恢复。`,
      '确认删除',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )

    const response = await materialUsageApi.delete(row.id)
    if (response.data.success) {
      ElMessage.success('删除成功')
      loadUsageList()
      loadStatistics()
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除使用记录失败:', error)
      ElMessage.error('删除使用记录失败')
    }
  }
}

// 表单成功回调
const handleFormSuccess = () => {
  formVisible.value = false
  loadUsageList()
  loadStatistics()
}

// 分页大小改变
const handleSizeChange = (size) => {
  pagination.per_page = size
  pagination.current_page = 1
  loadUsageList()
}

// 当前页改变
const handleCurrentChange = (page) => {
  pagination.current_page = page
  loadUsageList()
}

// 初始化
onMounted(() => {
  loadUsageList()
  loadStatistics()
})
</script>

<style scoped>
.usage-list {
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
.stat-icon.experiment { color: #67C23A; }
.stat-icon.teaching { color: #E6A23C; }
.stat-icon.maintenance { color: #909399; }

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
