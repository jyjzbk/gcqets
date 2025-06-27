<template>
  <div class="school-import">
    <div class="import-header">
      <h3>学校信息批量导入</h3>
      <el-text type="info" size="small">
        支持Excel和CSV格式文件，适用于省、市、区县、学区管理员批量导入学校信息
      </el-text>
    </div>

    <el-card class="import-card">
      <template #header>
        <div class="card-header">
          <span>导入设置</span>
          <div class="header-actions">
            <el-button 
              type="primary" 
              :icon="Download" 
              @click="downloadTemplate"
              :loading="templateLoading"
            >
              下载模板
            </el-button>
          </div>
        </div>
      </template>

      <el-form 
        ref="importFormRef" 
        :model="importForm" 
        :rules="importRules" 
        label-width="120px"
        class="import-form"
      >
        <el-form-item label="父级组织" prop="parent_id" required>
          <el-tree-select
            v-model="importForm.parent_id"
            :data="organizationTree"
            :props="treeProps"
            placeholder="请选择学校所属的父级组织"
            check-strictly
            :render-after-expand="false"
            style="width: 100%"
            filterable
          />
          <div class="form-tip">
            选择学校将要归属的学区或区县组织
          </div>
        </el-form-item>

        <el-form-item label="导入文件" prop="file" required>
          <el-upload
            ref="uploadRef"
            class="upload-demo"
            drag
            :auto-upload="false"
            :on-change="handleFileChange"
            :before-remove="handleFileRemove"
            accept=".xlsx,.xls,.csv"
            :limit="1"
          >
            <el-icon class="el-icon--upload"><upload-filled /></el-icon>
            <div class="el-upload__text">
              将文件拖到此处，或<em>点击上传</em>
            </div>
            <template #tip>
              <div class="el-upload__tip">
                支持 Excel (.xlsx, .xls) 和 CSV (.csv) 格式，文件大小不超过20MB
              </div>
            </template>
          </el-upload>
        </el-form-item>

        <el-form-item label="导入选项">
          <el-checkbox v-model="importForm.overwrite">
            覆盖已存在的学校信息
          </el-checkbox>
          <el-checkbox v-model="importForm.validate_only">
            仅验证数据（不实际导入）
          </el-checkbox>
        </el-form-item>

        <el-form-item>
          <el-button
            type="info"
            @click="handlePreview"
            :loading="previewing"
            :disabled="!importForm.file"
          >
            预览数据
          </el-button>
          <el-button
            type="primary"
            @click="handleImport"
            :loading="importing"
            :disabled="!importForm.file"
          >
            {{ importForm.validate_only ? '验证数据' : '开始导入' }}
          </el-button>
          <el-button @click="resetForm">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- 导入进度 -->
    <el-card v-if="showProgress" class="progress-card">
      <template #header>
        <span>导入进度</span>
      </template>
      
      <div class="progress-content">
        <el-progress 
          :percentage="progressPercentage" 
          :status="progressStatus"
          :stroke-width="8"
        />
        <div class="progress-info">
          <p>{{ progressText }}</p>
          <div v-if="importResult" class="result-summary">
            <el-tag type="success">成功: {{ importResult.success }}</el-tag>
            <el-tag type="danger">失败: {{ importResult.failed }}</el-tag>
            <el-tag type="warning">总计: {{ importResult.total }}</el-tag>
          </div>
        </div>
      </div>
    </el-card>

    <!-- 导入结果 -->
    <el-card v-if="importResult && importResult.errors.length > 0" class="result-card">
      <template #header>
        <span>导入错误详情</span>
      </template>
      
      <el-table 
        :data="importResult.errors" 
        style="width: 100%"
        max-height="400"
      >
        <el-table-column prop="row" label="行号" width="80" />
        <el-table-column prop="data.name" label="学校名称" width="150" />
        <el-table-column prop="data.code" label="学校代码" width="120" />
        <el-table-column prop="errors" label="错误信息">
          <template #default="{ row }">
            <div class="error-list">
              <el-tag 
                v-for="(error, index) in row.errors" 
                :key="index"
                type="danger" 
                size="small"
                class="error-tag"
              >
                {{ error }}
              </el-tag>
            </div>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 导入警告 -->
    <el-card v-if="importResult && importResult.warnings.length > 0" class="result-card">
      <template #header>
        <span>导入警告</span>
      </template>
      
      <el-table 
        :data="importResult.warnings" 
        style="width: 100%"
        max-height="300"
      >
        <el-table-column prop="row" label="行号" width="80" />
        <el-table-column prop="data.name" label="学校名称" width="150" />
        <el-table-column prop="data.code" label="学校代码" width="120" />
        <el-table-column prop="message" label="警告信息" />
      </el-table>
    </el-card>

    <!-- 导入历史 -->
    <el-card class="history-card">
      <template #header>
        <div class="card-header">
          <span>导入历史</span>
          <el-button 
            type="primary" 
            size="small" 
            @click="loadImportHistory"
            :loading="historyLoading"
          >
            刷新
          </el-button>
        </div>
      </template>

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
        <el-table-column prop="status_text" label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">
              {{ row.status_text }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="导入时间" width="180">
          <template #default="{ row }">
            {{ formatDateTime(row.created_at) }}
          </template>
        </el-table-column>
        <el-table-column label="操作" width="120">
          <template #default="{ row }">
            <el-button 
              type="primary" 
              size="small" 
              @click="viewImportDetail(row)"
            >
              详情
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
    </el-card>

    <!-- 数据预览对话框 -->
    <el-dialog
      v-model="previewDialogVisible"
      title="数据预览"
      width="90%"
      :before-close="handlePreviewDialogClose"
    >
      <div v-if="previewData" class="preview-content">
        <!-- 文件信息 -->
        <el-card class="preview-info-card">
          <template #header>
            <span>文件信息</span>
          </template>
          <el-descriptions :column="3" border>
            <el-descriptions-item label="文件名">
              {{ previewData.file_info?.name }}
            </el-descriptions-item>
            <el-descriptions-item label="文件大小">
              {{ formatFileSize(previewData.file_info?.size) }}
            </el-descriptions-item>
            <el-descriptions-item label="文件类型">
              {{ previewData.file_info?.type?.toUpperCase() }}
            </el-descriptions-item>
            <el-descriptions-item label="总行数">
              {{ previewData.total_rows }}
            </el-descriptions-item>
            <el-descriptions-item label="预览行数">
              {{ previewData.preview_rows }}
            </el-descriptions-item>
            <el-descriptions-item label="列数">
              {{ previewData.headers?.length }}
            </el-descriptions-item>
          </el-descriptions>
        </el-card>

        <!-- 验证摘要 -->
        <el-card class="preview-summary-card">
          <template #header>
            <span>验证摘要</span>
          </template>
          <div class="validation-summary">
            <el-tag type="success" size="large">
              有效: {{ previewData.validation_summary?.valid }}
            </el-tag>
            <el-tag type="danger" size="large">
              无效: {{ previewData.validation_summary?.invalid }}
            </el-tag>
            <el-tag type="warning" size="large">
              警告: {{ previewData.validation_summary?.warnings }}
            </el-tag>
          </div>

          <!-- 数据质量指标 -->
          <div v-if="previewData.data_quality" class="quality-metrics">
            <h4>数据质量指标</h4>
            <el-row :gutter="16">
              <el-col :span="8" v-for="(metric, key) in previewData.data_quality" :key="key">
                <div class="quality-item">
                  <div class="quality-label">{{ metric.description }}</div>
                  <el-progress
                    :percentage="metric.percentage"
                    :stroke-width="8"
                    :color="getQualityColor(metric.percentage)"
                  />
                </div>
              </el-col>
            </el-row>
          </div>

          <!-- 建议 -->
          <div v-if="previewData.recommendations && previewData.recommendations.length > 0" class="recommendations">
            <h4>优化建议</h4>
            <div class="recommendation-list">
              <el-alert
                v-for="(rec, index) in previewData.recommendations"
                :key="index"
                :title="rec.title"
                :description="rec.message"
                :type="getRecommendationType(rec.priority)"
                :closable="false"
                show-icon
                class="recommendation-item"
              >
                <template #default>
                  <div class="recommendation-content">
                    <p>{{ rec.message }}</p>
                    <p class="recommendation-action">
                      <strong>建议操作：</strong>{{ rec.action }}
                    </p>
                  </div>
                </template>
              </el-alert>
            </div>
          </div>
        </el-card>

        <!-- 数据预览表格 -->
        <el-card class="preview-table-card">
          <template #header>
            <span>数据预览</span>
          </template>
          <el-table
            :data="previewData.data"
            style="width: 100%"
            max-height="400"
            :row-class-name="getPreviewRowClass"
          >
            <el-table-column prop="row_number" label="行号" width="80" />
            <el-table-column prop="original_data.学校名称" label="学校名称" width="150" />
            <el-table-column prop="original_data.学校代码" label="学校代码" width="120" />
            <el-table-column prop="original_data.学校类型" label="学校类型" width="100" />
            <el-table-column prop="original_data.教育层次" label="教育层次" width="120" />
            <el-table-column prop="original_data.校长姓名" label="校长姓名" width="100" />
            <el-table-column prop="original_data.联系电话" label="联系电话" width="120" />
            <el-table-column label="状态" width="100">
              <template #default="{ row }">
                <el-tag v-if="row.is_valid" type="success" size="small">有效</el-tag>
                <el-tag v-else type="danger" size="small">无效</el-tag>
              </template>
            </el-table-column>
            <el-table-column label="问题" min-width="200">
              <template #default="{ row }">
                <div v-if="row.errors.length > 0" class="error-list">
                  <el-tag
                    v-for="(error, index) in row.errors"
                    :key="index"
                    type="danger"
                    size="small"
                    class="error-tag"
                  >
                    {{ error }}
                  </el-tag>
                </div>
                <div v-if="row.warnings.length > 0" class="warning-list">
                  <el-tag
                    v-for="(warning, index) in row.warnings"
                    :key="index"
                    type="warning"
                    size="small"
                    class="warning-tag"
                  >
                    {{ warning }}
                  </el-tag>
                </div>
                <span v-if="row.errors.length === 0 && row.warnings.length === 0" class="no-issues">
                  无问题
                </span>
              </template>
            </el-table-column>
          </el-table>
        </el-card>

        <!-- 错误汇总 -->
        <el-card v-if="previewData.errors.length > 0" class="preview-errors-card">
          <template #header>
            <span>错误汇总</span>
          </template>
          <el-table :data="previewData.errors" style="width: 100%" max-height="200">
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
      </div>

      <template #footer>
        <div class="dialog-footer">
          <el-button @click="handlePreviewDialogClose">关闭</el-button>
          <el-button
            v-if="previewData && previewData.validation_summary.valid > 0"
            type="primary"
            @click="proceedWithImport"
          >
            确认导入
          </el-button>
        </div>
      </template>
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
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Download, UploadFilled } from '@element-plus/icons-vue'
import { organizationApi } from '../../api/organization'
import { schoolImportApi } from '../../api/schoolImport'

// 响应式数据
const importFormRef = ref()
const uploadRef = ref()

const importForm = reactive({
  parent_id: null,
  file: null,
  overwrite: false,
  validate_only: false
})

const importRules = {
  parent_id: [
    { required: true, message: '请选择父级组织', trigger: 'change' }
  ],
  file: [
    { required: true, message: '请选择导入文件', trigger: 'change' }
  ]
}

// 组织树数据
const organizationTree = ref([])
const treeProps = {
  children: 'children',
  label: 'name',
  value: 'id'
}

// 导入状态
const importing = ref(false)
const templateLoading = ref(false)
const showProgress = ref(false)
const progressPercentage = ref(0)
const progressText = ref('')
const importResult = ref(null)

// 预览状态
const previewing = ref(false)
const previewDialogVisible = ref(false)
const previewData = ref(null)

// 导入历史
const importHistory = ref([])
const historyLoading = ref(false)
const historyPagination = reactive({
  current_page: 1,
  per_page: 10,
  total: 0
})

// 详情对话框
const detailDialogVisible = ref(false)
const selectedImportLog = ref(null)

// 计算属性
const progressStatus = computed(() => {
  if (importing.value) return 'active'
  if (importResult.value) {
    return importResult.value.failed > 0 ? 'warning' : 'success'
  }
  return ''
})

// 方法
const loadOrganizationTree = async () => {
  try {
    const response = await organizationApi.getTree()
    organizationTree.value = response.data.data
  } catch (error) {
    console.error('加载组织树失败:', error)
    ElMessage.error('加载组织树失败')
  }
}

const downloadTemplate = async () => {
  templateLoading.value = true
  try {
    const response = await schoolImportApi.downloadTemplate('excel')

    // 创建下载链接
    const blob = new Blob([response.data], {
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `学校信息导入模板_${new Date().toISOString().split('T')[0]}.xlsx`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)

    ElMessage.success('模板下载成功')
  } catch (error) {
    console.error('下载模板失败:', error)
    ElMessage.error('下载模板失败')
  } finally {
    templateLoading.value = false
  }
}

const handleFileChange = (file) => {
  importForm.file = file.raw
}

const handleFileRemove = () => {
  importForm.file = null
  previewData.value = null
  return true
}

const handlePreview = async () => {
  if (!importFormRef.value) return

  try {
    await importFormRef.value.validate()
  } catch (error) {
    return
  }

  previewing.value = true

  try {
    const formData = new FormData()
    formData.append('file', importForm.file)
    formData.append('parent_id', importForm.parent_id)
    formData.append('limit', '20') // 预览20行数据

    // 同时调用预览和分析接口
    const [previewResponse, analysisResponse] = await Promise.all([
      schoolImportApi.previewImport(formData),
      schoolImportApi.analyzeFile(formData)
    ])

    // 合并预览数据和分析结果
    previewData.value = {
      ...previewResponse.data.data,
      ...analysisResponse.data.data
    }

    previewDialogVisible.value = true

    ElMessage.success('数据预览和分析完成')

  } catch (error) {
    console.error('预览失败:', error)

    // 详细的错误处理
    if (error.response) {
      const { status, data } = error.response

      switch (status) {
        case 400:
          ElMessage.error('文件格式错误：' + (data.message || '请检查文件是否为有效的Excel或CSV文件'))
          break
        case 413:
          ElMessage.error('文件过大，无法预览（建议小于5MB）')
          break
        case 422:
          ElMessage.error('文件内容错误：' + (data.message || '请检查文件格式和表头'))
          break
        default:
          ElMessage.error(data.message || '数据预览失败')
      }
    } else {
      ElMessage.error('网络错误，预览失败')
    }
  } finally {
    previewing.value = false
  }
}

const handleImport = async () => {
  if (!importFormRef.value) return

  try {
    await importFormRef.value.validate()
  } catch (error) {
    return
  }

  const confirmText = importForm.validate_only
    ? '确定要验证选中的文件吗？'
    : '确定要导入选中的文件吗？'

  try {
    await ElMessageBox.confirm(confirmText, '确认操作', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
  } catch {
    return
  }

  importing.value = true
  showProgress.value = true
  progressPercentage.value = 0
  progressText.value = '准备导入...'
  importResult.value = null

  try {
    const formData = new FormData()
    formData.append('file', importForm.file)
    formData.append('parent_id', importForm.parent_id)
    formData.append('overwrite', importForm.overwrite ? '1' : '0')
    formData.append('validate_only', importForm.validate_only ? '1' : '0')

    progressPercentage.value = 30
    progressText.value = '上传文件中...'

    const response = await schoolImportApi.import(formData)

    progressPercentage.value = 100
    progressText.value = importForm.validate_only ? '验证完成' : '导入完成'

    importResult.value = response.data.data

    ElMessage.success(response.data.message)

    // 刷新导入历史
    await loadImportHistory()

    // 重置表单
    resetForm()

  } catch (error) {
    console.error('导入失败:', error)
    progressPercentage.value = 100
    progressText.value = '导入失败'

    // 详细的错误处理
    if (error.response) {
      const { status, data } = error.response

      switch (status) {
        case 400:
          ElMessage.error(data.message || '请求参数错误，请检查文件格式')
          break
        case 401:
          ElMessage.error('登录已过期，请重新登录')
          // 可以在这里触发重新登录逻辑
          break
        case 403:
          ElMessage.error('权限不足，无法执行导入操作')
          break
        case 413:
          ElMessage.error('文件过大，请选择较小的文件（建议小于10MB）')
          break
        case 422:
          // 显示详细的验证错误
          if (data.errors && typeof data.errors === 'object') {
            const errorMessages = Object.values(data.errors).flat()
            ElMessage.error('数据验证失败：' + errorMessages.join('；'))
          } else {
            ElMessage.error('数据验证失败：' + (data.message || '请检查数据格式'))
          }
          break
        case 500:
          ElMessage.error('服务器内部错误，请稍后重试或联系管理员')
          break
        case 503:
          ElMessage.error('服务暂时不可用，请稍后重试')
          break
        case 504:
          ElMessage.error('请求超时，文件可能过大或网络较慢，请稍后重试')
          break
        default:
          ElMessage.error(data.message || '导入失败，请稍后重试')
      }
    } else if (error.code === 'ECONNABORTED') {
      ElMessage.error('请求超时，请检查网络连接或稍后重试')
    } else if (error.code === 'NETWORK_ERROR') {
      ElMessage.error('网络错误，请检查网络连接')
    } else {
      ElMessage.error('未知错误，请稍后重试')
    }

    // 如果有错误详情，可以显示在结果中
    if (error.response?.data?.data) {
      importResult.value = error.response.data.data
    }
  } finally {
    importing.value = false

    // 延迟隐藏进度条
    setTimeout(() => {
      showProgress.value = false
    }, 2000)
  }
}

const resetForm = () => {
  importForm.parent_id = null
  importForm.file = null
  importForm.overwrite = false
  importForm.validate_only = false

  if (uploadRef.value) {
    uploadRef.value.clearFiles()
  }

  showProgress.value = false
  progressPercentage.value = 0
  importResult.value = null
}

const loadImportHistory = async () => {
  historyLoading.value = true
  try {
    const params = {
      page: historyPagination.current_page,
      per_page: historyPagination.per_page
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

const handleDetailDialogClose = () => {
  detailDialogVisible.value = false
  selectedImportLog.value = null
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

const handlePreviewDialogClose = () => {
  previewDialogVisible.value = false
  // 不清空预览数据，用户可能需要再次查看
}

const proceedWithImport = async () => {
  previewDialogVisible.value = false
  await handleImport()
}

const getPreviewRowClass = ({ row }) => {
  if (!row.is_valid) {
    return 'preview-row-error'
  }
  if (row.warnings && row.warnings.length > 0) {
    return 'preview-row-warning'
  }
  return 'preview-row-success'
}

const formatFileSize = (bytes) => {
  if (!bytes) return '0 bytes'

  if (bytes >= 1073741824) {
    return (bytes / 1073741824).toFixed(2) + ' GB'
  } else if (bytes >= 1048576) {
    return (bytes / 1048576).toFixed(2) + ' MB'
  } else if (bytes >= 1024) {
    return (bytes / 1024).toFixed(2) + ' KB'
  } else {
    return bytes + ' bytes'
  }
}

const getQualityColor = (percentage) => {
  if (percentage >= 90) return '#67c23a'
  if (percentage >= 70) return '#e6a23c'
  if (percentage >= 50) return '#f56c6c'
  return '#909399'
}

const getRecommendationType = (priority) => {
  const typeMap = {
    'high': 'error',
    'medium': 'warning',
    'low': 'info',
    'info': 'success'
  }
  return typeMap[priority] || 'info'
}

// 生命周期
onMounted(() => {
  loadOrganizationTree()
  loadImportHistory()
})
</script>

<style scoped>
.school-import {
  padding: 20px;
}

.import-header {
  margin-bottom: 20px;
}

.import-header h3 {
  margin: 0 0 8px 0;
  color: #303133;
  font-size: 18px;
  font-weight: 600;
}

.import-card,
.progress-card,
.result-card,
.history-card {
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-actions {
  display: flex;
  gap: 10px;
}

.import-form {
  max-width: 600px;
}

.form-tip {
  font-size: 12px;
  color: #909399;
  margin-top: 4px;
}

.upload-demo {
  width: 100%;
}

.progress-content {
  text-align: center;
}

.progress-info {
  margin-top: 16px;
}

.result-summary {
  margin-top: 12px;
  display: flex;
  justify-content: center;
  gap: 12px;
}

.error-list {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.error-tag {
  margin-bottom: 4px;
}

.error-item {
  padding: 4px 8px;
  background: #fef0f0;
  border: 1px solid #fbc4c4;
  border-radius: 4px;
  color: #f56c6c;
  font-size: 12px;
  margin-bottom: 4px;
}

.pagination-wrapper {
  margin-top: 20px;
  display: flex;
  justify-content: center;
}

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

/* 响应式设计 */
@media (max-width: 768px) {
  .school-import {
    padding: 10px;
  }

  .import-form {
    max-width: 100%;
  }

  .card-header {
    flex-direction: column;
    gap: 10px;
    align-items: flex-start;
  }

  .header-actions {
    width: 100%;
    justify-content: flex-end;
  }

  .result-summary {
    flex-direction: column;
    align-items: center;
  }
}

/* 上传组件样式调整 */
:deep(.el-upload-dragger) {
  width: 100%;
  height: 120px;
}

:deep(.el-upload__tip) {
  margin-top: 8px;
  font-size: 12px;
  color: #909399;
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
  border-radius: 6px;
}

:deep(.el-progress-bar__inner) {
  border-radius: 6px;
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

/* 预览对话框样式 */
.preview-content {
  max-height: 80vh;
  overflow-y: auto;
}

.preview-info-card,
.preview-summary-card,
.preview-table-card,
.preview-errors-card {
  margin-bottom: 16px;
}

.validation-summary {
  display: flex;
  gap: 16px;
  justify-content: center;
  align-items: center;
}

.validation-summary .el-tag {
  padding: 8px 16px;
  font-size: 14px;
  font-weight: 600;
}

/* 预览表格行样式 */
:deep(.preview-row-success) {
  background-color: #f0f9ff;
}

:deep(.preview-row-warning) {
  background-color: #fffbf0;
}

:deep(.preview-row-error) {
  background-color: #fef0f0;
}

.error-tag,
.warning-tag {
  margin: 2px;
  display: inline-block;
}

.no-issues {
  color: #67c23a;
  font-size: 12px;
}

.dialog-footer {
  text-align: right;
}

/* 预览卡片标题样式 */
:deep(.preview-info-card .el-card__header),
:deep(.preview-summary-card .el-card__header),
:deep(.preview-table-card .el-card__header),
:deep(.preview-errors-card .el-card__header) {
  background-color: #f5f7fa;
  border-bottom: 1px solid #ebeef5;
}

:deep(.preview-info-card .el-card__header span),
:deep(.preview-summary-card .el-card__header span),
:deep(.preview-table-card .el-card__header span),
:deep(.preview-errors-card .el-card__header span) {
  font-weight: 600;
  color: #303133;
}

/* 数据质量指标样式 */
.quality-metrics {
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid #ebeef5;
}

.quality-metrics h4 {
  margin: 0 0 16px 0;
  color: #303133;
  font-size: 14px;
  font-weight: 600;
}

.quality-item {
  text-align: center;
  margin-bottom: 16px;
}

.quality-label {
  font-size: 12px;
  color: #606266;
  margin-bottom: 8px;
}

/* 建议样式 */
.recommendations {
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid #ebeef5;
}

.recommendations h4 {
  margin: 0 0 16px 0;
  color: #303133;
  font-size: 14px;
  font-weight: 600;
}

.recommendation-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.recommendation-item {
  border-radius: 6px;
}

.recommendation-content p {
  margin: 0 0 8px 0;
  line-height: 1.5;
}

.recommendation-content p:last-child {
  margin-bottom: 0;
}

.recommendation-action {
  font-size: 12px;
  color: #606266;
}

/* 质量进度条样式 */
:deep(.quality-item .el-progress-bar__outer) {
  border-radius: 4px;
  height: 8px;
}

:deep(.quality-item .el-progress-bar__inner) {
  border-radius: 4px;
}
</style>
