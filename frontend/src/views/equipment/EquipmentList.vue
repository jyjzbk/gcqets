<template>
  <div class="equipment-list">
    <!-- 页面标题 -->
    <div class="page-header">
      <h2>设备管理</h2>
      <p>管理实验设备的基本信息、状态和使用情况</p>
    </div>

    <!-- 搜索和筛选 -->
    <el-card class="search-card" shadow="never">
      <el-form :model="searchForm" inline>
        <el-form-item label="设备名称">
          <el-input
            v-model="searchForm.search"
            placeholder="请输入设备名称或编码"
            clearable
            style="width: 200px"
          />
        </el-form-item>
        <el-form-item label="设备分类">
          <el-select
            v-model="searchForm.category_id"
            placeholder="请选择设备分类"
            clearable
            style="width: 200px"
          >
            <el-option
              v-for="category in categories"
              :key="category.id"
              :label="category.name"
              :value="category.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="设备状态">
          <el-select
            v-model="searchForm.status"
            placeholder="请选择设备状态"
            clearable
            style="width: 150px"
          >
            <el-option label="可用" value="available" />
            <el-option label="已借出" value="borrowed" />
            <el-option label="维护中" value="maintenance" />
            <el-option label="损坏" value="damaged" />
            <el-option label="报废" value="scrapped" />
          </el-select>
        </el-form-item>
        <el-form-item label="存放位置">
          <el-input
            v-model="searchForm.location"
            placeholder="请输入存放位置"
            clearable
            style="width: 150px"
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
              <div class="stat-label">设备总数</div>
            </div>
            <el-icon class="stat-icon total"><Box /></el-icon>
          </el-card>
        </el-col>
        <el-col :span="4">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.available || 0 }}</div>
              <div class="stat-label">可用设备</div>
            </div>
            <el-icon class="stat-icon available"><CircleCheck /></el-icon>
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
              <div class="stat-number">{{ statistics.maintenance || 0 }}</div>
              <div class="stat-label">维护中</div>
            </div>
            <el-icon class="stat-icon maintenance"><Tools /></el-icon>
          </el-card>
        </el-col>
        <el-col :span="4">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.damaged || 0 }}</div>
              <div class="stat-label">损坏</div>
            </div>
            <el-icon class="stat-icon damaged"><Warning /></el-icon>
          </el-card>
        </el-col>
        <el-col :span="4">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.needs_maintenance || 0 }}</div>
              <div class="stat-label">需要维护</div>
            </div>
            <el-icon class="stat-icon needs-maintenance"><Clock /></el-icon>
          </el-card>
        </el-col>
      </el-row>
    </div>

    <!-- 操作按钮 -->
    <el-card class="action-card" shadow="never">
      <el-button type="primary" @click="handleAdd">
        <el-icon><Plus /></el-icon>
        添加设备
      </el-button>
      <el-button @click="handleRefresh">
        <el-icon><Refresh /></el-icon>
        刷新
      </el-button>
    </el-card>

    <!-- 设备列表 -->
    <el-card class="table-card" shadow="never">
      <el-table
        v-loading="loading"
        :data="equipmentList"
        stripe
        style="width: 100%"
      >
        <el-table-column prop="code" label="设备编码" width="120" />
        <el-table-column prop="name" label="设备名称" min-width="150" />
        <el-table-column prop="category.name" label="设备分类" width="120" />
        <el-table-column prop="brand" label="品牌" width="100" />
        <el-table-column prop="model" label="型号" width="120" />
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">
              {{ getStatusText(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="location" label="存放位置" width="120" />
        <el-table-column prop="purchase_date" label="采购日期" width="120" />
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

    <!-- 设备详情对话框 -->
    <EquipmentDetail
      v-model:visible="detailVisible"
      :equipment-id="selectedEquipmentId"
      @refresh="loadEquipmentList"
    />

    <!-- 设备编辑对话框 -->
    <EquipmentForm
      v-model:visible="formVisible"
      :equipment-id="selectedEquipmentId"
      :categories="categories"
      @success="handleFormSuccess"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh, Plus, Box, CircleCheck, User, Tools, Warning, Clock } from '@element-plus/icons-vue'
import { equipmentApi, equipmentCategoryApi } from '@/api/equipment'
import EquipmentDetail from './components/EquipmentDetail.vue'
import EquipmentForm from './components/EquipmentForm.vue'

// 响应式数据
const loading = ref(false)
const equipmentList = ref([])
const categories = ref([])
const statistics = ref({})
const detailVisible = ref(false)
const formVisible = ref(false)
const selectedEquipmentId = ref(null)

// 搜索表单
const searchForm = reactive({
  search: '',
  category_id: '',
  status: '',
  location: ''
})

// 分页信息
const pagination = reactive({
  current_page: 1,
  per_page: 20,
  total: 0
})

// 计算属性
const searchParams = computed(() => ({
  ...searchForm,
  page: pagination.current_page,
  per_page: pagination.per_page
}))

// 获取状态类型
const getStatusType = (status) => {
  const types = {
    available: 'success',
    borrowed: 'warning',
    maintenance: 'info',
    damaged: 'danger',
    scrapped: 'info'
  }
  return types[status] || 'info'
}

// 获取状态文本
const getStatusText = (status) => {
  const texts = {
    available: '可用',
    borrowed: '已借出',
    maintenance: '维护中',
    damaged: '损坏',
    scrapped: '报废'
  }
  return texts[status] || status
}

// 加载设备列表
const loadEquipmentList = async () => {
  try {
    loading.value = true
    const response = await equipmentApi.getList(searchParams.value)
    if (response.data.success) {
      equipmentList.value = response.data.data.data
      pagination.current_page = response.data.data.current_page
      pagination.per_page = response.data.data.per_page
      pagination.total = response.data.data.total
    }
  } catch (error) {
    console.error('加载设备列表失败:', error)
    ElMessage.error('加载设备列表失败')
  } finally {
    loading.value = false
  }
}

// 加载设备分类
const loadCategories = async () => {
  try {
    const response = await equipmentCategoryApi.getList({ per_page: 1000 })
    if (response.data.success) {
      categories.value = response.data.data.data
    }
  } catch (error) {
    console.error('加载设备分类失败:', error)
  }
}

// 加载统计信息
const loadStatistics = async () => {
  try {
    const response = await equipmentApi.getStatistics()
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
  loadEquipmentList()
}

// 重置
const handleReset = () => {
  Object.keys(searchForm).forEach(key => {
    searchForm[key] = ''
  })
  pagination.current_page = 1
  loadEquipmentList()
}

// 刷新
const handleRefresh = () => {
  loadEquipmentList()
  loadStatistics()
}

// 添加设备
const handleAdd = () => {
  selectedEquipmentId.value = null
  formVisible.value = true
}

// 查看设备
const handleView = (row) => {
  selectedEquipmentId.value = row.id
  detailVisible.value = true
}

// 编辑设备
const handleEdit = (row) => {
  selectedEquipmentId.value = row.id
  formVisible.value = true
}

// 删除设备
const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(
      `确定要删除设备"${row.name}"吗？此操作不可恢复。`,
      '确认删除',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )

    const response = await equipmentApi.delete(row.id)
    if (response.data.success) {
      ElMessage.success('删除成功')
      loadEquipmentList()
      loadStatistics()
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除设备失败:', error)
      ElMessage.error('删除设备失败')
    }
  }
}

// 表单成功回调
const handleFormSuccess = () => {
  formVisible.value = false
  loadEquipmentList()
  loadStatistics()
}

// 分页大小改变
const handleSizeChange = (size) => {
  pagination.per_page = size
  pagination.current_page = 1
  loadEquipmentList()
}

// 当前页改变
const handleCurrentChange = (page) => {
  pagination.current_page = page
  loadEquipmentList()
}

// 初始化
onMounted(() => {
  loadEquipmentList()
  loadCategories()
  loadStatistics()
})
</script>

<style scoped>
.equipment-list {
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
.stat-icon.available { color: #67C23A; }
.stat-icon.borrowed { color: #E6A23C; }
.stat-icon.maintenance { color: #909399; }
.stat-icon.damaged { color: #F56C6C; }
.stat-icon.needs-maintenance { color: #E6A23C; }

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
