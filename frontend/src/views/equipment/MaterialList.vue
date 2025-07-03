<template>
  <div class="material-list">
    <!-- 页面标题 -->
    <div class="page-header">
      <h2>材料管理</h2>
      <p>管理实验材料的库存、使用和消耗情况</p>
    </div>

    <!-- 搜索和筛选 -->
    <el-card class="search-card" shadow="never">
      <el-form :model="searchForm" inline>
        <el-form-item label="材料名称">
          <el-input
            v-model="searchForm.search"
            placeholder="请输入材料名称或编码"
            clearable
            style="width: 200px"
          />
        </el-form-item>
        <el-form-item label="材料分类">
          <el-select
            v-model="searchForm.category_id"
            placeholder="请选择材料分类"
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
        <el-form-item label="材料状态">
          <el-select
            v-model="searchForm.status"
            placeholder="请选择材料状态"
            clearable
            style="width: 150px"
          >
            <el-option label="正常" value="active" />
            <el-option label="停用" value="inactive" />
            <el-option label="过期" value="expired" />
            <el-option label="缺货" value="out_of_stock" />
          </el-select>
        </el-form-item>
        <el-form-item label="库存状态">
          <el-select
            v-model="searchForm.stock_status"
            placeholder="请选择库存状态"
            clearable
            style="width: 150px"
          >
            <el-option label="库存不足" value="low_stock" />
            <el-option label="缺货" value="out_of_stock" />
            <el-option label="即将过期" value="expiring_soon" />
            <el-option label="已过期" value="expired" />
          </el-select>
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
              <div class="stat-label">材料总数</div>
            </div>
            <el-icon class="stat-icon total"><Box /></el-icon>
          </el-card>
        </el-col>
        <el-col :span="4">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.active || 0 }}</div>
              <div class="stat-label">正常材料</div>
            </div>
            <el-icon class="stat-icon active"><CircleCheck /></el-icon>
          </el-card>
        </el-col>
        <el-col :span="4">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.low_stock || 0 }}</div>
              <div class="stat-label">库存不足</div>
            </div>
            <el-icon class="stat-icon low-stock"><Warning /></el-icon>
          </el-card>
        </el-col>
        <el-col :span="4">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.out_of_stock || 0 }}</div>
              <div class="stat-label">缺货</div>
            </div>
            <el-icon class="stat-icon out-of-stock"><Close /></el-icon>
          </el-card>
        </el-col>
        <el-col :span="4">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.expiring_soon || 0 }}</div>
              <div class="stat-label">即将过期</div>
            </div>
            <el-icon class="stat-icon expiring-soon"><Clock /></el-icon>
          </el-card>
        </el-col>
        <el-col :span="4">
          <el-card class="stat-card">
            <div class="stat-content">
              <div class="stat-number">{{ statistics.expired || 0 }}</div>
              <div class="stat-label">已过期</div>
            </div>
            <el-icon class="stat-icon expired"><CircleClose /></el-icon>
          </el-card>
        </el-col>
      </el-row>
    </div>

    <!-- 操作按钮 -->
    <el-card class="action-card" shadow="never">
      <el-button type="primary" @click="handleAdd">
        <el-icon><Plus /></el-icon>
        添加材料
      </el-button>
      <el-button @click="handleRefresh">
        <el-icon><Refresh /></el-icon>
        刷新
      </el-button>
    </el-card>

    <!-- 材料列表 -->
    <el-card class="table-card" shadow="never">
      <el-table
        v-loading="loading"
        :data="materialList"
        stripe
        style="width: 100%"
      >
        <el-table-column prop="code" label="材料编码" width="120" />
        <el-table-column prop="name" label="材料名称" min-width="150" />
        <el-table-column prop="category.name" label="材料分类" width="120" />
        <el-table-column prop="specification" label="规格" width="120" />
        <el-table-column prop="brand" label="品牌" width="100" />
        <el-table-column label="库存状态" width="120">
          <template #default="{ row }">
            <div class="stock-info">
              <span :class="getStockClass(row)">{{ row.current_stock }}</span>
              <span class="unit">{{ row.unit }}</span>
            </div>
            <div class="stock-warning" v-if="isLowStock(row)">
              <el-tag type="warning" size="small">库存不足</el-tag>
            </div>
            <div class="stock-warning" v-if="isOutOfStock(row)">
              <el-tag type="danger" size="small">缺货</el-tag>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">
              {{ getStatusText(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="expiry_date" label="有效期至" width="120">
          <template #default="{ row }">
            <span v-if="row.expiry_date" :class="getExpiryClass(row.expiry_date)">
              {{ row.expiry_date }}
            </span>
            <span v-else>-</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="320" fixed="right">
          <template #default="{ row }">
            <div class="action-buttons">
              <el-button type="primary" size="small" @click="handleView(row)">
                查看
              </el-button>
              <el-button type="warning" size="small" @click="handleEdit(row)">
                编辑
              </el-button>
              <el-button type="info" size="small" @click="handleAdjustStock(row)">
                库存调整
              </el-button>
              <el-button type="danger" size="small" @click="handleDelete(row)">
                删除
              </el-button>
            </div>
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

    <!-- 材料详情对话框 -->
    <MaterialDetail
      v-model:visible="detailVisible"
      :material-id="selectedMaterialId"
      @refresh="loadMaterialList"
    />

    <!-- 材料编辑对话框 -->
    <MaterialForm
      v-model:visible="formVisible"
      :material-id="selectedMaterialId"
      :categories="categories"
      @success="handleFormSuccess"
    />

    <!-- 库存调整对话框 -->
    <StockAdjustment
      v-model:visible="stockVisible"
      :material-id="selectedMaterialId"
      @success="handleStockSuccess"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { 
  Search, Refresh, Plus, Box, CircleCheck, Warning, Close, Clock, CircleClose 
} from '@element-plus/icons-vue'
import { materialApi, materialCategoryApi } from '@/api/equipment'
import MaterialDetail from './components/MaterialDetail.vue'
import MaterialForm from './components/MaterialForm.vue'
import StockAdjustment from './components/StockAdjustment.vue'

// 响应式数据
const loading = ref(false)
const materialList = ref([])
const categories = ref([])
const statistics = ref({})
const detailVisible = ref(false)
const formVisible = ref(false)
const stockVisible = ref(false)
const selectedMaterialId = ref(null)

// 搜索表单
const searchForm = reactive({
  search: '',
  category_id: '',
  status: '',
  stock_status: ''
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
  
  // 处理库存状态筛选
  if (searchForm.stock_status) {
    params[searchForm.stock_status] = true
    delete params.stock_status
  }
  
  return params
})

// 获取状态类型
const getStatusType = (status) => {
  const types = {
    active: 'success',
    inactive: 'info',
    expired: 'danger',
    out_of_stock: 'warning'
  }
  return types[status] || 'info'
}

// 获取状态文本
const getStatusText = (status) => {
  const texts = {
    active: '正常',
    inactive: '停用',
    expired: '过期',
    out_of_stock: '缺货'
  }
  return texts[status] || status
}

// 获取库存样式
const getStockClass = (row) => {
  if (row.current_stock <= 0) return 'stock-danger'
  if (row.current_stock <= row.min_stock) return 'stock-warning'
  return 'stock-normal'
}

// 判断是否库存不足
const isLowStock = (row) => {
  return row.current_stock > 0 && row.current_stock <= row.min_stock
}

// 判断是否缺货
const isOutOfStock = (row) => {
  return row.current_stock <= 0
}

// 获取有效期样式
const getExpiryClass = (expiryDate) => {
  if (!expiryDate) return ''
  const today = new Date()
  const expiry = new Date(expiryDate)
  const diffDays = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24))
  
  if (diffDays < 0) return 'expiry-expired'
  if (diffDays <= 30) return 'expiry-warning'
  return ''
}

// 加载材料列表
const loadMaterialList = async () => {
  try {
    loading.value = true
    const response = await materialApi.getList(searchParams.value)
    if (response.data.success) {
      materialList.value = response.data.data.data
      pagination.current_page = response.data.data.current_page
      pagination.per_page = response.data.data.per_page
      pagination.total = response.data.data.total
    }
  } catch (error) {
    console.error('加载材料列表失败:', error)
    ElMessage.error('加载材料列表失败')
  } finally {
    loading.value = false
  }
}

// 加载材料分类
const loadCategories = async () => {
  try {
    const response = await materialCategoryApi.getList({ per_page: 1000 })
    if (response.data.success) {
      categories.value = response.data.data.data
    }
  } catch (error) {
    console.error('加载材料分类失败:', error)
  }
}

// 加载统计信息
const loadStatistics = async () => {
  try {
    const response = await materialApi.getStatistics()
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
  loadMaterialList()
}

// 重置
const handleReset = () => {
  Object.keys(searchForm).forEach(key => {
    searchForm[key] = ''
  })
  pagination.current_page = 1
  loadMaterialList()
}

// 刷新
const handleRefresh = () => {
  loadMaterialList()
  loadStatistics()
}

// 添加材料
const handleAdd = () => {
  selectedMaterialId.value = null
  formVisible.value = true
}

// 查看材料
const handleView = (row) => {
  selectedMaterialId.value = row.id
  detailVisible.value = true
}

// 编辑材料
const handleEdit = (row) => {
  selectedMaterialId.value = row.id
  formVisible.value = true
}

// 库存调整
const handleAdjustStock = (row) => {
  selectedMaterialId.value = row.id
  stockVisible.value = true
}

// 删除材料
const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(
      `确定要删除材料"${row.name}"吗？此操作不可恢复。`,
      '确认删除',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )

    const response = await materialApi.delete(row.id)
    if (response.data.success) {
      ElMessage.success('删除成功')
      loadMaterialList()
      loadStatistics()
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除材料失败:', error)
      ElMessage.error('删除材料失败')
    }
  }
}

// 表单成功回调
const handleFormSuccess = () => {
  formVisible.value = false
  loadMaterialList()
  loadStatistics()
}

// 库存调整成功回调
const handleStockSuccess = () => {
  stockVisible.value = false
  loadMaterialList()
  loadStatistics()
}

// 分页大小改变
const handleSizeChange = (size) => {
  pagination.per_page = size
  pagination.current_page = 1
  loadMaterialList()
}

// 当前页改变
const handleCurrentChange = (page) => {
  pagination.current_page = page
  loadMaterialList()
}

// 初始化
onMounted(() => {
  loadMaterialList()
  loadCategories()
  loadStatistics()
})
</script>

<style scoped>
.material-list {
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
.stat-icon.active { color: #67C23A; }
.stat-icon.low-stock { color: #E6A23C; }
.stat-icon.out-of-stock { color: #F56C6C; }
.stat-icon.expiring-soon { color: #E6A23C; }
.stat-icon.expired { color: #F56C6C; }

.action-card {
  margin-bottom: 20px;
}

.table-card {
  margin-bottom: 20px;
}

.stock-info {
  display: flex;
  align-items: center;
  gap: 4px;
}

.stock-normal { color: #67C23A; font-weight: bold; }
.stock-warning { color: #E6A23C; font-weight: bold; }
.stock-danger { color: #F56C6C; font-weight: bold; }

.unit {
  color: #909399;
  font-size: 12px;
}

.stock-warning {
  margin-top: 4px;
}

.expiry-warning { color: #E6A23C; }
.expiry-expired { color: #F56C6C; }

.pagination-wrapper {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}

.action-buttons {
  display: flex;
  flex-wrap: nowrap;
  gap: 4px;
  white-space: nowrap;
}
</style>
