<template>
  <div class="experiment-catalog-list">
    <!-- 页面标题 -->
    <div class="page-header">
      <h2>实验目录管理</h2>
      <p>管理实验教学目录，包括实验内容、材料、步骤等信息</p>
    </div>

    <!-- 搜索和筛选 -->
    <el-card class="search-card" shadow="never">
      <el-form :model="searchForm" inline>
        <el-form-item label="搜索">
          <el-input
            v-model="searchForm.search"
            placeholder="请输入实验名称、编码或描述"
            style="width: 300px"
            clearable
            @keyup.enter="handleSearch"
          >
            <template #prefix>
              <el-icon><Search /></el-icon>
            </template>
          </el-input>
        </el-form-item>
        
        <el-form-item label="学科">
          <el-select v-model="searchForm.subject" placeholder="请选择学科" clearable style="width: 120px">
            <el-option
              v-for="(label, value) in options.subjects"
              :key="value"
              :label="label"
              :value="value"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="年级">
          <el-select v-model="searchForm.grade" placeholder="请选择年级" clearable style="width: 120px">
            <el-option
              v-for="(label, value) in options.grades"
              :key="value"
              :label="label"
              :value="value"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="实验类型">
          <el-select v-model="searchForm.experiment_type" placeholder="请选择类型" clearable style="width: 120px">
            <el-option
              v-for="(label, value) in options.experiment_types"
              :key="value"
              :label="label"
              :value="value"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="请选择状态" clearable style="width: 100px">
            <el-option
              v-for="(label, value) in options.statuses"
              :key="value"
              :label="label"
              :value="value"
            />
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

    <!-- 操作栏 -->
    <el-card class="toolbar-card" shadow="never">
      <div class="toolbar">
        <div class="toolbar-left">
          <el-button type="primary" @click="handleCreate">
            <el-icon><Plus /></el-icon>
            新建实验目录
          </el-button>
          <el-button @click="handleBatchDelete" :disabled="!selectedRows.length">
            <el-icon><Delete /></el-icon>
            批量删除
          </el-button>
        </div>
        <div class="toolbar-right">
          <el-button @click="handleRefresh">
            <el-icon><Refresh /></el-icon>
            刷新
          </el-button>
        </div>
      </div>
    </el-card>

    <!-- 数据表格 -->
    <el-card class="table-card" shadow="never">
      <el-table
        v-loading="loading"
        :data="tableData"
        @selection-change="handleSelectionChange"
        stripe
        style="width: 100%"
      >
        <el-table-column type="selection" width="55" />
        
        <el-table-column prop="name" label="实验名称" min-width="200">
          <template #default="{ row }">
            <div class="experiment-name">
              <div class="name">{{ row.name }}</div>
              <div class="code">{{ row.code }}</div>
            </div>
          </template>
        </el-table-column>

        <el-table-column prop="subject_name" label="学科" width="80" />
        <el-table-column prop="grade_name" label="年级" width="80" />
        <el-table-column prop="textbook_version" label="教材版本" width="120" />
        <el-table-column prop="experiment_type_name" label="实验类型" width="100" />
        <el-table-column prop="difficulty_level_name" label="难度" width="80" />
        <el-table-column prop="duration_minutes" label="时长(分钟)" width="100" />

        <el-table-column prop="status" label="状态" width="80">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">
              {{ row.status_name }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column prop="creator.name" label="创建人" width="100" />
        <el-table-column prop="created_at" label="创建时间" width="160">
          <template #default="{ row }">
            {{ formatDateTime(row.created_at) }}
          </template>
        </el-table-column>

        <el-table-column label="操作" width="280" fixed="right">
          <template #default="{ row }">
            <div class="action-buttons">
              <el-button type="primary" link @click="handleView(row)">
                <el-icon><View /></el-icon>
                查看
              </el-button>
              <el-button type="primary" link @click="handleEdit(row)">
                <el-icon><Edit /></el-icon>
                编辑
              </el-button>
              <el-button type="primary" link @click="handleVersions(row)">
                <el-icon><Clock /></el-icon>
                版本
              </el-button>
              <el-button type="danger" link @click="handleDelete(row)">
                <el-icon><Delete /></el-icon>
                删除
              </el-button>
            </div>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <div class="pagination-wrapper">
        <el-pagination
          v-model:current-page="pagination.current"
          v-model:page-size="pagination.size"
          :total="pagination.total"
          :page-sizes="[10, 20, 50, 100]"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </el-card>

    <!-- 实验目录表单对话框 -->
    <ExperimentCatalogForm
      v-model:visible="formVisible"
      :form-data="formData"
      :mode="formMode"
      @success="handleFormSuccess"
    />

    <!-- 版本管理对话框 -->
    <CatalogVersionDialog
      v-model:visible="versionVisible"
      :catalog="selectedCatalog"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh, Plus, Delete, View, Edit, Clock } from '@element-plus/icons-vue'
import { experimentCatalogApi } from '../../api/experiment'
import { formatDateTime } from '../../utils/date'
import ExperimentCatalogForm from './components/ExperimentCatalogForm.vue'
import CatalogVersionDialog from './components/CatalogVersionDialog.vue'

// 响应式数据
const loading = ref(false)
const tableData = ref([])
const selectedRows = ref([])
const formVisible = ref(false)
const versionVisible = ref(false)
const formData = ref({})
const formMode = ref('create')
const selectedCatalog = ref(null)
const options = ref({
  subjects: {},
  grades: {},
  experiment_types: {},
  difficulty_levels: {},
  statuses: {}
})

// 搜索表单
const searchForm = reactive({
  search: '',
  subject: '',
  grade: '',
  experiment_type: '',
  status: ''
})

// 分页数据
const pagination = reactive({
  current: 1,
  size: 20,
  total: 0
})

// 计算属性
const getStatusType = computed(() => {
  return (status) => {
    const typeMap = {
      active: 'success',
      inactive: 'info',
      draft: 'warning'
    }
    return typeMap[status] || 'info'
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
    
    const response = await experimentCatalogApi.getList(params)
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

const loadOptions = async () => {
  try {
    const response = await experimentCatalogApi.getOptions()
    if (response.data.success) {
      options.value = response.data.data
    }
  } catch (error) {
    console.error('获取选项数据失败：', error)
  }
}

const handleSearch = () => {
  pagination.current = 1
  loadData()
}

const handleReset = () => {
  Object.keys(searchForm).forEach(key => {
    searchForm[key] = ''
  })
  pagination.current = 1
  loadData()
}

const handleRefresh = () => {
  loadData()
}

const handleCreate = () => {
  formData.value = {}
  formMode.value = 'create'
  formVisible.value = true
}

const handleEdit = (row) => {
  formData.value = { ...row }
  formMode.value = 'edit'
  formVisible.value = true
}

const handleView = (row) => {
  formData.value = { ...row }
  formMode.value = 'view'
  formVisible.value = true
}

const handleVersions = (row) => {
  selectedCatalog.value = row
  versionVisible.value = true
}

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(
      `确定要删除实验目录"${row.name}"吗？`,
      '确认删除',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )

    const response = await experimentCatalogApi.delete(row.id)
    if (response.data.success) {
      ElMessage.success('删除成功')
      loadData()
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败：' + error.message)
    }
  }
}

const handleBatchDelete = async () => {
  try {
    await ElMessageBox.confirm(
      `确定要删除选中的 ${selectedRows.value.length} 个实验目录吗？`,
      '确认批量删除',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )

    // 批量删除逻辑
    const promises = selectedRows.value.map(row => experimentCatalogApi.delete(row.id))
    await Promise.all(promises)
    
    ElMessage.success('批量删除成功')
    loadData()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('批量删除失败：' + error.message)
    }
  }
}

const handleSelectionChange = (selection) => {
  selectedRows.value = selection
}

const handleSizeChange = (size) => {
  pagination.size = size
  pagination.current = 1
  loadData()
}

const handleCurrentChange = (current) => {
  pagination.current = current
  loadData()
}

const handleFormSuccess = () => {
  formVisible.value = false
  loadData()
}

// 生命周期
onMounted(() => {
  loadOptions()
  loadData()
})
</script>

<style scoped>
.experiment-catalog-list {
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

.search-card,
.toolbar-card,
.table-card {
  margin-bottom: 20px;
}

.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.experiment-name .name {
  font-weight: 500;
  color: #303133;
}

.experiment-name .code {
  font-size: 12px;
  color: #909399;
  margin-top: 2px;
}

.pagination-wrapper {
  margin-top: 20px;
  text-align: right;
}

.action-buttons {
  display: flex;
  flex-wrap: nowrap;
  gap: 4px;
  white-space: nowrap;
}
</style>
