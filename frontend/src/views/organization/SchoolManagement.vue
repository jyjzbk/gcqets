<template>
  <div class="school-management">
    <div class="page-header">
      <h2>学校信息管理</h2>
      <div class="header-actions">
        <el-button-group>
          <el-button 
            :type="activeTab === 'import' ? 'primary' : ''"
            @click="activeTab = 'import'"
          >
            批量导入
          </el-button>
          <el-button 
            :type="activeTab === 'dashboard' ? 'primary' : ''"
            @click="activeTab = 'dashboard'"
          >
            统计仪表板
          </el-button>
          <el-button 
            :type="activeTab === 'list' ? 'primary' : ''"
            @click="activeTab = 'list'"
          >
            学校列表
          </el-button>
        </el-button-group>
      </div>
    </div>

    <!-- 标签页内容 -->
    <div class="tab-content">
      <!-- 批量导入 -->
      <div v-show="activeTab === 'import'" class="tab-pane">
        <SchoolImport @import-success="handleImportSuccess" />
      </div>

      <!-- 统计仪表板 -->
      <div v-show="activeTab === 'dashboard'" class="tab-pane">
        <SchoolImportDashboard @view-history="viewImportHistory" />
      </div>

      <!-- 学校列表 -->
      <div v-show="activeTab === 'list'" class="tab-pane">
        <SchoolList />
      </div>
    </div>

    <!-- 导入历史对话框 -->
    <el-dialog
      v-model="historyDialogVisible"
      title="导入历史"
      width="90%"
      :before-close="handleHistoryDialogClose"
    >
      <div class="import-history-content">
        <div class="history-filters">
          <el-form :inline="true" :model="historyFilters" class="filter-form">
            <el-form-item label="文件名">
              <el-input
                v-model="historyFilters.filename"
                placeholder="请输入文件名"
                clearable
                style="width: 200px"
              />
            </el-form-item>
            <el-form-item label="状态">
              <el-select
                v-model="historyFilters.status"
                placeholder="请选择状态"
                clearable
                style="width: 150px"
              >
                <el-option label="成功" value="success" />
                <el-option label="失败" value="failed" />
                <el-option label="部分成功" value="partial_success" />
                <el-option label="处理中" value="processing" />
              </el-select>
            </el-form-item>
            <el-form-item label="时间范围">
              <el-date-picker
                v-model="historyFilters.dateRange"
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
              <el-button type="primary" @click="searchHistory" :loading="historyLoading">
                搜索
              </el-button>
              <el-button @click="resetHistoryFilters">重置</el-button>
              <el-button type="info" @click="showCompareDialog">
                导入比较
              </el-button>
            </el-form-item>
          </el-form>
        </div>

        <el-table 
          :data="importHistory" 
          style="width: 100%"
          v-loading="historyLoading"
        >
          <el-table-column prop="filename" label="文件名" width="200" />
          <el-table-column prop="parent.name" label="父级组织" width="150" />
          <el-table-column prop="total_rows" label="总行数" width="100" />
          <el-table-column prop="success_rows" label="成功" width="80" />
          <el-table-column prop="failed_rows" label="失败" width="80" />
          <el-table-column prop="success_rate" label="成功率" width="120">
            <template #default="{ row }">
              <el-progress 
                :percentage="row.success_rate" 
                :stroke-width="6"
                :show-text="false"
                :color="getProgressColor(row.success_rate)"
              />
              <span class="success-rate-text">{{ row.success_rate }}%</span>
            </template>
          </el-table-column>
          <el-table-column prop="status_text" label="状态" width="100">
            <template #default="{ row }">
              <el-tag :type="getStatusType(row.status)">
                {{ row.status_text }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="user.real_name" label="导入用户" width="120" />
          <el-table-column prop="created_at" label="导入时间" width="180">
            <template #default="{ row }">
              {{ formatDateTime(row.created_at) }}
            </template>
          </el-table-column>
          <el-table-column label="操作" width="200">
            <template #default="{ row }">
              <el-button
                type="primary"
                size="small"
                @click="viewImportDetail(row)"
              >
                详情
              </el-button>
              <el-button
                type="info"
                size="small"
                @click="generateAuditReport(row)"
              >
                审计
              </el-button>
              <el-button
                v-if="row.can_rollback"
                type="warning"
                size="small"
                @click="rollbackImport(row)"
              >
                回滚
              </el-button>
            </template>
          </el-table-column>
        </el-table>

        <div class="pagination-wrapper">
          <el-pagination
            v-model:current-page="historyPagination.current_page"
            v-model:page-size="historyPagination.per_page"
            :page-sizes="[10, 20, 50, 100]"
            :total="historyPagination.total"
            layout="total, sizes, prev, pager, next, jumper"
            @size-change="loadImportHistory"
            @current-change="loadImportHistory"
          />
        </div>
      </div>
    </el-dialog>

    <!-- 审计报告对话框 -->
    <el-dialog
      v-model="auditDialogVisible"
      title="导入审计报告"
      width="90%"
      :before-close="handleAuditDialogClose"
    >
      <div v-if="auditReport" class="audit-report">
        <!-- 导入基本信息 -->
        <el-card class="audit-section">
          <template #header>
            <span>导入基本信息</span>
          </template>
          <el-descriptions :column="2" border>
            <el-descriptions-item label="导入ID">
              {{ auditReport.import_info.id }}
            </el-descriptions-item>
            <el-descriptions-item label="文件名">
              {{ auditReport.import_info.filename }}
            </el-descriptions-item>
            <el-descriptions-item label="导入用户">
              {{ auditReport.import_info.user }}
            </el-descriptions-item>
            <el-descriptions-item label="父级组织">
              {{ auditReport.import_info.parent_organization }}
            </el-descriptions-item>
            <el-descriptions-item label="开始时间">
              {{ formatDateTime(auditReport.import_info.started_at) }}
            </el-descriptions-item>
            <el-descriptions-item label="完成时间">
              {{ formatDateTime(auditReport.import_info.completed_at) }}
            </el-descriptions-item>
            <el-descriptions-item label="处理时长">
              {{ auditReport.import_info.duration }}秒
            </el-descriptions-item>
            <el-descriptions-item label="状态">
              <el-tag :type="getStatusType(auditReport.import_info.status)">
                {{ auditReport.import_info.status }}
              </el-tag>
            </el-descriptions-item>
          </el-descriptions>
        </el-card>

        <!-- 统计信息 -->
        <el-card class="audit-section">
          <template #header>
            <span>统计信息</span>
          </template>
          <el-row :gutter="20">
            <el-col :span="6">
              <div class="stat-item">
                <div class="stat-number">{{ auditReport.statistics.total_rows }}</div>
                <div class="stat-label">总行数</div>
              </div>
            </el-col>
            <el-col :span="6">
              <div class="stat-item">
                <div class="stat-number success">{{ auditReport.statistics.success_rows }}</div>
                <div class="stat-label">成功行数</div>
              </div>
            </el-col>
            <el-col :span="6">
              <div class="stat-item">
                <div class="stat-number error">{{ auditReport.statistics.failed_rows }}</div>
                <div class="stat-label">失败行数</div>
              </div>
            </el-col>
            <el-col :span="6">
              <div class="stat-item">
                <div class="stat-number">{{ auditReport.statistics.success_rate }}%</div>
                <div class="stat-label">成功率</div>
              </div>
            </el-col>
          </el-row>
        </el-card>

        <!-- 导入的学校列表 -->
        <el-card v-if="auditReport.imported_schools.length > 0" class="audit-section">
          <template #header>
            <span>导入的学校 ({{ auditReport.imported_schools.length }}所)</span>
          </template>
          <el-table :data="auditReport.imported_schools" style="width: 100%" max-height="300">
            <el-table-column prop="name" label="学校名称" />
            <el-table-column prop="code" label="学校代码" />
            <el-table-column prop="created_at" label="创建时间">
              <template #default="{ row }">
                {{ formatDateTime(row.created_at) }}
              </template>
            </el-table-column>
          </el-table>
        </el-card>

        <!-- 错误详情 -->
        <el-card v-if="auditReport.errors.length > 0" class="audit-section">
          <template #header>
            <span>错误详情 ({{ auditReport.errors.length }}项)</span>
          </template>
          <el-table :data="auditReport.errors" style="width: 100%" max-height="300">
            <el-table-column prop="row" label="行号" width="80" />
            <el-table-column prop="errors" label="错误信息">
              <template #default="{ row }">
                <div class="error-list">
                  <div v-for="(error, index) in row.errors" :key="index" class="error-item">
                    {{ error }}
                  </div>
                </div>
              </template>
            </el-table-column>
          </el-table>
        </el-card>

        <!-- 导入选项 -->
        <el-card v-if="auditReport.options" class="audit-section">
          <template #header>
            <span>导入选项</span>
          </template>
          <el-descriptions :column="2" border>
            <el-descriptions-item label="覆盖已存在数据">
              {{ auditReport.options.overwrite ? '是' : '否' }}
            </el-descriptions-item>
            <el-descriptions-item label="仅验证数据">
              {{ auditReport.options.validate_only ? '是' : '否' }}
            </el-descriptions-item>
            <el-descriptions-item label="导入类型">
              {{ auditReport.options.import_type }}
            </el-descriptions-item>
            <el-descriptions-item label="IP地址">
              {{ auditReport.options.ip_address }}
            </el-descriptions-item>
          </el-descriptions>
        </el-card>
      </div>

      <template #footer>
        <div class="dialog-footer">
          <el-button @click="handleAuditDialogClose">关闭</el-button>
          <el-button type="primary" @click="exportAuditReport">
            导出报告
          </el-button>
        </div>
      </template>
    </el-dialog>

    <!-- 导入比较对话框 -->
    <el-dialog
      v-model="compareDialogVisible"
      title="导入比较"
      width="80%"
      :before-close="handleCompareDialogClose"
    >
      <div class="compare-content">
        <el-form :inline="true" class="compare-form">
          <el-form-item label="选择导入记录进行比较:">
            <el-select v-model="compareImport1" placeholder="选择第一个导入" style="width: 200px">
              <el-option
                v-for="item in importHistory"
                :key="item.id"
                :label="`${item.filename} (${formatDateTime(item.created_at)})`"
                :value="item.id"
              />
            </el-select>
            <span style="margin: 0 10px">vs</span>
            <el-select v-model="compareImport2" placeholder="选择第二个导入" style="width: 200px">
              <el-option
                v-for="item in importHistory"
                :key="item.id"
                :label="`${item.filename} (${formatDateTime(item.created_at)})`"
                :value="item.id"
              />
            </el-select>
            <el-button
              type="primary"
              @click="performComparison"
              :disabled="!compareImport1 || !compareImport2"
              :loading="comparing"
            >
              比较
            </el-button>
          </el-form-item>
        </el-form>

        <div v-if="comparisonResult" class="comparison-result">
          <el-card>
            <template #header>
              <span>比较结果</span>
            </template>

            <el-row :gutter="20">
              <el-col :span="12">
                <div class="import-info">
                  <h4>{{ comparisonResult.import1.filename }}</h4>
                  <p>导入时间: {{ formatDateTime(comparisonResult.import1.date) }}</p>
                  <p>学校数量: {{ comparisonResult.import1.schools_count }}</p>
                </div>
              </el-col>
              <el-col :span="12">
                <div class="import-info">
                  <h4>{{ comparisonResult.import2.filename }}</h4>
                  <p>导入时间: {{ formatDateTime(comparisonResult.import2.date) }}</p>
                  <p>学校数量: {{ comparisonResult.import2.schools_count }}</p>
                </div>
              </el-col>
            </el-row>

            <el-divider />

            <div class="comparison-stats">
              <el-row :gutter="20">
                <el-col :span="6">
                  <div class="stat-item">
                    <div class="stat-number">{{ comparisonResult.comparison.common_schools }}</div>
                    <div class="stat-label">共同学校</div>
                  </div>
                </el-col>
                <el-col :span="6">
                  <div class="stat-item">
                    <div class="stat-number">{{ comparisonResult.comparison.unique_to_import1 }}</div>
                    <div class="stat-label">仅第一次导入</div>
                  </div>
                </el-col>
                <el-col :span="6">
                  <div class="stat-item">
                    <div class="stat-number">{{ comparisonResult.comparison.unique_to_import2 }}</div>
                    <div class="stat-label">仅第二次导入</div>
                  </div>
                </el-col>
                <el-col :span="6">
                  <div class="stat-item">
                    <div class="stat-number">{{ comparisonResult.comparison.total_difference }}</div>
                    <div class="stat-label">总差异</div>
                  </div>
                </el-col>
              </el-row>
            </div>
          </el-card>
        </div>
      </div>
    </el-dialog>

    <!-- 导入详情对话框 -->
    <el-dialog
      v-model="detailDialogVisible"
      title="导入详情"
      width="80%"
      :before-close="handleDetailDialogClose"
    >
      <div v-if="selectedImportLog" class="import-detail">
        <el-descriptions :column="2" border>
          <el-descriptions-item label="文件名">
            {{ selectedImportLog.filename }}
          </el-descriptions-item>
          <el-descriptions-item label="文件大小">
            {{ selectedImportLog.formatted_file_size }}
          </el-descriptions-item>
          <el-descriptions-item label="父级组织">
            {{ selectedImportLog.parent?.name || '无' }}
          </el-descriptions-item>
          <el-descriptions-item label="导入用户">
            {{ selectedImportLog.user?.real_name || '未知' }}
          </el-descriptions-item>
          <el-descriptions-item label="状态">
            <el-tag :type="getStatusType(selectedImportLog.status)">
              {{ selectedImportLog.status_text }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="成功率">
            {{ selectedImportLog.success_rate }}%
          </el-descriptions-item>
          <el-descriptions-item label="总行数">
            {{ selectedImportLog.total_rows }}
          </el-descriptions-item>
          <el-descriptions-item label="成功行数">
            {{ selectedImportLog.success_rows }}
          </el-descriptions-item>
          <el-descriptions-item label="失败行数">
            {{ selectedImportLog.failed_rows }}
          </el-descriptions-item>
          <el-descriptions-item label="处理时长">
            {{ selectedImportLog.duration ? selectedImportLog.duration + '秒' : '未知' }}
          </el-descriptions-item>
          <el-descriptions-item label="开始时间">
            {{ formatDateTime(selectedImportLog.started_at) }}
          </el-descriptions-item>
          <el-descriptions-item label="完成时间">
            {{ formatDateTime(selectedImportLog.completed_at) }}
          </el-descriptions-item>
        </el-descriptions>

        <!-- 错误详情 -->
        <div v-if="selectedImportLog.error_details && selectedImportLog.error_details.length > 0" class="error-section">
          <h4>错误详情</h4>
          <el-table :data="selectedImportLog.error_details" style="width: 100%" max-height="300">
            <el-table-column prop="row" label="行号" width="80" />
            <el-table-column prop="errors" label="错误信息">
              <template #default="{ row }">
                <div class="error-list">
                  <div v-for="(error, index) in row.errors" :key="index" class="error-item">
                    {{ error }}
                  </div>
                </div>
              </template>
            </el-table-column>
          </el-table>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import SchoolImport from '../../components/organization/SchoolImport.vue'
import SchoolImportDashboard from '../../components/organization/SchoolImportDashboard.vue'
import SchoolList from '../../components/organization/SchoolList.vue'
import { schoolImportApi } from '../../api/schoolImport'

const route = useRoute()

// 根据路由名称决定默认显示的标签页
const getDefaultTab = () => {
  switch (route.name) {
    case 'Schools':
      return 'list'
    case 'SchoolImport':
      return 'import'
    case 'SchoolDashboard':
      return 'dashboard'
    default:
      return 'list'
  }
}

// 响应式数据
const activeTab = ref(getDefaultTab())

// 导入历史相关
const historyDialogVisible = ref(false)
const historyLoading = ref(false)
const importHistory = ref([])
const historyPagination = reactive({
  current_page: 1,
  per_page: 20,
  total: 0
})

const historyFilters = reactive({
  filename: '',
  status: '',
  dateRange: null
})

// 导入详情相关
const detailDialogVisible = ref(false)
const selectedImportLog = ref(null)

// 审计报告相关
const auditDialogVisible = ref(false)
const auditReport = ref(null)
const auditLoading = ref(false)

// 导入比较相关
const compareDialogVisible = ref(false)
const compareImport1 = ref(null)
const compareImport2 = ref(null)
const comparisonResult = ref(null)
const comparing = ref(false)

// 监听路由变化
watch(() => route.name, () => {
  activeTab.value = getDefaultTab()
}, { immediate: true })

// 方法
const handleImportSuccess = () => {
  // 导入成功后切换到仪表板查看统计
  activeTab.value = 'dashboard'
  ElMessage.success('导入成功，已切换到统计仪表板')
}

const viewImportHistory = () => {
  historyDialogVisible.value = true
  loadImportHistory()
}

const loadImportHistory = async () => {
  historyLoading.value = true
  try {
    const params = {
      page: historyPagination.current_page,
      per_page: historyPagination.per_page,
      ...historyFilters
    }
    
    // 处理日期范围
    if (historyFilters.dateRange && historyFilters.dateRange.length === 2) {
      params.start_date = historyFilters.dateRange[0]
      params.end_date = historyFilters.dateRange[1]
    }
    
    const response = await schoolImportApi.getHistory(params)
    const data = response.data.data
    
    importHistory.value = data.data
    historyPagination.total = data.total
    historyPagination.current_page = data.current_page
    historyPagination.per_page = data.per_page
  } catch (error) {
    console.error('加载导入历史失败:', error)
    ElMessage.error('加载导入历史失败')
  } finally {
    historyLoading.value = false
  }
}

const searchHistory = () => {
  historyPagination.current_page = 1
  loadImportHistory()
}

const resetHistoryFilters = () => {
  historyFilters.filename = ''
  historyFilters.status = ''
  historyFilters.dateRange = null
  searchHistory()
}

const viewImportDetail = async (importLog) => {
  try {
    const response = await schoolImportApi.getDetail(importLog.id)
    selectedImportLog.value = response.data.data
    detailDialogVisible.value = true
  } catch (error) {
    console.error('获取导入详情失败:', error)
    ElMessage.error('获取导入详情失败')
  }
}

const handleHistoryDialogClose = () => {
  historyDialogVisible.value = false
}

const handleDetailDialogClose = () => {
  detailDialogVisible.value = false
  selectedImportLog.value = null
}

const generateAuditReport = async (importLog) => {
  auditLoading.value = true
  try {
    const response = await schoolImportApi.generateAuditReport(importLog.id)
    auditReport.value = response.data.data
    auditDialogVisible.value = true
    ElMessage.success('审计报告生成成功')
  } catch (error) {
    console.error('生成审计报告失败:', error)
    ElMessage.error('生成审计报告失败')
  } finally {
    auditLoading.value = false
  }
}

const handleAuditDialogClose = () => {
  auditDialogVisible.value = false
  auditReport.value = null
}

const exportAuditReport = () => {
  if (!auditReport.value) return

  // 生成审计报告的文本内容
  const reportContent = generateAuditReportText(auditReport.value)

  // 创建下载链接
  const blob = new Blob([reportContent], { type: 'text/plain;charset=utf-8' })
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `导入审计报告_${auditReport.value.import_info.id}_${new Date().toISOString().split('T')[0]}.txt`
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  window.URL.revokeObjectURL(url)

  ElMessage.success('审计报告导出成功')
}

const generateAuditReportText = (report) => {
  let content = '学校信息导入审计报告\n'
  content += '=' * 50 + '\n\n'

  content += '导入基本信息:\n'
  content += `导入ID: ${report.import_info.id}\n`
  content += `文件名: ${report.import_info.filename}\n`
  content += `导入用户: ${report.import_info.user}\n`
  content += `父级组织: ${report.import_info.parent_organization}\n`
  content += `开始时间: ${formatDateTime(report.import_info.started_at)}\n`
  content += `完成时间: ${formatDateTime(report.import_info.completed_at)}\n`
  content += `处理时长: ${report.import_info.duration}秒\n`
  content += `状态: ${report.import_info.status}\n\n`

  content += '统计信息:\n'
  content += `总行数: ${report.statistics.total_rows}\n`
  content += `成功行数: ${report.statistics.success_rows}\n`
  content += `失败行数: ${report.statistics.failed_rows}\n`
  content += `成功率: ${report.statistics.success_rate}%\n\n`

  if (report.imported_schools.length > 0) {
    content += `导入的学校 (${report.imported_schools.length}所):\n`
    report.imported_schools.forEach((school, index) => {
      content += `${index + 1}. ${school.name} (${school.code})\n`
    })
    content += '\n'
  }

  if (report.errors.length > 0) {
    content += `错误详情 (${report.errors.length}项):\n`
    report.errors.forEach((error, index) => {
      content += `${index + 1}. 行${error.row}: ${error.errors.join(', ')}\n`
    })
  }

  return content
}

const rollbackImport = async (importLog) => {
  try {
    await ElMessageBox.confirm(
      `确定要回滚导入"${importLog.filename}"吗？这将删除该次导入的所有学校数据，此操作不可恢复。`,
      '确认回滚',
      {
        confirmButtonText: '确定回滚',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )

    const response = await schoolImportApi.rollbackImport(importLog.id)
    ElMessage.success(response.data.message)

    // 刷新导入历史
    await loadImportHistory()

  } catch (error) {
    if (error !== 'cancel') {
      console.error('回滚失败:', error)
      ElMessage.error(error.response?.data?.message || '回滚失败')
    }
  }
}

const showCompareDialog = () => {
  compareDialogVisible.value = true
}

const handleCompareDialogClose = () => {
  compareDialogVisible.value = false
  compareImport1.value = null
  compareImport2.value = null
  comparisonResult.value = null
}

const performComparison = async () => {
  if (!compareImport1.value || !compareImport2.value) {
    ElMessage.warning('请选择两个导入记录进行比较')
    return
  }

  if (compareImport1.value === compareImport2.value) {
    ElMessage.warning('请选择不同的导入记录进行比较')
    return
  }

  comparing.value = true
  try {
    const response = await schoolImportApi.compareImports(compareImport1.value, compareImport2.value)
    comparisonResult.value = response.data.data
    ElMessage.success('导入比较完成')
  } catch (error) {
    console.error('导入比较失败:', error)
    ElMessage.error('导入比较失败')
  } finally {
    comparing.value = false
  }
}

const getProgressColor = (percentage) => {
  if (percentage >= 80) return '#67c23a'
  if (percentage >= 60) return '#e6a23c'
  return '#f56c6c'
}

const getStatusType = (status) => {
  const statusMap = {
    'pending': 'info',
    'processing': 'warning',
    'success': 'success',
    'partial_success': 'warning',
    'failed': 'danger'
  }
  return statusMap[status] || 'info'
}

const formatDateTime = (dateTime) => {
  if (!dateTime) return '无'
  return new Date(dateTime).toLocaleString('zh-CN')
}

// 生命周期
onMounted(() => {
  // 页面加载时默认显示导入页面
})
</script>

<style scoped>
.school-management {
  padding: 20px;
  min-height: calc(100vh - 120px);
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid #ebeef5;
}

.page-header h2 {
  margin: 0;
  color: #303133;
  font-size: 24px;
  font-weight: 600;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.tab-content {
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
}

.tab-pane {
  min-height: 600px;
}

/* 导入历史对话框样式 */
.import-history-content {
  max-height: 70vh;
  overflow-y: auto;
}

.history-filters {
  background: #f5f7fa;
  padding: 16px;
  border-radius: 6px;
  margin-bottom: 16px;
}

.filter-form {
  margin: 0;
}

.filter-form .el-form-item {
  margin-bottom: 0;
}

.success-rate-text {
  margin-left: 8px;
  font-size: 12px;
  color: #606266;
}

.pagination-wrapper {
  margin-top: 20px;
  display: flex;
  justify-content: center;
}

/* 导入详情样式 */
.import-detail {
  max-height: 70vh;
  overflow-y: auto;
}

.error-section {
  margin-top: 20px;
}

.error-section h4 {
  margin: 0 0 12px 0;
  color: #f56c6c;
  font-size: 14px;
}

.error-list {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.error-item {
  padding: 4px 8px;
  background: #fef0f0;
  border: 1px solid #fbc4c4;
  border-radius: 4px;
  color: #f56c6c;
  font-size: 12px;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .school-management {
    padding: 10px;
  }

  .page-header {
    flex-direction: column;
    gap: 16px;
    align-items: flex-start;
  }

  .header-actions {
    width: 100%;
    justify-content: center;
  }

  .history-filters {
    padding: 12px;
  }

  .filter-form {
    display: block;
  }

  .filter-form .el-form-item {
    margin-bottom: 12px;
    display: block;
  }

  .filter-form .el-form-item:last-child {
    margin-bottom: 0;
  }
}

/* 表格样式调整 */
:deep(.el-table) {
  font-size: 13px;
}

:deep(.el-table .cell) {
  padding: 8px 12px;
}

/* 进度条样式 */
:deep(.el-progress-bar__outer) {
  border-radius: 4px;
  height: 6px;
}

:deep(.el-progress-bar__inner) {
  border-radius: 4px;
}

/* 按钮组样式 */
:deep(.el-button-group .el-button) {
  border-radius: 0;
}

:deep(.el-button-group .el-button:first-child) {
  border-top-left-radius: 4px;
  border-bottom-left-radius: 4px;
}

:deep(.el-button-group .el-button:last-child) {
  border-top-right-radius: 4px;
  border-bottom-right-radius: 4px;
}

/* 对话框样式 */
:deep(.el-dialog) {
  border-radius: 8px;
}

:deep(.el-dialog__header) {
  padding: 20px 20px 10px;
  border-bottom: 1px solid #ebeef5;
}

:deep(.el-dialog__body) {
  padding: 20px;
}

/* 描述列表样式 */
:deep(.el-descriptions) {
  margin-bottom: 20px;
}

:deep(.el-descriptions__label) {
  font-weight: 600;
  color: #606266;
}

:deep(.el-descriptions__content) {
  color: #303133;
}

/* 审计报告样式 */
.audit-report {
  max-height: 70vh;
  overflow-y: auto;
}

.audit-section {
  margin-bottom: 20px;
}

.stat-item {
  text-align: center;
  padding: 16px;
  border: 1px solid #ebeef5;
  border-radius: 6px;
  background: #fafafa;
}

.stat-number {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 8px;
  color: #303133;
}

.stat-number.success {
  color: #67c23a;
}

.stat-number.error {
  color: #f56c6c;
}

.stat-label {
  font-size: 14px;
  color: #606266;
}

.error-list {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.error-item {
  padding: 4px 8px;
  background: #fef0f0;
  border: 1px solid #fbc4c4;
  border-radius: 4px;
  color: #f56c6c;
  font-size: 12px;
}

/* 导入比较样式 */
.compare-content {
  max-height: 70vh;
  overflow-y: auto;
}

.compare-form {
  margin-bottom: 20px;
  padding: 16px;
  background: #f5f7fa;
  border-radius: 6px;
}

.import-info {
  padding: 16px;
  border: 1px solid #ebeef5;
  border-radius: 6px;
  background: #fafafa;
}

.import-info h4 {
  margin: 0 0 12px 0;
  color: #303133;
  font-size: 16px;
  font-weight: 600;
}

.import-info p {
  margin: 4px 0;
  color: #606266;
  font-size: 14px;
}

.comparison-stats {
  margin-top: 20px;
}

.comparison-stats .stat-item {
  background: #fff;
  border: 2px solid #e4e7ed;
}

.comparison-stats .stat-item:hover {
  border-color: #409eff;
  box-shadow: 0 2px 12px rgba(64, 158, 255, 0.1);
}
</style>
