<template>
  <el-dialog
    v-model="dialogVisible"
    :title="dialogTitle"
    width="800px"
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <div class="batch-review-dialog">
      <!-- 选中记录列表 -->
      <el-card class="selected-records-card">
        <template #header>
          <span>选中的记录 ({{ selectedRecords.length }}条)</span>
        </template>
        <div class="records-list">
          <div
            v-for="record in selectedRecords"
            :key="record.id"
            class="record-item"
          >
            <div class="record-info">
              <div class="record-name">{{ record.experiment_plan?.name }}</div>
              <div class="record-meta">
                <span class="record-date">{{ record.execution_date }}</span>
                <el-tag :type="getCompletionStatusType(record.completion_status)" size="small">
                  {{ getCompletionStatusLabel(record.completion_status) }}
                </el-tag>
                <span class="record-completion">完成度: {{ record.completion_percentage || 0 }}%</span>
              </div>
            </div>
            <div class="record-actions">
              <el-button size="small" @click="viewRecord(record)">查看</el-button>
            </div>
          </div>
        </div>
      </el-card>

      <!-- 批量操作表单 -->
      <el-form
        ref="formRef"
        :model="formData"
        :rules="formRules"
        label-width="100px"
        @submit.prevent
      >
        <el-form-item label="操作类型">
          <el-radio-group v-model="formData.action" @change="handleActionChange">
            <el-radio label="approve">
              <el-icon color="#67c23a"><Check /></el-icon>
              批量通过
            </el-radio>
            <el-radio label="reject">
              <el-icon color="#f56c6c"><Close /></el-icon>
              批量拒绝
            </el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item
          v-if="formData.action === 'reject'"
          label="问题分类"
          prop="review_category"
        >
          <el-select v-model="formData.review_category" placeholder="选择问题分类" style="width: 100%">
            <el-option
              v-for="option in reviewCategoryOptions"
              :key="option.value"
              :label="option.label"
              :value="option.value"
            >
              <div class="category-option">
                <el-icon :color="option.color">
                  <component :is="option.icon" />
                </el-icon>
                <span class="category-label">{{ option.label }}</span>
              </div>
            </el-option>
          </el-select>
        </el-form-item>

        <el-form-item
          label="审核意见"
          prop="review_notes"
        >
          <el-input
            v-model="formData.review_notes"
            type="textarea"
            :rows="4"
            :placeholder="getNotesPlaceholder()"
            maxlength="1000"
            show-word-limit
          />
        </el-form-item>

        <!-- 批量操作选项 -->
        <el-form-item label="操作选项">
          <el-checkbox-group v-model="formData.options">
            <el-checkbox label="send_notification">发送通知给相关人员</el-checkbox>
            <el-checkbox label="auto_archive">自动归档已处理记录</el-checkbox>
            <el-checkbox v-if="formData.action === 'approve'" label="generate_report">生成批量审核报告</el-checkbox>
          </el-checkbox-group>
        </el-form-item>
      </el-form>

      <!-- 操作预览 -->
      <el-card class="preview-card">
        <template #header>
          <span>操作预览</span>
        </template>
        <div class="operation-preview">
          <el-alert
            :title="getPreviewMessage()"
            :type="formData.action === 'approve' ? 'success' : 'warning'"
            show-icon
            :closable="false"
          />
          
          <div class="preview-stats">
            <div class="stat-item">
              <span class="stat-label">操作记录数：</span>
              <span class="stat-value">{{ selectedRecords.length }}</span>
            </div>
            <div class="stat-item">
              <span class="stat-label">平均完成度：</span>
              <span class="stat-value">{{ averageCompletion }}%</span>
            </div>
            <div class="stat-item">
              <span class="stat-label">预计耗时：</span>
              <span class="stat-value">{{ estimatedTime }}分钟</span>
            </div>
          </div>
        </div>
      </el-card>

      <!-- 风险提示 -->
      <div v-if="riskWarnings.length > 0" class="risk-warnings">
        <h4>风险提示</h4>
        <el-alert
          v-for="(warning, index) in riskWarnings"
          :key="index"
          :title="warning.message"
          :type="warning.type"
          show-icon
          :closable="false"
          class="warning-item"
        />
      </div>
    </div>

    <template #footer>
      <div class="dialog-footer">
        <el-button @click="handleClose">取消</el-button>
        <el-button
          :type="formData.action === 'approve' ? 'success' : 'danger'"
          :loading="submitting"
          @click="handleSubmit"
        >
          {{ formData.action === 'approve' ? '批量通过' : '批量拒绝' }}
        </el-button>
      </div>
    </template>

    <!-- 记录详情对话框 -->
    <RecordDetail
      v-model:visible="detailVisible"
      :record-id="currentRecordId"
    />
  </el-dialog>
</template>

<script setup>
import { ref, reactive, computed, watch, nextTick } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Check, Close } from '@element-plus/icons-vue'
import { 
  experimentReviewApi,
  reviewCategoryOptions,
  validateReviewData,
  getReviewConfirmMessage
} from '@/api/experimentReview'
import { 
  getCompletionStatusType, 
  getCompletionStatusLabel
} from '@/api/experimentRecord'
import RecordDetail from '../../record/components/RecordDetail.vue'

// Props
const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  selectedRecords: {
    type: Array,
    default: () => []
  },
  batchAction: {
    type: String,
    default: 'approve' // approve | reject
  }
})

// Emits
const emit = defineEmits(['update:visible', 'success'])

// 响应式数据
const formRef = ref()
const submitting = ref(false)
const detailVisible = ref(false)
const currentRecordId = ref(null)

// 表单数据
const formData = reactive({
  action: 'approve',
  review_notes: '',
  review_category: '',
  options: ['send_notification']
})

// 表单验证规则
const formRules = computed(() => {
  const rules = {
    review_notes: [
      { required: true, message: '请填写审核意见', trigger: 'blur' },
      { min: 5, max: 1000, message: '长度在 5 到 1000 个字符', trigger: 'blur' }
    ]
  }

  if (formData.action === 'reject') {
    rules.review_category = [
      { required: true, message: '请选择问题分类', trigger: 'change' }
    ]
  }

  return rules
})

// 计算属性
const dialogVisible = computed({
  get: () => props.visible,
  set: (value) => emit('update:visible', value)
})

const dialogTitle = computed(() => {
  return `批量审核 - ${props.selectedRecords.length} 条记录`
})

const averageCompletion = computed(() => {
  if (props.selectedRecords.length === 0) return 0
  const total = props.selectedRecords.reduce((sum, record) => sum + (record.completion_percentage || 0), 0)
  return Math.round(total / props.selectedRecords.length)
})

const estimatedTime = computed(() => {
  // 估算每条记录需要2-3分钟审核时间
  return Math.round(props.selectedRecords.length * 2.5)
})

const riskWarnings = computed(() => {
  const warnings = []
  
  // 检查低完成度记录
  const lowCompletionRecords = props.selectedRecords.filter(r => (r.completion_percentage || 0) < 60)
  if (lowCompletionRecords.length > 0 && formData.action === 'approve') {
    warnings.push({
      type: 'warning',
      message: `有 ${lowCompletionRecords.length} 条记录完成度低于60%，建议仔细审核`
    })
  }
  
  // 检查缺少照片的记录
  const noPhotoRecords = props.selectedRecords.filter(r => (r.photo_count || 0) === 0)
  if (noPhotoRecords.length > 0) {
    warnings.push({
      type: 'error',
      message: `有 ${noPhotoRecords.length} 条记录没有上传照片，建议要求补充`
    })
  }
  
  // 检查未确认器材的记录
  const unconfirmedRecords = props.selectedRecords.filter(r => !r.equipment_confirmed)
  if (unconfirmedRecords.length > 0) {
    warnings.push({
      type: 'info',
      message: `有 ${unconfirmedRecords.length} 条记录未确认器材准备`
    })
  }
  
  return warnings
})

// 监听器
watch(() => props.visible, (newVal) => {
  if (newVal) {
    resetForm()
    formData.action = props.batchAction
  }
})

watch(() => props.batchAction, (newVal) => {
  formData.action = newVal
})

// 方法
const resetForm = () => {
  Object.assign(formData, {
    action: 'approve',
    review_notes: '',
    review_category: '',
    options: ['send_notification']
  })
  
  nextTick(() => {
    formRef.value?.clearValidate()
  })
}

const handleActionChange = () => {
  formData.review_category = ''
  formData.review_notes = ''
}

const getNotesPlaceholder = () => {
  if (formData.action === 'approve') {
    return '请填写批量通过的审核意见'
  } else {
    return '请详细说明批量拒绝的原因'
  }
}

const getPreviewMessage = () => {
  if (formData.action === 'approve') {
    return `将批量通过 ${props.selectedRecords.length} 条实验记录，这些记录将被标记为已审核通过状态。`
  } else {
    return `将批量拒绝 ${props.selectedRecords.length} 条实验记录，这些记录将被退回给创建者修改。`
  }
}

const viewRecord = (record) => {
  currentRecordId.value = record.id
  detailVisible.value = true
}

const handleSubmit = async () => {
  try {
    await formRef.value.validate()
    
    // 验证审核数据
    const validation = validateReviewData('batch', {
      ...formData,
      record_ids: props.selectedRecords.map(r => r.id)
    })
    if (!validation.valid) {
      ElMessage.error(validation.errors[0])
      return
    }
    
    const confirmMessage = getReviewConfirmMessage(formData.action, props.selectedRecords.length)
    await ElMessageBox.confirm(confirmMessage, '确认批量操作', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: formData.action === 'approve' ? 'success' : 'warning'
    })
    
    submitting.value = true
    
    const submitData = {
      record_ids: props.selectedRecords.map(r => r.id),
      action: formData.action,
      review_notes: formData.review_notes,
      review_category: formData.review_category
    }
    
    const response = await experimentReviewApi.batchReview(submitData)
    
    if (response.data.success) {
      const result = response.data.data
      if (result.failed_count > 0) {
        ElMessage.warning(`批量操作完成，成功处理 ${result.success_count} 条，失败 ${result.failed_count} 条`)
      } else {
        ElMessage.success(`批量操作成功，共处理 ${result.success_count} 条记录`)
      }
      emit('success')
    }
  } catch (error) {
    if (error === 'cancel') {
      return
    }
    if (error.errors) {
      // 表单验证错误
      return
    }
    ElMessage.error('批量操作失败：' + error.message)
  } finally {
    submitting.value = false
  }
}

const handleClose = () => {
  emit('update:visible', false)
}
</script>

<style scoped>
.batch-review-dialog {
  padding: 10px 0;
}

.selected-records-card {
  margin-bottom: 20px;
}

.records-list {
  max-height: 200px;
  overflow-y: auto;
}

.record-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
  border-bottom: 1px solid #f0f0f0;
}

.record-item:last-child {
  border-bottom: none;
}

.record-info {
  flex: 1;
}

.record-name {
  font-weight: 500;
  color: #303133;
  margin-bottom: 4px;
}

.record-meta {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 12px;
  color: #909399;
}

.record-date {
  color: #606266;
}

.record-completion {
  color: #909399;
}

.category-option {
  display: flex;
  align-items: center;
}

.category-label {
  margin-left: 8px;
}

.preview-card {
  margin-bottom: 20px;
}

.operation-preview {
  padding: 10px 0;
}

.preview-stats {
  margin-top: 15px;
  display: flex;
  gap: 20px;
}

.stat-item {
  display: flex;
  align-items: center;
}

.stat-label {
  color: #909399;
  margin-right: 4px;
}

.stat-value {
  color: #303133;
  font-weight: 500;
}

.risk-warnings {
  margin-bottom: 20px;
}

.risk-warnings h4 {
  margin: 0 0 10px 0;
  color: #f56c6c;
  font-size: 14px;
}

.warning-item {
  margin-bottom: 8px;
}

.warning-item:last-child {
  margin-bottom: 0;
}

.dialog-footer {
  text-align: right;
}
</style>
