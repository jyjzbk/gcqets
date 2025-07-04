<template>
  <el-dialog
    v-model="dialogVisible"
    :title="dialogTitle"
    width="700px"
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <div class="review-dialog">
      <!-- 记录基本信息 -->
      <el-card class="record-info-card">
        <template #header>
          <span>记录信息</span>
        </template>
        <div v-if="recordData" class="record-info">
          <el-descriptions :column="2" size="small">
            <el-descriptions-item label="实验计划">{{ recordData.experiment_plan?.name }}</el-descriptions-item>
            <el-descriptions-item label="执行日期">{{ recordData.execution_date }}</el-descriptions-item>
            <el-descriptions-item label="完成状态">
              <el-tag :type="getCompletionStatusType(recordData.completion_status)">
                {{ getCompletionStatusLabel(recordData.completion_status) }}
              </el-tag>
            </el-descriptions-item>
            <el-descriptions-item label="完成度">
              <el-progress
                :percentage="recordData.completion_percentage || 0"
                :color="getCompletionColor(recordData.completion_percentage || 0)"
                :stroke-width="6"
                text-inside
                style="width: 150px"
              />
            </el-descriptions-item>
            <el-descriptions-item label="照片数量">{{ recordData.photo_count || 0 }}</el-descriptions-item>
            <el-descriptions-item label="器材确认">
              <el-tag :type="recordData.equipment_confirmed ? 'success' : 'warning'" size="small">
                {{ recordData.equipment_confirmed ? '已确认' : '未确认' }}
              </el-tag>
            </el-descriptions-item>
          </el-descriptions>
        </div>
      </el-card>

      <!-- 审核建议 -->
      <div v-if="reviewSuggestions.length > 0" class="suggestions-section">
        <h4>审核建议</h4>
        <div class="suggestions-list">
          <el-alert
            v-for="(suggestion, index) in reviewSuggestions"
            :key="index"
            :title="suggestion.message"
            :type="suggestion.type"
            show-icon
            :closable="false"
            class="suggestion-item"
          />
        </div>
      </div>

      <!-- 审核表单 -->
      <el-form
        ref="formRef"
        :model="formData"
        :rules="formRules"
        label-width="100px"
        @submit.prevent
      >
        <el-form-item
          v-if="reviewAction !== 'force_complete'"
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
          v-if="reviewAction === 'approve'"
          label="审核评分"
          prop="review_score"
        >
          <el-rate
            v-model="formData.review_score"
            :max="10"
            show-score
            score-template="{value}分"
            style="margin-right: 20px"
          />
          <span class="score-grade" :style="{ color: getReviewScoreColor(formData.review_score) }">
            {{ getReviewGrade(formData.review_score).label }}
          </span>
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

        <el-form-item
          v-if="reviewAction === 'revision'"
          label="修改要求"
        >
          <div class="revision-requirements">
            <el-checkbox-group v-model="formData.revision_requirements">
              <el-checkbox label="补充执行说明">补充执行说明</el-checkbox>
              <el-checkbox label="上传更多照片">上传更多照片</el-checkbox>
              <el-checkbox label="完善教学反思">完善教学反思</el-checkbox>
              <el-checkbox label="确认器材准备">确认器材准备</el-checkbox>
              <el-checkbox label="补充实验数据">补充实验数据</el-checkbox>
              <el-checkbox label="修正安全记录">修正安全记录</el-checkbox>
            </el-checkbox-group>
          </div>
        </el-form-item>

        <el-form-item
          v-if="reviewAction === 'revision'"
          label="修改截止"
        >
          <el-date-picker
            v-model="formData.revision_deadline"
            type="datetime"
            placeholder="选择修改截止时间"
            format="YYYY-MM-DD HH:mm"
            value-format="YYYY-MM-DD HH:mm:ss"
            style="width: 100%"
          />
        </el-form-item>

        <el-form-item
          v-if="reviewAction === 'force_complete'"
          label="强制原因"
          prop="force_reason"
        >
          <el-input
            v-model="formData.force_reason"
            type="textarea"
            :rows="3"
            placeholder="请说明强制完成的原因"
            maxlength="500"
            show-word-limit
          />
        </el-form-item>
      </el-form>

      <!-- 操作确认 -->
      <el-alert
        :title="getConfirmMessage()"
        :type="getAlertType()"
        show-icon
        :closable="false"
        class="confirm-alert"
      />
    </div>

    <template #footer>
      <div class="dialog-footer">
        <el-button @click="handleClose">取消</el-button>
        <el-button
          :type="getButtonType()"
          :loading="submitting"
          @click="handleSubmit"
        >
          {{ getButtonText() }}
        </el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, reactive, computed, watch, nextTick } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { 
  experimentReviewApi,
  reviewCategoryOptions,
  getReviewGrade,
  getReviewScoreColor,
  validateReviewData,
  generateReviewSuggestions,
  getReviewConfirmMessage
} from '@/api/experimentReview'
import { 
  getCompletionStatusType, 
  getCompletionStatusLabel,
  getCompletionColor
} from '@/api/experimentRecord'

// Props
const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  recordData: {
    type: Object,
    default: null
  },
  reviewAction: {
    type: String,
    default: 'approve' // approve | reject | revision | force_complete
  }
})

// Emits
const emit = defineEmits(['update:visible', 'success'])

// 响应式数据
const formRef = ref()
const submitting = ref(false)
const startTime = ref(null)

// 表单数据
const formData = reactive({
  review_notes: '',
  review_category: '',
  review_score: null,
  revision_requirements: [],
  revision_deadline: null,
  force_reason: ''
})

// 表单验证规则
const formRules = computed(() => {
  const rules = {
    review_notes: [
      { required: true, message: '请填写审核意见', trigger: 'blur' },
      { min: 5, max: 1000, message: '长度在 5 到 1000 个字符', trigger: 'blur' }
    ]
  }

  if (props.reviewAction === 'reject' || props.reviewAction === 'revision') {
    rules.review_category = [
      { required: true, message: '请选择问题分类', trigger: 'change' }
    ]
  }

  if (props.reviewAction === 'force_complete') {
    rules.force_reason = [
      { required: true, message: '请填写强制完成原因', trigger: 'blur' },
      { min: 10, max: 500, message: '长度在 10 到 500 个字符', trigger: 'blur' }
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
  const titles = {
    approve: '审核通过',
    reject: '审核拒绝',
    revision: '要求修改',
    force_complete: '强制完成'
  }
  return titles[props.reviewAction] || '审核记录'
})

const reviewSuggestions = computed(() => {
  return props.recordData ? generateReviewSuggestions(props.recordData) : []
})

// 监听器
watch(() => props.visible, (newVal) => {
  if (newVal) {
    resetForm()
    startTime.value = Date.now()
  }
})

watch(() => props.reviewAction, () => {
  resetForm()
})

// 方法
const resetForm = () => {
  Object.assign(formData, {
    review_notes: '',
    review_category: '',
    review_score: null,
    revision_requirements: [],
    revision_deadline: null,
    force_reason: ''
  })
  
  nextTick(() => {
    formRef.value?.clearValidate()
  })
}

const getNotesPlaceholder = () => {
  const placeholders = {
    approve: '请填写审核通过的意见（可选）',
    reject: '请详细说明拒绝的原因',
    revision: '请说明需要修改的具体内容',
    force_complete: '请说明强制完成的理由'
  }
  return placeholders[props.reviewAction] || '请填写审核意见'
}

const getConfirmMessage = () => {
  return getReviewConfirmMessage(props.reviewAction)
}

const getAlertType = () => {
  const types = {
    approve: 'success',
    reject: 'error',
    revision: 'warning',
    force_complete: 'warning'
  }
  return types[props.reviewAction] || 'info'
}

const getButtonType = () => {
  const types = {
    approve: 'success',
    reject: 'danger',
    revision: 'warning',
    force_complete: 'primary'
  }
  return types[props.reviewAction] || 'primary'
}

const getButtonText = () => {
  const texts = {
    approve: '确认通过',
    reject: '确认拒绝',
    revision: '要求修改',
    force_complete: '强制完成'
  }
  return texts[props.reviewAction] || '确认'
}

const handleSubmit = async () => {
  try {
    await formRef.value.validate()
    
    // 验证审核数据
    const validation = validateReviewData(props.reviewAction, formData)
    if (!validation.valid) {
      ElMessage.error(validation.errors[0])
      return
    }
    
    await ElMessageBox.confirm(getConfirmMessage(), '确认操作', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: getAlertType()
    })
    
    submitting.value = true
    
    const submitData = { ...formData }
    
    // 计算审核耗时
    if (startTime.value) {
      submitData.review_duration = Math.round((Date.now() - startTime.value) / 1000 / 60)
    }
    
    let response
    switch (props.reviewAction) {
      case 'approve':
        response = await experimentReviewApi.approve(props.recordData.id, submitData)
        break
      case 'reject':
        response = await experimentReviewApi.reject(props.recordData.id, submitData)
        break
      case 'revision':
        response = await experimentReviewApi.requestRevision(props.recordData.id, submitData)
        break
      case 'force_complete':
        response = await experimentReviewApi.forceComplete(props.recordData.id, submitData)
        break
    }
    
    if (response.data.success) {
      ElMessage.success(response.data.message)
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
    ElMessage.error('操作失败：' + error.message)
  } finally {
    submitting.value = false
  }
}

const handleClose = () => {
  emit('update:visible', false)
}
</script>

<style scoped>
.review-dialog {
  padding: 10px 0;
}

.record-info-card {
  margin-bottom: 20px;
}

.record-info {
  padding: 10px 0;
}

.suggestions-section {
  margin-bottom: 20px;
}

.suggestions-section h4 {
  margin: 0 0 10px 0;
  color: #303133;
  font-size: 14px;
}

.suggestions-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.suggestion-item {
  margin-bottom: 0;
}

.category-option {
  display: flex;
  align-items: center;
}

.category-label {
  margin-left: 8px;
}

.score-grade {
  font-weight: 500;
  margin-left: 10px;
}

.revision-requirements {
  padding: 10px;
  background-color: #fafafa;
  border-radius: 4px;
  border: 1px solid #ebeef5;
}

.confirm-alert {
  margin-top: 20px;
}

.dialog-footer {
  text-align: right;
}
</style>
